<?php

require_once __DIR__ . '/db_connection.php';
require_once __DIR__ . '/db_functions.php';

if (!defined('BASE_DIR')) {
    define('BASE_DIR', __DIR__ . '/../');
}
if (!defined('BASE_URL_PATH')) {
    define('BASE_URL_PATH', '/');
}

// Define FULL_BASE_URL for absolute redirects
if (!defined('FULL_BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $domainName = $_SERVER['HTTP_HOST'];
    define('FULL_BASE_URL', $protocol . $domainName . BASE_URL_PATH);
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function redirectToLogin() {
    header("Location: " . FULL_BASE_URL . "login.php");
    exit();
}

function redirectToAdminDashboard() {
    header("Location: " . FULL_BASE_URL . "admin/index.php");
    exit();
}

function redirectToHome() {
    header("Location: " . FULL_BASE_URL . "index.php");
    exit();
}

function getCategories() {
    return db_getAll('categories', 'name'); 
}

function getCategoryById($id) {
    return db_getById('categories', $id);
}

function getUsers() {
    return db_getAll('users', 'username');
}

function getUserById($id) {
    return db_getById('users', $id);
}

function getUserByUsername($username) {
    return db_getByCriteria('users', ['username' => $username]);
}

function getOrders() {
    return db_getAll('orders', 'order_date', 'DESC');
}

function getOrderById($id) {
    return db_getById('orders', $id);
}

function getOrderItemsByOrderId($order_id) {
    return db_queryAll("SELECT * FROM order_items WHERE order_id = :order_id", [':order_id' => $order_id]);
}

function generateUniqueId($prefix = '') {
    return $prefix . uniqid();
}

function getOrdersByUserId($user_id) {
    return db_queryAll("SELECT * FROM orders WHERE user_id = :user_id ORDER BY order_date DESC", [':user_id' => $user_id]);
}

?>