<?php
include 'koneksi.php';
session_start();

// Proteksi: Hanya guru yang bisa tambah kelas
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}

if (isset($_POST['submit'])) {
    $teacher_id = $_SESSION['user_id'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    
    // Generate kode kelas unik secara otomatis (6 karakter)
    $class_code = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));

    $query = "INSERT INTO courses (teacher_id, title, class_code, description) 
              VALUES ('$teacher_id', '$title', '$class_code', '$description')";

    if (mysqli_query($conn, $query)) {
        header("Location: dashboard_teacher.php?status=success");
        exit;
    } else {
        $error = "Gagal membuat kelas: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Course</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-6">

    <div class="w-full max-w-2xl bg-white rounded-[40px] shadow-2xl shadow-indigo-100 overflow-hidden border border-slate-100">
        <div class="flex flex-col md:flex-row">
            
            <div class="md:w-1/3 bg-indigo-600 p-10 text-white flex flex-col justify-center">
                <a href="dashboard_teacher.php" class="text-indigo-200 hover:text-white mb-8 transition flex items-center gap-2">
                    <i class="fa-solid fa-arrow-left"></i> Back
                </a>
                <h2 class="text-3xl font-bold mb-4">Add New Class.</h2>
                <p class="text-indigo-100 text-sm leading-relaxed opacity-80">
                    Satu langkah lagi untuk mulai membagikan ilmu dengan siswa-siswamu.
                </p>
            </div>

            <div class="md:w-2/3 p-12">
                <?php if(isset($error)): ?>
                    <div class="bg-red-50 text-red-500 p-4 rounded-2xl mb-6 text-sm border border-red-100 italic">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" class="space-y-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Course Title</label>
                        <input type="text" name="title" required
                            class="w-full px-6 py-4 rounded-2xl bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition duration-200" 
                            placeholder="Contoh: Pemrograman Web Dasar">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Description</label>
                        <textarea name="description" rows="4" required
                            class="w-full px-6 py-4 rounded-2xl bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition duration-200" 
                            placeholder="Jelaskan apa yang akan dipelajari di kelas ini..."></textarea>
                    </div>

                    <div class="pt-4">
                        <button type="submit" name="submit" 
                            class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-4 rounded-2xl shadow-lg shadow-orange-100 transition transform active:scale-95">
                            Publish Course
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>