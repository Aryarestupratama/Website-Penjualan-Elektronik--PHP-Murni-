<?php
session_start();
include 'config.php'; // Menghubungkan ke database

$successMessage = '';
if (isset($_SESSION['success_message'])) {
    $successMessage = $_SESSION['success_message'];
    unset($_SESSION['success_message']); // Hapus pesan setelah ditampilkan
}

$notification = '';
// Cek apakah ada notifikasi
if (isset($_SESSION['notification'])) {
    echo '<div class="alert alert-success" role="alert">' . $_SESSION['notification'] . '</div>';
    unset($_SESSION['notification']); // Hapus notifikasi setelah ditampilkan
}


// Menangani pencarian produk
$searchKeyword = '';
if (isset($_GET['search'])) {
    $searchKeyword = $_GET['search'];
    // Query dengan JOIN untuk mendapatkan nama pengunggah
    $stmt = $pdo->prepare("SELECT products.*, users.username AS nama_pengunggah 
                           FROM products 
                           JOIN users ON products.user_id = users.id
                           WHERE deskripsi LIKE :keyword");
    $stmt->execute(['keyword' => "%$searchKeyword%"]);
} else {
    // Mengambil semua produk jika tidak ada pencarian dengan JOIN
    $stmt = $pdo->prepare("SELECT products.*, users.username AS nama_pengunggah 
                           FROM products 
                           JOIN users ON products.user_id = users.id");
    $stmt->execute();
}
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - E-lektronik</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.6.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <link href="https://unpkg.com/feather-icons" rel="stylesheet">
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

        .welcome-section {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh; /* Penuh layar */
            background-color: #ffffff; /* Biru navy */
            color: #fff;
            text-align: center;
        }                                                                               

        /* Tambahkan styling untuk teks */
        .welcome-text {
            font-size: 2rem;
            font-weight: bold;
            line-height: 1.5;
            color: #001f3f;
        }

        .search-form {
            display: flex;
            justify-content: center;
            align-items: center;
            max-width: 100%; /* Ubah max-width menjadi 100% agar form menggunakan lebar penuh */
            margin: 0 auto;
            padding: 0 15px; /* Memberikan padding agar tidak terlalu mepet */
        }

        .search-input {
            flex-grow: 1; /* Membuat input mengisi ruang yang tersedia */
            padding: 12px 15px;
            font-size: 1.2rem; /* Ukuran font lebih besar */
            border: 2px solid #001f3f; /* Border navy */
            border-radius: 20px 0 0 20px;
            outline: none;
            transition: box-shadow 0.3s ease;
        }

        .search-input:focus {
            box-shadow: 0 0 8px rgba(0, 31, 63, 0.5); /* Warna navy */
        }

        .search-button {
            background-color: #001f3f; /* Warna biru navy */
            color: white; /* Warna teks */
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            transition: background-color 0.3s ease, box-shadow 0.3s ease, transform 0.3s ease;
        }

        /* Efek hover */
        .search-button:hover {
            background-color: #003366; /* Warna biru lebih terang */
            box-shadow: 0px 8px 15px rgba(0, 51, 102, 0.3); /* Menambah bayangan */
            transform: translateY(-3px); /* Mengangkat tombol sedikit */
            color: white; /* Tetap putih */
            text-shadow: 0px 0px 8px rgba(255, 255, 255, 0.8);
        }

        /* Efek aktif (saat ditekan) */
        .search-button:active {
            background-color: #00284d; /* Warna sedikit lebih gelap */
            box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.2); /* Bayangan sedikit berkurang */
            transform: translateY(-1px); /* Menurunkan sedikit */
        }

        /* Tombol Utama */
        .btn-container {
            display: flex;
            justify-content: space-between; /* Meratakan tombol di bagian bawah */
            gap: 10px; /* Menambahkan jarak antara tombol */
        }

        
        /* Gaya dasar untuk tombol .btn-primary dan .btn-success */
        .btn-primary, .btn-success {
            background-color: #002d5c; /* Warna biru navy utama */
            transition: color 0.3s ease, background-color 0.3s ease, box-shadow 0.3s ease, transform 0.3s ease;
            width: 100%; /* Menjaga lebar tombol */
            text-align: center;
            padding: 12px; /* Ruang yang lebih nyaman dalam tombol */
            font-weight: bold;
            border-radius: 8px; /* Sudut yang lebih halus */
            color: #ffffff;
        }

        /* Efek hover untuk tombol .btn-primary */
        .btn-primary:hover {
            background-color: #001f47; /* Biru navy yang lebih gelap */
            color: #ffffff;
            box-shadow: 0 8px 16px rgba(0, 45, 92, 0.4), 0 0 6px rgba(255, 255, 255, 0.5); /* Shadow lembut dan glowing */
            text-shadow: 0 0 6px rgba(255, 255, 255, 0.7); /* Glowing pada teks */
            transform: scale(1.07); /* Sedikit lebih besar saat di-hover */
        }

        /* Efek hover untuk tombol .btn-success */
        .btn-success:hover {
            background-color: #003366; /* Biru navy yang sedikit lebih gelap */
            color: #ffffff;
            box-shadow: 0 8px 16px rgba(0, 51, 102, 0.4), 0 0 6px rgba(255, 255, 255, 0.5); /* Shadow lembut dan glowing */
            text-shadow: 0 0 6px rgba(255, 255, 255, 0.7); /* Glowing pada teks */
            transform: scale(1.07); /* Sedikit lebih besar saat di-hover */
        }

        /* Gaya untuk tombol .btn-secondary */
        .btn-secondary {
            background-color: #606060; /* Abu-abu untuk tampilan netral */
            color: #ffffff;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .btn-secondary:hover {
            background-color: #505050; /* Lebih gelap saat hover */
            color: #ffffff;
            transform: scale(1.05); /* Sedikit membesar saat hover */
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

        .fi-rr-shopping-cart-add{
            font-size: 25px;
        }

        

        /* Modal */
        .modal-content {
            border-radius: 10px;
        }

        .modal-header {
            background-color: #001f3f;
            color: #fff;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }

        .modal-footer .btn-primary {
            background-color: #003366;
            color: #fff;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .modal-footer .btn-primary:hover {
            background-color: #001f3f;
            transform: translateY(-2px);
        }

        /* Input Form */
        .form-control {
            border: 1px solid #ddd;
            border-radius: 5px;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            border-color: #003366;
            box-shadow: 0 0 5px rgba(0, 51, 102, 0.5);
        }

        /* Card Harga dan Detail */
        .card-text {
            color: #555;
            font-size: 0.9rem;
        }

        /* Link aktif atau tombol pencarian */
        .form-inline .btn-primary {
            background-color: #003366;
            color: #fff;
            transition: background-color 0.3s ease;
        }

        .form-inline .btn-primary:hover {
            background-color: #001f3f;
        }

        /* Animasi Feather Icons */
        .feather {
            transition: stroke 0.3s ease;
        }

        .feather:hover {
            stroke: #f1c40f;
        }

        /* Footer */
        .footer {
            background-color: #001f3f;
            color: #ffffff;
            padding: 15px;
            text-align: center;
            box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.1);
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <a class="navbar-brand" href="dashboard.php">E-lektronik</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ml-auto">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Jika pengguna sudah login -->
                    <li class="nav-item"><a class="nav-link" href="keranjang.php">Keranjang</a></li>
                    <li class="nav-item"><a class="nav-link" href="proses.php">Proses</a></li>
                    <li class="nav-item"><a class="nav-link" href="upload_barang.php">Upload Barang</a></li>
                    <li class="nav-item"><a class="nav-link" href="toko_saya.php">Toko Saya</a></li>
                    <li class="nav-item"><a class="nav-link" href="history.php">History</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                <?php else: ?>
                    <!-- Jika pengguna belum login -->
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="register.php">Daftar</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <div class="container mt-5">
        <section class="welcome-section">
            <h2 class="welcome-text">
                <span id="typed-text"></span>
            </h2>
        </section>

        <!-- Search bar -->
        <form method="GET" class="form-inline mb-4 search-form">
        <input 
            type="text" 
            class="form-control search-input" 
            name="search" 
            id="search-input"
            placeholder="Cari produk elektronik..." 
            value="<?php echo htmlspecialchars($searchKeyword); ?>"
        >
        <button type="submit" class="btn search-button">Cari</button>
    </form> 
        <!-- Product Cards -->
<div class="row">
    <?php if (count($products) > 0): ?>
        <?php foreach ($products as $product): ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <a href="detailproduk.php?id=<?php echo $product['id']; ?>" style="text-decoration: none; color: inherit;">
                    <img src="<?php echo htmlspecialchars($product['gambar']); ?>" class="card-img-top" alt="Product Image">
                    <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($product['nama_barang']); ?></h5>
                            <p class="card-price">Rp <?php echo number_format($product['harga'], 0, ',', '.'); ?></p>
                            <p class="card-text">Stok: <?php echo $product['stok']; ?></p>
                             <p class="card-text">Lokasi: <?php echo htmlspecialchars($product['lokasi']); ?></p>
                            <p class="card-text"><strong>Diunggah oleh:</strong> <?php echo htmlspecialchars($product['nama_pengunggah']); ?></p> <!-- Nama Pengunggah -->
                        </div>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Produk tidak ditemukan.</p>
    <?php endif; ?>
</div>


    <div class="modal fade" id="addCartModal" tabindex="-1" aria-labelledby="addCartModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addToCartForm" method="POST" action="keranjang.php">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addCartModalLabel">Tambah ke Keranjang</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="product_id" name="product_id">
                        <label for="quantity">Jumlah:</label>
                        <input type="number" id="quantity" name="quantity" class="form-control" value="1" min="1" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Tambahkan ke Keranjang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Footer -->
    <div class="footer">
        <p>&copy; 2024 E-lektronik | Semua hak cipta dilindungi</p>
    </div>

    <!-- Feather Icons -->
    <script src="https://unpkg.com/feather-icons"></script>
    <script src="https://cdn.jsdelivr.net/npm/typed.js@2.0.12"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script>
        feather.replace(); // Mengganti semua ikon dengan Feather Icons
    </script>
    <script>
    function showAddToCartModal(productId) {
        document.getElementById('product_id').value = productId;
        $('#addCartModal').modal('show');
    }
    </script> 
   <script>
        // Menampilkan SweetAlert jika ada pesan sukses
        <?php if ($successMessage): ?>
            swal({
                title: 'Login Berhasil!',
                text: '<?php echo addslashes($successMessage); ?>',
                icon: 'success',
                button: 'OK',
            });
        <?php endif; ?>

        // Menampilkan notifikasi menggunakan Toastify jika ada notifikasi
        <?php if (isset($_SESSION['notification'])): ?>
             swal({
            title: 'Barang berhasil ditambahkan ke keranjang',
            text: '<?php echo addslashes($successMessage); ?>',
            icon: 'success',
            buttons: false, // Menghilangkan tombol
            timer: 3000 // Durasi tampil dalam milidetik (3 detik)
        });
            <?php unset($_SESSION['notification']); // Hapus notifikasi setelah ditampilkan ?>
        <?php endif; ?>
    </script>
    <script>
        setTimeout(function() {
        // Inisialisasi Typed.js
        var typed = new Typed('#typed-text', {
            strings: ["Selamat datang di website E-lektronik", "Temukan produk elektronik terbaik di sini"],
            typeSpeed: 50,  // Kecepatan mengetik
            backSpeed: 30,  // Kecepatan menghapus
            loop: true      // Loop animasi
        });
         }, 1000);
    </script>
    <script>
        setTimeout(function() {
        // Typed.js untuk animasi placeholder
        var typed = new Typed('#search-input', {
            strings: ["Cari produk elektronik...", "Temukan gadget impian Anda...", "Cari TV, laptop, dan banyak lagi..."],
            typeSpeed: 50,
            backSpeed: 30,
            attr: 'placeholder',
            loop: true
        });
         }, 1000);
    </script> 
    
</body>
</html>
