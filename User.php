<?php
require_once 'config.php';

// Error handling untuk koneksi database
if ($db->connect_error) {
    die("Koneksi database gagal: " . $db->connect_error);
}

// Ambil data kategori dengan error handling
$kategori = [];
$kategori_query = $db->query("SELECT * FROM kategori");
if ($kategori_query === false) {
    die("Error mengambil data kategori: " . $db->error);
}
while ($row = $kategori_query->fetch_assoc()) {
    $kategori[$row['id']] = $row['nama'];
}

// Ambil data produk dengan error handling
$produk_per_kategori = [];
$produk_query = $db->query("SELECT * FROM produk");
if ($produk_query === false) {
    die("Error mengambil data produk: " . $db->error);
}
while ($row = $produk_query->fetch_assoc()) {
    $produk_per_kategori[$row['kategori_id']][] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Ordering</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #ff5722;
            --primary-dark: #e64a19;
            --secondary-color: #4caf50;
            --secondary-dark: #388e3c;
            --dark-color: #333;
            --light-color: #f5f5f5;
            --white: #fff;
            --gray: #ddd;
            --text-color: #333;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: var(--light-color);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        header {
            background-color: var(--primary-color);
            color: var(--white);
            padding: 20px 0;
            text-align: center;
            margin-bottom: 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        header h1 {
            font-size: 2.5rem;
        }
        
        nav {
            background-color: var(--dark-color);
            margin-bottom: 30px;
            border-radius: 5px;
            overflow: hidden;
        }
        
        nav ul {
            display: flex;
            list-style: none;
        }
        
        nav ul li {
            flex: 1;
            text-align: center;
        }
        
        nav ul li a {
            display: block;
            color: var(--white);
            text-decoration: none;
            padding: 15px 20px;
            transition: background-color 0.3s;
        }
        
        nav ul li a:hover {
            background-color: rgba(255,255,255,0.1);
        }
        
        .menu-category {
            background-color: var(--white);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .menu-category h2 {
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--gray);
            font-size: 1.8rem;
        }
        
        .menu-items {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }
        
        .menu-item {
            border: 1px solid var(--gray);
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .menu-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .menu-item-img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }
        
        .menu-item-content {
            padding: 15px;
        }
        
        .menu-item h3 {
            font-size: 1.2rem;
            margin-bottom: 8px;
            color: var(--dark-color);
        }
        
        .menu-item-code {
            display: inline-block;
            background-color: var(--light-color);
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            margin-bottom: 10px;
            color: #666;
        }
        
        .menu-item-desc {
            color: #666;
            margin-bottom: 15px;
            font-size: 0.9rem;
        }
        
        .menu-item-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .price {
            color: var(--primary-color);
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .order-btn {
            background-color: var(--primary-color);
            color: var(--white);
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .order-btn:hover {
            background-color: var(--primary-dark);
        }
        
        .cart {
            position: sticky;
            bottom: 20px;
            background-color: var(--white);
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            margin-top: 30px;
        }
        
        .cart h2 {
            color: var(--primary-color);
            margin-bottom: 20px;
            font-size: 1.5rem;
        }
        
        .cart-items {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 20px;
        }
        
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid var(--gray);
        }
        
        .cart-item-info {
            flex: 1;
        }
        
        .cart-item-name {
            font-weight: 500;
        }
        
        .cart-item-qty {
            color: #666;
            font-size: 0.9rem;
        }
        
        .cart-item-price {
            font-weight: bold;
        }
        
        .cart-item-actions {
            margin-left: 15px;
        }
        
        .cart-item-remove {
            background: none;
            border: none;
            color: #ff4444;
            cursor: pointer;
            font-size: 1rem;
        }
        
        .total {
            font-weight: bold;
            text-align: right;
            font-size: 1.3rem;
            margin: 20px 0;
        }
        
        .checkout-btn {
            background-color: var(--secondary-color);
            color: var(--white);
            border: none;
            padding: 12px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 1.1rem;
            transition: background-color 0.3s;
        }
        
        .checkout-btn:hover {
            background-color: var(--secondary-dark);
        }
        
        .empty-cart {
            text-align: center;
            color: #666;
            padding: 20px;
        }
        
        @media (max-width: 768px) {
            nav ul {
                flex-direction: column;
            }
            
            .menu-items {
                grid-template-columns: 1fr;
            }
            
            .cart {
                position: static;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>FOOD ORDERING </h1>
        </header>
        
        <nav>
            <ul>
                <li><a href="#"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="#"><i class="fas fa-list"></i> Menu</a></li>
                <li><a href="#"><i class="fas fa-history"></i> Order History</a></li>
                <li><a href="#"><i class="fas fa-info-circle"></i> About</a></li>
            </ul>
        </nav>
        
        <div class="menu">
            <?php foreach ($kategori as $id => $nama_kategori): ?>
                <div class="menu-category">
                    <h2><?php echo htmlspecialchars($nama_kategori); ?></h2>
                    <div class="menu-items">
                        <?php if (isset($produk_per_kategori[$id])): ?>
                            <?php foreach ($produk_per_kategori[$id] as $produk): ?>
                                <div class="menu-item">
                                    <img src="<?php echo htmlspecialchars($produk['gambar'] ?? 'images/default-food.jpg'); ?>" 
                                         alt="<?php echo htmlspecialchars($produk['nama']); ?>" 
                                         class="menu-item-img">
                                    <div class="menu-item-content">
                                        <h3><?php echo htmlspecialchars($produk['nama']); ?></h3>
                                        <span class="menu-item-code"><?php echo htmlspecialchars($produk['kode']); ?></span>
                                        <p class="menu-item-desc"><?php echo htmlspecialchars($produk['deskripsi'] ?? 'Deskripsi tidak tersedia'); ?></p>
                                        <div class="menu-item-footer">
                                            <div class="price">Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?></div>
                                            <button class="order-btn" 
                                                    onclick="addToCart('<?php echo $produk['kode']; ?>', 
                                                                 '<?php echo htmlspecialchars($produk['nama']); ?>', 
                                                                 <?php echo $produk['harga']; ?>)">
                                                <i class="fas fa-plus"></i> Pesan
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>Tidak ada produk dalam kategori ini.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="cart">
            <h2><i class="fas fa-shopping-cart"></i> Keranjang Belanja</h2>
            <div class="cart-items" id="cart-items">
                <div class="empty-cart">Keranjang belanja kosong</div>
            </div>
            <div class="total" id="total">
                Total: Rp 0
            </div>
            <button class="checkout-btn" onclick="checkout()">
                <i class="fas fa-credit-card"></i> Checkout
            </button>
        </div>
    </div>






    <script>
    let cart = JSON.parse(localStorage.getItem('cart')) || [];

    function addToCart(kode, nama, harga) {
        const existingItem = cart.find(item => item.kode === kode);
        
        if (existingItem) {
            existingItem.jumlah++;
        } else {
            cart.push({
                kode: kode,
                nama: nama,
                harga: harga,
                jumlah: 1
            });
        }
        
        updateCartDisplay();
        saveCartToLocalStorage();
        showToast(`${nama} ditambahkan ke keranjang`);
    }

    function removeFromCart(index) {
        const removedItem = cart[index];
        cart.splice(index, 1);
        updateCartDisplay();
        saveCartToLocalStorage();
        showToast(`${removedItem.nama} dihapus dari keranjang`);
    }

    function updateCartDisplay() {
        const cartItemsElement = document.getElementById('cart-items');
        const totalElement = document.getElementById('total');
        
        if (cart.length === 0) {
            cartItemsElement.innerHTML = '<div class="empty-cart">Keranjang belanja kosong</div>';
            totalElement.textContent = 'Total: Rp 0';
            return;
        }
        
        cartItemsElement.innerHTML = '';
        let total = 0;
        
        cart.forEach((item, index) => {
            total += item.harga * item.jumlah;
            
            const itemElement = document.createElement('div');
            itemElement.className = 'cart-item';
            itemElement.innerHTML = `
                <div class="cart-item-info">
                    <div class="cart-item-name">${item.nama}</div>
                    <div class="cart-item-qty">${item.jumlah} x Rp ${item.harga.toLocaleString('id-ID')}</div>
                </div>
                <div class="cart-item-price">Rp ${(item.harga * item.jumlah).toLocaleString('id-ID')}</div>
                <div class="cart-item-actions">
                    <button class="cart-item-remove" onclick="removeFromCart(${index})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
            
            cartItemsElement.appendChild(itemElement);
        });
        
        totalElement.textContent = `Total: Rp ${total.toLocaleString('id-ID')}`;
    }

    function saveCartToLocalStorage() {
        localStorage.setItem('cart', JSON.stringify(cart));
    }

    function showToast(message) {
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('show');
        }, 10);
        
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, 3000);
    }

    function checkout() {
        if (cart.length === 0) {
            showToast('Keranjang belanja kosong!');
            return;
        }

        const total = cart.reduce((sum, item) => sum + (item.harga * item.jumlah), 0);
        const payload = { 
            items: cart, 
            total: total,
            timestamp: new Date().toISOString()
        };

        fetch('checkout.php', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showToast(`Pesanan berhasil! ID: ${data.order_id}`);
                cart = [];
                updateCartDisplay();
                saveCartToLocalStorage();
            } else {
                showToast(`Error: ${data.message || 'Gagal memproses pesanan'}`);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Terjadi kesalahan saat memproses pesanan');
        });
    }

    // Inisialisasi tampilan keranjang saat halaman dimuat
    document.addEventListener('DOMContentLoaded', updateCartDisplay);
    
    // Tambahan: Style untuk toast notification
    const style = document.createElement('style');
    style.textContent = `
        .toast {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #333;
            color: white;
            padding: 12px 24px;
            border-radius: 4px;
            opacity: 0;
            transition: opacity 0.3s;
            z-index: 1000;
        }
        .toast.show {
            opacity: 1;
        }
    `;
    document.head.appendChild(style);
    </script>
</body>
</html>