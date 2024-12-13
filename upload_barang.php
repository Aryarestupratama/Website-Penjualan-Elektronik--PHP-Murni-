<?php
session_start();
include 'config.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Ambil data pengguna yang sedang login untuk mendapatkan alamat (lokasi)
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT alamat FROM users WHERE id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$user = $stmt->fetch();

// Cek jika form telah disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_barang = $_POST['nama_barang'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    $deskripsi = $_POST['deskripsi'];
    $gambar = $_FILES['gambar']['name'];
    $lokasi = $user['alamat'];  // Ambil alamat pengguna yang sedang login

    // Upload gambar
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($gambar);
    move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file);

    // Query untuk menyimpan produk
    $stmt = $pdo->prepare("INSERT INTO products (user_id, nama_barang, harga, stok, deskripsi, gambar, lokasi) 
                           VALUES (:user_id, :nama_barang, :harga, :stok, :deskripsi, :gambar, :lokasi)");
    $stmt->execute([
        'user_id' => $user_id,
        'nama_barang' => $nama_barang,
        'harga' => $harga,
        'stok' => $stok,
        'deskripsi' => $deskripsi,
        'gambar' => $target_file,
        'lokasi' => $lokasi  // Menyimpan lokasi pengguna yang login
    ]);

    // Setelah query untuk menyimpan produk
    $_SESSION['upload_success'] = 'Berhasil mengupload barang, barang telah ditambahkan ke toko anda!';
    header("Location: toko_saya.php");
    exit;

   
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Barang - E-lektronik</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
       .navbar {
            background-color: #001f3f;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        /* Gaya dasar merek navbar */
        .navbar-brand {
            font-weight: bold;
            font-size: 1.8rem; /* Sedikit lebih besar untuk menonjolkan merek */
            color: #f9f9f9; /* Warna awal putih */
            transition: color 0.3s ease, transform 0.3s ease, text-shadow 0.3s ease;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); /* Sedikit bayangan untuk kedalaman */
        }

        /* Efek saat hover */
        .navbar-brand:hover {
            color: #f0902f; /* Warna hover menjadi oranye terang */
            text-shadow: 0 0 8px rgba(240, 144, 47, 0.8), 0 0 15px rgba(240, 144, 47, 0.5); /* Efek glowing */
            transform: scale(1.05); /* Sedikit memperbesar untuk kesan interaktif */
        }


        /* Nav-link Hover Effect */
        .nav-link {
            color: #f9f9f9; /* Warna default link */
            position: relative;
            transition: color 0.3s ease;
        }

        /* Hover color and underline animation */
        .nav-link:hover {
            color: #f0902f; /* Warna teks saat di-hover */
        }

        .nav-link::after {
            content: ""; /* Pseudo-element for underline */
            position: absolute;
            left: 0;
            right: 0;
            bottom: -5px;
            height: 2px;
            background-color: #e0e0e0;
            transform: scaleX(0); /* Mulai dengan garis yang tidak terlihat */
            transition: transform 0.3s ease;
        }

        /* Show underline on hover */
        .nav-link:hover::after {
            transform: scaleX(1); /* Garis muncul saat hover */
        }

        /* Container Utama */
        .container {
            background-color: #ffffff;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            max-width: 600px;
            margin-top: 100px;
        }

        /* Judul */
        .container h2 {
            color: #001f3f;
            text-align: center;
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            font-weight: bold;
            text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.2);
        }

        /* Alerts */
        .alert {
            font-size: 1rem;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            margin-bottom: 1.5rem;
        }

        /* Form Group */
        .form-group label {
            font-weight: 600;
            color: #001f3f;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #001f3f;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 0.6rem;
            transition: box-shadow 0.3s ease;
        }

        .form-control:focus {
            border-color: #003366;
            box-shadow: 0 0 5px rgba(0, 31, 63, 0.3);
        }

        /* Tombol Upload */
        .btn-primary {
            background-color: #001f3f;
            border: none;
            color: #ffffff;
            font-size: 1.1rem;
            font-weight: bold;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            transition: background-color 0.3s ease, transform 0.3s ease;
            display: block;
            width: 100%;
            margin-top: 1rem;
        }

        .btn-primary:hover {
            background-color: #003366;
            transform: translateY(-3px);
            box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <a class="navbar-brand" href="dashboard.php">E-lektronik</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item"><a class="nav-link" href="keranjang.php">Keranjang</a></li>
                <li class="nav-item"><a class="nav-link" href="proses.php">Proses</a></li>
                <li class="nav-item"><a class="nav-link" href="toko_saya.php">Toko Saya</a></li>
                <li class="nav-item"><a class="nav-link" href="history.php">History</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h2>Upload Barang Anda</h2>

        <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nama_barang">Nama Barang:</label>
                <input type="text" class="form-control" name="nama_barang" required>
            </div>
            <div class="form-group">
                <label for="gambar">Gambar Barang:</label>
                <input type="file" class="form-control" name="gambar" required>
            </div>
            <div class="form-group">
                <label for="harga">Harga Barang:</label>
                <input type="number" class="form-control" name="harga" required>
            </div>
            <div class="form-group">
                <label for="stok">Stok Barang:</label>
                <input type="number" class="form-control" name="stok" required>
            </div>
            <div class="form-group">
                <label for="deskripsi">Deskripsi Barang:</label>
                <textarea class="form-control" name="deskripsi" rows="4" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Upload Barang</button>
        </form>
    </div>
</body>
</html>
