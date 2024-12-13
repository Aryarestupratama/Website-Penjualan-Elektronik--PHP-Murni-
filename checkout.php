<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Ambil user_id dari session
$user_id = $_SESSION['user_id'];

// Ambil produk yang ada di keranjang
$stmt = $pdo->prepare("SELECT c.id AS cart_id, c.quantity, p.id AS product_id, p.nama_barang, p.harga FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$cart_items = $stmt->fetchAll();

// Cek apakah keranjang kosong
if (count($cart_items) == 0) {
    echo "Keranjang Anda kosong. Silakan tambahkan produk ke keranjang terlebih dahulu.";
    exit;
}

// Proses Checkout
if (isset($_POST['checkout'])) {
    $shipping_method = $_POST['shipping_method'];
    $payment_method = 'cod';  // Metode pembayaran hanya ada "COD"
    $user_address = $_POST['user_address'];

    // Hitung total harga
    $total_price = 0;
    foreach ($cart_items as $item) {
        $total_price += $item['harga'] * $item['quantity'];
    }

    // Tambahkan biaya pengiriman
    switch ($shipping_method) {
        case 'regular':
            $shipping_cost = 5000;
            break;
        case 'sameday':
            $shipping_cost = 10000;
            break;
        case 'instant':
            $shipping_cost = 15000;
            break;
        default:
            $shipping_cost = 0; // Default jika tidak ada metode yang dipilih
            break;
    }

    $total_price += $shipping_cost;

    try {
        // Simpan transaksi ke dalam tabel transactions
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, total_harga, alamat, metode_pembayaran, jenis_pengiriman, created_at) 
                               VALUES (:user_id, :total_price, :user_address, :payment_method, :shipping_method, NOW())");
        $stmt->execute([
            'user_id' => $user_id,
            'total_price' => $total_price,
            'user_address' => $user_address,
            'payment_method' => $payment_method,
            'shipping_method' => $shipping_method
        ]);

        // Ambil ID transaksi yang baru saja disimpan
        $transaction_id = $pdo->lastInsertId();

        // Simpan detail barang ke dalam tabel transaction_items
        $stmt_items = $pdo->prepare("INSERT INTO transaction_items (transaction_id, product_id, quantity, harga, total_harga) 
                                      VALUES (:transaction_id, :product_id, :quantity, :harga, :total_harga)");

        foreach ($cart_items as $item) {
            // Cek apakah product_id ada di tabel products
            $stmt_check_product = $pdo->prepare("SELECT COUNT(*) FROM products WHERE id = :product_id");
            $stmt_check_product->execute(['product_id' => $item['product_id']]);
            $product_exists = $stmt_check_product->fetchColumn();

            if ($product_exists) {
                $subtotal = $item['harga'] * $item['quantity'];
                $stmt_items->execute([
                    'transaction_id' => $transaction_id,
                    'product_id' => $item['product_id'], 'quantity' => $item['quantity'],
                    'harga' => $item['harga'],
                    'total_harga' => $subtotal
                ]);
            } else {
                echo "Produk dengan ID " . $item['product_id'] . " tidak ditemukan. Transaksi tidak dapat dilanjutkan.";
                exit;
            }
        }

        // Hapus produk yang ada di keranjang setelah checkout
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $user_id]);

        // Redirect ke halaman proses
        header('Location: proses.php');
        exit;

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - E-lektronik</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Styling untuk tabel */
.table {
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

/* Styling tombol Pembayaran */
.btn-primary {
    background-color: #001f3f;
    border-color: #001f3f;
    border-radius: 8px;
    padding: 12px 20px;
    font-size: 1.2rem;
    transition: background-color 0.3s ease;
}

.btn-primary:hover {
    background-color: #004080;
    border-color: #003366;
}

/* Hover Effect untuk tombol */
.btn-primary:hover {
    transform: translateY(-2px);
}

    </style>
</head>
<body>

    <div class="container mt-5">
    <h2 class="text-center mb-4">Checkout</h2>

    <form method="POST">
        <div class="row">
            <div class="col-md-8">
                <h3>Daftar Barang</h3>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Nama Barang</th>
                            <th>Harga</th>
                            <th>Jumlah</th>
                            <th>Total Harga</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total_price = 0;
                        foreach ($cart_items as $item):
                            $subtotal = $item['harga'] * $item['quantity'];
                            $total_price += $subtotal;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['nama_barang']); ?></td>
                            <td>Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <h4>Total Harga: Rp <?php echo number_format($total_price, 0, ',', '.'); ?></h4>

                <!-- Formulir Pengiriman dan Pembayaran -->
                <div class="form-group">
                    <label for="user_address">Alamat Pengiriman:</label>
                    <textarea name="user_address" id="user_address" class="form-control" required placeholder="Masukkan alamat pengiriman"></textarea>
                </div>

                <div class="form-group">
                    <label for="shipping_method">Metode Pengiriman:</label>
                    <select name="shipping_method" id="shipping_method" class="form-control" required>
                        <option value="regular">Regular (+Rp 5000)</option>
                        <option value="sameday">Sameday (+Rp 10000)</option>
                        <option value="instant">Instant (+Rp 15000)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="payment_method">Metode Pembayaran:</label>
                    <select name="payment_method" id="payment_method" class="form-control" required>
                        <option value="cod">Cash on Delivery (COD)</option>
                    </select>
                </div>

                <button type="submit" name="checkout" class="btn btn-primary btn-lg w-100">Selesaikan Pembayaran</button>
            </div>
        </div>
    </form>
</div>

</body>
</html>
