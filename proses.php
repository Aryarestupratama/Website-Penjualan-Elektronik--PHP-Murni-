<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Inisialisasi $transaction_details sebagai array kosong
$transaction_details = [];

// Query untuk mengambil transaksi user
$stmt = $pdo->prepare("
    SELECT t.id, t.total_harga, t.alamat, t.metode_pembayaran, t.jenis_pengiriman, t.created_at
    FROM transactions t
    WHERE t.user_id = :user_id
");
$stmt->execute(['user_id' => $user_id]);
$transaction_details = $stmt->fetchAll();

// Jika tidak ada hasil, pastikan $transaction_details adalah array kosong
if (!$transaction_details) {
    $transaction_details = [];
}

// Ambil semua transaksi untuk user ini
if (isset($_POST['delete_transaction'])) {
    $transaction_id = $_POST['transaction_id'];

    // Mulai transaksi
    $pdo->beginTransaction();
    try {
        // Ambil data transaksi dan items
        $stmt_transaction = $pdo->prepare("SELECT * FROM transactions WHERE id = :transaction_id");
        $stmt_transaction->execute(['transaction_id' => $transaction_id]);
        $transaction = $stmt_transaction->fetch();

        $stmt_items = $pdo->prepare("SELECT * FROM transaction_items WHERE transaction_id = :transaction_id");
        $stmt_items->execute(['transaction_id' => $transaction_id]);
        $items = $stmt_items->fetchAll();

        // Insert data transaksi ke transaction_history
        $stmt_history = $pdo->prepare("
            INSERT INTO transaction_history (user_id, total_harga, alamat, metode_pembayaran, jenis_pengiriman, created_at)
            VALUES (:user_id, :total_harga, :alamat, :metode_pembayaran, :jenis_pengiriman, :created_at)
        ");
        $stmt_history->execute([
            'user_id' => $transaction['user_id'],
            'total_harga' => $transaction['total_harga'],
            'alamat' => $transaction['alamat'],
            'metode_pembayaran' => $transaction['metode_pembayaran'],
            'jenis_pengiriman' => $transaction['jenis_pengiriman'],
            'created_at' => $transaction['created_at']
        ]);

        // Ambil ID transaksi baru di transaction_history
        $history_id = $pdo->lastInsertId();

        // Insert setiap item ke transaction_history_items
        $stmt_history_item = $pdo->prepare("
            INSERT INTO transaction_history_items (transaction_history_id, product_id, quantity, harga, total_harga)
            VALUES (:transaction_history_id, :product_id, :quantity, :harga, :total_harga)
        ");
        foreach ($items as $item) {
            $stmt_history_item->execute([
                'transaction_history_id' => $history_id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'harga' => $item['harga'],
                'total_harga' => $item['total_harga']
            ]);
        }

        // Hapus detail barang dari transaction_items
        $stmt_delete_items = $pdo->prepare("DELETE FROM transaction_items WHERE transaction_id = :transaction_id");
        $stmt_delete_items->execute(['transaction_id' => $transaction_id]);

        // Hapus transaksi dari tabel transactions
        $stmt_delete_transaction = $pdo->prepare("DELETE FROM transactions WHERE id = :transaction_id");
        $stmt_delete_transaction->execute(['transaction_id' => $transaction_id]);

        // Commit transaksi
        $pdo->commit();
        
        // Set session untuk menampilkan notifikasi
        $_SESSION['notification'] = "Terima kasih telah berbelanja di web kami. Transaksi Anda telah dipindahkan ke riwayat.";
        header('Location: proses.php');
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proses - E-lektronik</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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
    max-width: 800px;
    background-color: #ffffff;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    margin-top: 100px;
}

/* Judul utama */
h2.text-center {
    font-size: 2rem;
    color: #001f3f;
    font-weight: bold;
}

/* Transaction Card */
.transaction-card {
    background-color: #f9f9f9;
    border-radius: 8px;
    box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
    padding: 1.5rem;
    margin-bottom: 1rem;
}

/* Judul Transaksi */
.transaction-title {
    font-size: 1.4rem;
    color: #001f3f;
    font-weight: bold;
    margin-bottom: 1rem;
}

/* Detail Barang */
.item-title {
    font-size: 1.2rem;
    color: #001f3f;
    font-weight: bold;
    margin-top: 1.5rem;
}

/* Daftar Barang */
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

/* Jika Tidak Ada Barang */
.no-items {
    font-size: 1rem;
    color: #666;
}

/* Divider */
.transaction-divider {
    border: none;
    border-top: 1px solid #001f3f;
    margin: 2rem 0;
}

/* Styling Tombol Selesai */
.btn-finish {
    background-color: #d9534f; /* Warna merah untuk tampilan peringatan */
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 10px 20px;
    font-size: 1rem;
    font-weight: bold;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: background-color 0.3s ease, transform 0.3s ease, box-shadow 0.3s ease;
}

.btn-finish i {
    margin-right: 5px;
}

.btn-finish:hover {
    background-color: #c9302c;
    box-shadow: 0 8px 20px rgba(217, 83, 79, 0.4);
    transform: translateY(-3px);
}

.btn-finish:active {
    transform: translateY(0);
    box-shadow: none;
}


/* Responsif */
@media (max-width: 576px) {
    .container {
        padding: 1.5rem;
    }
    .transaction-card {
        padding: 1rem;
    }
    h2.text-center {
        font-size: 1.6rem;
    }
    .transaction-title {
        font-size: 1.2rem;
    }
    .item-title {
        font-size: 1rem;
    }
}

    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <a class="navbar-brand" href="dashboard.php">E-lektronik</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ml-auto">               
                <li class="nav-item"><a class="nav-link" href="keranjang.php">Keranjang</a></li>
                <li class="nav-item"><a class="nav-link" href="upload_barang.php">Upload Barang</a></li>
                <li class="nav-item"><a class="nav-link" href="toko_saya.php">Toko Saya</a></li>
                <li class="nav-item"><a class="nav-link" href="history.php">History</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>
    <div class="container">
        <h2 class="text-center text-primary mb-4">Transaksi</h2>
        
        <?php if (count($transaction_details) > 0): ?>
            <div class="transaction-list">
                <?php foreach ($transaction_details as $transaction): ?>
                    <div class="transaction-card">
                        <h3 class="transaction-title">Transaksi</h3>
                        <p><strong>Total Harga:</strong> Rp <?php echo number_format($transaction['total_harga'], 0, ',', '.'); ?></p>
                        <p><strong>Alamat:</strong> <?php echo htmlspecialchars($transaction['alamat']); ?></p>
                        <p><strong>Metode Pembayaran:</strong> <?php echo htmlspecialchars($transaction['metode_pembayaran']); ?></p>
                        <p><strong>Jenis Pengiriman:</strong> <?php echo htmlspecialchars($transaction['jenis_pengiriman']); ?></p>
                        <p><strong>Tanggal Transaksi:</strong> <?php echo $transaction['created_at']; ?></p>

                        <h4 class="item-title">Detail Barang</h4>
                        <?php
                        $transaction_id = $transaction['id'];
                         $stmt_detail = $pdo->prepare("
                            SELECT ti.*, p.nama_barang 
                            FROM transaction_items ti 
                            JOIN products p ON ti.product_id = p.id 
                            WHERE ti.transaction_id = :transaction_id
                        ");
                        $stmt_detail->execute(['transaction_id' => $transaction_id]);
                        $transaction_items = $stmt_detail->fetchAll();

                        if ($transaction_items === false) {
                            $transaction_items = []; // Jika tidak ada hasil, set menjadi array kosong
                        }

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
                            <p class="no-items">Belum ada barang dalam transaksi ini.</p>
                        <?php endif; ?>

                        <form method="post" action="">
                            <input type="hidden" name="transaction_id" value="<?php echo $transaction['id']; ?>">
                            <button type="submit" name="delete_transaction" class="btn btn-finish">Selesai</button>
                        </form>
                    </div>
                    <hr class="transaction-divider">
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-center">Anda belum melakukan transaksi apapun.</p>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            <?php if (isset($_SESSION['notification'])): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Sukses!',
                    text: '<?php echo $_SESSION['notification']; ?>',
                    confirmButtonText: 'OK'
                });
                <?php unset($_SESSION['notification']); ?>
            <?php endif; ?>
        });
    </script>
</body>
</html>