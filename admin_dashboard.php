<?php
session_start();
include 'config.php'; // Menghubungkan ke database

// Cek apakah pengguna adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Menghapus akun pengguna
if (isset($_GET['delete_user'])) {
    $user_id = $_GET['delete_user'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute(['id' => $user_id]);
    $message = "Akun pengguna berhasil dihapus!";
}

// Menghapus produk
if (isset($_GET['delete_product'])) {
    $product_id = $_GET['delete_product'];
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
    $stmt->execute(['id' => $product_id]);
    $message = "Produk berhasil dihapus!";
}

// Mendapatkan daftar pengguna
$users = $pdo->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);

// Mendapatkan daftar produk
$products = $pdo->query("SELECT * FROM products")->fetchAll(PDO::FETCH_ASSOC);

// Mendapatkan daftar transaksi
$transactions = $pdo->query("SELECT * FROM transactions")->fetchAll(PDO::FETCH_ASSOC);

// Mendapatkan daftar riwayat transaksi dengan nama pengguna
$transaction_history = $pdo->query("
    SELECT th.*, u.username 
    FROM transaction_history AS th
    JOIN users AS u ON th.user_id = u.id
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- SweetAlert CSS & JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@10/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            background-color: #001f3f;
            color: white;
            position: fixed;
            height: 100vh;
            width: 250px;
            padding-top: 20px;
            transition: all 0.3s ease;
        }
        .sidebar a {
            color: #ffffff;
            display: block;
            padding: 15px 20px;
            text-decoration: none;
            font-size: 1.1rem;
            transition: background-color 0.2s ease;
        }
        .sidebar a:hover {
            background-color: #004080;
            color: white;
            text-decoration: none;
        }
        .content {
            margin-left: 250px;
            padding: 20px;
        }
        .table {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .btn-delete:hover {
            background-color: #ff5c5c;
            color: white;
        }
        .table th, .table td {
            vertical-align: middle;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <a href="#" onclick="showSection('users')">Daftar Pengguna</a>
    <a href="#" onclick="showSection('products')">Daftar Produk</a>
    <a href="#" onclick="showSection('transactions')">Riwayat Transaksi</a>
    <a href="logout.php">Logout</a>
</div>

<div class="content">
    <h1 class="text-center mb-5">Admin Dashboard</h1>

    <div id="users-section" class="section" style="display: none;">
        <h2>Daftar Pengguna</h2>
        <table class="table table-hover table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user) : ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo $user['username']; ?></td>
                        <td><?php echo $user['email']; ?></td>
                        <td>
                            <button class="btn btn-danger btn-delete" onclick="confirmDelete('user', <?php echo $user['id']; ?>)">Hapus</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div id="products-section" class="section" style="display: none;">
        <h2>Daftar Produk</h2>
        <table class="table table-hover table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Nama Barang</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product) : ?>
                    <tr>
                        <td><?php echo $product['id']; ?></td>
                        <td><?php echo $product['nama_barang']; ?></td>
                        <td>Rp <?php echo number_format($product['harga'], 0, ',', '.'); ?></td>
                        <td><?php echo $product['stok']; ?></td>
                        <td>
                            <button class="btn btn-danger btn-delete" onclick="confirmDelete('product', <?php echo $product['id']; ?>)">Hapus</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div id="transactions-section" class="section" style="display: none;">
        <h2>Riwayat Transaksi</h2>
        <table class="table table-hover table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Nama User</th>
                    <th>Total Harga</th>
                    <th>Alamat</th>
                    <th>Metode Pembayaran</th>
                    <th>Jenis Pengiriman</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transaction_history as $history) : ?>
                    <tr>
                        <td><?php echo $history['id']; ?></td>
                        <td><?php echo $history['username']; ?></td>
                        <td>Rp <?php echo number_format($history['total_harga'], 0, ',', '.'); ?></td>
                        <td><?php echo htmlspecialchars($history['alamat']); ?></td>
                        <td><?php echo htmlspecialchars($history['metode_pembayaran']); ?></td>
                        <td><?php echo htmlspecialchars($history['jenis_pengiriman']); ?></td>
                        <td><?php echo $history['created_at']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- SweetAlert and JavaScript for Delete Confirmation -->
<script>
    function confirmDelete(type, id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data yang telah dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `?delete_${type}=${id}`;
            }
        });
    }

    function showSection(section) {
        // Hide all sections
        document.querySelectorAll('.section').forEach(function(div) {
            div.style.display = 'none';
        });
        // Show the selected section
        document.getElementById(section + '-section').style.display = 'block';
    }

    // Show default section on load
    document.addEventListener("DOMContentLoaded", function() {
        showSection('users');
    });
</script>

<!-- Bootstrap & Feather Icons -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/feather-icons"></script>
<script>
    feather.replace();
</script>
</body>
</html>
