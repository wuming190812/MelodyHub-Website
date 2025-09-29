<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "../database/func.php";

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

// Check if user is logged in
if (!isset($_SESSION['user']) || !isset($_SESSION['user_id'])) {
    $response['message'] = 'User not logged in';
    error_log('Session error: User not logged in');
    echo json_encode($response);
    exit();
}

$connect = new functional();
$user_id = intval($_SESSION['user_id']);
$artist_id = isset($_POST['artist_id']) ? intval($_POST['artist_id']) : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($artist_id <= 0 || !in_array($action, ['like', 'unlike', 'follow', 'unfollow'])) {
    $response['message'] = 'Invalid request';
    error_log("Invalid request: artist_id=$artist_id, action=$action");
    echo json_encode($response);
    exit();
}

try {
    if ($action === 'like') {
        $checkLike = $connect->select("COUNT(*) as count", "artist_likes", "WHERE artist_id = '$artist_id' AND user_id = '$user_id'");
        $result = mysqli_fetch_assoc($checkLike);
        error_log("Check like query result: " . print_r($result, true));
        if ($result['count'] == 0) {
            $connect->insert("artist_likes(artist_id, user_id)", "VALUES('$artist_id', '$user_id')");
            $response['success'] = true;
            $response['message'] = 'Artist liked';
            $response['is_liked'] = true;
        } else {
            $response['message'] = 'Already liked';
        }
    } elseif ($action === 'unlike') {
        $checkLike = $connect->select("COUNT(*) as count", "artist_likes", "WHERE artist_id = '$artist_id' AND user_id = '$user_id'");
        $result = mysqli_fetch_assoc($checkLike);
        error_log("Check unlike query result: " . print_r($result, true));
        if ($result['count'] > 0) {
            error_log("Executing delete: DELETE FROM artist_likes WHERE artist_id = '$artist_id' AND user_id = '$user_id'");
            $connect->delete("artist_likes", "WHERE artist_id = '$artist_id' AND user_id = '$user_id'");
            $response['success'] = true;
            $response['message'] = 'Artist unliked';
            $response['is_liked'] = false;
        } else {
            $response['message'] = 'Not liked yet';
        }
    } elseif ($action === 'follow') {
        $checkFollow = $connect->select("COUNT(*) as count", "artist_follows", "WHERE artist_id = '$artist_id' AND user_id = '$user_id'");
        $result = mysqli_fetch_assoc($checkFollow);
        error_log("Check follow query result: " . print_r($result, true));
        if ($result['count'] == 0) {
            $connect->insert("artist_follows(artist_id, user_id)", "VALUES('$artist_id', '$user_id')");
            $response['success'] = true;
            $response['message'] = 'Artist followed';
            $response['is_followed'] = true;
        } else {
            $response['message'] = 'Already followed';
        }
    } elseif ($action === 'unfollow') {
        $checkFollow = $connect->select("COUNT(*) as count", "artist_follows", "WHERE artist_id = '$artist_id' AND user_id = '$user_id'");
        $result = mysqli_fetch_assoc($checkFollow);
        error_log("Check unfollow query result: " . print_r($result, true));
        if ($result['count'] > 0) {
            error_log("Executing delete: DELETE FROM artist_follows WHERE artist_id = '$artist_id' AND user_id = '$user_id'");
            $connect->delete("artist_follows", "WHERE artist_id = '$artist_id' AND user_id = '$user_id'");
            $response['success'] = true;
            $response['message'] = 'Artist unfollowed';
            $response['is_followed'] = false;
        } else {
            $response['message'] = 'Not followed yet';
        }
    }

    // Update like and follow counts
    $like_count_query = $connect->select("COUNT(*) as total", "artist_likes", "WHERE artist_id = '$artist_id'");
    $response['like_count'] = mysqli_fetch_assoc($like_count_query)['total'];

    $follow_count_query = $connect->select("COUNT(*) as total", "artist_follows", "WHERE artist_id = '$artist_id'");
    $response['follow_count'] = mysqli_fetch_assoc($follow_count_query)['total'];

} catch (Exception $e) {
    $response['message'] = 'Server error: ' . $e->getMessage();
    error_log('Exception in artist-action.php: ' . $e->getMessage());
}

echo json_encode($response);
exit();