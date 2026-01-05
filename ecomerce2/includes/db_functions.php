<?php
require_once __DIR__ . '/db_connection.php';

function db_getAll($table_name, $order_by = 'id', $direction = 'ASC') {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM `$table_name` ORDER BY `$order_by` $direction");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function db_getById($table_name, $id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM `$table_name` WHERE id = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function db_getByCriteria($table_name, $criteria) {
    global $pdo;
    $where_clauses = [];
    $params = [];
    foreach ($criteria as $key => $value) {
        $where_clauses[] = "`$key` = :$key";
        $params[":$key"] = $value;
    }
    $query = "SELECT * FROM `$table_name` WHERE " . implode(' AND ', $where_clauses);
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function db_insert($table_name, $data) {
    global $pdo;
    $columns = implode(', ', array_keys($data));
    $placeholders = ':' . implode(', :', array_keys($data));
    $query = "INSERT INTO `$table_name` ($columns) VALUES ($placeholders)";
    $stmt = $pdo->prepare($query);
    return $stmt->execute($data);
}

function db_update($table_name, $id, $data) {
    global $pdo;
    $set_clauses = [];
    foreach ($data as $key => $value) {
        $set_clauses[] = "`$key` = :$key";
    }
    $query = "UPDATE `$table_name` SET " . implode(', ', $set_clauses) . " WHERE id = :id";
    $params = array_merge($data, [':id' => $id]);
    $stmt = $pdo->prepare($query);
    return $stmt->execute($params);
}

function db_delete($table_name, $id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM `$table_name` WHERE id = :id");
    return $stmt->execute([':id' => $id]);
}

function db_count($table_name, $criteria = []) {
    global $pdo;
    $where_clauses = [];
    $params = [];
    foreach ($criteria as $key => $value) {
        $where_clauses[] = "`$key` = :$key";
        $params[":$key"] = $value;
    }
    $query = "SELECT COUNT(*) FROM `$table_name`" . (empty($where_clauses) ? '' : " WHERE " . implode(' AND ', $where_clauses));
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}

function db_queryAll($query, $params = []) {
    global $pdo;
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function db_queryOne($query, $params = []) {
    global $pdo;
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function db_execute($query, $params = []) {
    global $pdo;
    $stmt = $pdo->prepare($query);
    return $stmt->execute($params);
}

function db_getRecent($table_name, $limit, $order_by = 'id', $direction = 'DESC') {
    global $pdo;
    $limit = (int)$limit;
    $stmt = $pdo->prepare("SELECT * FROM `$table_name` ORDER BY `$order_by` $direction LIMIT :limit");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>
