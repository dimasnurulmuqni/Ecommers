<?php
session_start();
require_once __DIR__ . '/../includes/functions.php'; 
require_once __DIR__ . '/../includes/product_functions.php'; 

if (!isLoggedIn() || !isAdmin()) {
    redirectToLogin();
}

$total_users = db_count('users');
$total_products = db_count('products');
$total_categories = db_count('categories');
$total_orders = db_count('orders');
$pending_orders = db_count('orders', ['status' => 'pending']);

$recent_orders = db_queryAll("
    SELECT o.id, o.status, o.total_amount, o.order_date, u.username
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.order_date DESC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Modern Maroon & Navy</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --maroon: #800000;
            --navy: #1a2a6c;
            --white: #ffffff;
            --light-gray: #f4f7f6;
            --text-dark: #333;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--light-gray);
        }

       .admin-container {
            display: flex;
            min-height: 100vh;
        }

        .admin-sidebar {
            width: 260px;
            background: linear-gradient(180deg, var(--maroon) 0%, #5a0000 100%);
            color: white;
            position: fixed;
            height: 100vh;
            padding: 2rem 0;
            z-index: 100;
        }

        .admin-sidebar h3 {
            text-align: center;
            font-size: 1.4rem;
            margin-bottom: 2rem;
            color: var(--white);
            border-bottom: 1px solid rgba(255,255,255,0.1);
            padding-bottom: 1rem;
        }

        .admin-sidebar ul {
            list-style: none;
        }

        .admin-sidebar ul li a {
            display: block;
            padding: 1rem 2rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
        }

        .admin-sidebar ul li a:hover, .admin-sidebar ul li a.active {
            background-color: rgba(255,255,255,0.15);
            color: white;
            border-left: 4px solid var(--navy);
        }

        .admin-content {
            margin-left: 260px;
            flex-grow: 1;
            padding: 2.5rem;
        }

        .header-title {
            margin-bottom: 2rem;
        }

        .header-title h1 {
            color: var(--navy);
            font-size: 1.8rem;
            font-weight: 600;
        }
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .summary-card {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
            border-top: 5px solid var(--maroon);
        }

        .summary-card:nth-child(even) {
            border-top: 5px solid var(--navy);
        }

        .summary-card:hover {
            transform: translateY(-5px);
        }

        .summary-card h4 {
            color: #777;
            font-size: 0.9rem;
            text-transform: uppercase;
            margin-bottom: 1rem;
            letter-spacing: 0.5px;
        }

        .summary-card p {
            font-size: 2.2rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        .data-table th, .data-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .data-table th {
            background-color: #f8f8f8;
            font-weight: 600;
            color: #555;
        }
        .data-table tbody tr:hover {
            background-color: #f1f1f1;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: capitalize;
        }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-paid { background-color: #d1ecf1; color: #0c5460; }
        .status-shipped { background-color: #d4edda; color: #155724; }
        .status-delivered { background-color: #e9d4ed; color: #521557; }
        .status-cancelled { background-color: #f8d7da; color: #721c24; }

        @media (max-width: 768px) {
            .admin-sidebar { width: 70px; }
            .admin-sidebar h3, .admin-sidebar span { display: none; }
            .admin-content { margin-left: 70px; }
            .summary-cards { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>
    
    <div class="admin-container">
        <aside class="admin-sidebar">
            <h3>ADMIN PANEL</h3>
            <ul>
                <li><a href="index.php"  class="active"><i class="fas fa-home"></i> &nbsp; Dashboard</a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> &nbsp; Manajemen Pesanan</a></li>
                <li><a href="products.php" ><i class="fas fa-box"></i> &nbsp; Manajemen Produk</a></li>
                <li><a href="categories.php"><i class="fas fa-tags"></i> &nbsp;Manajemen Kategori</a></li>
                <li style="margin-top: 30px;"><a href="../logout.php" style="color: #ffbaba;"><i class="fas fa-sign-out-alt"></i> &nbsp; Logout</a></li>
            </ul>
        </aside>

        <main class="admin-content">
            <div class="header-title">
                <h1>Selamat Datang, Admin</h1>
                <p style="color: #888;">Ringkasan aktivitas toko Anda hari ini.</p>
            </div>

            <div class="summary-cards">
                <div class="summary-card">
                    <h4>Pengguna</h4>
                    <p><?php echo $total_users; ?></p>
                </div>
                <div class="summary-card">
                    <h4>Produk</h4>
                    <p><?php echo $total_products; ?></p>
                </div>
                <div class="summary-card">
                    <h4>Kategori</h4>
                    <p><?php echo $total_categories; ?></p>
                </div>
                <div class="summary-card">
                    <h4>Total Pesanan</h4>
                    <p><?php echo $total_orders; ?></p>
                </div>
                <div class="summary-card" style="background-color: #fff5f5; border-top: 5px solid #ff4d4d;">
                    <h4 style="color: #ff4d4d;">Pending</h4>
                    <p style="color: #ff4d4d;"><?php echo $pending_orders; ?></p>
                </div>
            </div>

            <div style="background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                <h3 style="color: var(--navy); margin-bottom: 1rem;">Pesanan Terbaru</h3>
                <?php if (empty($recent_orders)): ?>
                    <p style="color: #666;">Belum ada pesanan yang masuk.</p>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID Pesanan</th>
                                <th>Pelanggan</th>
                                <th>Status</th>
                                <th>Total</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td><a href="orders.php?action=view&id=<?php echo htmlspecialchars($order['id']); ?>"><?php echo htmlspecialchars($order['id']); ?></a></td>
                                <td><?php echo htmlspecialchars($order['username']); ?></td>
                                <td><span class="status-badge status-<?php echo htmlspecialchars($order['status']); ?>"><?php echo htmlspecialchars($order['status']); ?></span></td>
                                <td>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></td>
                                <td><?php echo date('d M Y', strtotime($order['order_date'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>
    </div>

</body>
</html>