<?php
include 'koneksi.php';
session_start();

// Proteksi Halaman
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
$teacher_id = $_SESSION['user_id'];

// 1. Total Kelas milik Guru
$count_classes = mysqli_query($conn, "SELECT COUNT(*) as total FROM courses WHERE teacher_id = '$teacher_id'");
$total_classes = mysqli_fetch_assoc($count_classes)['total'];

// 2. Total Siswa Unik yang mengikuti semua kelas Guru ini
// Kita gunakan DISTINCT agar siswa yang ikut 2 kelas berbeda tetap dihitung 1 orang (atau hapus DISTINCT jika ingin menghitung total kursi)
$count_students = mysqli_query($conn, "SELECT COUNT(DISTINCT enrollments.student_id) as total 
    FROM enrollments 
    JOIN courses ON enrollments.course_id = courses.id 
    WHERE courses.teacher_id = '$teacher_id'");
$total_students = mysqli_fetch_assoc($count_students)['total'];

// 3. Total Materi yang aktif di semua kelas milik Guru ini
$count_materials = mysqli_query($conn, "SELECT COUNT(*) as total 
    FROM materials 
    JOIN courses ON materials.course_id = courses.id 
    WHERE courses.teacher_id = '$teacher_id'");
$total_materials = mysqli_fetch_assoc($count_materials)['total'];

// Ambil daftar kelas untuk ditampilkan di tabel
$query_classes = "SELECT * FROM courses WHERE teacher_id = '$teacher_id' ORDER BY id DESC";
$result_classes = mysqli_query($conn, $query_classes);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - LMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 flex h-screen overflow-hidden">

    <aside class="w-72 bg-white border-r border-slate-200 flex flex-col p-8">
        <div class="flex items-center gap-3 mb-12">
            <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white font-bold text-xl">L</div>
            <span class="text-xl font-extrabold text-slate-800 tracking-tight">Learning</span>
        </div>

        <nav class="space-y-3 flex-1">
            <a href="dashboard_teacher.php" class="flex items-center gap-4 text-indigo-600 bg-indigo-50 px-4 py-3 rounded-2xl font-bold transition">
                <i class="fa-solid fa-house"></i> Dashboard
            </a>
            <a href="course_teacher.php" class="flex items-center gap-4 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 px-4 py-3 rounded-2xl font-semibold transition">
                <i class="fa-solid fa-book"></i> My Courses
            </a>
            <a href="students.php" class="flex items-center gap-4 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 px-4 py-3 rounded-2xl font-semibold transition">
                <i class="fa-solid fa-users"></i> Students
            </a>
            <a href="analytics.php" class="flex items-center gap-4 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 px-4 py-3 rounded-2xl font-semibold transition">
                <i class="fa-solid fa-chart-line"></i> Analytics
            </a>
        </nav>

        <a href="logout.php" class="flex items-center gap-4 text-red-400 hover:text-red-600 px-4 py-3 rounded-2xl font-semibold transition mt-auto border-t border-slate-100 pt-6">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
    </aside>

    <main class="flex-1 overflow-y-auto p-12">
        
        <header class="flex justify-between items-center mb-10">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-800">Hello, Teacher <?php echo $username; ?>! 👋</h1>
                <p class="text-slate-400 font-medium">Manage your students and materials efficiently.</p>
            </div>
            <div class="flex items-center gap-4">
                <button class="w-12 h-12 bg-white border border-slate-200 rounded-2xl flex items-center justify-center text-slate-500 hover:bg-slate-50 transition shadow-sm">
                    <i class="fa-solid fa-bell"></i>
                </button>
                <img src="https://ui-avatars.com/api/?name=<?php echo $username; ?>&background=4f46e5&color=fff" class="w-12 h-12 rounded-2xl shadow-md border-2 border-white">
            </div>
        </header>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <div class="bg-indigo-600 p-8 rounded-[32px] text-white shadow-xl shadow-indigo-100 flex flex-col justify-between relative overflow-hidden group">
                <i class="fa-solid fa-graduation-cap text-6xl absolute -right-4 -bottom-4 opacity-20 group-hover:scale-110 transition"></i>
                <div class="relative z-10">
                    <h4 class="text-indigo-100 text-sm font-semibold uppercase tracking-wider mb-1">Total Classes</h4>
                    <span class="text-4xl font-extrabold"><?php echo $total_classes; ?></span>
                </div>
            </div>

            <div class="bg-orange-600 p-8 rounded-[32px] btext-white shadow-xl shadow-orange-100 flex flex-col justify-between relative overflow-hidden group">
                <i class="fa-solid fa-users text-6xl absolute -right-4 -bottom-4 opacity-20 group-hover:scale-110 transition"></i>
                <div class="relative z-10">
                    <h4 class="text-orange-100 text-sm font-semibold uppercase tracking-wider mb-1">Total Students</h4>
                    <span class="text-4xl font-extrabold text-white"><?php echo $total_students; ?></span>
                </div>
            </div>

            <div class="bg-white p-8 rounded-[32px] border border-slate-200 shadow-sm flex flex-col justify-between relative overflow-hidden group">
                <i class="fa-solid fa-file-invoice text-6xl absolute -right-4 -bottom-4 text-emerald-500 opacity-10 group-hover:scale-110 transition"></i>
                <div class="relative z-10">
                    <h4 class="text-slate-400 text-sm font-semibold uppercase tracking-wider mb-1">Active Materials</h4>
                    <span class="text-4xl font-extrabold text-slate-800"><?php echo $total_materials; ?></span>
                </div>
            </div>
        </div>
            </div>
        </div>

        <div class="flex flex-col md:flex-row gap-8">
            <div class="flex-1">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Your Courses</h2>
                    <a href="course_teacher.php" class="text-indigo-600 font-bold hover:underline">View All</a>
                </div>

                <div class="space-y-4">
                    <?php if(mysqli_num_rows($result_classes) > 0): ?>
                        <?php while($class = mysqli_fetch_assoc($result_classes)): ?>
                        <div class="bg-white p-6 rounded-[28px] border border-slate-200 shadow-sm flex items-center justify-between hover:border-indigo-300 transition group">
                            <div class="flex items-center gap-5">
                                <div class="w-14 h-14 bg-slate-100 text-slate-600 rounded-2xl flex items-center justify-center text-xl group-hover:bg-indigo-600 group-hover:text-white transition">
                                    <i class="fa-solid fa-folder-open"></i>
                                </div>
                                <div>
                                    <h4 class="font-extrabold text-slate-800"><?php echo $class['title']; ?></h4>
                                    <p class="text-slate-400 text-sm">Created: <?php echo date('d M Y', strtotime($class['created_at'] ?? 'now')); ?></p>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <a href="edit_course.php?id=<?php echo $class['id']; ?>" class="w-10 h-10 bg-slate-50 text-slate-400 rounded-xl flex items-center justify-center hover:bg-amber-50 hover:text-amber-600 transition">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <a href="course_detail.php?id=<?php echo $class['id']; ?>" class="px-5 py-2 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition">
                                    Manage Class
                                </a>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-10 bg-white rounded-[28px] border-2 border-dashed border-slate-200">
                            <p class="text-slate-400">No courses created yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="w-full md:w-80">
                <div class="bg-slate-900 p-8 rounded-[32px] text-white">
                    <h3 class="text-xl font-bold mb-4 leading-tight">Create a New Learning Path</h3>
                    <p class="text-slate-400 text-sm mb-6 leading-relaxed">Add a new course, upload materials, and start teaching your students.</p>
                    <a href="create_course.php" class="block w-full text-center bg-orange-500 hover:bg-orange-600 text-white font-bold py-4 rounded-2xl shadow-xl shadow-orange-900/20 transition transform active:scale-95">
                        <i class="fa-solid fa-plus mr-2"></i> Create Course
                    </a>
                </div>
            </div>
        </div>

    </main>

</body>
</html>