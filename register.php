<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - MelodyHub</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts with fallback -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet" onerror="this.href='https://fonts.cdnfonts.com/css/poppins';">
    <!-- Custom Styles -->
    <link rel="stylesheet" href="resources/css/register.css">
</head>
<body class="d-flex flex-column min-vh-100">
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="row justify-content-center">
            <div>
                <!-- Register Form -->
                <div class="bg-dark p-4 rounded-3">
                    <h4 class="text-center mb-4 gradient-text">Register for MelodyHub</h4>
                    <form action="resources/php/register_process.php" method="POST" onsubmit="return validateForm()">
                        <div class="mb-3">
                            <label for="email" class="form-label text-white">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email">
                            <div id="email-error" class="error-message">Email is required.</div>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label text-white">Password</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password">
                            <div id="password-error" class="error-message">Password is required.</div>
                        </div>
                        <div class="mb-3">
                            <label for="confirm-password" class="form-label text-white">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm-password" name="confirm-password" placeholder="Confirm your password">
                            <div id="confirm-password-error" class="error-message">Confirm password is required.</div>
                        </div>
                        <button type="submit" name="submit" class="btn btn-warning text-dark w-100 mb-3">Register</button>
                        <div class="text-center">
                            <a href="login.php" class="text-white">Already have an account? Login here</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="resources/javascript/register.js"></script>
</body>
</html>