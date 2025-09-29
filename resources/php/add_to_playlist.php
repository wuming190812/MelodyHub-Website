<?php
include "../database/func.php";
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in.']);
    exit();
}

if (!isset($_GET['song_id'], $_GET['playlist_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
    exit();
}

$connect     = new functional();
$song_id     = intval($_GET['song_id']);
$playlist_id = intval($_GET['playlist_id']);
$user_id     = intval($_SESSION['user_id']);

// Confirm playlist belongs to user
$check = $connect->select("COUNT(*) as c", "playlist", "WHERE playlist_id='$playlist_id' AND creator_id='$user_id'");
$row = mysqli_fetch_assoc($check);
if ($row['c'] == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized playlist']);
    exit();
}

// Check if already exists in truck
$exist = $connect->select("COUNT(*) as c", "track", "WHERE playlist_id='$playlist_id' AND songs_id='$song_id'");
$row2 = mysqli_fetch_assoc($exist);

if ($row2['c'] == 0) {
    $insert = $connect->insert(
        "track(songs_id, playlist_id)",
        "VALUES('$song_id', '$playlist_id')"
    );
    if ($insert) {
        echo json_encode(['status' => 'success', 'message' => 'Song added to playlist']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($connect->conn)]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Song already in this playlist']);
}
?>
