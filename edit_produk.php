<?php
require_once 'config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

if (!isset($_GET['kode'])) {
    redirect('admin.php');
}

$kode = $_GET['kode'];

// Ambil data produk
$query = $db->prepare("SELECT * FROM produk WHERE kode = ?");
$query->bind_param("s", $kode);
$query->execute();
$produk = $query->get_result()->fetch_assoc();

if (!$produk) {
    redirect('admin.php');
}

// Ambil data kategori
$kategori_query = $db->query("SELECT * FROM kategori");
$kategori = [];
while ($row = $kategori_query->fetch_assoc()) {
    $kategori[$row['id']] = $row;
}

// Proses update produk
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $kategori_id = $_POST['kategori_id'];
    $harga = $_POST['harga'];
    
    $stmt = $db->prepare("UPDATE produk SET nama = ?, kategori_id = ?, harga = ? WHERE kode = ?");
    $stmt->bind_param("sdis", $nama, $kategori_id, $harga, $kode);
    
    if ($stmt->execute()) {
        redirect('admin.php');
    } else {
        $error = "Gagal mengupdate produk. Silakan coba lagi.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk - Kadaharan MANTEP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        header {
            background-color: #ff5722;
            color: white;
            padding: 15px 0;
            text-align: center;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        nav {
            background-color: #333;
            color: white;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
        }
        nav ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
            display: flex;
        }
        nav ul li {
            margin-right: 20px;
        }
        nav ul li a {
            color: white;
            text-decoration: none;
        }
        .logout-btn {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
        }
        .logout-btn:hover {
            background-color: #d32f2f;
        }
        .edit-produk {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .edit-produk h2 {
            margin-top: 0;
            color: #ff5722;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 3px;
            box-sizing: border-box;
        }
        .btn {
            background-color: #ff5722;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 3px;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #e64a19;
        }
        .btn-cancel {
            background-color: #757575;
        }
        .btn-cancel:hover {
            background-color: #616161;
        }
        .error {
            color: red;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Edit Produk - Kadaharan MANTEP</h1>
        </header>
        
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="admin.php">Admin</a></li>
            </ul>
            <a href="logout.php" class="logout-btn">Logout</a>
        </nav>
        
        <div class="edit-produk">
            <h2>Edit Produk: <?php echo htmlspecialchars($produk['nama']); ?></h2>
            
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="kode">Kode Produk</label>
                    <input type="text" id="kode" value="<?php echo $produk['kode']; ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="nama">Nama Produk</label>
                    <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($produk['nama']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="kategori_id">Kategori</label>
                    <select id="kategori_id" name="kategori_id" required>
                        <?php foreach ($kategori as $id => $kat): ?>
                            <option value="<?php echo $id; ?>" <?php echo $id == $produk['kategori_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($kat['nama']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="harga">Harga</label>
                    <input type="number" id="harga" name="harga" value="<?php echo $produk['harga']; ?>" required>
                </div>
                <button type="submit" class="btn">Simpan Perubahan</button>
                <a href="admin.php" class="btn btn-cancel">Batal</a>
            </form>
        </div>
    </div>
</body>
</html>