<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/functions.php'; 
=
if (!isLoggedIn() || !isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
    exit();
}
=
$input = json_decode(file_get_contents('php://input'), true);

$order_id = $input['order_id'] ?? null;
$new_status = $input['status'] ?? null;
$tracking_number = $input['tracking_number'] ?? null;
$rejection_reason = $input['rejection_reason'] ?? null;

if (!$order_id || !$new_status) {
    echo json_encode(['success' => false, 'message' => 'ID Pesanan atau status baru tidak valid.']);
    exit();
}
=
$data_to_update = [
    'status' => $new_status
];

if ($new_status === 'shipped' && $tracking_number) {
    $data_to_update['tracking_number'] = $tracking_number;
}

if ($new_status === 'cancelled' && $rejection_reason) {
    $data_to_update['rejection_reason'] = $rejection_reason;
}

if (db_update('orders', $order_id, $data_to_update)) {
    echo json_encode(['success' => true, 'message' => 'Status pesanan berhasil diperbarui.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal memperbarui status pesanan di database.']);
}
?>
