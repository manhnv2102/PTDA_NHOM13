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

// Lấy thông tin người dùng sinh viên từ session
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = '$user_id' AND role = 'sinhvien'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "Không tìm thấy thông tin người dùng.";
    exit();
}

// Xử lý cập nhật ảnh đại diện
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['avatar'])) {
    if ($_FILES['avatar']['error'] == UPLOAD_ERR_OK) {
        $file_tmp_name = $_FILES['avatar']['tmp_name'];
        $file_name = $_FILES['avatar']['name'];
        $upload_dir = "uploads/avatars/";
        $file_path = $upload_dir . basename($file_name);

        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png'];

        if (in_array($file_ext, $allowed_ext)) {
            if (move_uploaded_file($file_tmp_name, $file_path)) {
                $sql_avatar = "UPDATE users SET avatar = '$file_path' WHERE id = '$user_id'";
                if ($conn->query($sql_avatar) === TRUE) {
                    header("Location: home_sinhvien.php");
                    exit();
                } else {
                    $avatar_message = "Lỗi cập nhật ảnh đại diện!";
                }
            } else {
                $avatar_message = "Lỗi tải lên ảnh!";
            }
        } else {
            $avatar_message = "Chỉ hỗ trợ ảnh JPG, JPEG và PNG!";
        }
    } else {
        $avatar_message = "Chưa chọn ảnh hoặc có lỗi trong quá trình tải lên.";
    }
}
// Xử lý cập nhật thông tin cá nhân
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['name'])) {
    $name = $_POST['name'];
    $khoa = $_POST['khoa'];
    $nganh = $_POST['nganh'];
    $dia_chi = $_POST['dia_chi'];
    $phone = $_POST['phone'];

    // Cập nhật vào cơ sở dữ liệu
    $sql_update = "UPDATE users SET name = '$name', khoa = '$khoa', nganh = '$nganh', dia_chi = '$dia_chi', phone = '$phone' WHERE id = '$user_id'";

    if ($conn->query($sql_update) === TRUE) {
        header("Location: home_sinhvien.php"); // Điều hướng lại trang sau khi cập nhật
        exit();
    } else {
        $update_message = "Lỗi cập nhật thông tin!";
    }
}


// Xử lý tải lên đồ án
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['doan'])) {
    if ($_FILES['doan']['error'] == UPLOAD_ERR_OK) {
        $file_tmp_name = $_FILES['doan']['tmp_name'];
        $file_name = $_FILES['doan']['name'];
        $upload_dir = "uploads/doans/";
        $file_path = $upload_dir . basename($file_name);

        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['pdf', 'docx', 'zip'];

        if (in_array($file_ext, $allowed_ext)) {
            if (move_uploaded_file($file_tmp_name, $file_path)) {
                $sql_doan = "INSERT INTO doan (user_id, filename, file_path) VALUES ('$user_id', '$file_name', '$file_path')";
                if ($conn->query($sql_doan) === TRUE) {
                    header("Location: home_sinhvien.php");
                    exit();
                } else {
                    $doan_message = "Lỗi tải lên đồ án!";
                }
            } else {
                $doan_message = "Lỗi tải lên đồ án!";
            }
        } else {
            $doan_message = "Chỉ hỗ trợ các file PDF, DOCX và ZIP!";
        }
    } else {
        $doan_message = "Chưa chọn đồ án hoặc có lỗi trong quá trình tải lên.";
    }
}

