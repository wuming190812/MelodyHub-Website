<?php   
include "../database/func.php";
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
        
        echo "<script>alert('Login Successful. Welcome " . htmlspecialchars($_SESSION['user']) . " to visit MelodyHub.'); window.location.href='../../index.php';</script>";
    } else {
        echo "<script>alert('Your password is wrong or the email does not exist. Please try again.');</script>";
    }
}
?>