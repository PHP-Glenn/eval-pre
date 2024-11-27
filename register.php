<?php
session_start();
include 'db_connect.php'; 

$school_id = $firstname = $lastname = $class_id = $email = $avatar = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $school_id = $_POST['school_id'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $class_id = $_POST['class_id'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $cpass = $_POST['cpass'];

    if ($password !== $cpass) {
        echo "<script>alert('Passwords do not match.');</script>";
    } else {
        $stmt = $conn->prepare("SELECT id FROM student_list WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            echo "<script>alert('Email already exists. Please use a different email.');</script>";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            if (isset($_FILES['img']) && $_FILES['img']['error'] == UPLOAD_ERR_OK) {
                $file_tmp = $_FILES['img']['tmp_name'];
                $file_name = basename($_FILES['img']['name']);
                $file_type = mime_content_type($file_tmp);

                if (in_array($file_type, ['image/jpeg', 'image/png', 'image/gif']) && $_FILES['img']['size'] <= 2 * 1024 * 1024) {
                    $avatar = $file_name;
                    move_uploaded_file($file_tmp, 'assets/uploads/' . $avatar);
                } else {
                    echo "<script>alert('Invalid file type or size. Only images under 2MB are allowed.');</script>";
                    exit;
                }
            }

            $stmt = $conn->prepare("INSERT INTO student_list (school_id, firstname, lastname, class_id, email, password, avatar) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $school_id, $firstname, $lastname, $class_id, $email, $hashed_password, $avatar);

        
            if ($stmt->execute()) {
                echo "<script>alert('Data successfully saved.');</script>";
                header("Location: index.php?page=home");
                exit();
            } else {
                echo "<script>alert('Error saving data.');</script>";
            }

            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Student</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
    <style>
        body, html {
            height: 100%;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            background-image: url('login.png'); 
            background-size: cover;
            background-position: center;
            font-family: 'Arial', sans-serif;
        }
</style>
</head>
<body>
    <div class="card">
        <h2 class="text-center"><b>Student Registration</b></h2>
        <div class="card-body">
            <form action="" id="register_student" method="post" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6 border-right">
                        <div class="form-group">
                            <label for="school_id" class="control-label">School ID</label>
                            <input type="text" name="school_id" class="form-control form-control-sm" required value="<?php echo htmlspecialchars($school_id); ?>">
                        </div>
                        <div class="form-group">
                            <label for="firstname" class="control-label">First Name</label>
                            <input type="text" name="firstname" class="form-control form-control-sm" required value="<?php echo htmlspecialchars($firstname); ?>">
                        </div>
                        <div class="form-group">
                            <label for="lastname" class="control-label">Last Name</label>
                            <input type="text" name="lastname" class="form-control form-control-sm" required value="<?php echo htmlspecialchars($lastname); ?>">
                        </div>
                        <div class="form-group">
                            <label for="class_id" class="control-label">Year and Section</label>
                            <select name="class_id" id="class_id" class="form-control form-control-sm select2" required>
                                <option value=""></option>
                                <?php 
                                $classes = $conn->query("SELECT id, CONCAT(curriculum,' ',level,' - ',section) as class FROM class_list");
                                while ($row = $classes->fetch_assoc()): ?>
                                    <option value="<?php echo $row['id']; ?>" <?php echo ($class_id == $row['id']) ? "selected" : ""; ?>>
                                        <?php echo htmlspecialchars($row['class']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email" class="control-label">Email</label>
                            <input type="email" class="form-control form-control-sm" name="email" required value="<?php echo htmlspecialchars($email); ?>">
                        </div>
                        <div class="form-group">
                            <label for="password" class="control-label">Password</label>
                            <input type="password" class="form-control form-control-sm" name="password" required>
                        </div>
                        <div class="form-group">
                            <label for="cpass" class="label control-label">Confirm Password</label>
                            <input type="password" class="form-control form-control-sm" name="cpass" required>
                            <small id="pass_match" data-status=''></small>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="col-lg-12 text-right justify-content-center d-flex">
                    <button class="btn btn-primary mr-2">Register</button>
                    <button class="btn btn-secondary" type="button" onclick="location.href = 'index.php?page=student_list'"> Sign In</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        $('[name="password"],[name="cpass"]').keyup(function () {
            var pass = $('[name="password"]').val();
            var cpass = $('[name="cpass"]').val();
            if (cpass == '' || pass == '') {
                $('#pass_match').attr('data-status', '');
            } else {
                if (cpass === pass) {
                    $('#pass_match').attr('data-status', '1').html('<i class="text-success">Password Matched.</i>');
                } else {
                    $('#pass_match').attr('data-status', '2').html('<i class="text-danger">Password does not match.</i>');
                }
            }
        });

        function displayImg(input, _this) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    $('#cimg').attr('src', e.target.result);
                }
                reader.readAsDataURL(input.files[0]);
                _this.siblings('.custom-file-label').html(input.files[0].name);
            }
        }
    </script>
</body>
</html>
