<?php
include 'config.php'; // Menghubungkan ke database

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $alamat = $_POST['alamat'];

    // Cek apakah email sudah terdaftar
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $existingUser = $stmt->fetch();

    if ($existingUser) {
        $error = "Email sudah terdaftar!";
    } else {
        // Query untuk menyimpan pengguna baru
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, alamat) VALUES (:username, :email, :password, :alamat)");
        $stmt->execute(['username' => $username, 'email' => $email, 'password' => $password, 'alamat' => $alamat]);

        header("Location: login.php");
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi</title>
    <style>
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
    max-width: 700px; /* Lebar maksimal container */
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
    font-weight: bold;
    background-color: #f9f9f9;
    padding: 8px;
    border-radius: 5px;
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
input[type="text"], input[type="email"], input[type="password"], textarea {
    border-radius: 8px;
    padding: 12px 5px;
    width: 100%; /* Input mengambil lebar penuh */
    background-color: #f9f9f9;
    border: 1px solid #ccc;
    font-size: 1rem;
    transition: all 0.3s ease;
}

input[type="text"]:focus, input[type="email"]:focus, input[type="password"]:focus, textarea:focus {
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
</head>
<body>
    <div class="container mt-5">
    <div class="card">
        <h1 class="text-center">E-lektronik</h1>
        <h2 class="text-center">Registrasi</h2>
        <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <form method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" class="form-control" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" class="form-control" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" class="form-control" name="password" required>
            </div>
            <div class="form-group">
                <label for="alamat">Alamat:</label>
                <textarea class="form-control" name="alamat" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Daftar</button>
            <a href="login.php" class="btn btn-link">Sudah punya akun? Login di sini</a>
        </form>
        <p class="instruction mt-4">Nikmati kemudahan berbelanja elektronik terbaik hanya di E-lektronik!</p>
    </div>
</div>


</body>
</html>