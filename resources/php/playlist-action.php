<?php
include "../database/func.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit();
}

$connect = new functional();
$user_id = intval($_SESSION['user_id']);
$playlist_id = intval($_POST['playlist_id'] ?? 0);
$action = $_POST['action'] ?? '';

if ($playlist_id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid playlist ID"]);
    exit();
}

if ($action === 'like') {
    $connect->insert("playlist_likes(playlist_id, user_id)",  "values('$playlist_id', '$user_id')");
} elseif ($action === 'unlike') {
    $connect->delete("playlist_likes", "WHERE playlist_id='$playlist_id' AND user_id='$user_id'");
} elseif ($action === 'follow') {
    $connect->insert("playlist_follows(playlist_id, user_id)", "values('$playlist_id', '$user_id')");
} elseif ($action === 'unfollow') {
    $connect->delete("playlist_follows", "WHERE playlist_id='$playlist_id' AND user_id='$user_id'");
} else {
    echo json_encode(["success" => false, "message" => "Invalid action"]);
    exit();
}

// Get updated counts
$like_count = mysqli_fetch_assoc($connect->select("COUNT(*) as total", "playlist_likes", "WHERE playlist_id='$playlist_id'"))['total'];
$follow_count = mysqli_fetch_assoc($connect->select("COUNT(*) as total", "playlist_follows", "WHERE playlist_id='$playlist_id'"))['total'];

echo json_encode([
    "success" => true,
    "like_count" => $like_count,
    "follow_count" => $follow_count
]);
