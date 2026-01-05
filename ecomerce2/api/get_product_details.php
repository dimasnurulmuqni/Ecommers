<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/product_functions.php';

header('Content-Type: application/json');

$product_id = $_GET['id'] ?? null;

if (!$product_id) {
    echo json_encode(['error' => 'Product ID is required']);
    exit();
}

$product = getProductById($product_id);

if (!$product) {
    echo json_encode(['error' => 'Product not found']);
    exit();
}

echo json_encode([
    'id' => $product['id'],
    'name' => $product['name'],
    'price' => $product['price'],
    'stock' => $product['stock'],
    'image' => !empty($product['images']) ? BASE_URL_PATH . $product['images'][0] : BASE_URL_PATH . 'assets/images/placeholder.jpg',
    'category_name' => $product['category_name']
]);
?>