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
$status_msg = "";

// 2. Logika Join Kelas
if (isset($_POST['join_class'])) {
    $code = mysqli_real_escape_string($conn, $_POST['class_code']);
    $check_course = mysqli_query($conn, "SELECT id FROM courses WHERE class_code = '$code'");
    
    if (mysqli_num_rows($check_course) > 0) {
        $course_data = mysqli_fetch_assoc($check_course);
        $course_id = $course_data['id'];
        $check_enroll = mysqli_query($conn, "SELECT id FROM enrollments WHERE student_id = '$student_id' AND course_id = '$course_id'");
        
        if (mysqli_num_rows($check_enroll) == 0) {
            mysqli_query($conn, "INSERT INTO enrollments (student_id, course_id) VALUES ('$student_id', '$course_id')");
            $status_msg = "success";
        } else { $status_msg = "already"; }
    } else { $status_msg = "notfound"; }
}

// 3. Statistik Student
$stat_query = mysqli_query($conn, "SELECT AVG(grade) as avg_grade, COUNT(id) as total_submitted FROM submissions WHERE student_id = '$student_id'");
$stats = mysqli_fetch_assoc($stat_query);

// 4. Ambil Daftar Kelas
$query_my_classes = "SELECT courses.*, users.username as teacher_name 
                     FROM courses 
                     JOIN enrollments ON courses.id = enrollments.course_id 
                     JOIN users ON courses.teacher_id = users.id
                     WHERE enrollments.student_id = '$student_id'
                     ORDER BY enrollments.enrolled_at DESC";
$result_classes = mysqli_query($conn, $query_my_classes);

// 5. Ambil Tugas Mendatang
$query_upcoming = "SELECT m.title, m.id, c.title as course_title 
                   FROM materials m
                   JOIN courses c ON m.course_id = c.id
                   JOIN enrollments e ON c.id = e.course_id
                   LEFT JOIN submissions s ON m.id = s.material_id AND s.student_id = '$student_id'
                   WHERE e.student_id = '$student_id' 
                   AND m.type = 'assignment' 
                   AND s.id IS NULL 
                   ORDER BY m.id DESC LIMIT 5";
$result_upcoming = mysqli_query($conn, $query_upcoming);

// Konfigurasi Kalender
$bulan_ini = date('m'); $tahun_ini = date('Y'); $nama_bulan = date('F Y');
$jumlah_hari = cal_days_in_month(CAL_GREGORIAN, $bulan_ini, $tahun_ini);
$tanggal_pertama = date('N', strtotime("$tahun_ini-$bulan_ini-01"));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Lecture LMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #F8FAFC; }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
    </style>
