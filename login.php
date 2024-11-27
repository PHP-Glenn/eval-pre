<!DOCTYPE html>
<html lang="en">
<?php 
session_start();
include('./db_connect.php');
ob_start();

// Fetch system settings
$system = $conn->query("SELECT * FROM system_settings")->fetch_array();
foreach($system as $k => $v){
    $_SESSION['system'][$k] = $v;
}
ob_end_flush();

if (isset($_SESSION['login_id'])) {
    header("location:index.php?page=home");
    exit();
}
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        /* General Styling */
        body, html {
            height: 100%;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            background-image: url('login.png'); /* Use the background image */
            background-size: cover;
            background-position: center;
            font-family: 'Arial', sans-serif;
        }

        .login-box {
            background-color: rgba(115, 115, 115, 0.6); /* Slightly whiter transparent background */

            width: 400px;
            padding: 20px;
            border-radius: 15px; /* Softer corners */
            bo
            .card {
    width: 100%;
    max-width: 600px;
    padding: 20px;
    background-color: rgba(1, 1, 1, 0.3); /* Slightly whiter transparent background */
    border-radius: 15px; /* Rounded corners */
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.5); /* Shadow for depth */
    color: white; /* White text for better contrast */
}x-shadow: 0 5px 20px rgba(0, 0, 0, 0.5); /* Stronger shadow for depth */
        }
    </style>
</head>

<body class="hold-transition login-page">
    <div class="login-box">
        
        <div class="card">
            <div class="card-body login-card-body">
                <form action="" id="login-form">
                    <div class="input-group mb-3">
                        <input type="email" class="form-control" name="email" required placeholder="Email">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
    <input type="password" class="form-control" name="password" id="password" required placeholder="Password">
    <div class="input-group-append">
        <div class="input-group-text">
            <span class="fas fa-eye" id="toggle-password" style="cursor: pointer;"></span>
        </div>
    </div>
</div>



                    <div class="form-group mb-3">
                        <label for="login">Login As</label>
                        <select name="login" id="login" class="custom-select custom-select-sm">
                            <option value="3">Student</option>
                            <option value="4">Supervisor</option>
                            <option value="1">Admin</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-8">
                            <div class="icheck-primary">
                                <a href="register.php"> Register here.</a>
                                </div>
                           
                        </div>
                        <div class="col-4">
                            <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
       
        <div id="alert-container"></div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        // Toggle Password Visibility
        $('#toggle-password').click(function () {
            const passwordField = $('#password');
            const type = passwordField.attr('type') === 'password' ? 'text' : 'password';
            passwordField.attr('type', type);

            // Toggle the eye icon
            $(this).toggleClass('fa-eye fa-eye-slash');
        });

        // Login Form Submission
        $('#login-form').submit(function(e) {
            e.preventDefault();
            start_load(); // Ensure this function exists in your codebase

            // Remove previous alerts
            if ($(this).find('.alert-danger').length > 0) {
                $(this).find('.alert-danger').remove();
            }

            $.ajax({
                url: 'ajax.php?action=login',
                method: 'POST',
                data: $(this).serialize(),
                error: function(err) {
                    console.error(err);
                    end_load(); // Ensure this function exists in your codebase
                },
                success: function(resp) {
                    if (resp == 1) {
                        location.href = 'index.php?page=home';
                    } else {
                        $('#login-form').prepend('<div class="alert alert-danger">Username or password is incorrect.</div>');
                        end_load();
                    }
                }
            });
        });
    });
</script>

    <?php include 'footer.php'; ?>
</body>
</html>
