<?php
include 'koneksi.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit;
}

$course_id = mysqli_real_escape_string($conn, $_GET['id']);
$student_id = $_SESSION['user_id'];

// 1. Ambil Detail Kelas
$course_query = mysqli_query($conn, "SELECT courses.*, users.username as teacher_name 
    FROM courses 
    JOIN users ON courses.teacher_id = users.id 
    WHERE courses.id = '$course_id'");
$course = mysqli_fetch_assoc($course_query);

if (!$course) { die("Kelas tidak ditemukan."); }

// 2. Ambil Materi & Tugas (JOIN dengan submissions untuk cek status)
$query_materials = "SELECT m.*, s.id as submission_id, s.grade 
                    FROM materials m 
                    LEFT JOIN submissions s ON m.id = s.material_id AND s.student_id = '$student_id'
                    WHERE m.course_id = '$course_id' 
                    ORDER BY m.id ASC";
$result_materials = mysqli_query($conn, $query_materials);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $course['title']; ?> - Learning Path</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #F8FAFC; }
    </style>
</head>
<body class="p-6 md:p-12">
    <div class="max-w-5xl mx-auto">
        <div class="mb-8">
            <a href="dashboard_student.php" class="text-indigo-600 font-bold flex items-center gap-2 hover:translate-x-[-4px] transition-transform">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>

        <div class="bg-white p-8 md:p-12 rounded-[48px] border border-slate-100 shadow-sm relative overflow-hidden mb-12">
            <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-50 rounded-full -mr-32 -mt-32 z-0 opacity-50"></div>
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-4">
                    <span class="bg-indigo-600 text-white px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest shadow-lg shadow-indigo-100">Course Module</span>
                    <span class="bg-slate-100 text-slate-500 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest"><?php echo mysqli_num_rows($result_materials); ?> Items</span>
                </div>
                <h1 class="text-4xl md:text-5xl font-black text-slate-800 mt-2 mb-3 tracking-tight"><?php echo $course['title']; ?></h1>
                <p class="text-slate-400 font-medium flex items-center gap-2 text-lg">
                    <i class="fa-solid fa-chalkboard-user text-indigo-400"></i> Instructor: <span class="text-slate-600 font-bold"><?php echo $course['teacher_name']; ?></span>
                </p>
            </div>
        </div>

        <div class="space-y-6">
            <div class="flex items-center justify-between px-4">
                <h2 class="text-2xl font-extrabold text-slate-800">Kurikulum Kelas</h2>
            </div>
            
            <?php if(mysqli_num_rows($result_materials) > 0): ?>
                <?php $no = 1; while($mat = mysqli_fetch_assoc($result_materials)): 
                    $is_assignment = ($mat['type'] === 'assignment');
                    $is_done = ($mat['submission_id'] !== NULL);
                ?>
                    <div class="bg-white p-6 rounded-[35px] border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-6 hover:shadow-xl hover:shadow-slate-200/50 transition-all duration-300 group">
                        <div class="flex items-center gap-6">
                            <div class="w-14 h-14 shrink-0 rounded-2xl flex items-center justify-center font-black text-xl transition-all
                                <?php echo $is_done ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-100' : 'bg-slate-50 text-slate-300 group-hover:bg-indigo-50 group-hover:text-indigo-400'; ?>">
                                <?php if($is_done): ?>
                                    <i class="fa-solid fa-check"></i>
                                <?php else: ?>
                                    <?php echo str_pad($no++, 2, "0", STR_PAD_LEFT); ?>
                                <?php endif; ?>
                            </div>

                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <?php if($is_assignment): ?>
                                        <span class="text-[9px] font-black bg-orange-100 text-orange-600 px-2 py-0.5 rounded-md uppercase tracking-widest">Assignment</span>
                                    <?php else: ?>
                                        <span class="text-[9px] font-black bg-blue-100 text-blue-600 px-2 py-0.5 rounded-md uppercase tracking-widest">Material</span>
                                    <?php endif; ?>
                                    
                                    <?php if($is_done && $mat['grade'] !== NULL): ?>
                                        <span class="text-[9px] font-black bg-emerald-100 text-emerald-600 px-2 py-0.5 rounded-md uppercase tracking-widest">Graded: <?php echo $mat['grade']; ?></span>
                                    <?php endif; ?>
                                </div>
                                <h4 class="font-extrabold text-slate-800 text-lg group-hover:text-indigo-600 transition-colors"><?php echo $mat['title']; ?></h4>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <a href="view_material.php?id=<?php echo $mat['id']; ?>" 
                               class="px-8 py-3.5 <?php echo $is_done ? 'bg-slate-50 text-slate-600' : 'bg-slate-900 text-white'; ?> rounded-2xl font-bold text-sm hover:bg-indigo-600 hover:text-white transition-all shadow-sm active:scale-95">
                                <?php 
                                    if($is_assignment) {
                                        echo $is_done ? 'Lihat Tugas' : 'Kerjakan Tugas';
                                    } else {
                                        echo $is_done ? 'Baca Lagi' : 'Mulai Belajar';
                                    }
                                ?>
                                <i class="fa-solid fa-chevron-right ml-2 text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="bg-white p-20 rounded-[48px] border border-dashed border-slate-200 text-center">
                    <i class="fa-solid fa-box-open text-5xl text-slate-100 mb-4"></i>
                    <p class="text-slate-400 font-bold uppercase tracking-widest text-xs">Belum ada materi atau tugas di kelas ini.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>