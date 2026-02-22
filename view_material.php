<?php
include 'koneksi.php';
session_start();

// 1. Proteksi: Cek login secara umum
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$material_id = $_GET['id'];
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role']; // Ambil role dari session

// 2. Ambil Data Materi & Course
$query = "SELECT materials.*, courses.title as course_title 
          FROM materials 
          JOIN courses ON materials.course_id = courses.id 
          WHERE materials.id = '$material_id'";
$result = mysqli_query($conn, $query);
$mat = mysqli_fetch_assoc($result);

if (!$mat) {
    die("Materi tidak ditemukan.");
}

// 3. LOGIKA BACK URL DINAMIS
// Jika yang buka adalah guru, arahkan ke course_detail.php
if ($user_role === 'teacher') {
    $back_url = "course_detail.php?id=" . $mat['course_id'];
} else {
    // Jika siswa, arahkan ke course_view.php (atau assignments jika ada parameter 'from')
    $back_url = "course_view.php?id=" . $mat['course_id']; 
    if (isset($_GET['from']) && $_GET['from'] == 'assignments') {
        $back_url = "assignments.php"; 
    }
}

// 4. Cek Status Pengumpulan (Hanya untuk siswa dan tipe assignment)
$already_submitted = null;
if ($user_role === 'student' && $mat['type'] === 'assignment') {
    $check_sub = mysqli_query($conn, "SELECT * FROM submissions WHERE material_id = '$material_id' AND student_id = '$user_id'");
    $already_submitted = mysqli_fetch_assoc($check_sub);
}

// 5. Logika Upload Tugas (Hanya diproses jika role adalah student)
if (isset($_POST['upload_tugas']) && $user_role === 'student' && $mat['type'] === 'assignment') {
    if ($_FILES['file_siswa']['name']) {
        $target_dir = "uploads/submissions/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_name = "SUB_" . time() . "_" . basename($_FILES["file_siswa"]["name"]);
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES["file_siswa"]["tmp_name"], $target_file)) {
            $insert = "INSERT INTO submissions (material_id, student_id, file_submission) 
                       VALUES ('$material_id', '$user_id', '$file_name')";
            mysqli_query($conn, $insert);
            
            header("Location: view_material.php?id=$material_id&status=success" . (isset($_GET['from']) ? "&from=".$_GET['from'] : ""));
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $mat['title']; ?> - Learning</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-50 min-h-screen font-sans">

    <div class="max-w-5xl mx-auto p-6 md:p-12">
        <div class="mb-10">
            <a href="<?php echo $back_url; ?>" class="text-indigo-600 font-bold flex items-center gap-2 hover:underline mb-4">
                <i class="fa-solid fa-arrow-left"></i> 
                Kembali ke <?php echo ($user_role === 'teacher') ? 'Manajemen Kelas' : 'Kurikulum'; ?>
            </a>
            
            <div class="flex items-center gap-3 mb-2">
                <span class="<?php echo $mat['type'] === 'assignment' ? 'bg-orange-100 text-orange-600' : 'bg-indigo-100 text-indigo-600'; ?> px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">
                    <?php echo $mat['type'] === 'assignment' ? 'Assignment' : 'Reading Material'; ?>
                </span>
                
                <?php if($user_role === 'teacher'): ?>
                    <span class="bg-slate-800 text-white px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">
                        Preview Mode
                    </span>
                <?php endif; ?>
            </div>
            
            <h1 class="text-4xl font-black text-slate-800 leading-tight"><?php echo $mat['title']; ?></h1>
            <p class="text-slate-400 font-medium mt-1 uppercase tracking-widest text-sm"><?php echo $mat['course_title']; ?></p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            <div class="lg:col-span-2 space-y-8">
                <div class="bg-white p-10 rounded-[40px] border border-slate-200 shadow-sm">
                    <h3 class="text-xl font-bold text-slate-700 mb-6 border-b border-slate-100 pb-4">
                        <?php echo $mat['type'] === 'assignment' ? 'Instruksi Tugas' : 'Isi Materi'; ?>
                    </h3>
                    <div class="prose max-w-none text-slate-600 leading-relaxed text-lg">
                        <?php echo nl2br($mat['content']); ?>
                    </div>

                    <?php if(!empty($mat['file_path'])): ?>
                    <div class="mt-10 p-6 bg-slate-50 rounded-3xl border border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-white text-indigo-600 rounded-2xl flex items-center justify-center shadow-sm">
                                <i class="fa-solid fa-file-arrow-down text-xl"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-800 italic">File Lampiran Pengajar</p>
                            </div>
                        </div>
                        <a href="uploads/<?php echo $mat['file_path']; ?>" download class="bg-indigo-600 text-white px-6 py-3 rounded-2xl font-bold hover:bg-indigo-700 transition">
                            Download
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="space-y-6">
                <?php if($mat['type'] === 'assignment'): ?>
                    <div class="bg-white p-8 rounded-[40px] border border-slate-200 shadow-sm">
                        <h3 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                            <i class="fa-solid fa-paper-plane text-orange-500"></i> Pengumpulan
                        </h3>

                        <?php if($user_role === 'teacher'): ?>
                            <div class="p-6 bg-slate-50 rounded-3xl border border-dashed border-slate-200 text-center">
                                <i class="fa-solid fa-eye text-3xl text-slate-300 mb-3"></i>
                                <p class="text-xs font-bold text-slate-500">Tombol upload dinonaktifkan dalam mode Preview.</p>
                            </div>
                        <?php elseif($already_submitted): ?>
                            <div class="text-center py-6">
                                <div class="w-20 h-20 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fa-solid fa-check-double text-3xl"></i>
                                </div>
                                <p class="font-bold text-slate-800">Selesai!</p>
                                <div class="mt-6 p-4 bg-slate-50 rounded-2xl border border-slate-100 truncate text-[10px] text-slate-500 font-mono">
                                    <?php echo $already_submitted['file_submission']; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
                                <div class="border-2 border-dashed border-slate-200 p-8 rounded-3xl text-center hover:border-indigo-400 transition group relative">
                                    <input type="file" name="file_siswa" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                    <i class="fa-solid fa-cloud-arrow-up text-4xl text-slate-300 group-hover:text-indigo-500 mb-3 transition"></i>
                                    <p class="text-sm font-bold text-slate-500">Klik atau Drag File</p>
                                </div>
                                <button type="submit" name="upload_tugas" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-4 rounded-2xl shadow-xl transition transform active:scale-95 uppercase tracking-widest text-sm">
                                    Kirim Sekarang
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="bg-indigo-900 p-8 rounded-[40px] text-white shadow-xl shadow-indigo-100">
                        <div class="w-12 h-12 bg-indigo-800 rounded-2xl flex items-center justify-center mb-6">
                            <i class="fa-solid fa-circle-info text-indigo-400 text-xl"></i>
                        </div>
                        <h3 class="text-lg font-bold mb-3">Materi Bacaan</h3>
                        <p class="text-sm leading-relaxed text-indigo-200">
                            Bagian ini adalah konten materi. Tidak ada tugas yang perlu dikumpulkan.
                        </p>
                    </div>
                <?php endif; ?>

                <div class="bg-white p-8 rounded-[40px] border border-slate-200 shadow-sm">
                    <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4 italic text-center">--- Lecture Notes ---</p>
                    <p class="text-sm leading-relaxed text-slate-500 text-center">
                        <?php echo ($user_role === 'teacher') ? 'Halaman ini menampilkan apa yang akan dilihat oleh siswa Anda.' : 'Butuh bantuan? Hubungi pengajar melalui grup diskusi kelas.'; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

</body>
</html>