<?php
include "../database/func.php";
$connect = new functional();

header('Content-Type: application/json');

if (!isset($_GET['song_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No song specified']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to download songs']);
    exit;
}

$song_id = intval($_GET['song_id']);
$user_id = intval($_SESSION['user_id']);

// Check if the user already downloaded this song
$checkSong = $connect->select("COUNT(*) as count", "song_downloads", "WHERE song_id = '$song_id' AND user_id = '$user_id'");
$rowSong = mysqli_fetch_assoc($checkSong);

if ($rowSong['count'] > 0) {
    echo json_encode(['status' => 'error', 'message' => 'You have already downloaded this song']);
    exit;
}

// Count how many songs the user has already downloaded
$downloadCountQuery = $connect->select("COUNT(*) as count", "song_downloads", "WHERE user_id = '$user_id'");
$downloadCount = mysqli_fetch_assoc($downloadCountQuery)['count'] ?? 0;

if ($downloadCount >= 5) {
    echo json_encode(['status' => 'error', 'message' => 'You have reached the maximum of 5 downloads']);
    exit;
}

// Insert download record
$connect->insert("song_downloads(song_id, user_id, downloaded_at)", "VALUES('$song_id', '$user_id', NOW())");

echo json_encode(['status' => 'success', 'message' => 'Download recorded']);
