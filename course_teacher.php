<?php
include 'koneksi.php';
session_start();

// 1. Proteksi Halaman: Hanya Guru
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}

$teacher_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// 2. Query Ambil Kelas yang diajar oleh guru ini
// Kita gunakan COUNT untuk menghitung jumlah siswa yang enroll di tiap kelas
$query = "SELECT c.*, COUNT(e.id) as total_students 
          FROM courses c 
          LEFT JOIN enrollments e ON c.id = e.course_id 
          WHERE c.teacher_id = '$teacher_id' 
          GROUP BY c.id 
          ORDER BY c.id DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - Teacher Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #F8FAFC; }
    </style>
</head>
<body class="flex min-h-screen">

    <aside class="w-72 bg-white border-r border-slate-200 hidden md:flex flex-col p-8 fixed h-full">
        <div class="flex items-center gap-3 mb-12">
            <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white font-bold text-xl shadow-lg">L</div>
            <span class="text-xl font-extrabold tracking-tight text-slate-800">Lecture.</span>
        </div>
        
        <nav class="space-y-3 flex-1">
            <a href="dashboard_teacher.php" class="flex items-center gap-4 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 px-4 py-3 rounded-2xl font-semibold transition">
                <i class="fa-solid fa-house"></i> Dashboard
            </a>
            <a href="courses_teacher.php" class="flex items-center gap-4 text-indigo-600 bg-indigo-50 px-4 py-3 rounded-2xl font-bold transition">
                <i class="fa-solid fa-book"></i> My Courses
            </a>
            <a href="students.php" class="flex items-center gap-4 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 px-4 py-3 rounded-2xl font-semibold transition">
                <i class="fa-solid fa-users"></i> Students
            </a>
            <a href="analytics.php" class="flex items-center gap-4 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 px-4 py-3 rounded-2xl font-semibold transition">
                <i class="fa-solid fa-chart-line"></i> Analytics
            </a>
        </nav>

        <a href="logout.php" class="flex items-center gap-4 text-red-400 hover:text-red-500 font-bold border-t border-slate-50 pt-6 px-4">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
    </aside>

    <main class="flex-1 md:ml-72 p-8 md:p-12">
        <header class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-12">
            <div>
                <h1 class="text-4xl font-black text-slate-800 tracking-tight">Kelola Kelas</h1>
                <p class="text-slate-400 font-medium mt-1">Buat kelas baru atau perbarui materi kursus Anda.</p>
            </div>
            <div class="flex items-center gap-4 bg-white p-2 pr-6 rounded-3xl border border-slate-100 shadow-sm">
                <img src="https://ui-avatars.com/api/?name=<?php echo $username; ?>&background=4f46e5&color=fff" class="w-12 h-12 rounded-2xl shadow-md">
                <div>
                    <p class="text-sm font-bold text-slate-800"><?php echo $username; ?></p>
                    <p class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">Teacher</p>
                </div>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                <div class="bg-white rounded-[40px] border border-slate-100 shadow-sm overflow-hidden group hover:shadow-2xl hover:shadow-indigo-100/50 transition-all duration-300">
                    <div class="p-8 md:p-10">
                        <div class="flex justify-between items-start mb-6">
                            <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center text-2xl group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-300">
                                <i class="fa-solid fa-book"></i>
                            </div>
                            <span class="bg-emerald-50 text-emerald-600 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest">
                                <?php echo $row['total_students']; ?> Students
                            </span>
                        </div>
                        
                        <h3 class="text-2xl font-extrabold text-slate-800 mb-2 group-hover:text-indigo-600 transition-colors"><?php echo $row['title']; ?></h3>
                        <p class="text-slate-500 line-clamp-2 text-sm leading-relaxed mb-8">
                            <?php echo $row['description']; ?>
                        </p>

                        <div class="flex items-center gap-3 pt-6 border-t border-slate-50">
                            <a href="course_detail.php?id=<?php echo $row['id']; ?>" 
                            class="flex-1 bg-slate-900 text-white text-center py-4 rounded-2xl font-bold text-sm hover:bg-indigo-600 transition shadow-lg">
                            Kelola Materi
                            </a>
                            <a href="edit_course.php?id=<?php echo $row['id']; ?>" class="w-14 h-14 bg-slate-50 text-slate-400 rounded-2xl flex items-center justify-center hover:bg-orange-50 hover:text-orange-500 transition shadow-sm">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-full py-20 bg-white rounded-[40px] border-2 border-dashed border-slate-200 text-center">
                    <div class="w-20 h-20 bg-slate-50 text-slate-200 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fa-solid fa-folder-open text-4xl"></i>
                    </div>
                    <p class="text-slate-400 font-bold uppercase tracking-widest text-sm">Anda belum membuat kelas.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>