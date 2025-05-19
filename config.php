<?php
session_start();

// Koneksi ke database
$server="localhost";
$username = "root";
$password = "";
$database = "pesanmakanan";

$db = new mysqli($server, $username, $password, $database);
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Fungsi untuk redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Fungsi untuk mengecek login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Fungsi untuk mengecek role admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Fungsi untuk mendapatkan data user saat ini
function currentUser() {
    global $db;
    if (!isLoggedIn()) return null;

    $user_id = $_SESSION['user_id'];
    $query = $db->prepare("SELECT * FROM user WHERE id = ?");
    $query->bind_param("i", $user_id);
    $query->execute();
    return $query->get_result()->fetch_assoc();
}
?>
