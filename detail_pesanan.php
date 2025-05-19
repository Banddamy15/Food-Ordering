<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

if (!isset($_GET['id'])) {
    redirect('index.php');
}

$pesanan_id = $_GET['id'];

// Ambil data pesanan
$query = $db->prepare("
    SELECT p.*, u.nama as nama_pelanggan 
    FROM pesanan p
    JOIN user u ON p.user_id = u.id
    WHERE p.id = ?
");
$query->bind_param("i", $pesanan_id);
$query->execute();
$pesanan = $query->get_result()->fetch_assoc();

if (!$pesanan) {
    redirect('index.php');
}

// Ambil detail pesanan
$detail_query = $db->prepare("
    SELECT d.*, pr.nama as nama_produk 
    FROM detail_pesanan d
    JOIN produk pr ON d.produk_kode = pr.kode
    WHERE d.pesanan_id = ?
");
$detail_query->bind_param("i", $pesanan_id);
$detail_query->execute();
$detail_pesanan = $detail_query->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan - Kadaharan MANTEP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
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
        .detail-pesanan {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .detail-pesanan h2 {
            margin-top: 0;
            color: #ff5722;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .info-pesanan {
            margin-bottom: 20px;
        }
        .info-pesanan p {
            margin: 5px 0;
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
        .total {
            font-weight: bold;
            text-align: right;
            font-size: 1.2em;
            margin-top: 10px;
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
        .btn {
            background-color: #ff5722;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            background-color: #e64a19;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Detail Pesanan #<?php echo $pesanan['id']; ?></h1>
        </header>
        
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <?php if (isAdmin()): ?>
                    <li><a href="admin.php">Admin</a></li>
                <?php else: ?>
                    <li><a href="riwayat.php">Riwayat Pesanan</a></li>
                <?php endif; ?>
            </ul>
            <a href="logout.php" class="logout-btn">Logout</a>
        </nav>
        
        <div class="detail-pesanan">
            <div class="info-pesanan">
                <h2>Informasi Pesanan</h2>
                <p><strong>ID Pesanan:</strong> #<?php echo $pesanan['id']; ?></p>
                <p><strong>Pelanggan:</strong> <?php echo htmlspecialchars($pesanan['nama_pelanggan']); ?></p>
                <p><strong>Tanggal:</strong> <?php echo date('d/m/Y H:i', strtotime($pesanan['tanggal'])); ?></p>
                <p><strong>Status:</strong> 
                    <span class="status-<?php echo $pesanan['status']; ?>">
                        <?php echo ucfirst($pesanan['status']); ?>
                    </span>
                </p>
            </div>
            
            <h2>Detail Pesanan</h2>
            <table>
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Jumlah</th>
                        <th>Harga</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($detail_pesanan as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['nama_produk']); ?></td>
                            <td><?php echo $item['jumlah']; ?></td>
                            <td>Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?></td>
                            <td>Rp <?php echo number_format($item['harga'] * $item['jumlah'], 0, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="total">
                Total: Rp <?php echo number_format($pesanan['total_harga'], 0, ',', '.'); ?>
            </div>
            
            <?php if (isAdmin()): ?>
                <form method="POST" action="admin.php">
                    <input type="hidden" name="pesanan_id" value="<?php echo $pesanan['id']; ?>">
                    <label for="status">Ubah Status:</label>
                    <select name="status" id="status">
                        <option value="pending" <?php echo $pesanan['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="diproses" <?php echo $pesanan['status'] === 'diproses' ? 'selected' : ''; ?>>Diproses</option>
                        <option value="selesai" <?php echo $pesanan['status'] === 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                        <option value="dibatalkan" <?php echo $pesanan['status'] === 'dibatalkan' ? 'selected' : ''; ?>>Dibatalkan</option>
                    </select>
                    <input type="hidden" name="update_status" value="1">
                    <button type="submit" class="btn">Update Status</button>
                </form>
            <?php endif; ?>
            
            <a href="<?php echo isAdmin() ? 'admin.php' : 'riwayat.php'; ?>" class="btn">Kembali</a>
        </div>
    </div>
</body>
</html>