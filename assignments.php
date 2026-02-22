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

// 2. Query Detail Tugas (Hanya mengambil materials bertipe 'assignment')
$query_assignments = "SELECT 
    m.id as material_id,
    m.title as assignment_title,
    c.title as course_name,
    s.submitted_at,
    s.grade,
    s.file_submission
FROM materials m
JOIN courses c ON m.course_id = c.id
JOIN enrollments e ON c.id = e.course_id
LEFT JOIN submissions s ON m.id = s.material_id AND s.student_id = '$student_id'
WHERE e.student_id = '$student_id' 
AND m.type = 'assignment' 
ORDER BY s.submitted_at DESC, m.id DESC";

$result_assignments = mysqli_query($conn, $query_assignments);

// 3. Statistik Akurat
// Hitung berapa banyak konten bertipe 'assignment' yang harus dikerjakan
$total_assigned = mysqli_num_rows($result_assignments);

// Hitung berapa banyak tugas yang sudah dikirim oleh siswa ini
$query_done = "SELECT COUNT(*) as total FROM submissions WHERE student_id = '$student_id'";
$count_done = mysqli_fetch_assoc(mysqli_query($conn, $query_done))['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Assignments - Learning System</title>
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
            <a href="class_detail.php" class="flex items-center gap-4 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 px-4 py-3 rounded-2xl font-semibold transition">
                <i class="fa-solid fa-book-open"></i> My Classes
            </a>
            <a href="assignments.php" class="flex items-center gap-4 text-indigo-600 bg-indigo-50 px-4 py-3 rounded-2xl font-bold transition">
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
                <h1 class="text-4xl font-black text-slate-800 tracking-tight">Assignments</h1>
                <p class="text-slate-400 font-medium mt-1">Daftar tugas yang harus kamu selesaikan.</p>
            </div>
            <div class="flex items-center gap-4 bg-white p-2 pr-6 rounded-3xl border border-slate-100 shadow-sm">
                <img src="https://ui-avatars.com/api/?name=<?php echo $username; ?>&background=4f46e5&color=fff" class="w-12 h-12 rounded-2xl shadow-md">
                <div>
                    <p class="text-sm font-bold text-slate-800"><?php echo $username; ?></p>
                    <p class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">Student</p>
                </div>
            </div>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
            <div class="bg-white p-8 rounded-[35px] border border-slate-100 shadow-sm flex items-center gap-6">
                <div class="w-16 h-16 bg-orange-50 text-orange-600 rounded-2xl flex items-center justify-center text-2xl">
                    <i class="fa-solid fa-tasks"></i>
                </div>
                <div>
                    <p class="text-slate-400 text-xs font-black uppercase tracking-widest">Total Tugas</p>
                    <p class="text-3xl font-black text-slate-800"><?php echo $total_assigned; ?></p>
                </div>
            </div>
            <div class="bg-white p-8 rounded-[35px] border border-slate-100 shadow-sm flex items-center gap-6">
                <div class="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center text-2xl">
                    <i class="fa-solid fa-check-double"></i>
                </div>
                <div>
                    <p class="text-slate-400 text-xs font-black uppercase tracking-widest">Sudah Dikirim</p>
                    <p class="text-3xl font-black text-slate-800"><?php echo $count_done; ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-[40px] border border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Tugas & Mata Pelajaran</th>
                            <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Status</th>
                            <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Nilai</th>
                            <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if($total_assigned > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result_assignments)): ?>
                            <tr class="group hover:bg-slate-50/50 transition">
                                <td class="p-6">
                                    <p class="font-extrabold text-slate-800 group-hover:text-indigo-600 transition"><?php echo $row['assignment_title']; ?></p>
                                    <p class="text-xs text-slate-400 font-bold uppercase mt-1 tracking-wider"><?php echo $row['course_name']; ?></p>
                                </td>
                                <td class="p-6 text-center">
                                    <?php if($row['file_submission']): ?>
                                        <span class="bg-emerald-100 text-emerald-600 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-emerald-200">
                                            <i class="fa-solid fa-check mr-1"></i> Terkirim
                                        </span>
                                    <?php else: ?>
                                        <span class="bg-orange-100 text-orange-600 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-orange-200">
                                            <i class="fa-solid fa-clock mr-1"></i> Belum Selesai
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-6 text-center">
                                    <?php if($row['grade'] !== NULL): ?>
                                        <div class="inline-block bg-indigo-50 px-4 py-2 rounded-2xl">
                                            <span class="text-xl font-black text-indigo-600"><?php echo $row['grade']; ?></span>
                                        </div>
                                    <?php elseif($row['file_submission']): ?>
                                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-tighter">Menunggu Nilai</span>
                                    <?php else: ?>
                                        <span class="text-xl font-black text-slate-200">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-6 text-right">
                                    <a href="view_material.php?id=<?php echo $row['material_id']; ?>&from=assignments" 
                                    class="inline-flex items-center gap-2 bg-slate-900 text-white px-6 py-3 rounded-2xl font-bold text-xs hover:bg-indigo-600 transition shadow-lg shadow-slate-100">
                                        Buka Tugas <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="p-24 text-center">
                                    <div class="flex flex-col items-center">
                                        <i class="fa-solid fa-ghost text-5xl text-slate-100 mb-4"></i>
                                        <p class="text-slate-400 font-bold uppercase tracking-widest text-xs">Hore! Tidak ada tugas yang tertunda.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

</body>
</html>