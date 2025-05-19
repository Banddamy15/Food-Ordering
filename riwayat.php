<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Ambil data pesanan user
$pesanan_query = $db->prepare("
    SELECT * FROM pesanan 
    WHERE user_id = ?
    ORDER BY tanggal DESC
");
$pesanan_query->bind_param("i", $user_id);
$pesanan_query->execute();
$pesanan = $pesanan_query->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan - Kadaharan MANTEP</title>
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
        .riwayat-pesanan {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .riwayat-pesanan h2 {
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
        .empty-message {
            text-align: center;
            padding: 20px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Riwayat Pesanan - Kadaharan MANTEP</h1>
        </header>
        
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="riwayat.php">Riwayat Pesanan</a></li>
            </ul>
            <a href="logout.php" class="logout-btn">Logout</a>
        </nav>
        
        <div class="riwayat-pesanan">
            <h2>Daftar Pesanan Anda</h2>
            
            <?php if ($pesanan->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID Pesanan</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $pesanan->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $row['id']; ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($row['tanggal'])); ?></td>
                                <td>Rp <?php echo number_format($row['total_harga'], 0, ',', '.'); ?></td>
                                <td class="status-<?php echo $row['status']; ?>">
                                    <?php echo ucfirst($row['status']); ?>
                                </td>
                                <td>
                                    <a href="detail_pesanan.php?id=<?php echo $row['id']; ?>" class="btn">Detail</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-message">
                    <p>Anda belum memiliki pesanan.</p>
                    <a href="index.php" class="btn">Pesan Sekarang</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>