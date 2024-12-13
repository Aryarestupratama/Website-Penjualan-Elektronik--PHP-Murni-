 <?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Periksa pesan sukses
if (isset($_SESSION['upload_success'])) {
    $uploadSuccessMessage = $_SESSION['upload_success'];
    unset($_SESSION['upload_success']); // Hapus pesan setelah ditampilkan
}

// Ambil produk yang diupload oleh pengguna
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM products WHERE user_id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$products = $stmt->fetchAll();
?>




<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko Saya - E-lektronik</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/feather-icons"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
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

        .container {
            margin-top: 100px;
        }

        .card {
            height: 100%; /* Semua kartu memiliki tinggi yang sama */
            display: flex;
            flex-direction: column;
            border-radius: 12px; /* Membulatkan sudut */
            overflow: hidden;
            background-color: #ffffff; /* Latar belakang putih */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Bayangan lembut di sekitar kartu */
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        /* Efek hover pada kartu */
        .card:hover {
            transform: translateY(-8px); /* Mengangkat sedikit saat hover */
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15); /* Bayangan lebih tebal saat hover */
        }

        /* Gaya gambar di bagian atas kartu */
        .card-img-top {
            height: 220px;
            object-fit: contain; /* Memuat gambar agar sesuai area */
            width: 100%;
            border-top-left-radius: 12px; /* Membulatkan sudut gambar sesuai kartu */
            border-top-right-radius: 12px;
            transition: transform 0.4s ease; /* Transisi untuk gambar saat hover */
        }

        /* Efek hover pada gambar */
        .card:hover .card-img-top {
            transform: scale(1.05); /* Memperbesar gambar sedikit saat hover */
        }

        /* Tampilan body kartu */
        .card-body {
            padding: 16px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        
        }


        /* Tampilan Nama Produk */
        .card-title {
            font-family: 'Roboto', sans-serif; /* Font modern */
            font-size: 1.2rem;
            font-weight: 500;
            color: #333333;
            margin-bottom: 0.5rem;
            text-transform: capitalize;
        }

        /* Tampilan Harga Produk */
        .card-price {
            font-family: 'Roboto Slab', serif; /* Font serif untuk tampilan premium */
            font-size: 1.5rem;
            font-weight: bold;
            color: #FF6F61; /* Warna oranye mencolok untuk harga */
            margin-bottom: 0.3rem;
        }

        /* Tampilan Stok Produk */
        .card-stock {
            font-family: 'Roboto', sans-serif;
            font-size: 0.9rem;
            color: #555555; /* Warna abu untuk teks tambahan */
            margin-top: 0.5rem;
        }

/* Styling untuk Tombol */
.view-detail-btn {
    background-color: #001f3f;
    border: none;
    color: #fff;
    border-radius: 8px;
    font-size: 1rem;
    padding: 8px 15px;
    transition: background-color 0.3s ease, transform 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    margin-left: 30px;
}

.view-detail-btn:hover {
    background-color: #003366;
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

/* Text Centering dan Warna */
.text-center {
    color: #001f3f;
    font-weight: bold;
}

/* Styling tombol Hapus */
.delete-btn {
    background-color: #dc3545;
    color: #fff;
    border-radius: 8px;
    padding: 8px 15px;
    font-size: 0.9rem;
    transition: background-color 0.3s ease;
 
    margin-left: 10px;
}

.delete-btn:hover {
    background-color: #c82333;
    transform: translateY(-2px);

}

.button-box {
    display: flex;
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
                    <li class="nav-item"><a class="nav-link" href="upload_barang.php">Upload Barang</a></li>
                    <li class="nav-item"><a class="nav-link" href="history.php">History</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
    <h2 class="text-center mb-5">Toko Saya</h2>

    <div class="row">
    <?php if (count($products) > 0): ?>
        <?php foreach ($products as $product): ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <img src="<?php echo htmlspecialchars($product['gambar']); ?>" class="card-img-top rounded-top" alt="Product Image">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($product['nama_barang']); ?></h5>
                        <p class="card-price">Rp <?php echo number_format($product['harga'], 0, ',', '.'); ?></p>
                        <p class="card-stock">Stok: <?php echo $product['stok']; ?></p>
                        <p class="card-text">Lokasi: <?php echo htmlspecialchars($product['lokasi'] ?? 'Tidak diketahui'); ?></p>
                        <div class="button-box">
                            <!-- Tombol Lihat Detail -->
                            <a href="detailproduk.php?id=<?php echo $product['id']; ?>" class="btn btn-primary view-detail-btn">
                                <i data-feather="eye"></i> Lihat Detail
                            </a>
                            
                            <!-- Tombol Hapus Produk -->
                            <a href="delete_product.php?id=<?php echo $product['id']; ?>" class="btn btn-danger delete-btn" onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?');">
                                <i data-feather="trash-2"></i> Hapus
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-center">Anda belum mengupload produk.</p>
    <?php endif; ?>
</div>
<script>
        <?php if (isset($uploadSuccessMessage)): ?>
            Toastify({
                text: "<?php echo $uploadSuccessMessage; ?>",
                duration: 3000, // Durasi dalam milidetik
                gravity: "top", // "top" atau "bottom"
                position: 'right', // "left", "center", atau "right"
                backgroundColor: "#4CAF50", // Warna latar belakang
                stopOnFocus: true // Hentikan saat fokus
            }).showToast();
        <?php endif; ?>
    </script>
<script>
        feather.replace();
    </script>
</body>
</html>
