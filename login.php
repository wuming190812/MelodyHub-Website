<?php   
include "resources/database/func.php";
$connect = new functional();
$isLoggedIn = isset($_SESSION['user']);
if ($isLoggedIn) {
    header('Location: index.php');
    exit();
}

if (isset($_POST['submit'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = $connect->select("*", "user", "where email='$email'");
    $fetch = mysqli_fetch_array($sql);

    if ($fetch && password_verify($password, $fetch['password'])) {
        $_SESSION['user'] = $fetch['email'];
        $_SESSION['user_id'] = $fetch['id'];
        
        echo "<script>alert('Login Successful. Welcome " . htmlspecialchars($_SESSION['user']) . " to visit MelodyHub.'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Your password is wrong or the email does not exist. Please try again.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MelodyHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="resources/css/login.css">
</head>
<body class="d-flex flex-column min-vh-100">
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="row justify-content-center">
            <div>
                <div class="bg-dark p-4 rounded-3">
                    <h4 class="text-center mb-4 gradient-text">Login to MelodyHub</h4>
                    <form action="resources/php/login-process.php" method="POST" onsubmit="return validateForm()">
                        <div class="mb-3">
                            <label for="email" class="form-label text-white">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email">
                            <div id="email-error" class="error-message">Please provide your email address.</div>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label text-white">Password</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password">
                            <div id="password-error" class="error-message">Please provide your password.</div>
                        </div>
                        <button type="submit" name="submit" class="btn btn-warning text-dark w-100 mb-3">Login</button>
                        <div class="text-center">
                            <a href="register.php" class="text-white">Don't have an account? Register here</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <script src="resources/javascript/login.js"></script>
</body>
</html>