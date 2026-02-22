<?php
include 'koneksi.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php"); exit;
}

$id = $_GET['id'];
$course_id = $_GET['course_id'];

// Ambil data lama
$get_data = mysqli_query($conn, "SELECT * FROM materials WHERE id = '$id'");
$data = mysqli_fetch_assoc($get_data);

if (isset($_POST['update'])) {
    $title   = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $type    = $_POST['type'];
    
    // Logika Update File (Jika ada file baru)
    if (!empty($_FILES['file_materi']['name'])) {
        $file_name = time() . '_' . basename($_FILES["file_materi"]["name"]);
        move_uploaded_file($_FILES["file_materi"]["tmp_name"], "uploads/" . $file_name);
        $query = "UPDATE materials SET title='$title', content='$content', type='$type', file_path='$file_name' WHERE id='$id'";
    } else {
        $query = "UPDATE materials SET title='$title', content='$content', type='$type' WHERE id='$id'";
    }

    if (mysqli_query($conn, $query)) {
        header("Location: course_detail.php?id=$course_id&status=updated");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Konten</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-50 min-h-screen p-6 flex justify-center items-center">
    <div class="bg-white w-full max-w-2xl rounded-[40px] shadow-xl p-10 border border-slate-100">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-2xl font-black text-slate-800 tracking-tight">Edit Konten</h2>
            <a href="course_detail.php?id=<?php echo $course_id; ?>" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-xl"></i></a>
        </div>

        <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Tipe Konten</label>
                <div class="flex gap-4">
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" name="type" value="material" <?php echo ($data['type']=='material')?'checked':''; ?> class="peer hidden">
                        <div class="p-3 border-2 rounded-xl text-center peer-checked:border-indigo-600 peer-checked:bg-indigo-50 peer-checked:text-indigo-600 text-slate-400 font-bold text-xs transition">Materi</div>
                    </label>
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" name="type" value="assignment" <?php echo ($data['type']=='assignment')?'checked':''; ?> class="peer hidden">
                        <div class="p-3 border-2 rounded-xl text-center peer-checked:border-orange-500 peer-checked:bg-orange-50 peer-checked:text-orange-500 text-slate-400 font-bold text-xs transition">Tugas</div>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2">Judul</label>
                <input type="text" name="title" value="<?php echo $data['title']; ?>" required class="w-full px-5 py-3 rounded-xl bg-slate-50 border border-slate-200 focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2">Isi Konten</label>
                <textarea name="content" rows="6" required class="w-full px-5 py-3 rounded-xl bg-slate-50 border border-slate-200 focus:ring-2 focus:ring-indigo-500 outline-none"><?php echo $data['content']; ?></textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2">Ganti File (Biarkan kosong jika tidak ingin mengubah)</label>
                <input type="file" name="file_materi" class="text-sm text-slate-500">
                <?php if($data['file_path']): ?>
                    <p class="text-[10px] text-indigo-500 mt-1">File saat ini: <?php echo $data['file_path']; ?></p>
                <?php endif; ?>
            </div>

            <button type="submit" name="update" class="w-full bg-indigo-600 text-white font-bold py-4 rounded-2xl shadow-lg hover:bg-indigo-700 transition transform active:scale-95">
                Simpan Perubahan
            </button>
        </form>
    </div>
</body>
</html>