$sql_doan = "SELECT * FROM doan WHERE user_id = '$user_id'";
$result_doan = $conn->query($sql_doan);

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chính - Sinh viên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            background-color: lightskyblue;
        }

        h1 {
            color: #343a40;
            font-size: 2.5rem;
            text-align: center;
            margin-bottom: 30px;
        }

        .avatar {
            width: 200px;
            height: 200px;
            object-fit: cover;
            border-radius: 50%;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 4px solid #007bff;
        }

        .avatar:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.5);
        }

        .btn-custom {
            background-color: #28a745;
            color: white;
            border-radius: 30px;
            padding: 10px 25px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .btn-custom:hover {
            background-color: #218838;
        }

        .modal-content {
            border-radius: 15px;
        }

        .table th,
        .table td {
            text-align: center;
            vertical-align: middle;
            padding: 15px;
        }

        .table th {
            background-color: #007bff;
            color: white;
            border-top: 1px solid #ddd;
        }

        .table td {
            background-color: #f8f9fa;
            border-top: 1px solid #ddd;
        }

        .table a {
            color: #007bff;
            text-decoration: none;
        }

        .table a:hover {
            text-decoration: underline;
        }

        .modal-header {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }

        button.btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        button.btn-primary:hover {
            background-color: #0056b3;
            border-color: #004085;
        }

        input[type="text"],
        input[type="file"],
        textarea {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 10px;
            font-size: 1rem;
            width: 100%;
            margin-bottom: 20px;
        }

        input[type="text"]:focus,
        textarea:focus {
            border-color: #007bff;
            outline: none;
        }

        .alert-danger {
            border-radius: 10px;
            padding: 15px;
            background-color: #f8d7da;
            color: #721c24;
            margin-top: 20px;
        }

        .navbar {
            background-color: #343a40;
        }

        .navbar a {
            color: white;
            font-size: 1.2rem;
            padding: 12px 20px;
        }

        .navbar a:hover {
            background-color: #007bff;
            border-radius: 5px;
        }

        .container {
            max-width: 1200px;
            margin-top: 40px;
            margin-bottom: 40px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            background-color: white;
            padding: 30px;
        }

        a.btn-danger {
            background-color: #dc3545;
            color: white;
            font-weight: bold;
            border-radius: 30px;
            padding: 10px 20px;
        }

        a.btn-danger:hover {
            background-color: #c82333;
        }

        .table tr:hover {
            background-color: #f1f1f1;
            cursor: pointer;
        }

        /* Modal Đồ án của bạn */
        .modal-dialog {
            max-width: 900px;
            margin: 30px auto;
            overflow-y: auto;
        }

        .modal-content {
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 20px;
            background-color: #ffffff;
            overflow-x: hidden;
        }

        .modal-header {
            background-color: #007bff;
            color: white;
            font-weight: bold;
            border-bottom: 2px solid #0056b3;
            padding: 15px;
            border-radius: 10px 10px 0 0;
        }

        .modal-header h5 {
            margin: 0;
            font-size: 1.8rem;
        }

        .modal-body {
            padding: 20px;
            background-color: #f9f9f9;
            max-height: 400px;
            overflow-y: auto;
        }

        .modal-body p {
            font-size: 1.1rem;
            color: #333;
            line-height: 1.6;
        }

        .table {
            margin-top: 20px;
            width: 100%;
            background-color: #fff;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            text-align: center;
            padding: 15px;
            border: 1px solid #ddd;
        }

        .table th {
            background-color: #007bff;
            color: white;
        }

        .table td {
            background-color: #f8f9fa;
        }

        .table a {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }

        .table a:hover {
            text-decoration: underline;
        }

        .table tr:hover {
            background-color: #f1f1f1;
            cursor: pointer;
        }

        .modal-footer {
            background-color: #f1f1f1;
            padding: 15px;
            text-align: right;
            border-radius: 0 0 10px 10px;
        }

        .modal-footer button {
            background-color: #28a745;
            color: white;
            border-radius: 5px;
            padding: 10px 20px;
            font-size: 1rem;
            transition: background-color 0.3s;
        }

        .modal-footer button:hover {
            background-color: #218838;
        }

        .table td a.btn-danger {
            padding: 6px 12px;
            border-radius: 5px;
            text-align: center;
            display: inline-block;
        }

        @media (max-width: 768px) {
            .modal-dialog {
                width: 90%;
            }

            .table th,
            .table td {
                font-size: 0.875rem;
                padding: 10px;
            }

            .modal-body {
                padding: 15px;
                max-height: 300px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Chào mừng, <?php echo $user['name']; ?>!</h1>

        <div class="row">
            <div class="col-md-4">
                <img src="<?php echo !empty($user['avatar']) ? $user['avatar'] : 'uploads/avatars/default_avatar.png'; ?>" alt="Avatar" class="img-thumbnail" style="width: 200px; height: 200px;">
                <br><br>
            </div>
            <div class="col-md-8">
                <h2>Thông tin cá nhân:</h2>
                <p><strong>Mã sinh viên:</strong> <?php echo $user['msv']; ?></p>
                <p><strong>Khoa:</strong> <?php echo $user['khoa']; ?></p>
                <p><strong>Ngành:</strong> <?php echo $user['nganh']; ?></p>
                <p><strong>Địa chỉ:</strong> <?php echo $user['dia_chi']; ?></p>
                <p><strong>Số điện thoại:</strong> <?php echo $user['phone']; ?></p>


                <button class="btn btn-primary" data-toggle="modal" data-target="#updateModal">Cập nhật thông tin</button>
                <!-- Modal Cập nhật thông tin -->
                <div class="modal fade" id="updateModal" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="updateModalLabel">Cập nhật thông tin cá nhân</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form action="home_sinhvien.php" method="POST" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label for="name">Họ tên:</label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?php echo $user['name']; ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="khoa">Khoa:</label>
                                        <input type="text" class="form-control" id="khoa" name="khoa" value="<?php echo $user['khoa']; ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="nganh">Ngành:</label>
                                        <input type="text" class="form-control" id="nganh" name="nganh" value="<?php echo $user['nganh']; ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="dia_chi">Địa chỉ:</label>
                                        <input type="text" class="form-control" id="dia_chi" name="dia_chi" value="<?php echo $user['dia_chi']; ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="phone">Số điện thoại:</label>
                                        <input type="text" class="form-control" id="phone" name="phone" value="<?php echo $user['phone']; ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="avatar">Chọn ảnh đại diện mới:</label>
                                        <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
                                    </div>
                                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>


                <button class="btn btn-primary" data-toggle="modal" data-target="#uploadModal">Tải lên đồ án</button>
                <!-- Modal Tải lên đồ án -->
                <div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="uploadModalLabel">Tải lên đồ án</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form action="home_sinhvien.php" method="POST" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label for="doan">Chọn file đồ án (PDF, DOCX, ZIP):</label>
                                        <input type="file" class="form-control-file" id="doan" name="doan" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Tải lên</button>
                                </form>
                                <?php if (isset($doan_message)) { ?>
                                    <div class="alert alert-danger mt-3">
                                        <?php echo $doan_message; ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>

                <button class="btn btn-success" data-toggle="modal" data-target="#projectModal">Đồ án của bạn</button>
                <!-- Modal Đồ án của bạn -->
                <div class="modal fade" id="projectModal" tabindex="-1" role="dialog" aria-labelledby="projectModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="projectModalLabel">Đồ án của bạn</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <p><strong>Họ và tên:</strong> <?php echo $user['name']; ?></p>
                                <p><strong>Mã sinh viên:</strong> <?php echo $user['msv']; ?></p>

                                <!-- Danh sách đồ án -->
                                <h5>Danh sách đồ án:</h5>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Mã đồ án</th>
                                            <th>Trạng thái</th>
                                            <th>File</th>
                                            <th>Điểm</th>
                                            <th>Nhận xét</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if ($result_doan->num_rows > 0) {
                                            while ($doan = $result_doan->fetch_assoc()) {
                                                echo "<tr>";
                                                echo "<td>" . $doan['id'] . "</td>";
                                                echo "<td>" . $doan['status'] . "</td>";
                                                echo "<td><a href='" . $doan['file_path'] . "' target='_blank'>" . $doan['filename'] . "</a></td>";

                                                if ($doan['status'] == 'Đã duyệt') {
                                                    echo "<td>" . (isset($doan['point']) ? $doan['point'] : 'Chưa có điểm') . "</td>";
                                                    echo "<td>" . (isset($doan['comments']) ? $doan['comments'] : 'Chưa có nhận xét') . "</td>";
                                                    echo "<td></td>";
                                                } else {
                                                    echo "<td>-</td>";
                                                    echo "<td>-</td>";
                                                    echo "<td>";
                                                    echo "<a href='delete_doan.php?id=" . $doan['id'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Bạn có chắc chắn muốn xóa đồ án này không?\")'>Xóa</a>";
                                                    echo "</td>";
                                                }

                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='6'>Chưa có đồ án nào.</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <a href="logout.php" class="btn btn-danger">Đăng xuất</a>
        </div>
    </div>

    <!-- Bootstrap JS và jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $('#editProjectModal').on('show.bs.modal', function(event) {
            $('#projectModal').modal('hide');

            var button = $(event.relatedTarget);
            var doan_id = button.data('id');
            var filename = button.data('filename');
            var status = button.data('status');

            var modal = $(this);
            modal.find('#doan_id').val(doan_id);
            modal.find('#filename').val(filename);
            modal.find('#status').val(status);
        });

        $('#editProjectModal').on('hidden.bs.modal', function() {
            $('#projectModal').modal('show');
        });
    </script>
</body>

</html>