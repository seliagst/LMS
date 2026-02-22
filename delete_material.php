<?php
include 'koneksi.php';
session_start();

// 1. Proteksi Halaman: Hanya Guru
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    $material_id = mysqli_real_escape_string($conn, $_GET['id']);
    $teacher_id = $_SESSION['user_id'];

    // 2. Keamanan: Pastikan materi ini milik kelas yang diajar oleh guru yang sedang login
    $check_owner = mysqli_query($conn, "SELECT m.id, m.course_id FROM materials m 
                                       JOIN courses c ON m.course_id = c.id 
                                       WHERE m.id = '$material_id' AND c.teacher_id = '$teacher_id'");
    
    if (mysqli_num_rows($check_owner) > 0) {
        $data = mysqli_fetch_assoc($check_owner);
        $course_id = $data['course_id'];

        // 3. Hapus Submissions terkait (jika ada) agar tidak terjadi error foreign key
        mysqli_query($conn, "DELETE FROM submissions WHERE material_id = '$material_id'");

        // 4. Hapus Materi
        $delete = mysqli_query($conn, "DELETE FROM materials WHERE id = '$material_id'");

        if ($delete) {
            // Kembali ke halaman detail kelas dengan status sukses
            header("Location: course_detail.php?id=$course_id&delete=success");
        } else {
            echo "Gagal menghapus materi.";
        }
    } else {
        die("Anda tidak memiliki akses untuk menghapus materi ini.");
    }
} else {
    header("Location: dashboard_teacher.php");
}
?>