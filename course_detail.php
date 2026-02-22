<?php
include 'koneksi.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}

$course_id = $_GET['id'];
$teacher_id = $_SESSION['user_id'];

// 1. Ambil detail kelas
$course_query = mysqli_query($conn, "SELECT * FROM courses WHERE id = '$course_id' AND teacher_id = '$teacher_id'");
$course = mysqli_fetch_assoc($course_query);

if (!$course) { die("Kelas tidak ditemukan."); }

// 2. Ambil daftar materi dan hitung jumlah tugas masuk
// Menambahkan m.type ke dalam SELECT
$query_materials = "SELECT m.*, 
                    (SELECT COUNT(*) FROM submissions WHERE material_id = m.id) as total_submissions
                    FROM materials m 
                    WHERE m.course_id = '$course_id' 
                    ORDER BY m.id DESC";
$materials_result = mysqli_query($conn, $query_materials);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Class - <?php echo $course['title']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #F8FAFC; }
    </style>
</head>
<body class="min-h-screen p-6 md:p-12">

    <div class="max-w-5xl mx-auto">
        <div class="mb-10">
            <a href="dashboard_teacher.php" class="text-indigo-600 font-bold flex items-center gap-2 mb-6 hover:underline transition">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-8 rounded-[40px] border border-slate-100 shadow-sm">
                <div>
                    <h1 class="text-3xl font-black text-slate-800 tracking-tight"><?php echo $course['title']; ?></h1>
                    <p class="text-slate-400 font-medium mt-1">Kode Kelas: <span class="text-indigo-600 font-bold uppercase tracking-widest bg-indigo-50 px-3 py-1 rounded-xl"><?php echo $course['class_code']; ?></span></p>
                </div>
                <a href="add_material.php?course_id=<?php echo $course_id; ?>" class="bg-slate-900 hover:bg-indigo-600 text-white px-8 py-4 rounded-2xl font-bold transition shadow-xl shadow-slate-100 flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i> Tambah Konten
                </a>
            </div>
        </div>

        <h2 class="text-xl font-extrabold text-slate-800 mb-6 px-4 flex items-center gap-3">
            <span class="w-2 h-6 bg-indigo-600 rounded-full"></span> Kurikulum Kelas
        </h2>

        <div class="grid grid-cols-1 gap-6">
            <?php if(mysqli_num_rows($materials_result) > 0): ?>
                <?php while($mat = mysqli_fetch_assoc($materials_result)): 
                    // Tentukan warna dan ikon berdasarkan tipe
                    $is_assignment = ($mat['type'] === 'assignment');
                    $theme_color = $is_assignment ? 'orange' : 'indigo';
                    $icon = $is_assignment ? 'fa-tasks' : 'fa-book-open';
                ?>
                    <div class="bg-white p-6 md:p-8 rounded-[35px] border border-slate-100 shadow-sm hover:shadow-xl hover:shadow-<?php echo $theme_color; ?>-100/50 transition-all group">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                            
                            <div class="flex items-start gap-6">
                                <div class="w-14 h-14 bg-<?php echo $theme_color; ?>-50 text-<?php echo $theme_color; ?>-600 rounded-2xl flex items-center justify-center text-xl shadow-sm group-hover:bg-<?php echo $theme_color; ?>-600 group-hover:text-white transition-colors">
                                    <i class="fa-solid <?php echo $icon; ?>"></i>
                                </div>
                                
                                <div>
                                    <h3 class="text-xl font-extrabold text-slate-800 group-hover:text-<?php echo $theme_color; ?>-600 transition-colors">
                                        <?php echo $mat['title']; ?>
                                    </h3>
                                    <p class="text-slate-400 text-sm mt-1 font-medium truncate max-w-xs md:max-w-md">
                                        <?php echo strip_tags($mat['content']); ?>
                                    </p>
                                    
                                    <div class="flex items-center gap-3 mt-4">
                                        <span class="bg-slate-100 text-slate-500 px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest flex items-center gap-1">
                                            <?php echo $is_assignment ? 'Assignment' : 'Material'; ?>
                                        </span>

                                        <?php if($is_assignment): ?>
                                            <span class="bg-orange-50 text-orange-600 px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest flex items-center gap-1">
                                                <i class="fa-solid fa-user-graduate"></i> <?php echo $mat['total_submissions']; ?> Tugas Masuk
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if(!empty($mat['file_path'])): ?>
                                            <span class="bg-blue-50 text-blue-600 px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest">
                                                <i class="fa-solid fa-paperclip"></i> Attachment
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                           <div class="flex items-center gap-2 border-t md:border-t-0 pt-4 md:pt-0">
                            <a href="view_material.php?id=<?php echo $mat['id']; ?>" 
                            class="w-10 h-10 flex items-center justify-center bg-slate-50 text-slate-400 hover:bg-indigo-50 hover:text-indigo-600 rounded-xl transition-all" title="Lihat Tampilan">
                                <i class="fa-solid fa-eye"></i>
                            </a>

                            <a href="edit_material.php?id=<?php echo $mat['id']; ?>&course_id=<?php echo $course_id; ?>" 
                            class="w-10 h-10 flex items-center justify-center bg-slate-50 text-slate-400 hover:bg-amber-50 hover:text-amber-600 rounded-xl transition-all" title="Edit Konten">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>

                            <?php if($is_assignment): ?>
                                <a href="view_submissions.php?material_id=<?php echo $mat['id']; ?>" 
                                class="bg-orange-50 text-orange-600 hover:bg-orange-600 hover:text-white px-4 py-2 rounded-xl font-bold text-xs transition-all shadow-sm">
                                    Cek Tugas (<?php echo $mat['total_submissions']; ?>)
                                </a>
                            <?php endif; ?>

                            <a href="delete_material.php?id=<?php echo $mat['id']; ?>&course_id=<?php echo $course_id; ?>" 
                            onclick="return confirm('Hapus konten ini?')"
                            class="w-10 h-10 flex items-center justify-center bg-slate-50 text-slate-400 hover:bg-red-50 hover:text-red-500 rounded-xl transition-all">
                                <i class="fa-solid fa-trash-can"></i>
                            </a>
                        </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="py-20 text-center bg-white rounded-[40px] border-2 border-dashed border-slate-200">
                    <i class="fa-solid fa-folder-open text-5xl text-slate-200 mb-4"></i>
                    <p class="text-slate-400 font-bold uppercase tracking-widest text-sm">Belum ada konten di kelas ini</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>