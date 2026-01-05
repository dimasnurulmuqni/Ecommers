<?php
session_start();

include_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>𝐀𝐥𝐥𝐢𝐠𝐚𝐭𝐨𝐫 𝐒𝐭𝐮𝐝𝐢𝐨t - 𝐑𝐞𝐟𝐢𝐧𝐞𝐝 𝐰𝐚𝐫𝐝𝐫𝐨𝐛𝐞 𝐬𝐭𝐚𝐩𝐥𝐞 𝐟𝐨𝐫 𝐞𝐟𝐟𝐨𝐫𝐭𝐥𝐞𝐬𝐬 𝐞𝐯𝐞𝐫𝐝𝐚𝐲𝐰𝐞𝐚𝐫</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <nav class="navbar">
        <a href="index.php" class="logo">
            <div class="logo-icon">𝐀s</div>
            <span>𝐀𝐥𝐥𝐢𝐠𝐚𝐭𝐨𝐫 𝐒𝐭𝐮𝐝𝐢𝐨</span> 
        </a>
        <ul class="nav-menu">
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="products.php">Produk</a></li>
            <li><a href="about.php">Tentang</a></li>
            <li><a href="orders.php">Pesanan Saya</a></li>
            <?php if (isLoggedIn()): ?>
                <li><a href="logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php">Register</a></li>
            <?php endif; ?>
        </ul>
        <div class="cart-icon-wrapper" onclick="window.location.href='cart.php'">
            <img src="assets/icons/ic_cart.svg" alt="Keranjang" class="cart-icon">
            <span class="cart-badge" id="cartCountNav">0</span>
        </div>

    </nav>
