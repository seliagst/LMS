<?php
include 'koneksi.php';
session_start();

// 1. Proteksi Halaman
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}


$teacher_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// 2. Query Statistik Ringkas
// Total Kelas
$res_courses = mysqli_query($conn, "SELECT COUNT(*) as total FROM courses WHERE teacher_id = '$teacher_id'");
$total_courses = mysqli_fetch_assoc($res_courses)['total'];

// Total Siswa Unik (Siswa yang mengambil kelas milik guru ini)
$res_students = mysqli_query($conn, "SELECT COUNT(DISTINCT e.student_id) as total 
    FROM enrollments e JOIN courses c ON e.course_id = c.id 
    WHERE c.teacher_id = '$teacher_id'");
$total_students = mysqli_fetch_assoc($res_students)['total'];

// Rata-rata Nilai dari semua tugas yang sudah dinilai
$res_grade = mysqli_query($conn, "SELECT AVG(s.grade) as avg_grade 
    FROM submissions s JOIN materials m ON s.material_id = m.id 
    JOIN courses c ON m.course_id = c.id 
    WHERE c.teacher_id = '$teacher_id' AND s.grade IS NOT NULL");
$avg_grade = round(mysqli_fetch_assoc($res_grade)['avg_grade'], 1);

// 3. Data untuk Grafik (Jumlah Pengumpulan Tugas per Kelas)
$query_chart = "SELECT c.title, COUNT(s.id) as total_sub 
                FROM courses c 
                LEFT JOIN materials m ON c.id = m.course_id 
                LEFT JOIN submissions s ON m.id = s.material_id 
                WHERE c.teacher_id = '$teacher_id' 
                GROUP BY c.id";
$chart_result = mysqli_query($conn, $query_chart);

$labels = [];
$data = [];
while($row = mysqli_fetch_assoc($chart_result)) {
    $labels[] = $row['title'];
    $data[] = $row['total_sub'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Analytics - Teacher Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap');
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
            <a href="students.php" class="flex items-center gap-4 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 px-4 py-3 rounded-2xl font-semibold transition">
                <i class="fa-solid fa-users"></i> Students
            </a>
            <a href="analytics.php" class="flex items-center gap-4 text-indigo-600 bg-indigo-50 px-4 py-3 rounded-2xl font-bold transition">
                <i class="fa-solid fa-chart-line"></i> Analytics
            </a>
        </nav>
        <a href="logout.php" class="flex items-center gap-4 text-red-400 hover:text-red-600 px-4 py-3 rounded-2xl font-semibold transition mt-auto border-t border-slate-100 pt-6">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
    </aside>

    <main class="flex-1 md:ml-72 p-8 md:p-12">
        <header class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-12">
            <div>
                <h1 class="text-4xl font-black text-slate-800 tracking-tight">Analytics</h1>
                <p class="text-slate-400 font-medium mt-1">Laporan performa dan keterlibatan siswa.</p>
            </div>
            <div class="flex items-center gap-4 bg-white p-2 pr-6 rounded-3xl border border-slate-100 shadow-sm">
                <img src="https://ui-avatars.com/api/?name=<?php echo $username; ?>&background=4f46e5&color=fff" class="w-12 h-12 rounded-2xl shadow-md">
                <div>
                    <p class="text-sm font-bold text-slate-800"><?php echo $username; ?></p>
                    <p class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">Teacher</p>
                </div>
            </div>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
            <div class="bg-white p-8 rounded-[40px] border border-slate-100 shadow-sm">
                <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mb-4">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
                <p class="text-slate-400 text-xs font-black uppercase tracking-widest">Total Students</p>
                <p class="text-4xl font-black text-slate-800 mt-1"><?php echo $total_students; ?></p>
            </div>

            <div class="bg-white p-8 rounded-[40px] border border-slate-100 shadow-sm">
                <div class="w-12 h-12 bg-orange-50 text-orange-600 rounded-2xl flex items-center justify-center mb-4">
                    <i class="fa-solid fa-book-open"></i>
                </div>
                <p class="text-slate-400 text-xs font-black uppercase tracking-widest">Courses Active</p>
                <p class="text-4xl font-black text-slate-800 mt-1"><?php echo $total_courses; ?></p>
            </div>

            <div class="bg-white p-8 rounded-[40px] border border-slate-100 shadow-sm">
                <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center mb-4">
                    <i class="fa-solid fa-star"></i>
                </div>
                <p class="text-slate-400 text-xs font-black uppercase tracking-widest">Avg. Grade</p>
                <p class="text-4xl font-black text-slate-800 mt-1"><?php echo $avg_grade ?: '0'; ?><span class="text-lg text-slate-300">/100</span></p>
            </div>
        </div>

        <div class="bg-white p-10 rounded-[48px] border border-slate-100 shadow-sm">
            <h3 class="text-xl font-bold text-slate-800 mb-8">Submission Activity per Course</h3>
            <canvas id="performanceChart" height="100"></canvas>
        </div>
    </main>

    <script>
        const ctx = document.getElementById('performanceChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    label: 'Total Submissions',
                    data: <?php echo json_encode($data); ?>,
                    backgroundColor: '#4F46E5',
                    borderRadius: 12,
                    barThickness: 40
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true, grid: { display: false } },
                    x: { grid: { display: false } }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    </script>
</body>
</html>