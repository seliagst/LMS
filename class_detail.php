<?php
include 'koneksi.php';
session_start();

// 1. Proteksi Halaman
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit;
}

$student_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// 2. Query Ambil Daftar Kelas + Hitung jumlah materi di tiap kelas
$query_classes = "SELECT 
                    c.*, 
                    u.username as teacher_name,
                    (SELECT COUNT(*) FROM materials WHERE course_id = c.id) as total_materials,
                    (SELECT COUNT(*) FROM submissions s 
                     JOIN materials m ON s.material_id = m.id 
                     WHERE m.course_id = c.id AND s.student_id = '$student_id') as completed_tasks
                  FROM courses c
                  JOIN enrollments e ON c.id = e.course_id
                  JOIN users u ON c.teacher_id = u.id
                  WHERE e.student_id = '$student_id'
                  ORDER BY e.enrolled_at DESC";

$result_classes = mysqli_query($conn, $query_classes);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Classes - Learning System</title>
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
            <a href="dashboard_student.php" class="flex items-center gap-4 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 px-4 py-3 rounded-2xl font-semibold transition">
                <i class="fa-solid fa-house"></i> Dashboard
            </a>
            <a href="class_detail.php" class="flex items-center gap-4 text-indigo-600 bg-indigo-50 px-4 py-3 rounded-2xl font-bold transition">
                <i class="fa-solid fa-book-open"></i> My Classes
            </a>
            <a href="assignments.php" class="flex items-center gap-4 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 px-4 py-3 rounded-2xl font-semibold transition">
                <i class="fa-solid fa-calendar-check"></i> Assignments
            </a>
        </nav>

        <a href="logout.php" class="flex items-center gap-4 text-red-400 hover:text-red-500 font-bold border-t border-slate-50 pt-6 px-4">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
    </aside>

    <main class="flex-1 md:ml-72 p-8 md:p-12">
        <header class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-12">
            <div>
                <h1 class="text-4xl font-black text-slate-800 tracking-tight">My Classes</h1>
                <p class="text-slate-400 font-medium mt-1">Daftar seluruh kelas yang kamu ikuti.</p>
            </div>
            <div class="flex items-center gap-4 bg-white p-2 pr-6 rounded-3xl border border-slate-100 shadow-sm">
                <img src="https://ui-avatars.com/api/?name=<?php echo $username; ?>&background=4f46e5&color=fff" class="w-12 h-12 rounded-2xl shadow-md">
                <div>
                    <p class="text-sm font-bold text-slate-800"><?php echo $username; ?></p>
                    <p class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">Student</p>
                </div>
            </div>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php if(mysqli_num_rows($result_classes) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result_classes)): 
                    // Hitung persentase progres sederhana
                    $progress = ($row['total_materials'] > 0) ? ($row['completed_tasks'] / $row['total_materials']) * 100 : 0;
                ?>
                    <div class="bg-white rounded-[40px] border border-slate-100 shadow-sm hover:shadow-2xl hover:shadow-indigo-100/50 transition-all duration-500 overflow-hidden group">
                        <div class="h-32 bg-indigo-600 relative overflow-hidden p-8">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16"></div>
                            <div class="relative z-10 w-12 h-12 bg-white/20 backdrop-blur-md rounded-xl flex items-center justify-center text-white text-xl">
                                <i class="fa-solid fa-graduation-cap"></i>
                            </div>
                        </div>

                        <div class="p-8">
                            <div class="mb-6">
                                <h3 class="font-black text-xl text-slate-800 mb-1 group-hover:text-indigo-600 transition-colors">
                                    <?php echo $row['title']; ?>
                                </h3>
                                <p class="text-slate-400 text-xs font-bold uppercase tracking-wider italic">
                                    Prof. <?php echo $row['teacher_name']; ?>
                                </p>
                            </div>

                            <div class="mb-8">
                                <div class="flex justify-between items-end mb-2">
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Course Progress</span>
                                    <span class="text-xs font-bold text-indigo-600"><?php echo round($progress); ?>%</span>
                                </div>
                                <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-indigo-600 transition-all duration-1000" style="width: <?php echo $progress; ?>%"></div>
                                </div>
                                <p class="text-[10px] text-slate-400 mt-2 font-bold uppercase">
                                    <?php echo $row['completed_tasks']; ?> / <?php echo $row['total_materials']; ?> Lessons Completed
                                </p>
                            </div>
                            
                            <a href="course_view.php?id=<?php echo $row['id']; ?>" class="block w-full text-center py-4 bg-slate-900 text-white font-bold rounded-2xl hover:bg-indigo-600 transition-all shadow-xl shadow-slate-200 active:scale-95">
                                Enter Classroom
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-full py-32 text-center bg-white rounded-[50px] border-2 border-dashed border-slate-200">
                    <div class="w-24 h-24 bg-slate-50 text-slate-200 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fa-solid fa-book-open text-5xl"></i>
                    </div>
                    <h3 class="text-2xl font-black text-slate-800 mb-2">Belum ada kelas diikuti</h3>
                    <p class="text-slate-400 font-medium mb-8">Masukkan kode kelas di dashboard untuk memulai belajar.</p>
                    <a href="dashboard_student.php" class="inline-block px-10 py-4 bg-indigo-600 text-white font-bold rounded-2xl shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition">
                        Back to Dashboard
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>