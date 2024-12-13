<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$product_id = $_GET['id'];

// Ambil data produk dari database termasuk username dari tabel users
$stmt = $pdo->prepare("
    SELECT p.*, u.username, p.lokasi 
    FROM products p 
    JOIN users u ON p.user_id = u.id 
    WHERE p.id = :id
");
$stmt->execute(['id' => $product_id]);
$product = $stmt->fetch();

if (!$product) {
    echo "Produk tidak ditemukan.";
    exit;
}

// Proses menambah ke keranjang
if (isset($_POST['add_to_cart'])) {
    $quantity = $_POST['quantity'];
    $user_id = $_SESSION['user_id'];
    $product_id = $_POST['product_id']; // Ambil product_id dari formulir

    // Cek apakah produk sudah ada di keranjang
    $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = :user_id AND product_id = :product_id");
    $stmt->execute(['user_id' => $user_id, 'product_id' => $product_id]);
    $existing_item = $stmt->fetch();

    
    if ($existing_item) {
        // Jika produk sudah ada, update jumlahnya
        $new_quantity = $existing_item['quantity'] + $quantity;
        $stmt = $pdo->prepare("UPDATE cart SET quantity = :quantity WHERE id = :id");
        $stmt->execute(['quantity' => $new_quantity, 'id' => $existing_item['id']]);
    } else {
        // Jika produk belum ada, insert ke tabel cart
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)");
        $stmt->execute(['user_id' => $user_id, 'product_id' => $product_id, 'quantity' => $quantity]);
    }

    $_SESSION['notification'] = "Produk telah ditambahkan ke keranjang.";
    header('Location: dashboard.php');
    exit;
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Produk - E-lektronik</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/feather-icons"></script>
    <style>
        body {
            padding-top: 70px; /* Sesuaikan dengan tinggi navbar Anda */
        }
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

        /* Styling untuk card produk */
/* Navbar */
.navbar {
    background-color: #001f3f; /* Navy blue */
    position: fixed;
    width: 100%;
    top: 0;
    z-index: 1000;
}

.navbar-brand {
    color: #ffffff;
    font-weight: bold;
    font-size: 1.8rem;
    transition: color 0.3s ease, transform 0.3s ease, text-shadow 0.3s ease;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.navbar-brand:hover {
    color: #ffffff;
    text-shadow: 0 0 8px rgba(255, 255, 255, 0.8), 0 0 15px rgba(255, 255, 255, 0.5);
    transform: scale(1.05);
}

.nav-link {
    color: #ffffff;
    transition: color 0.3s ease;
}

.nav-link:hover {
    color: #ffffff; /* Keep white */
    text-shadow: 0 0 10px rgba(255, 255, 255, 0.8); /* Glowing effect */
}

/* Styling for cards (products) */
.card {
    background: linear-gradient(145deg, #ffffff, #f5f5f5); /* Light gray gradient */
    border-radius: 12px;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    margin-top: 70px;
    border: 1px solid #ddd; /* Soft border for separation */
    max-width: 700px;
    margin-left: 200px;
}

.card:hover {
    transform: translateY(-10px); /* Lift effect */
    box-shadow: 0 20px 30px rgba(0, 0, 0, 0.15); /* More prominent shadow */
}

/* Styling untuk nama produk */
.product-name {
    font-size: 2.5rem;   /* Ukuran font lebih besar */
    font-weight: 700;    /* Ketebalan font agar lebih tebal */
    color: #001f3f;      /* Warna biru navy yang digunakan di website */
    text-shadow: 2px 2px 6px rgba(0, 0, 0, 0.2); /* Efek bayangan pada teks untuk kesan depth */
    margin-top: 20px;
    letter-spacing: 5px;
    text-align: left;
}

.card img {
    border-radius: 8px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
    width: 100%;
}

.card img:hover {
    transform: scale(1.05); /* Slight zoom on image */
}

/* Styling for prices */
.price {
    font-size: 2rem;
    font-weight: 700;
    color: red; /* Navy color for price */
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    font-family: 'Roboto Slab', serif;
}

/* Styling untuk stok produk */
.stock {
    font-size: 1.2rem;  /* Ukuran font lebih besar */
    color: #6c757d;     /* Warna abu-abu lebih terang */
    font-weight: 600;   /* Menebalkan teks agar lebih jelas */
    
}

/* Styling untuk deskripsi produk */
.description {
    font-size: 1.1rem;  /* Ukuran font lebih besar */
    color: #000;     /* Warna abu-abu terang */
    line-height: 1.6;    /* Menambah jarak antar baris agar lebih mudah dibaca */
}



/* Styling for modal */
.modal-content {
    border-radius: 15px;
    background: linear-gradient(135deg, #ffffff, #f1f1f1); /* Subtle gradient */
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.modal-header {
    border-bottom: none;
}

.modal-body {
    padding: 25px;
}

.modal-footer {
    border-top: none;
}

/* Styling input fields */
input[type="number"] {
    border-radius: 8px;
    box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.2);
    padding: 10px;
    font-size: 1rem;
}

/* Styling for buttons */
.btn-primary {
    background-color: #001f3f; /* Navy blue */
    border-color: #001f3f;
    border-radius: 8px;
    padding: 12px 24px;
    font-size: 1.2rem;
    transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.2s ease;
}

.btn-primary:hover {
    background-color: #002b5c; /* Slightly lighter navy blue */
    border-color: #002b5c;
    transform: scale(1.05); /* Slight scale effect on hover */
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15); /* Subtle shadow effect */
}

/* Additional button styles for modal */
.btn-primary.btn-lg {
    font-size: 1.3rem;
    padding: 15px 30px;
}

/* Modal button style */
.btn-lg {
    background-color: #001f3f; /* Navy for modal button */
    border-color: #001f3f;
}

.btn-lg:hover {
    background-color: #002b5c; /* Darker navy on hover */
    border-color: #002b5c;
}

/* Text shadow effect for all headings */
h2, .modal-title {
    color: #001f3f; /* Navy for headings */
    text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
}

/* Make modal footer button larger */
.modal-footer .btn {
    font-size: 1.1rem;
    padding: 10px 20px;
}

.user-bag {
    display: flex;
    margin-bottom: 20px;
}

.location-bag {
    display: flex;
}

.location-bag .location {
    margin-left: 10px;
}

.user-bag .nameuser {
    margin-left: 10px;
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
                    <li class="nav-item"><a class="nav-link" href="history.php">History</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container mt-5">
        <!-- Card untuk produk -->
        <div class="card shadow-lg border-0 rounded-lg p-4">
                    <img src="<?php echo htmlspecialchars($product['gambar']); ?>" class="img-fluid rounded shadow-sm" alt="Product Image">
                    <h2 class="product-name"><?php echo htmlspecialchars($product['nama_barang']); ?></h2>
                <hr>
                    <p class="price mt-3">Rp <?php echo number_format($product['harga'], 0, ',', '.'); ?></p>
                    <p class="stock">Stok: <?php echo $product['stok']; ?></p>
                <hr>
                    <p style="font-weight: bold; font-size: 1.3rem;margin-top: 20px;">Deskripsi Produk :</p>
                    <p class="description"><?php echo htmlspecialchars($product['deskripsi']); ?></p>
                <hr>
                    <!-- Menambahkan informasi pengunggah dan lokasi -->
                     <div class="location-bag">
                        <i data-feather="map-pin"></i>
                       <p class="location"><?php echo htmlspecialchars($product['lokasi']); ?></p>
                    </div>
                    
                    <div class="user-bag">
                        <i data-feather="user"></i>
                        <p class="nameuser"><?php echo htmlspecialchars($product['username']); ?></p>
                    </div>
            <!-- Tombol untuk membuka Modal -->
                <button class="btn btn-primary btn-lg" data-toggle="modal" data-target="#addToCartModal" onclick="setProductId(<?php echo $product['id']; ?>, <?php echo $product['stok']; ?>)">
                    <i data-feather="shopping-cart"></i> <!-- Feather icon keranjang -->
                </button>
            
        </div>
    </div>

    <!-- Modal untuk memilih jumlah dan menambahkan ke keranjang -->
    <div class="modal fade" id="addToCartModal" tabindex="-1" role="dialog" aria-labelledby="addToCartModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addToCartModalLabel">Pilih Jumlah</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="POST" id="addToCartForm">
                        <input type="hidden" name="product_id" id="product_id">
                        <div class="form-group">
                            <label for="quantity">Jumlah:</label>
                            <input type="number" name="quantity" id="quantity" class="form-control" min="1" required>
                        </div>
                        <button type="submit" name="add_to_cart" class="btn btn-primary btn-lg btn-block">Tambah ke Keranjang</button>
                        <input type="hidden" name="add_to_cart" value="1">
                    </form>
                </div>
            </div>
        </div>
    </div>


    <script>
        // Set product id and max quantity in the modal
        function setProductId(productId, maxQuantity) {
    document.getElementById('product_id').value = productId; // Set product ID
    document.getElementById('addToCartForm').onsubmit = function(event) {
    let quantity = document.getElementById('quantity').value;
    if (quantity >= 1 && quantity <= maxQuantity) {
        // Proceed with adding the product to the cart
        this.submit(); // Mengirim formulir jika valid
    } else {
        alert('Jumlah tidak valid.');
    }
};
}

        // Function to handle adding the product to the cart (using AJAX or PHP as needed)
        function addToCart(productId, quantity) {
            // Here, you can send the product ID and quantity to your PHP handler (e.g., via AJAX)
            // This example just alerts the action.
            alert('Produk dengan ID ' + productId + ' dan jumlah ' + quantity + ' telah ditambahkan ke keranjang.');

            // Close the modal after adding to the cart
            $('#addToCartModal').modal('hide');
        }
    </script>
    <script>
        feather.replace();
    </script>
    <!-- Menyertakan jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Menyertakan Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