</head>
<body class="flex min-h-screen">

    <aside class="w-72 bg-white border-r border-slate-200 hidden md:flex flex-col p-8 fixed h-screen z-20">
        <div class="flex items-center gap-3 mb-12">
            <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white font-bold text-xl shadow-lg">L</div>
            <span class="text-xl font-extrabold tracking-tight text-slate-800">Lecture.</span>
        </div>
        
        <nav class="space-y-3 flex-1">
            <a href="dashboard_student.php" class="flex items-center gap-4 text-indigo-600 bg-indigo-50 px-4 py-3 rounded-2xl font-bold transition">
                <i class="fa-solid fa-house"></i> Dashboard
            </a>
            <a href="class_detail.php" class="flex items-center gap-4 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 px-4 py-3 rounded-2xl font-semibold transition">
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

    <main class="flex-1 md:ml-72 xl:mr-80 p-6 md:p-12 min-h-screen">
        
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-6">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight">Semangat Belajar, <?php echo $username; ?>! ⚡</h1>
                <p class="text-slate-400 font-medium mt-1">Hari ini adalah hari yang baik untuk menyelesaikan tugas.</p>
            </div>
        </header>

        <section class="bg-indigo-600 p-8 rounded-[40px] text-white shadow-2xl shadow-indigo-100 mb-10 flex flex-col lg:flex-row items-center justify-between gap-6 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16"></div>
            <div class="relative z-10 text-center lg:text-left">
                <h2 class="text-2xl font-bold">Punya Kode Kelas Baru?</h2>
                <p class="text-indigo-100 opacity-80">Masukkan kode untuk bergabung dengan teman lainnya.</p>
            </div>
            <form action="" method="POST" class="relative z-10 flex gap-3 w-full lg:w-auto">
                <input type="text" name="class_code" required placeholder="KODE" 
                    class="bg-white/20 border border-white/30 px-6 py-4 rounded-2xl focus:outline-none focus:bg-white focus:text-indigo-600 transition placeholder:text-indigo-200 font-black uppercase tracking-widest w-full lg:w-36 text-center">
                <button type="submit" name="join_class" 
                    class="bg-orange-500 hover:bg-orange-600 text-white px-8 py-4 rounded-2xl font-extrabold shadow-lg transition transform active:scale-95">
                    Join
                </button>
            </form>
        </section>

        <?php if($status_msg): ?>
            <div class="mb-8 p-4 rounded-2xl font-bold flex items-center gap-3 <?php echo $status_msg == 'success' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-red-50 text-red-600 border border-red-100'; ?>">
                <i class="fa-solid <?php echo $status_msg == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                <?php echo ($status_msg == 'success' ? "Berhasil bergabung!" : ($status_msg == 'already' ? "Anda sudah di kelas ini." : "Kode tidak ditemukan.")); ?>
            </div>
        <?php endif; ?>

        <div class="mb-8 flex justify-between items-center">
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Kelas Saya</h2>
            <a href="class_detail.php" class="text-indigo-600 font-bold text-sm hover:underline">Lihat Semua</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
            <?php if(mysqli_num_rows($result_classes) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result_classes)): ?>
                    <div class="bg-white p-8 rounded-[40px] border border-slate-100 shadow-sm hover:shadow-xl transition-all group">
                        <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center mb-6 group-hover:bg-indigo-600 group-hover:text-white transition">
                            <i class="fa-solid fa-book text-xl"></i>
                        </div>
                        <h3 class="font-extrabold text-xl text-slate-800 mb-1"><?php echo $row['title']; ?></h3>
                        <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-6">Instructor: <?php echo $row['teacher_name']; ?></p>
                        <a href="course_view.php?id=<?php echo $row['id']; ?>" class="inline-flex items-center gap-2 text-indigo-600 font-bold">
                            Masuk Kelas <i class="fa-solid fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-full py-16 text-center bg-white rounded-[40px] border-2 border-dashed border-slate-100">
                    <p class="text-slate-400 font-medium italic">Belum ada kelas diikuti.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <aside class="w-80 bg-white border-l border-slate-200 p-8 hidden xl:flex flex-col fixed right-0 h-screen z-20 overflow-y-auto hide-scrollbar">
        <div class="mb-10 bg-slate-900 p-6 rounded-[32px] text-white shadow-xl">
            <h3 class="font-bold text-center text-sm mb-4"><?php echo $nama_bulan; ?></h3>
            <div class="grid grid-cols-7 gap-y-2 text-center text-[9px] font-black text-slate-500 uppercase mb-2">
                <span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span><span>S</span>
            </div>
            <div class="grid grid-cols-7 gap-1 text-center text-xs font-bold">
                <?php
                for ($i = 1; $i < $tanggal_pertama; $i++) echo '<div></div>';
                for ($tgl = 1; $tgl <= $jumlah_hari; $tgl++) {
                    $is_today = ($tgl == date('j')) ? 'bg-indigo-500 text-white rounded-lg' : 'text-slate-400';
                    echo "<div class='p-1.5 $is_today'>$tgl</div>";
                }
                ?>
            </div>
        </div>

        <h3 class="font-black text-slate-800 text-lg mb-6 flex items-center gap-2">
            <i class="fa-solid fa-clock-rotate-left text-orange-500"></i> Deadline
        </h3>
        
        <div class="space-y-4">
            <?php if(mysqli_num_rows($result_upcoming) > 0): ?>
                <?php while($up = mysqli_fetch_assoc($result_upcoming)): ?>
                    <div class="p-5 rounded-[24px] bg-slate-50 hover:bg-white hover:shadow-lg border border-transparent hover:border-slate-100 transition-all group">
                        <a href="view_material.php?id=<?php echo $up['id']; ?>">
                            <span class="text-[9px] font-black bg-orange-100 text-orange-600 px-2 py-0.5 rounded uppercase mb-2 inline-block">Assignment</span>
                            <h4 class="font-bold text-sm text-slate-800 leading-tight mb-1 group-hover:text-indigo-600"><?php echo $up['title']; ?></h4>
                            <p class="text-[10px] text-slate-400 font-medium"><?php echo $up['course_title']; ?></p>
                        </a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="py-8 text-center bg-emerald-50 rounded-3xl border border-dashed border-emerald-200">
                    <p class="text-[10px] text-emerald-600 font-black uppercase">Semua beres!</p>
                </div>
            <?php endif; ?>
        </div>
    </aside>

</body>
</html>