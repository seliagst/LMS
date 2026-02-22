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

// 2. Query Ambil Daftar Siswa yang mengambil kelas milik guru ini
// Kita mengambil nama siswa, nama kelas yang mereka ambil, dan tanggal mereka bergabung
// 2. Query Ambil Daftar Siswa (Disederhanakan tanpa kolom email)
$query = "SELECT 
            u.username as student_name, 
            c.title as course_name, 
            e.enrolled_at 
          FROM enrollments e
          JOIN users u ON e.student_id = u.id
          JOIN courses c ON e.course_id = c.id
          WHERE c.teacher_id = '$teacher_id'
          ORDER BY e.enrolled_at DESC";

$result = mysqli_query($conn, $query);
$total_students = mysqli_num_rows($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Students - Teacher Panel</title>
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
            <a href="course_teacher.php" class="flex items-center gap-4 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 px-4 py-3 rounded-2xl font-semibold transition">
                <i class="fa-solid fa-book"></i> My Courses
            </a>
            <a href="students.php" class="flex items-center gap-4 text-indigo-600 bg-indigo-50 px-4 py-3 rounded-2xl font-bold transition">
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
                <h1 class="text-4xl font-black text-slate-800 tracking-tight">Daftar Siswa</h1>
                <p class="text-slate-400 font-medium mt-1">Lihat semua siswa yang terdaftar dalam kelas anda!</p>
            </div>
            <div class="flex items-center gap-4 bg-white p-2 pr-6 rounded-3xl border border-slate-100 shadow-sm">
                <img src="https://ui-avatars.com/api/?name=<?php echo $username; ?>&background=4f46e5&color=fff" class="w-12 h-12 rounded-2xl shadow-md">
                <div>
                    <p class="text-sm font-bold text-slate-800"><?php echo $username; ?></p>
                    <p class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">Teacher</p>
                </div>
            </div>
        </header>
        <div class="mb-10">
            <div class="bg-white p-8 rounded-[35px] border border-slate-100 shadow-sm inline-flex items-center gap-6">
                <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center text-2xl">
                    <i class="fa-solid fa-user-graduate"></i>
                </div>
                <div>
                    <p class="text-slate-400 text-xs font-black uppercase tracking-widest">Total Students</p>
                    <p class="text-3xl font-black text-slate-800"><?php echo $total_students; ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-[40px] border border-slate-100 shadow-sm overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Student Information</th>
                        <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Enrolled Course</th>
                        <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Join Date</th>
                        <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if(mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr class="group hover:bg-slate-50/50 transition">
                            <td class="p-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center text-slate-400 font-bold group-hover:bg-indigo-600 group-hover:text-white transition">
                                        <?php echo strtoupper(substr($row['student_name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <p class="font-extrabold text-slate-800"><?php echo $row['student_name']; ?></p>
                                        </div>
                                </div>
                            </td>
                                                        <td class="p-6">
                                <span class="text-sm font-bold text-slate-600"><?php echo $row['course_name']; ?></span>
                            </td>
                            <td class="p-6 text-center">
                                <span class="text-xs font-bold text-slate-400">
                                    <?php echo date('d M Y', strtotime($row['enrolled_at'])); ?>
                                </span>
                            </td>
                            <td class="p-6 text-right">
                                <span class="bg-emerald-100 text-emerald-600 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest">Active</span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="p-20 text-center text-slate-400 font-medium italic">Belum ada siswa yang mendaftar di kelas Anda.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>