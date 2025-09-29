<?php
include "../database/func.php";
$connect = new functional();

$isLoggedIn = isset($_SESSION['user']);
if ($isLoggedIn) {
    header('Location: index.php');
    exit();
}

if (isset($_POST['submit'])) 
{
    $email = $_POST['email'];
    $password = $_POST['password'];
    $conf_password = $_POST['confirm-password'];

    $sql = $connect->select("*", "user", "where email = '$email'");
    $row = mysqli_num_rows($sql);

    if ($row == 0) 
    {
        if ($password == $conf_password) 
        {
            $password_hashed = password_hash($password, PASSWORD_BCRYPT);
            $save = $connect->insert("user(email, password)", "values('$email', '$password_hashed')");
            
            if ($save) 
            {
                echo "<script>alert('Register Success.'); window.location.href='../../login.php';</script>";
            } else 
            {
                echo "<script>alert('Register failed.'); window.location.href='../../register.php';</script>";
            }
        } else 
        {
            echo "<script>alert('Password not match.'); window.location.href='../../register.php';</script>";
        }
    } else 
    {
        echo "<script>alert('This email was already registered.'); window.location.href='../../login.php';</script>";
    }
}
?>