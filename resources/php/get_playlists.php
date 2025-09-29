<?php
include "../database/func.php";
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['playlists' => []]);
    exit();
}

$connect = new functional();
$user_id = intval($_SESSION['user_id']);

// ambil semua playlist user
$result = $connect->select("*", "playlist", "WHERE creator_id='$user_id'");
$playlists = [];

while ($row = mysqli_fetch_assoc($result)) {
    $playlists[] = [
        'playlist_id'   => $row['playlist_id'],
        'playlist_name' => $row['playlist_name'],
        'playlist_image'=> $row['playlist_image']
    ];
}

echo json_encode(['playlists' => $playlists]);
