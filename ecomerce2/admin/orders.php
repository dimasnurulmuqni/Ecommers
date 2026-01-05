<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/product_functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirectToLogin();
}

$orders = getOrders(); 

function getStatusBadgeClass($status) {
    switch ($status) {
        case 'pending': return 'badge-pending';
        case 'paid': return 'badge-paid';
        case 'shipped': return 'badge-shipped';
        case 'delivered': return 'badge-delivered';
        case 'cancelled': return 'badge-cancelled';
        default: return 'badge-default';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pesanan - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --maroon: #800000;
            --maroon-dark: #600000;
            --navy: #1a2a6c;
            --white: #ffffff;
            --bg: #f1f5f9;
            --text-main: #1e293b;
            --border: #e2e8f0;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg); color: var(--text-main); }
        .admin-container { display: flex; min-height: 100vh; }
        .admin-sidebar { width: 260px; background: linear-gradient(180deg, var(--maroon) 0%, #5a0000 100%); color: white; position: fixed; height: 100vh; padding: 2rem 0; z-index: 100; }
        .admin-sidebar h3 { text-align: center; font-size: 1.4rem; margin-bottom: 2rem; color: var(--white); border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 1rem; }
        .admin-sidebar ul { list-style: none; }
        .admin-sidebar ul li a { display: block; padding: 1rem 2rem; color: rgba(255,255,255,0.8); text-decoration: none; transition: all 0.3s; }
        .admin-sidebar ul li a:hover, .admin-sidebar ul li a.active { background-color: rgba(255,255,255,0.15); color: white; border-left: 4px solid var(--navy); }
        .main-content { margin-left: 260px; flex: 1; padding: 2.5rem; }
        .page-header { margin-bottom: 2rem; }
        .page-header h1 { color: var(--navy); font-size: 1.8rem; font-weight: 700; }
        .table-container { background: white; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); overflow-x: auto; border: 1px solid var(--border); }
        .order-table { width: 100%; border-collapse: collapse; }
        .order-table th { background: #f8fafc; padding: 1rem 1.5rem; text-align: left; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; border-bottom: 2px solid var(--border); }
        .order-table td { padding: 1rem 1.5rem; border-bottom: 1px solid var(--border); font-size: 0.95rem; vertical-align: middle; }
        .order-table tr:hover { background-color: #f1f5f9; }
        .badge { padding: 5px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; text-transform: capitalize; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-paid { background: #dcfce7; color: #166534; }
        .badge-shipped { background: #dbeafe; color: #1e40af; }
        .badge-delivered { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
        .badge-cancelled { background: #fee2e2; color: #991b1b; }
        .status-select { padding: 6px 10px; border-radius: 6px; border: 1px solid var(--border); font-size: 0.85rem; background: #fff; }
        .btn-action { background: var(--navy); color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 0.8rem; transition: 0.3s; margin-left: 5px; }
        .btn-action:hover { background: #111d4a; }
        .empty-state { text-align: center; padding: 4rem; color: #94a3b8; }

        /* Modal Styles */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); }
        .modal-content { background-color: #fefefe; margin: 15% auto; padding: 25px; border: 1px solid #888; width: 80%; max-width: 500px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
        .modal-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 15px; margin-bottom: 20px; }
        .modal-header h2 { color: var(--navy); }
        .close-btn { color: #aaa; font-size: 28px; font-weight: bold; cursor: pointer; }
        .close-btn:hover, .close-btn:focus { color: black; text-decoration: none; }
        .modal-body .form-group { margin-bottom: 15px; }
        .modal-body label { display: block; margin-bottom: 5px; font-weight: 600; }
        .modal-body input, .modal-body textarea { width: 100%; padding: 10px; border-radius: 6px; border: 1px solid var(--border); }
        .modal-footer { text-align: right; border-top: 1px solid var(--border); padding-top: 15px; margin-top: 20px; }
        #proof-image { max-width: 100%; height: auto; border-radius: 8px; }
    </style>
</head>
<body>

    <div class="admin-container">
        <aside class="admin-sidebar">
            <h3>ADMIN PANEL</h3>
            <ul>
                <li><a href="index.php"><i class="fas fa-home"></i> &nbsp; Dashboard</a></li>
                <li><a href="orders.php" class="active"><i class="fas fa-shopping-cart"></i> &nbsp; Manajemen Pesanan</a></li>
                <li><a href="products.php"><i class="fas fa-box"></i> &nbsp; Manajemen Produk</a></li>
                <li><a href="categories.php"><i class="fas fa-tags"></i> &nbsp;Manajemen Kategori</a></li>
                <li style="margin-top: 30px;"><a href="../logout.php" style="color: #ffbaba;"><i class="fas fa-sign-out-alt"></i> &nbsp; Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <h1>Manajemen Pesanan</h1>
                <p style="color: #64748b;">Pantau dan perbarui status pengiriman pelanggan.</p>
            </div>

            <div class="table-container">
                <table class="order-table">
                    <thead>
                        <tr>
                            <th>ID Order</th>
                            <th>Pelanggan</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Ubah Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr><td colspan="7" class="empty-state"><i class="fas fa-receipt fa-3x" style="margin-bottom: 1rem;"></i><p>Belum ada transaksi masuk.</p></td></tr>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <?php $user = getUserById($order['user_id']); ?>
                                <tr>
                                    <td style="font-weight: 600; color: var(--navy);"><a href="../order-detail.php?id=<?php echo htmlspecialchars($order['id']); ?>" target="_blank">#<?php echo htmlspecialchars($order['id']); ?></a></td>
                                    <td><?php echo htmlspecialchars($user['username'] ?? 'N/A'); ?></td>
                                    <td><?php echo date('d M Y', strtotime($order['order_date'])); ?></td>
                                    <td style="font-weight: 700;">Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></td>
                                    <td>
                                        <span id="status-badge-<?php echo htmlspecialchars($order['id']); ?>" class="badge <?php echo getStatusBadgeClass($order['status']); ?>">
                                            <?php echo htmlspecialchars($order['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <select class="status-select" data-order-id="<?php echo htmlspecialchars($order['id']); ?>">
                                            <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Menunggu</option>
                                            <option value="paid" <?php echo $order['status'] == 'paid' ? 'selected' : ''; ?>>Sudah Dibayar</option>
                                            <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Dikirim</option>
                                            <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>Selesai</option>
                                            <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Ditolak</option>
                                        </select>
                                    </td>
                                    <td>
                                        <?php if (!empty($order['upload'])): ?>
                                            <button class="btn-action view-proof-btn" data-image-url="../<?php echo htmlspecialchars($order['upload']); ?>">Lihat Bukti</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <div id="proof-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Bukti Pembayaran</h2>
                <span class="close-btn">&times;</span>
            </div>
            <div class="modal-body">
                <img id="proof-image" src="" alt="Bukti Pembayaran">
            </div>
        </div>
    </div>

    <div id="shipping-modal" class="modal">
        <div class="modal-content">
            <form id="shipping-form">
                <div class="modal-header">
                    <h2>Masukkan Nomor Resi</h2>
                    <span class="close-btn">&times;</span>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="tracking_number">Nomor Resi</label>
                        <input type="text" id="tracking_number" name="tracking_number" required>
                        <input type="hidden" id="shipping-order-id" name="order_id">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn-action">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div id="rejection-modal" class="modal">
        <div class="modal-content">
            <form id="rejection-form">
                <div class="modal-header">
                    <h2>Masukkan Alasan Penolakan</h2>
                    <span class="close-btn">&times;</span>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="rejection_reason">Alasan Penolakan</label>
                        <textarea id="rejection_reason" name="rejection_reason" rows="4" required></textarea>
                        <input type="hidden" id="rejection-order-id" name="order_id">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn-action">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../js/admin_orders.js"></script>
</body>
</html>