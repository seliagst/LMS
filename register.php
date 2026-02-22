<?php
include 'koneksi.php';
session_start();

if (isset($_POST['register'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password']; // Disarankan menggunakan password_hash() untuk produksi
    $role     = $_POST['role'];

    // Cek apakah username sudah ada
    $check_user = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
    
    if (mysqli_num_rows($check_user) > 0) {
        $error = "Username sudah digunakan, cari yang lain!";
    } else {
        // Simpan ke database
        $query = "INSERT INTO users (username, password, role) VALUES ('$username', '$password', '$role')";
        if (mysqli_query($conn, $query)) {
            $_SESSION['success_msg'] = "Registrasi berhasil! Silakan login.";
            header("Location: login.php");
            exit;
        } else {
            $error = "Terjadi kesalahan saat mendaftar.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - LMS Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-white h-screen overflow-hidden font-sans">

    <div class="flex h-screen w-screen">
        
        <div class="hidden md:flex md:w-1/2 bg-indigo-600 p-20 flex-col justify-between relative">
            <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-500 rounded-bl-full opacity-50"></div>
            
            <div class="w-[250px] relative z-10 flex justify-center items-center">
                <img src="asset/gambar.png" 
                     alt="Register Illustration" 
                     class="w-full max-w-sm drop-shadow-2xl transform rotate-3 hover:rotate-0 transition duration-500">
            </div>

            <div class="relative z-10">
                <h2 class="text-5xl font-bold text-white leading-tight mb-6">Start your learning journey today</h2>
                <p class="text-indigo-100 text-lg leading-relaxed max-w-md">
                    Join thousands of students and teachers. Create an account to access courses, materials, and track your progress easily.
                </p>
            </div>
        </div>

        <div class="w-full md:w-1/2 flex flex-col justify-center p-12 md:p-24 bg-white overflow-y-auto">
            <div class="max-w-md mx-auto w-full">
                
                <div class="mb-10">
                    <h3 class="text-4xl font-extrabold text-slate-800 mb-2">Daftar Akun</h3>
                    <p class="text-slate-400">Buat akun baru untuk memulai pengalaman belajar.</p>
                </div>

                <?php if(isset($error)): ?>
                    <div class="bg-red-50 text-red-600 p-4 rounded-2xl text-sm mb-6 border border-red-100 flex items-center gap-2">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" class="space-y-5">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wider">Username</label>
                        <input type="text" name="username" required
                            class="w-full px-6 py-4 rounded-2xl bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition duration-200" 
                            placeholder="buat username">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wider">Password</label>
                        <input type="password" name="password" required
                            class="w-full px-6 py-4 rounded-2xl bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition duration-200" 
                            placeholder="••••••••">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wider">Daftar Sebagai</label>
                        <select name="role" required 
                            class="w-full px-6 py-4 rounded-2xl bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition duration-200 appearance-none">
                            <option value="student">Siswa (Student)</option>
                            <option value="teacher">Guru (Teacher)</option>
                        </select>
                    </div>

                    <button type="submit" name="register" 
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-2xl shadow-xl shadow-indigo-100 transition duration-300 transform hover:-translate-y-1 active:scale-95 mt-4">
                        Daftar Sekarang
                    </button>
                </form>

                <div class="mt-12 text-center md:text-left">
                    <p class="text-slate-500">
                        Sudah punya akun? <a href="login.php" class="text-indigo-600 font-bold hover:underline">Masuk di sini</a>
                    </p>
                </div>
            </div>
        </div>

    </div>

</body>
</html>