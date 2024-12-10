<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'sinhvien') {
    header("Location: login.html");
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

$msv = $_SESSION['username'];
$sql = "SELECT * FROM users WHERE msv='$msv'";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $khoa = $_POST['khoa'];
    $nganh = $_POST['nganh'];
    $dia_chi = $_POST['dia_chi'];
    $phone = $_POST['phone'];

    $sql = "UPDATE users SET name='$name', khoa='$khoa', nganh='$nganh', dia_chi='$dia_chi', phone='$phone' WHERE msv='$msv'";
    if ($conn->query($sql) === TRUE) {
        header("Location: home_sinhvien.php");
    } else {
        echo "Error updating record: " . $conn->error;
    }
}

?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa thông tin</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <h2>Chỉnh sửa thông tin cá nhân</h2>
        <form action="edit_profile.php" method="POST">
            <label for="name">Họ tên:</label>
            <input type="text" id="name" name="name" value="<?php echo $user['name']; ?>" required><br>

            <label for="khoa">Khoa:</label>
            <input type="text" id="khoa" name="khoa" value="<?php echo $user['khoa']; ?>" required><br>

            <label for="nganh">Ngành:</label>
            <input type="text" id="nganh" name="nganh" value="<?php echo $user['nganh']; ?>" required><br>

            <label for="dia_chi">Địa chỉ:</label>
            <input type="text" id="dia_chi" name="dia_chi" value="<?php echo $user['dia_chi']; ?>" required><br>

            <label for="phone">Số điện thoại:</label>
            <input type="text" id="phone" name="phone" value="<?php echo $user['phone']; ?>" required><br>

            <button type="submit">Cập nhật</button>
        </form>
    </div>
</body>

</html>

<?php
$conn->close();
?>