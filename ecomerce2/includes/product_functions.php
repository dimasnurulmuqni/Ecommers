<?php
function getProducts($search_query = null, $category_id = null, $order_by = 'name', $direction = 'ASC') {
    $where_clauses = [];
    $params = [];

    if ($search_query) {
        $where_clauses[] = "(p.name LIKE :search_query_name OR p.description LIKE :search_query_desc)";
        $params[':search_query_name'] = '%' . $search_query . '%';
        $params[':search_query_desc'] = '%' . $search_query . '%';
    }

    if ($category_id) {
        $where_clauses[] = "p.category_id = :category_id";
        $params[':category_id'] = $category_id;
    }
    
    $query = "SELECT p.*, c.name as category_name 
              FROM products p
              JOIN categories c ON p.category_id = c.id" .
             (empty($where_clauses) ? '' : " WHERE " . implode(' AND ', $where_clauses)) .
             " ORDER BY p.`$order_by` $direction";
    
    $products = db_queryAll($query, $params);

    foreach ($products as &$product) {
        $product['images'] = getProductImages($product['id']);
    }

    return $products;
}

function getProductById($id) {
    $query = "SELECT p.*, c.name as category_name
              FROM products p
              JOIN categories c ON p.category_id = c.id
              WHERE p.id = :id";
    $product = db_queryOne($query, [':id' => $id]);
    if ($product) {
        $product['images'] = getProductImages($id);
    }
    return $product;
}

function getProductImages($product_id) {
    $images = db_queryAll("SELECT image_path, is_main FROM product_images WHERE product_id = :product_id ORDER BY is_main DESC, id ASC", [':product_id' => $product_id]);
    $image_paths = [];
    foreach ($images as $img) {
        $image_paths[] = $img['image_path'];
    }
    return $image_paths;
}

function addProduct($product_data, $image_paths = []) {
    global $pdo;
    try {
        $pdo->beginTransaction();

        $product_id = generateUniqueId('PROD');
        $product_data['id'] = $product_id;

        db_insert('products', [
            'id' => $product_data['id'],
            'name' => $product_data['name'],
            'category_id' => $product_data['category_id'],
            'price' => $product_data['price'],
            'stock' => $product_data['stock'],
            'description' => $product_data['description']
        ]);

        foreach ($image_paths as $index => $path) {
            db_insert('product_images', [
                'product_id' => $product_id,
                'image_path' => $path,
                'is_main' => ($index === 0)
            ]);
        }

        $pdo->commit();
        return true;
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error adding product: " . $e->getMessage());
        return false;
    }
}

function updateProduct($product_id, $product_data, $new_image_paths = []) {
    global $pdo;
    try {
        $pdo->beginTransaction();

        db_update('products', $product_id, [
            'name' => $product_data['name'],
            'category_id' => $product_data['category_id'],
            'price' => $product_data['price'],
            'stock' => $product_data['stock'],
            'description' => $product_data['description']
        ]);

        db_execute("DELETE FROM product_images WHERE product_id = :product_id", [':product_id' => $product_id]);
        foreach ($new_image_paths as $index => $path) {
            db_insert('product_images', [
                'product_id' => $product_id,
                'image_path' => $path,
                'is_main' => ($index === 0)
            ]);
        }

        $pdo->commit();
        return true;
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error updating product: " . $e->getMessage());
        return false;
    }
}


function deleteProduct($id) {
    global $pdo;
    try {
        $pdo->beginTransaction();
        db_delete('products', $id);
        $pdo->commit();
        return true;
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error deleting product: " . $e->getMessage());
        return false;
    }
}
?>