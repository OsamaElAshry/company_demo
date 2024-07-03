<?php
$host = "localhost";
$username = "root";
$password = "";
$dbName = "company";
$con = mysqli_connect($host, $username, $password, $dbName);

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Initialize variables
$mode = "create";
$name = "";
$department = "";
$phone = "";
$gender = "";
$userid = null;
$image_name = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST["name"];
    $department = $_POST["department"];
    $phone = $_POST["phone"];
    $gender = $_POST["gender"];
    $image_name = $_FILES['image']['name'];
    $image_tmp = $_FILES['image']['tmp_name'];
    $location = "./upload/" . $image_name;

    // Move uploaded file
    if (move_uploaded_file($image_tmp, $location)) {
        if (isset($_POST["submit"])) {
            // Insert query
            $insert = "INSERT INTO `employees` (name, department, phone, gender, image) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($con, $insert);
            mysqli_stmt_bind_param($stmt, 'sssss', $name, $department, $phone, $gender, $image_name);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } elseif (isset($_POST["update"])) {
            // Update query
            $userid = $_POST["userid"];
            $update = "UPDATE `employees` SET `name` = ?, department = ?, phone = ?, gender = ?, image = ? WHERE id = ?";
            $stmt = mysqli_prepare($con, $update);
            mysqli_stmt_bind_param($stmt, 'sssssi', $name, $department, $phone, $gender, $image_name, $userid);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            header('Location: index.php');
        }
    }
}

if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $selectOne = "SELECT * FROM `employees` WHERE id = ?";
    $stmt = mysqli_prepare($con, $selectOne);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    $name = $row['name'];
    $department = $row['department'];
    $phone = $row['phone'];
    $gender = $row['gender'];
    $userid = $id;
    $mode = "update";
}

if (isset($_GET['delete'])) {
    $id = $_GET["delete"];
    $delete = "DELETE FROM `employees` WHERE id = ?";
    $stmt = mysqli_prepare($con, $delete);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: index.php");
}

// Read query
$select = "SELECT * FROM `employees`";
$selectquery = mysqli_query($con, $select);

mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="./main.css">
</head>
<body>
    <div class="container col-6 py-5">
        <div class="row justify-content-center mt-5">
            <div class="col-12">
                <div class="card bg-dark text-light">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="form-group mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" value="<?=$name?>" class="form-control" name="name" id="name">
                            </div>
                            <div class="form-group mb-3">
                                <label for="department" class="form-label">Department</label>
                                <input type="text" class="form-control" value="<?=$department?>" name="department" id="department">
                            </div>
                            <div class="form-group mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" class="form-control" value="<?=$phone?>" name="phone" id="phone">
                            </div>
                            <div class="form-group mb-3">
                                <label for="gender" class="form-label">Gender</label>
                                <select name="gender" id="gender" class="form-select">
                                    <option value="male" <?= $gender == "male" ? "selected" : "" ?>>Male</option>
                                    <option value="female" <?= $gender == "female" ? "selected" : "" ?>>Female</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="image">Employee Image</label>
                                <input type="file" name="image" accept="image/*" class="form-control">
                            </div>
                            <div class="text-center form-group">
                                <?php if ($mode == "create"): ?>
                                    <button name="submit" class="btn btn-primary">Add Employee</button>
                                <?php else: ?>
                                    <input type="hidden" name="userid" value="<?=$userid?>">
                                    <button name="update" class="btn btn-primary">Update</button>
                                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <table class="table table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Phone</th>
                        <th>Gender</th>
                        <th>Image</th>
                        <th colspan="2">Action</th>
                    </tr>
                    <?php foreach($selectquery as $employee): ?>
                        <tr>
                            <td><?= $employee['id'] ?></td>
                            <td><?= $employee['name'] ?></td>
                            <td><?= $employee['department'] ?></td>
                            <td><?= $employee['phone'] ?></td>
                            <td><?= $employee['gender'] ?></td>
                            <td><img src="./upload/<?= $employee['image'] ?>" alt="Employee Image" width="50"></td>
                            <td><a href="?edit=<?= $employee['id'] ?>" class="btn btn-warning">Edit</a></td>
                            <td><a href="?delete=<?= $employee['id'] ?>" class="btn btn-danger">Delete</a></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>
</body>
</html>