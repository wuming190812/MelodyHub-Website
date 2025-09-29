<?php
include "resources/database/func.php";
include "resources/php/youtube_url.php"; 
$connect = new functional();
$isLoggedIn = isset($_SESSION['user']);
$user_id = intval($_SESSION['user_id']);

// Fetch playlist details
$playlist_id = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : '';
$playlist_result = $connect->select("*", "playlist", "WHERE playlist_id = '$playlist_id' AND creator_id = '$user_id'");
$playlist = mysqli_fetch_assoc($playlist_result);

// Likes & follows
$like_check = $connect->select("COUNT(*) as count", "playlist_likes", "WHERE playlist_id='$playlist_id' AND user_id='$user_id'");
$is_liked = mysqli_fetch_assoc($like_check)['count'] > 0;
$like_count = mysqli_fetch_assoc($connect->select("COUNT(*) as total", "playlist_likes", "WHERE playlist_id='$playlist_id'"))['total'];

$follow_check = $connect->select("COUNT(*) as count", "playlist_follows", "WHERE playlist_id='$playlist_id' AND user_id='$user_id'");
$is_followed = mysqli_fetch_assoc($follow_check)['count'] > 0;
$follow_count = mysqli_fetch_assoc($connect->select("COUNT(*) as total", "playlist_follows", "WHERE playlist_id='$playlist_id'"))['total'];

// Fetch tracks with artist info
$tracks_query = $connect->select(
    "t.*, s.songs_name, s.songs_link, s.songs_image, a.artist_name",
    "track t 
     JOIN songs s ON t.songs_id = s.songs_id 
     LEFT JOIN artist a ON s.artist_from_id = a.artist_id",
    "WHERE t.playlist_id='$playlist_id'"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Playlist Details - MelodyHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" type="text/css" href="resources/css/main.css">
    <link rel="stylesheet" type="text/css" href="resources/css/playlist_details.css">
</head>
<body class="d-flex flex-column min-vh-100">

<?php include 'resources/html/header.php'; ?>

<section class="container my-5">
<?php if ($playlist): ?>
    <div class="playlist-detail-header text-center mb-5">
        <img src="<?= htmlspecialchars($playlist['playlist_image'] ?: 'resources/images/placeholder.jpg') ?>" class="playlist-cover mb-3" alt="Playlist Cover">
        <h2 class="gradient-text"><?= htmlspecialchars($playlist['playlist_name']) ?></h2>
        <div class="playlist-stats mt-3">
            <span class="stat-item"><i class="fas fa-heart"></i> <?= $like_count ?> Likes</span>
            <span class="stat-item"><i class="fas fa-user-plus"></i> <?= $follow_count ?> Followers</span>
        </div>
    </div>

    <div class="playlist-actions text-center mb-5">
        <!-- Play All -->
        <button id="play-all-btn" class="action-btn play-all-btn">
            <i class="fas fa-play"></i>
        </button>

        <!-- Delete Playlist -->
        <button 
            class="action-btn delete-btn" 
            onclick="deletePlaylist(<?= intval($playlist['playlist_id']) ?>)" 
            aria-label="delete playlist">
            <i class="fas fa-trash"></i>
        </button>


        <!-- Like Playlist -->
        <button 
            class="action-btn like-btn <?= $is_liked ? 'liked' : '' ?>" 
            onclick="togglePlaylistLike(<?= intval($playlist['playlist_id']) ?>, this)"
            aria-label="<?= $is_liked ? 'unlike' : 'like' ?>">
            <i class="fas fa-heart"></i>
        </button>

        <!-- Follow Playlist -->
        <button 
            class="action-btn follow-btn <?= $is_followed ? 'followed' : '' ?>" 
            onclick="togglePlaylistFollow(<?= intval($playlist['playlist_id']) ?>, this)"
            aria-label="<?= $is_followed ? 'unfollow' : 'follow' ?>">
            <i class="fas fa-user-plus"></i>
        </button>

        <!-- Back -->
        <a href="playlist.php" class="action-btn back-btn"><i class="fas fa-arrow-left"></i></a>
    </div>


<?php else: ?>
    <p class="text-white">Playlist not found or access denied.</p>
<?php endif; ?>
</section>

<div class="tracks-section container">
    <h3>Tracks</h3>
    <table class="tracks-table table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Duration</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php
        mysqli_data_seek($tracks_query, 0); // Reset the query pointer to the beginning
        while ($track = mysqli_fetch_assoc($tracks_query)) {
            $videoId = extractVideoId($track['songs_link']);
            $duration = $videoId ? getYouTubeDuration($youtube, $videoId) : 'N/A';
            $songData = [
                'videoId' => $videoId ?? '',
                'title' => htmlspecialchars($track['songs_name']),
                'image' => htmlspecialchars($track['songs_image'] ?? 'resources/images/placeholder.jpg'),
                'duration' => $duration,
                'link' => $track['songs_link']
            ];
            $jsonSongData = htmlspecialchars(json_encode($songData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
        ?>
            <tr>
                <td>
                    <div class="song-info">
                        <img src="<?= htmlspecialchars($track['songs_image'] ?? 'resources/images/placeholder.jpg', ENT_QUOTES, 'UTF-8') ?>" 
                            alt="<?= htmlspecialchars($track['songs_name'], ENT_QUOTES, 'UTF-8') ?>" 
                            class="song-image"
                            onerror="this.src='resources/images/placeholder.jpg'">
                        <div class="song-details">
                            <h6><?= htmlspecialchars($track['songs_name'], ENT_QUOTES, 'UTF-8') ?></h6>
                            <p class="artist"><?= htmlspecialchars($track['artist_name'] ?? 'Unknown Artist', ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    </div>
                </td>
                <td><?= $songData['duration'] ?></td>
                <td>
                    <div class="action-buttons">
                        <button class="btn btn-sm play-btn" data-song="<?= $jsonSongData ?>" <?= $videoId ? '' : 'disabled' ?>>
                            <i class="fas fa-play"></i>
                        </button>
                        <button class="btn btn-remove remove-btn" onclick="removeTrack(<?= intval($track['track_id']) ?>, this)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        <?php
        }
        if (mysqli_num_rows($tracks_query) == 0) {
        ?>
            <tr>
                <td colspan="3">No tracks available.</td>
            </tr>
        <?php
        }
        ?>
        </tbody>
    </table>
</div>

<?php include 'resources/html/playbar.php'; ?>
<?php include 'resources/html/footer.php'; ?>

<script src="resources/javascript/script.js"></script>
<script src="https://www.youtube.com/iframe_api"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
<script src="resources/javascript/playlist-details.js"></script>
</body>
</html>