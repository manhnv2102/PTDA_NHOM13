<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "duan_project";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $doan_id = $_GET['id'];
    $sql = "SELECT * FROM doan WHERE id = '$doan_id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $doan = $result->fetch_assoc();
        $file_path = $doan['file_path'];

        if (file_exists($file_path)) {
            unlink($file_path);
        }

        $sql_delete = "DELETE FROM doan WHERE id = '$doan_id'";
        if ($conn->query($sql_delete) === TRUE) {
            header("Location: home_sinhvien.php");
            exit();
        } else {
            echo "Lỗi xóa đồ án!";
        }
    } else {
        echo "Không tìm thấy đồ án.";
        exit();
    }
}
