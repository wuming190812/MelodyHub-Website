<?php
include "../database/func.php";
$connect = new functional();
$isLoggedIn = isset($_SESSION['user']);

// Handle the form submission to create a playlist
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    $name = $_POST['name'];
    $creator_id = $_SESSION['user_id'];

    // Define a default image URL based on the playlist name
    $imageUrl = "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQg_ITtT5GMZ-5j9ybW17fpwAOm3Lg0hdzNcw&s?text=" . urlencode($name);

    // Insert the playlist data into the database with the image URL
    if ($connect->insert("playlist (playlist_name, creator_id, playlist_image)", "values('$name', '$creator_id', '$imageUrl')")) {
        // Redirect with the image URL as a query parameter
        echo "<script>
            alert('Playlist created successfully!');
            window.location.href = '../../playlist.php';
        </script>";
    } else {
        echo "<script>alert('Failed to create playlist.');</script>";
    }
}
?>