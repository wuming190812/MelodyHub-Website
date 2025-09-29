<?php
include "../database/func.php";
$connect = new functional();
$isLoggedIn = isset($_SESSION['user']);

// Handle like/unlike & follow/unfollow actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['playlist_id'], $_POST['action'])) {
    $playlist_id = intval($_POST['playlist_id']);
    $user_id = $_SESSION['user_id'];

    if ($_POST['action'] === 'like') {
        $connect->insert("playlist_likes(playlist_id,user_id)", "values('$playlist_id','$user_id')");
    } elseif ($_POST['action'] === 'unlike') {
        $connect->delete("playlist_likes", "WHERE playlist_id = '$playlist_id' AND user_id = '$user_id'");
    } elseif ($_POST['action'] === 'follow') {
        $connect->insert("playlist_follows(playlist_id,user_id)", "values('$playlist_id','$user_id')");
    } elseif ($_POST['action'] === 'unfollow') {
        $connect->delete("playlist_follows", "WHERE playlist_id = '$playlist_id' AND user_id = '$user_id'");
    }

    // 刷新页面避免重复提交
    header("Location: ../../playlist_details.php?id=$playlist_id");
    exit();
}

?>