<?php
include 'koneksi.php';
session_start();

// 1. Proteksi Guru
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}

$material_id = $_GET['material_id'];

// 2. Ambil Detail Materi & Validasi Tipe
$mat_query = mysqli_query($conn, "SELECT materials.*, courses.title as course_title 
                                  FROM materials 
                                  JOIN courses ON materials.course_id = courses.id 
                                  WHERE materials.id = '$material_id'");
$mat = mysqli_fetch_assoc($mat_query);

// Validasi: Jika ini bukan tugas, lempar kembali ke detail kelas
if (!$mat || $mat['type'] !== 'assignment') {
    header("Location: course_detail.php?id=" . ($mat['course_id'] ?? ''));
    exit;
}

// 3. Logika Simpan Nilai
if (isset($_POST['update_grade'])) {
    $sub_id = $_POST['submission_id'];
    $grade = mysqli_real_escape_string($conn, $_POST['grade']);
    
    // Pastikan nilai berada di range 0-100 (opsional)
    $update = mysqli_query($conn, "UPDATE submissions SET grade = '$grade' WHERE id = '$sub_id'");
    if ($update) {
        $status_msg = "Nilai berhasil diperbarui!";
    }
}

// 4. Ambil Daftar Siswa yang sudah mengumpulkan tugas
$query_subs = "SELECT submissions.*, users.username 
               FROM submissions 
               JOIN users ON submissions.student_id = users.id 
               WHERE submissions.material_id = '$material_id'
               ORDER BY submissions.submitted_at DESC";
$result_subs = mysqli_query($conn, $query_subs);
$total_subs = mysqli_num_rows($result_subs);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Submissions - <?php echo $mat['title']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #F8FAFC; }
    </style>
</head>
<body class="p-6 md:p-12">

    <div class="max-w-6xl mx-auto">
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <a href="course_detail.php?id=<?php echo $mat['course_id']; ?>" class="text-indigo-600 font-bold inline-flex items-center gap-2 mb-6 hover:underline group">
                    <i class="fa-solid fa-arrow-left group-hover:-translate-x-1 transition-transform"></i> Kembali ke Manajemen Kelas
                </a>
                <div class="flex items-center gap-3 mb-2">
                    <span class="bg-orange-100 text-orange-600 px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest">Assignment</span>
                </div>
                <h1 class="text-4xl font-black text-slate-800 tracking-tight"><?php echo $mat['title']; ?></h1>
                <p class="text-slate-400 font-medium mt-1">
                    <i class="fa-solid fa-graduation-cap mr-1"></i> <?php echo $mat['course_title']; ?> 
                    <span class="mx-2 text-slate-200">|</span> 
                    Daftar Pengumpulan Siswa
                </p>
            </div>

            <div class="flex gap-4">
                <div class="bg-white px-6 py-4 rounded-[25px] border border-slate-100 shadow-sm text-center min-w-[120px]">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Masuk</p>
                    <p class="text-3xl font-black text-indigo-600"><?php echo $total_subs; ?></p>
                </div>
            </div>
        </div>

        <?php if(isset($status_msg)): ?>
            <div class="mb-8 p-5 bg-emerald-500 text-white rounded-[25px] shadow-lg shadow-emerald-100 flex items-center gap-4 animate-bounce">
                <i class="fa-solid fa-circle-check text-xl"></i>
                <span class="font-bold"><?php echo $status_msg; ?></span>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-[40px] border border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center w-20">No</th>
                            <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Nama Siswa</th>
                            <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Waktu Kumpul</th>
                            <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Hasil Karya</th>
                            <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Input Nilai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if($total_subs > 0): ?>
                            <?php $no = 1; while($sub = mysqli_fetch_assoc($result_subs)): 
                                $is_graded = ($sub['grade'] !== NULL && $sub['grade'] !== '');
                            ?>
                            <tr class="hover:bg-slate-50/80 transition-all group">
                                <td class="p-6 text-center font-bold text-slate-300 group-hover:text-indigo-400 transition-colors">
                                    <?php echo $no++; ?>
                                </td>
                                <td class="p-6">
                                    <div class="flex items-center gap-4">
                                        <div class="relative">
                                            <img src="https://ui-avatars.com/api/?name=<?php echo $sub['username']; ?>&background=4f46e5&color=fff" class="w-11 h-11 rounded-2xl shadow-sm">
                                            <?php if($is_graded): ?>
                                                <div class="absolute -top-1 -right-1 w-4 h-4 bg-emerald-500 border-2 border-white rounded-full"></div>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <span class="font-extrabold text-slate-800 block"><?php echo $sub['username']; ?></span>
                                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter italic">Student</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-6">
                                    <div class="text-sm font-bold text-slate-600">
                                        <?php echo date('d M Y', strtotime($sub['submitted_at'])); ?>
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-medium">
                                        Jam <?php echo date('H:i', strtotime($sub['submitted_at'])); ?> WIB
                                    </div>
                                </td>
                                <td class="p-6">
                                    <a href="uploads/submissions/<?php echo $sub['file_submission']; ?>" target="_blank" 
                                       class="inline-flex items-center gap-3 bg-slate-50 text-slate-700 hover:bg-indigo-600 hover:text-white px-5 py-2.5 rounded-2xl font-bold text-xs transition-all border border-slate-100 shadow-sm">
                                        <i class="fa-solid fa-file-pdf"></i> Lihat Tugas
                                    </a>
                                </td>
                                <td class="p-6">
                                    <form action="" method="POST" class="flex items-center justify-center gap-3">
                                        <input type="hidden" name="submission_id" value="<?php echo $sub['id']; ?>">
                                        <div class="relative">
                                            <input type="number" name="grade" 
                                                   value="<?php echo $sub['grade']; ?>" 
                                                   max="100" min="0"
                                                   placeholder="--" 
                                                   class="w-20 bg-slate-50 border border-slate-200 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 px-3 py-2.5 rounded-xl text-center font-black text-indigo-600 outline-none transition-all">
                                        </div>
                                        <button type="submit" name="update_grade" 
                                                class="bg-indigo-600 text-white w-10 h-10 rounded-xl flex items-center justify-center hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 active:scale-90"
                                                title="Simpan Nilai">
                                            <i class="fa-solid fa-floppy-disk"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="p-32 text-center">
                                    <div class="flex flex-col items-center gap-4">
                                        <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center">
                                            <i class="fa-solid fa-folder-open text-3xl text-slate-200"></i>
                                        </div>
                                        <p class="text-slate-400 font-bold uppercase tracking-widest text-xs">Belum ada tugas yang dikumpulkan siswa.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>