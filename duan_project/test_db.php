<?php
include('db.php');

try {
    $stmt = $pdo->query("SELECT 1");
    echo "Kết nối thành công!";
} catch (PDOException $e) {
    echo "Lỗi kết nối: " . $e->getMessage();
}
?>
