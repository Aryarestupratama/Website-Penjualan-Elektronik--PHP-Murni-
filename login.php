<?php
session_start();
include 'config.php'; // Menghubungkan ke database

$loginSuccess = false;
$loginError = false;

// Periksa pesan logout
$logoutMessage = '';
if (isset($_SESSION['logout_message'])) {
    $logoutMessage = $_SESSION['logout_message'];
    unset($_SESSION['logout_message']); // Hapus pesan setelah ditampilkan
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid!";
    } else {
        // Query untuk memeriksa pengguna
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role']; // Simpan peran pengguna dalam session

            $_SESSION['success_message'] = 'Login Berhasil! Selamat datang di E-lektronik!';
            // Redirect berdasarkan peran
            if ($user['role'] === 'admin') {
                header("Location: admin_dashboard.php"); // Halaman khusus admin
            } else {
                header("Location: dashboard.php"); // Halaman pengguna biasa
            }
            exit;
        } else {
            $error = "Email atau password salah!";
        }
    }
}


?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        /* Mengatur tampilan keseluruhan halaman */
        body {
            background: linear-gradient(135deg, #002d5c, #239a3b); /* Latar belakang gradient biru dan hijau */
            font-family: 'Arial', sans-serif;
            color: #fff;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
        }

        /* Mengatur tampilan container form */
        .container {
            width: 100%;
            max-width: 400px; /* Lebar maksimal container */
            padding: 40px;
        }

        /* Styling untuk card */
        .card {
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            background-color: #fff;
            padding: 30px;
        }

        /* Heading */
        h1, h2 {
            text-align: center;
            color: #002d5c;
        }

        h1 {
            font-size: 2.5rem;
            font-weight: bold;
        }

        h2 {
            font-size: 1.8rem;
            color: #239a3b;
            margin-top: 15px;
        }

        /* Teks instruksi */
        p.instruction {
            text-align: center;
            font-size: 1rem;
            color: #555;
            margin-top: 10px;
            margin-bottom: 20px;
        }

        /* Styling untuk error alert */
        .alert {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
        }

        /* Styling untuk form-group */
        .form-group {
            margin-bottom: 20px;
        }

        label {
            font-size: 1rem;
            color: #333;
        }

        /* Styling input fields */
        input[type="email"], input[type="password"] {
            border-radius: 8px;
            padding: 12px 15px;
            width: 100%; /* Input mengambil lebar penuh */
            background-color: #f9f9f9;
            border: 1px solid #ccc;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        input[type="email"]:focus, input[type="password"]:focus {
            border-color: #239a3b;
            outline: none;
            box-shadow: 0 0 5px rgba(35, 154, 59, 0.5);
        }

        /* Styling tombol */
        button[type="submit"] {
            background-color: #002d5c; /* Navy blue */
            color: white;
            font-weight: bold;
            padding: 12px;
            width: 100%; /* Tombol mengisi lebar container */
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            margin-top: 15px;
        }

        button[type="submit"]:hover {
            background-color: #001f3f;
            transform: scale(1.05);
        }

        /* Teks instruksi iklan */
        p.instruction {
            text-align: center;
            font-size: 1rem;
            color: #555;
            margin-top: 10px;
            font-weight: bold;
            background-color: #f9f9f9;
            padding: 8px;
            border-radius: 5px;
        }

        /* Menambahkan sedikit gaya pada link Daftar */
        a.btn-link {
            text-decoration: none;
            color: #239a3b;
            font-weight: bold;
            display: block;
            text-align: center;
            margin-top: 15px;
        }

        a.btn-link:hover {
            text-decoration: underline;
        }
    </style>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
</head>
<body>
   <div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-body">
            <h1 class="text-center">E-lektronik</h1>
            <h2 class="text-center">Login</h2>
            <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
            <form method="POST">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" class="form-control" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Login</button>
                <a href="register.php" class="btn btn-link btn-block">Belum punya akun? Daftar di sini</a>
            </form>
            <p class="text-center instruction mt-4">Dapatkan penawaran terbaik untuk produk elektronik berkualitas hanya di E-lektronik!</p>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
<script>
    // Tampilkan pesan logout jika ada
    <?php if ($logoutMessage): ?>
        swal({
            title: 'Logout Berhasil!',
            text: '<?php echo $logoutMessage; ?>',
            icon: 'info',
            button: 'OK',
        });
    <?php elseif (isset($_SESSION['success_message'])): ?>
        swal({
            title: 'Login Berhasil!',
            text: '<?php echo $_SESSION['success_message']; ?>',
            icon: 'success',
            button: 'OK',
        });
    <?php endif; ?>
</script>
</body>
</html>