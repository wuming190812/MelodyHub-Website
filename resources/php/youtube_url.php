<?php
require_once 'vendor/autoload.php'; // This loads the Google Client library

//Function to extract video ID from YouTube URL
function extractVideoId($url) {
    if (empty($url)) {
        return null;
    }
    $parsed = parse_url($url);
    if (isset($parsed['query'])) {
        parse_str($parsed['query'], $params);
        if (isset($params['v'])) {
            return $params['v']; // Returns video ID (e.g., dQw4w9WgXcQ)
        }
    }
    if (preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
        return $matches[1];
    }
    return null; // Return null if no video ID is found
}

// Initialize Google Client
$client = new Google_Client();
$client->setApplicationName('MelodyHub');
$client->setDeveloperKey('AIzaSyCWuALmke8T7oW6wZK8lYdmWnnxNH_-7qU');
$youtube = new Google_Service_YouTube($client);

// Function to get YouTube video duration
function getYouTubeDuration($youtube, $videoId) {
    try {
        $response = $youtube->videos->listVideos('contentDetails', ['id' => $videoId]);
        if (!empty($response['items'])) {
            $duration = $response['items'][0]['contentDetails']['duration'];
            preg_match('/PT(?:(\d+)H)?(?:(\d+)M)?(?:(\d+)S)?/', $duration, $parts);
            $hours = isset($parts[1]) ? intval($parts[1]) : 0;
            $minutes = isset($parts[2]) ? intval($parts[2]) : 0;
            $seconds = isset($parts[3]) ? intval($parts[3]) : 0;
            $totalMinutes = $hours * 60 + $minutes;
            return sprintf('%d:%02d', $totalMinutes, $seconds);
        }
        return 'N/A';
    } catch (Exception $e) {
        error_log('YouTube API Error: ' . $e->getMessage());
        return 'N/A';
    }
}
?>
