<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil semua transaksi selesai dari tabel transaction_history untuk user ini
$stmt = $pdo->prepare("
    SELECT th.id, th.total_harga, th.alamat, th.metode_pembayaran, th.jenis_pengiriman, th.created_at
    FROM transaction_history th
    WHERE th.user_id = :user_id
");
$stmt->execute(['user_id' => $user_id]);
$transaction_history = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi - E-lektronik</title>
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

        /* Gaya CSS khusus untuk halaman */
        .container {
            max-width: 800px;
            background-color: #ffffff;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            margin-top: 100px;
        }

        h2.text-center {
            font-size: 2rem;
            color: #001f3f;
            font-weight: bold;
        }

        .transaction-card {
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            margin-bottom: 1rem;
        }

        .transaction-title {
            font-size: 1.4rem;
            color: #001f3f;
            font-weight: bold;
            margin-bottom: 1rem;
        }

        .item-title {
            font-size: 1.2rem;
            color: #001f3f;
            font-weight: bold;
            margin-top: 1.5rem;
        }

        .item-list {
            list-style-type: none;
            padding-left: 0;
        }

        .item-list li {
            color: #333;
            font-size: 1rem;
            padding: 0.2rem 0;
            border-bottom: 1px dashed #ddd;
        }

        .no-items {
            font-size: 1rem;
            color: #666;
        }

        .transaction-divider {
            border: none;
            border-top: 1px solid #001f3f;
            margin: 2rem 0;
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
                    <li class="nav-item"><a class="nav-link" href="toko_saya.php">Toko Saya</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>
    <div class="container">
        <h2 class="text-center text-primary mb-4">Riwayat Transaksi</h2>
        
        <?php if (count($transaction_history) > 0): ?>
            <div class="transaction-list">
                <?php foreach ($transaction_history as $transaction): ?>
                    <div class="transaction-card">
                        <h3 class="transaction-title">Transaksi Selesai</h3>
                        <p><strong>Total Harga:</strong> Rp <?php echo number_format($transaction['total_harga'], 0, ',', '.'); ?></p>
                        <p><strong>Alamat:</strong> <?php echo htmlspecialchars($transaction['alamat']); ?></p>
                        <p><strong>Metode Pembayaran:</strong> <?php echo htmlspecialchars($transaction['metode_pembayaran']); ?></p>
                        <p><strong>Jenis Pengiriman:</strong> <?php echo htmlspecialchars($transaction['jenis_pengiriman']); ?></p>
                        <p><strong>Tanggal Transaksi:</strong> <?php echo $transaction['created_at']; ?></p>

                        <h4 class="item-title">Detail Barang</h4>
                        <?php
                        // Ambil detail barang dari transaction_history_items
                        $transaction_id = $transaction['id'];
                        $stmt_detail = $pdo->prepare("
                            SELECT thi.*, p.nama_barang 
                            FROM transaction_history_items thi
                            JOIN products p ON thi.product_id = p.id 
                            WHERE thi.transaction_history_id = :transaction_id
                        ");
                        $stmt_detail->execute(['transaction_id' => $transaction_id]);
                        $transaction_items = $stmt_detail->fetchAll();

                        if (count($transaction_items) > 0): ?>
                            <ul class="item-list">
                                <?php foreach ($transaction_items as $item): ?>
                                    <li>
                                        <?php echo htmlspecialchars($item['nama_barang']); ?> - 
                                        Jumlah: <?php echo $item['quantity']; ?> - 
                                        Harga: Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?> - 
                                        Total: Rp <?php echo number_format($item['total_harga'], 0, ',', '.'); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="no-items">Tidak ada barang dalam transaksi ini.</p>
                        <?php endif; ?>
                    </div>
                    <hr class="transaction-divider">
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-center">Anda belum memiliki riwayat transaksi.</p>
        <?php endif; ?>
    </div>
</body>
</html>
