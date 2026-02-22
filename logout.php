<?php
// 1. Inisialisasi session
session_start();

// 2. Hapus semua variabel session
$_SESSION = array();

// 3. Jika ingin menghapus cookie session, hancurkan juga cookie-nya
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Hancurkan session secara total
session_destroy();

// 5. Alihkan pengguna kembali ke halaman login
header("Location: login.php");
exit;
?>