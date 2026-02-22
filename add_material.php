<?php
include 'koneksi.php';
session_start();

// 1. Proteksi Guru: Hanya guru yang bisa menambah materi
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}

// 2. Ambil ID Course dari URL
if (!isset($_GET['course_id'])) {
    header("Location: my_courses_teacher.php");
    exit;
}
$course_id = $_GET['course_id'];

// 3. Logika Simpan Data
if (isset($_POST['submit'])) {
    $title   = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $type    = mysqli_real_escape_string($conn, $_POST['type']); // 'material' atau 'assignment'
    
    // Logika Upload File
    $file_path = "";
    if (!empty($_FILES['file_materi']['name'])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_name = time() . '_' . basename($_FILES["file_materi"]["name"]);
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES["file_materi"]["tmp_name"], $target_file)) {
            $file_path = $file_name;
        }
    }

    // Query Insert dengan kolom 'type'
    $query = "INSERT INTO materials (course_id, type, title, content, file_path) 
              VALUES ('$course_id', '$type', '$title', '$content', '$file_path')";

    if (mysqli_query($conn, $query)) {
        // Kembali ke detail kelas milik guru (course_detail_teacher.php)
        header("Location: course_detail.php?id=$course_id&status=success");
        exit;
    } else {
        $error = "Gagal mengunggah: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Content - Teacher Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen p-4 md:p-12 flex justify-center items-center">

    <div class="bg-white w-full max-w-4xl rounded-[40px] shadow-2xl overflow-hidden flex flex-col md:flex-row border border-slate-100">
        
        <div class="md:w-1/3 bg-indigo-600 p-10 text-white flex flex-col justify-between relative overflow-hidden">
            <div class="relative z-10">
                <a href="course_detail_teacher.php?id=<?php echo $course_id; ?>" class="text-indigo-200 hover:text-white transition flex items-center gap-2 mb-12 font-bold text-sm">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Kelas
                </a>
                <h2 class="text-4xl font-black leading-tight mb-4">Post New Content.</h2>
                <p class="text-indigo-100 text-sm leading-relaxed opacity-80">
                    Gunakan opsi <b>Materi</b> untuk bacaan teks, atau <b>Tugas</b> jika Anda ingin siswa mengunggah file jawaban.
                </p>
            </div>
            
            <i class="fa-solid fa-file-circle-plus absolute right-[-20px] bottom-[-20px] text-[150px] text-white/10 rotate-12"></i>
        </div>

        <div class="md:w-2/3 p-8 md:p-14">
            <?php if(isset($error)): ?>
                <div class="mb-6 p-4 bg-red-50 text-red-600 rounded-2xl border border-red-100 text-sm font-bold flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data" class="space-y-8">
                
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Pilih Jenis Konten</label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="type" value="material" checked class="peer hidden">
                            <div class="p-4 border-2 border-slate-100 rounded-2xl text-center transition peer-checked:border-indigo-600 peer-checked:bg-indigo-50 peer-checked:text-indigo-600 hover:bg-slate-50">
                                <i class="fa-solid fa-book-open text-xl mb-1"></i>
                                <span class="block text-xs font-bold uppercase tracking-wider">Materi</span>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="type" value="assignment" class="peer hidden">
                            <div class="p-4 border-2 border-slate-100 rounded-2xl text-center transition peer-checked:border-orange-500 peer-checked:bg-orange-50 peer-checked:text-orange-500 hover:bg-slate-50">
                                <i class="fa-solid fa-tasks text-xl mb-1"></i>
                                <span class="block text-xs font-bold uppercase tracking-wider">Tugas</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Judul Konten</label>
                    <input type="text" name="title" required
                        class="w-full px-6 py-4 rounded-2xl bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition font-semibold" 
                        placeholder="Contoh: Dasar-dasar Pemrograman">
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Isi atau Instruksi</label>
                    <textarea name="content" rows="6" required
                        class="w-full px-6 py-4 rounded-2xl bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition leading-relaxed" 
                        placeholder="Tuliskan materi lengkap atau instruksi pengerjaan tugas..."></textarea>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Lampiran Modul (Opsional)</label>
                    <div class="relative group">
                        <input type="file" name="file_materi" 
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                        <div class="w-full px-6 py-5 rounded-2xl bg-slate-50 border-2 border-dashed border-slate-200 group-hover:border-indigo-400 transition flex items-center justify-center gap-3 text-slate-400 group-hover:text-indigo-600">
                            <i class="fa-solid fa-folder-open text-lg"></i>
                            <span class="text-sm font-bold">Pilih File (PDF, DOCX, IMG)</span>
                        </div>
                    </div>
                </div>

                <button type="submit" name="submit" 
                    class="w-full bg-slate-900 hover:bg-indigo-600 text-white font-bold py-5 rounded-2xl shadow-xl transition transform active:scale-95 uppercase tracking-widest text-sm">
                    Simpan & Publikasikan
                </button>
            </form>
        </div>
    </div>

</body>
</html>