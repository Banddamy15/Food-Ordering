<?php
require_once 'config.php';

// if (!isLoggedIn() || !isAdmin()) {
//     redirect('login.php');
// }

// Ambil data pesanan
$pesanan_query = $db->query("
    SELECT p.*, u.nama as nama_pelanggan 
    FROM pesanan p
    JOIN user u ON p.user_id = u.id
    ORDER BY p.tanggal DESC
");

// Ambil data produk
$produk_query = $db->query("SELECT * FROM produk");
$produk = [];
while ($row = $produk_query->fetch_assoc()) {
    $produk[$row['kode']] = $row;
}

// Ambil data kategori
$kategori_query = $db->query("SELECT * FROM kategori");
$kategori = [];
while ($row = $kategori_query->fetch_assoc()) {
    $kategori[$row['id']] = $row;
}

// Proses update status pesanan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $pesanan_id = $_POST['pesanan_id'];
    $status = $_POST['status'];
    
    $stmt = $db->prepare("UPDATE pesanan SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $pesanan_id);
    $stmt->execute();
    
    redirect('admin.php');
}

// Proses tambah/edit produk
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['tambah_produk']) || isset($_POST['edit_produk']))) {
    $kode = $_POST['kode'];
    $nama = $_POST['nama'];
    $kategori_id = $_POST['kategori_id'];
    $harga = $_POST['harga'];
    
    if (isset($_POST['tambah_produk'])) {
        $stmt = $db->prepare("INSERT INTO produk (kode, nama, kategori_id, harga) VALUES (?, ?, ?, ?)");
    } else {
        $stmt = $db->prepare("UPDATE produk SET nama = ?, kategori_id = ?, harga = ? WHERE kode = ?");
        $stmt->bind_param("sdis", $nama, $kategori_id, $harga, $kode);
    }
    
    $stmt->bind_param("ssdi", $kode, $nama, $kategori_id, $harga);
    $stmt->execute();
    
    redirect('admin.php');
}

// Proses hapus produk
if (isset($_GET['hapus_produk'])) {
    $kode = $_GET['hapus_produk'];
    $stmt = $db->prepare("DELETE FROM produk WHERE kode = ?");
    $stmt->bind_param("s", $kode);
    $stmt->execute();
    
    redirect('admin.php');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Food Ordering</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
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
        .admin-section {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .admin-section h2 {
            margin-top: 0;
            color: #ff5722;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .status-pending {
            color: #ff9800;
            font-weight: bold;
        }
        .status-diproses {
            color: #2196f3;
            font-weight: bold;
        }
        .status-selesai {
            color: #4caf50;
            font-weight: bold;
        }
        .status-dibatalkan {
            color: #f44336;
            font-weight: bold;
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
        .btn-edit {
            background-color: #2196f3;
        }
        .btn-edit:hover {
            background-color: #0b7dda;
        }
        .btn-hapus {
            background-color: #f44336;
        }
        .btn-hapus:hover {
            background-color: #d32f2f;
        }
        .tabs {
            display: flex;
            margin-bottom: 20px;
        }
        .tab {
            padding: 10px 20px;
            background-color: #ddd;
            cursor: pointer;
            margin-right: 5px;
            border-radius: 5px 5px 0 0;
        }
        .tab.active {
            background-color: white;
            font-weight: bold;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Admin Panel - Food Ordering</h1>
        </header>
        
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="admin.php">Admin</a></li>
            </ul>
            <a href="logout.php" class="logout-btn">Logout</a>
        </nav>
        
        <div class="tabs">
            <div class="tab active" onclick="openTab('pesanan')">Daftar Pesanan</div>
            <div class="tab" onclick="openTab('produk')">Kelola Produk</div>
        </div>
        
        <div id="pesanan" class="tab-content active">
            <div class="admin-section">
                <h2>Daftar Pesanan</h2>
                
                <table>
                    <thead>
                        <tr>
                            <th>ID Pesanan</th>
                            <th>Pelanggan</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $pesanan_query->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['user_id']; ?></td>
                                <td><?php echo htmlspecialchars($row['nama_pelanggan']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($row['tanggal'])); ?></td>
                                <td>Rp <?php echo number_format($row['total_harga'], 0, ',', '.'); ?></td>
                                <td class="status-<?php echo $row['status']; ?>">
                                    <?php echo ucfirst($row['status']); ?>
                                </td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="pesanan_id" value="<?php echo $row['id']; ?>">
                                        <select name="status" onchange="this.form.submit()">
                                            <option value="pending" <?php echo $row['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="diproses" <?php echo $row['status'] === 'diproses' ? 'selected' : ''; ?>>Diproses</option>
                                            <option value="selesai" <?php echo $row['status'] === 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                                            <option value="dibatalkan" <?php echo $row['status'] === 'dibatalkan' ? 'selected' : ''; ?>>Dibatalkan</option>
                                        </select>
                                        <input type="hidden" name="update_status" value="1">
                                    </form>
                                    <a href="detail_pesanan.php?id=<?php echo $row['id']; ?>" class="btn btn-edit">Detail</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div id="produk" class="tab-content">
            <div class="admin-section">
                <h2>Kelola Produk</h2>
                
                <h3>Tambah Produk Baru</h3>
                <form method="POST">
                    <div class="form-group">
                        <label for="kode">Kode Produk</label>
                        <input type="text" id="kode" name="kode" required>
                    </div>
                    <div class="form-group">
                        <label for="nama">Nama Produk</label>
                        <input type="text" id="nama" name="nama" required>
                    </div>
                    <div class="form-group">
                        <label for="kategori_id">Kategori</label>
                        <select id="kategori_id" name="kategori_id" required>
                            <?php foreach ($kategori as $id => $kat): ?>
                                <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($kat['nama']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="harga">Harga</label>
                        <input type="number" id="harga" name="harga" required>
                    </div>
                    <button type="submit" name="tambah_produk" class="btn">Tambah Produk</button>
                </form>
                
                <h3>Daftar Produk</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produk as $kode => $prod): ?>
                            <tr>
                                <td><?php echo $kode; ?></td>
                                <td><?php echo htmlspecialchars($prod['nama']); ?></td>
                                <td><?php echo htmlspecialchars($kategori[$prod['kategori_id']]['nama']); ?></td>
                                <td>Rp <?php echo number_format($prod['harga'], 0, ',', '.'); ?></td>
                                <td>
                                    <a href="edit_produk.php?kode=<?php echo $kode; ?>" class="btn btn-edit">Edit</a>
                                    <a href="admin.php?hapus_produk=<?php echo $kode; ?>" class="btn btn-hapus" onclick="return confirm('Yakin ingin menghapus produk ini?')">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function openTab(tabName) {
            // Sembunyikan semua tab content
            const tabContents = document.getElementsByClassName('tab-content');
            for (let i = 0; i < tabContents.length; i++) {
                tabContents[i].classList.remove('active');
            }
            
            // Hapus active class dari semua tab
            const tabs = document.getElementsByClassName('tab');
            for (let i = 0; i < tabs.length; i++) {
                tabs[i].classList.remove('active');
            }
            
            // Tampilkan tab yang dipilih
            document.getElementById(tabName).classList.add('active');
            
            // Tambahkan active class ke tab yang diklik
            event.currentTarget.classList.add('active');
        }
    </script>
</body>
</html>