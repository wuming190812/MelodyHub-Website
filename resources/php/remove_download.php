<?php
header('Content-Type: application/json');

include "../database/func.php";

$connect = new functional();

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['user']) || !isset($_SESSION['user_id'])) {
    $response['message'] = 'User not logged in';
    echo json_encode($response);
    exit;
}

$user_id = intval($_SESSION['user_id']);

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['song_id']) || !isset($input['user_id'])) {
    $response['message'] = 'Invalid request data';
    echo json_encode($response);
    exit;
}

$song_id = intval($input['song_id']);
$request_user_id = intval($input['user_id']);

// Verify that the user_id matches the session user_id for security
if ($request_user_id !== $user_id) {
    $response['message'] = 'Unauthorized action';
    echo json_encode($response);
    exit;
}

// Delete the download record
if ($connect->sql("DELETE FROM song_downloads WHERE user_id = '$user_id' AND song_id = '$song_id'")) {
    $response['success'] = true;
    $response['message'] = 'Song removed successfully';
} else {
    $response['message'] = 'Database error: Unable to remove song';
}

echo json_encode($response);
?>