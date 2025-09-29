<?php
header("Content-Type: application/json");

include "../database/func.php";
$connect = new functional();

$response = ["success" => false, "message" => ""];

$user_id = intval($_SESSION['user_id'] ?? 0);
$action = $_POST['action'] ?? '';

if ($user_id <= 0) {
    $response['message'] = "User not logged in";
    echo json_encode($response);
    exit;
}

try {
    if ($action === "delete_playlist") {
        $playlist_id = intval($_POST['playlist_id'] ?? 0);

        if ($playlist_id <= 0) {
            $response['message'] = "Invalid playlist ID";
            echo json_encode($response);
            exit;
        }

        // 确认是 playlist 的 owner
        $check = $connect->select("*", "playlist", "WHERE playlist_id='$playlist_id' AND creator_id='$user_id'");
        if (mysqli_num_rows($check) === 0) {
            $response['message'] = "You don't have permission to delete this playlist";
            echo json_encode($response);
            exit;
        }

        // 删除 playlist 下所有 tracks
        $connect->delete("track", "where playlist_id='$playlist_id'");
        // 删除 likes / follows
        $connect->delete("playlist_likes", "where playlist_id='$playlist_id'");
        $connect->delete("playlist_follows", "where playlist_id='$playlist_id'");
        // 删除 playlist 本身
        $connect->delete("playlist", "where playlist_id='$playlist_id'");

        $response['success'] = true;
        $response['message'] = "Playlist deleted successfully";

    } elseif ($action === "remove_track") {
        $track_id = intval($_POST['track_id'] ?? 0);

        if ($track_id <= 0) {
            $response['message'] = "Invalid track ID";
            echo json_encode($response);
            exit;
        }

        // 确认这个 track 属于用户的 playlist
        $check = $connect->select(
            "t.track_id",
            "track t JOIN playlist p ON t.playlist_id = p.playlist_id",
            "WHERE t.track_id='$track_id' AND p.creator_id='$user_id'"
        );

        if (mysqli_num_rows($check) === 0) {
            $response['message'] = "You don't have permission to remove this track";
            echo json_encode($response);
            exit;
        }

        $connect->delete("track", "where track_id='$track_id'");

        $response['success'] = true;
        $response['message'] = "Track removed successfully";

    } else {
        $response['message'] = "Invalid action";
    }
} catch (Exception $e) {
    $response['message'] = "Server error: " . $e->getMessage();
}

echo json_encode($response);
