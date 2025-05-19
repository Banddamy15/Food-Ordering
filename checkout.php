<?php
require_once 'config.php'; // Pastikan config.php sudah membuat $db
header('Content-Type: application/json');



// Validasi koneksi
if (!$db || $db->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Koneksi database gagal: ' . $db->connect_error]);
    exit;
}

// Ambil data dari request body (JSON)
$data = json_decode(file_get_contents('php://input'), true);

// Validasi data utama
if (
    !$data ||
    !isset($data['items']) || !is_array($data['items']) ||
    !isset($data['total']) ||
    count($data['items']) === 0
) {
    echo json_encode(['success' => false, 'message' => 'Data pesanan tidak valid']);
    exit;
}

// Validasi total harga cocok dengan item
$totalHitung = 0;
foreach ($data['items'] as $item) {
    if (!isset($item['kode'], $item['jumlah'], $item['harga'])) {
        echo json_encode(['success' => false, 'message' => 'Data item tidak lengkap']);
        exit;
    }

    $jumlah = (int)$item['jumlah'];
    $harga = (int)$item['harga'];

    if ($jumlah <= 0 || $harga <= 0) {
        echo json_encode(['success' => false, 'message' => 'Jumlah atau harga tidak valid']);
        exit;
    }

    $totalHitung += $jumlah * $harga;
}

if ($totalHitung !== (int)$data['total']) {
    echo json_encode(['success' => false, 'message' => 'Total tidak sesuai dengan jumlah item']);
    exit;
}

// Mulai transaksi
$db->begin_transaction();

try {
    // Simpan ke tabel pesanan
    $stmtPesanan = $db->prepare("INSERT INTO pesanan (total_harga, tanggal) VALUES (?, NOW())");
    if (!$stmtPesanan) {
        throw new Exception("Prepare statement pesanan gagal: " . $db->error);
    }

    $stmtPesanan->bind_param("i", $data['total']);
    if (!$stmtPesanan->execute()) {
        throw new Exception("Gagal menyimpan pesanan: " . $stmtPesanan->error);
    }

    $pesanan_id = $db->insert_id;
    $stmtPesanan->close();

    // Siapkan statement untuk detail_pesanan
    $stmtDetail = $db->prepare("INSERT INTO detail_pesanan (pesanan_id, produk_kode, jumlah, harga) VALUES (?, ?, ?, ?)");
    if (!$stmtDetail) {
        throw new Exception("Prepare statement detail_pesanan gagal: " . $db->error);
    }

    foreach ($data['items'] as $item) {
        $kode = trim($item['kode']);
        $jumlah = (int)$item['jumlah'];
        $harga = (int)$item['harga'];

        // Validasi apakah produk_kode benar-benar ada
        $cekProduk = $db->prepare("SELECT 1 FROM produk WHERE kode = ?");
        if (!$cekProduk) {
            throw new Exception("Prepare statement cek produk gagal: " . $db->error);
        }

        $cekProduk->bind_param("s", $kode);
        $cekProduk->execute();
        $cekProduk->store_result();
        if ($cekProduk->num_rows === 0) {
            throw new Exception("Produk dengan kode $kode tidak ditemukan");
        }
        $cekProduk->close();

        // Simpan detail pesanan
        $stmtDetail->bind_param("isii", $pesanan_id, $kode, $jumlah, $harga);
        if (!$stmtDetail->execute()) {
            throw new Exception("Gagal menyimpan detail pesanan: " . $stmtDetail->error);
        }
    }

    $stmtDetail->close();

    // Commit transaksi
    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Pesanan berhasil dibuat',
        'order_id' => $pesanan_id
    ]);
} catch (Exception $e) {
    $db->rollback();

    // Simpan log error ke file jika dibutuhkan (opsional)
    file_put_contents('log_error.txt', date('Y-m-d H:i:s') . ' - ' . $e->getMessage() . "\n", FILE_APPEND);

    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
    ]);
}
?>
