<?php
include 'koneksi.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}

$course_id = mysqli_real_escape_string($conn, $_GET['id']);
$teacher_id = $_SESSION['user_id'];

// Ambil data kelas lama & pastikan ini milik guru yang login
$query = mysqli_query($conn, "SELECT * FROM courses WHERE id = '$course_id' AND teacher_id = '$teacher_id'");
$course = mysqli_fetch_assoc($query);

if (!$course) { die("Kelas tidak ditemukan atau Anda tidak memiliki akses."); }

// Proses Update saat tombol Simpan ditekan
if (isset($_POST['update_course'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    $update = mysqli_query($conn, "UPDATE courses SET title = '$title', description = '$description' WHERE id = '$course_id'");

    if ($update) {
        header("Location: dashboard_teacher.php?update=success");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Kelas - <?php echo $course['title']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-50 p-6 md:p-12">
    <div class="max-w-2xl mx-auto">
        <div class="mb-8">
            <a href="dashboard_teacher.php" class="text-indigo-600 font-bold flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="bg-white p-10 rounded-[40px] shadow-sm border border-slate-100">
            <h1 class="text-3xl font-black text-slate-800 mb-2">Edit Informasi Kelas</h1>
            <p class="text-slate-400 mb-8">Ubah detail nama atau deskripsi kelas Anda.</p>

            <form action="" method="POST" class="space-y-6">
                <div>
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Nama Kelas</label>
                    <input type="text" name="title" value="<?php echo $course['title']; ?>" required
                        class="w-full bg-slate-50 border border-slate-100 px-6 py-4 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-bold text-slate-700">
                </div>

                <div>
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Deskripsi</label>
                    <textarea name="description" rows="4" required
                        class="w-full bg-slate-50 border border-slate-100 px-6 py-4 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium text-slate-600"><?php echo $course['description']; ?></textarea>
                </div>

                <div class="pt-4">
                    <button type="submit" name="update_course" 
                        class="w-full bg-indigo-600 text-white py-4 rounded-2xl font-black shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition transform active:scale-95">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>