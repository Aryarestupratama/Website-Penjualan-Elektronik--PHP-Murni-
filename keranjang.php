<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Ambil data barang yang ada di keranjang
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT c.id AS cart_id, p.id AS product_id, p.nama_barang, p.harga, p.gambar, c.quantity
                       FROM cart c
                       JOIN products p ON c.product_id = p.id
                       WHERE c.user_id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$cart_items = $stmt->fetchAll();

// Hitung total harga
$total_harga = 0;
foreach ($cart_items as $item) {
    $total_harga += $item['harga'] * $item['quantity'];
}

// Proses hapus barang dari keranjang
if (isset($_GET['hapus'])) {
    $cart_id = $_GET['hapus'];
    $stmt = $pdo->prepare("DELETE FROM cart WHERE id = :cart_id");
    $stmt->execute(['cart_id' => $cart_id]);
    header('Location: keranjang.php');
    exit;
}

// Proses checkout
if (isset($_POST['checkout'])) {
    header('Location: checkout.php');
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'], $_POST['quantity'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $user_id = $_SESSION['user_id'];

    // Tambahkan produk ke keranjang
    $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)
                           ON DUPLICATE KEY UPDATE quantity = quantity + :quantity");
    $stmt->execute(['user_id' => $user_id, 'product_id' => $product_id, 'quantity' => $quantity]);

    header('Location: keranjang.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - E-lektronik</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/feather-icons"></script>
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

        /* Container utama */
        .container {
            background-color: #f5f8fa;
            padding: 2rem;
            border-radius: 12px;
            margin-top:100px;
        }

        /* Judul Keranjang Belanja */
        .cart-container h2 {
            color: #001f3f;
            font-size: 2.2rem;
            text-shadow: 2px 2px 6px rgba(0, 0, 0, 0.2);
            margin-bottom: 1rem;
            text-align: center;
            
        }

        .cart-icon {
            color: #001f3f;
            font-size: 2.5rem;
            margin-right: 0.5rem;
            vertical-align: middle;
        }

        /* Card Produk */
        .card {
            
            background: #ffffff;
            border: none;
            border-radius: 15px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 25px rgba(0, 0, 0, 0.2);
        }

        /* Gambar Produk */
        .card-img-top {
            border-radius: 12px;
            transition: transform 0.3s ease;
        }

        .card:hover .card-img-top {
            transform: scale(1.05);
        }

        /* Judul Produk */
        .card-title {
            font-size: 1.3rem;
            color: #001f3f;
            font-weight: bold;
        }

        /* Harga dan Jumlah Produk */
        .card-text {
            color: #4c4c4c;
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }

        /* Tombol Hapus */
        .btn-danger {
            background-color: #ff4c4c;
            border: none;
            color: #ffffff;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            transition: background-color 0.3s ease, transform 0.3s ease;
            margin-left: 110px;
        }

        .btn-danger:hover {
            background-color: #cc0000;
            transform: translateY(-2px);
        }

        /* Total Harga dan Checkout */
        h4 {
            color: #001f3f;
            font-size: 1.5rem;
            margin-top: 1rem;
        }

        /* Tombol Checkout */
        .btn-success {
            background-color: #001f3f;
            border: none;
            color: #ffffff;
            padding: 0.7rem 2rem;
            font-size: 1.2rem;
            border-radius: 10px;
            transition: background-color 0.3s ease, transform 0.3s ease;
            margin-top: 1rem;
        }

        .btn-success:hover {
            background-color: #003366;
            transform: translateY(-2px);
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
        }

        /* Pesan Keranjang Kosong */
        .container p {
            color: #001f3f;
            font-size: 1.2rem;
            text-align: center;
            margin-top: 2rem;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <a class="navbar-brand" href="dashboard.php">E-lektronik</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ml-auto">
            <li class="nav-item"><a class="nav-link" href="proses.php">Proses</a></li>
            <li class="nav-item"><a class="nav-link" href="upload_barang.php">Upload Barang</a></li>
            <li class="nav-item"><a class="nav-link" href="toko_saya.php">Toko Saya</a></li>
            <li class="nav-item"><a class="nav-link" href="history.php">History</a></li>
            <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>


    <div class="container">
        <div class="cart-container">           
            <h2>Keranjang Belanja</h2>
        </div>
        

        <?php if (count($cart_items) > 0): ?>
            <div class="row">
                <?php foreach ($cart_items as $item): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <img src="<?php echo htmlspecialchars($item['gambar']); ?>" class="card-img-top" alt="Product Image">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($item['nama_barang']); ?></h5>
                                <p class="card-text">Harga: Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?></p>
                                <p class="card-text">Jumlah: <?php echo $item['quantity']; ?></p>
                                <a href="keranjang.php?hapus=<?php echo $item['cart_id']; ?>" class="btn btn-danger">Hapus</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <h4>Total Harga: Rp <?php echo number_format($total_harga, 0, ',', '.'); ?></h4>
                    <form method="POST">
                        <button type="submit" name="checkout" class="btn btn-success">Checkout</button>
                    </form>
                </div>
            </div>

        <?php else: ?>
            <p>Keranjang Anda kosong.</p>
        <?php endif; ?>
    </div>
    <script>
        feather.replace();
    </script>
</body>
</html>
