<?php
include "resources/database/func.php";
include "resources/php/youtube_url.php";

// Initialize database connection
$connect = new functional();
$isLoggedIn = isset($_SESSION['user']);

if (!isset($_SESSION['user'])) {
    header("Location: login.php"); // Redirect to your login page
    exit();
}

// Get artist ID from URL and sanitize
$artist_id = isset($_GET['artist_id']) ? intval($_GET['artist_id']) : 0;

// Query to fetch artist details with artist_id
$artistform = $connect->select("*", "artist", "WHERE artist_id = " . $artist_id);
$artistdata = mysqli_fetch_assoc($artistform);

// Check if user likes the artist
$like_check = $connect->select("COUNT(*) as count", "artist_likes", "WHERE artist_id = '$artist_id' AND user_id = '{$_SESSION['user_id']}'");
$is_liked = $isLoggedIn ? mysqli_fetch_assoc($like_check)['count'] > 0 : false;

// Get artist like count
$like_count_query = $connect->select("COUNT(*) as total", "artist_likes", "WHERE artist_id = '$artist_id'");
$like_count = mysqli_fetch_assoc($like_count_query)['total'];

// Check if user follows the artist
$follow_check = $connect->select("COUNT(*) as count", "artist_follows", "WHERE artist_id = '$artist_id' AND user_id = '{$_SESSION['user_id']}'");
$is_followed = $isLoggedIn ? mysqli_fetch_assoc($follow_check)['count'] > 0 : false;

// Get artist follow count
$follow_count_query = $connect->select("COUNT(*) as total", "artist_follows", "WHERE artist_id = '$artist_id'");
$follow_count = mysqli_fetch_assoc($follow_count_query)['total'];

// Query to fetch songs for the current artist
$songs_query = $connect->select("*", "songs", "WHERE artist_from_id = '$artist_id'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($artistdata['artist_name'], ENT_QUOTES, 'UTF-8'); ?> - MelodyHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="resources/css/main.css">
    <link rel="stylesheet" type="text/css" href="resources/css/artist.css">
    <link rel="stylesheet" type="text/css" href="resources/css/artist_detail.css">
</head>
<body class="d-flex flex-column min-vh-100" data-page="artist-detail">
    <?php include 'resources/html/header.php'; ?>

    <!-- Artist Hero Section -->
    <section class="artist-hero bg-dark text-white text-center py-5">
        <div class="container">
            <div class="artist-circle mx-auto mb-4">
                <img src="<?php echo htmlspecialchars($artistdata['artist_image'] ?? 'resources/images/default.jpg', ENT_QUOTES, 'UTF-8'); ?>" 
                     alt="<?php echo htmlspecialchars($artistdata['artist_name'], ENT_QUOTES, 'UTF-8'); ?>" 
                     class="img-fluid rounded-circle"
                     onerror="this.src='resources/images/default.jpg';">
            </div>
            <h5 class="artist-name mt-2"><?php echo htmlspecialchars($artistdata['artist_name'], ENT_QUOTES, 'UTF-8'); ?></h5>
            <!-- In your artist-hero section -->
            <div class="artist-stats mt-3"> 
                <span class="stat-item like-count">
                    <i class="fas fa-heart"></i> <?php echo $like_count; ?> Likes
                </span>
                <span class="stat-item follow-count">
                    <i class="fas fa-user-plus"></i> <?php echo $follow_count; ?> Followers
                </span>
            </div>
        </div>
    </section>

    <!-- Artist Actions Section -->
    <section class="container my-5">
        <div class="artist-actions text-center mb-5">
            <!-- Like Artist Button -->
            <button 
                class="action-btn like-btn <?php echo $is_liked ? 'liked' : ''; ?>" 
                onclick="toggleLike(<?php echo htmlspecialchars($artistdata['artist_id'], ENT_QUOTES, 'UTF-8'); ?>, this)"
                aria-label="<?php echo $is_liked ? 'Unlike' : 'Like'; ?> artist">
                <i class="fas fa-heart"></i>
            </button>

            <!-- Follow Artist Button -->
            <button 
                class="action-btn follow-btn <?php echo $is_followed ? 'followed' : ''; ?>" 
                onclick="toggleFollow(<?php echo htmlspecialchars($artistdata['artist_id'], ENT_QUOTES, 'UTF-8'); ?>, this)"
                aria-label="<?php echo $is_followed ? 'Unfollow' : 'Follow'; ?> artist">
                <i class="fas fa-user-plus"></i>
            </button>

            <!-- Back Button -->
            <a href="index.php" class="action-btn back-btn" aria-label="Back to artists">
                <i class="fas fa-arrow-left"></i>
            </a>
        </div>
    </section>

    <!-- Songs Table Section -->
    <section class="container my-5">
        <h2 class="text-center mb-4">Songs by <?= htmlspecialchars($artistdata['artist_name'], ENT_QUOTES) ?></h2>
        
        <table class="song-table table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Duration</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($song = mysqli_fetch_assoc($songs_query)) {
                    // Extract video ID from songs_link
                    $videoId = extractVideoId($song['songs_link']);
                    if (!$videoId) {
                        continue; // Skip if no valid video ID can be extracted
                    }
                    $songData = [
                        'videoId' => $videoId,
                        'title' => $song['songs_name'],
                        'image' => $song['songs_image'] ?? 'resources/images/default-song.jpg',
                        'duration' => $videoId ? getYouTubeDuration($youtube, $videoId) : 'N/A',
                        'link' => $song['songs_link'] ?? '#'
                    ];

                    $jsonSongData = htmlspecialchars(json_encode($songData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                    ?>
                    <tr>
                        <td>
                            <div class="song-info">
                                <img src="<?= htmlspecialchars($song['songs_image'] ?? 'resources/images/default-song.jpg', ENT_QUOTES) ?>" 
                                    class="song-image" 
                                    alt="<?= htmlspecialchars($song['songs_name'], ENT_QUOTES) ?>">
                                <div class="song-details">
                                    <h6><?= htmlspecialchars($song['songs_name'], ENT_QUOTES) ?></h6>
                                    <p class="artist"><?= htmlspecialchars($artistdata['artist_name'], ENT_QUOTES) ?></p>
                                </div>
                            </div>
                        </td>
                        <td><?= $songData['duration'] ?></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-sm play-btn" data-song='<?= $jsonSongData ?>' aria-label="Play <?= htmlspecialchars($song['songs_name'], ENT_QUOTES) ?>">
                                    <i class="fas fa-play"></i>
                                </button>
                                <button class="btn btn-sm share-btn" 
                                    onclick='shareSong(<?= json_encode($songData["link"]) ?>, <?= json_encode($song["songs_name"]) ?>, this)'>
                                    <i class="fas fa-share"></i>
                                </button>
                                <button class="btn btn-sm download-btn" onclick="recordDownload(<?= intval($song['songs_id']) ?>)">
                                    <i class="fas fa-download"></i>
                                </button>
                                <button class="btn btn-sm list-btn" onclick="openPlaylistModal(<?= intval($song['songs_id']) ?>, '<?= htmlspecialchars($song['songs_name'], ENT_QUOTES) ?>')">
                                    <i class="fas fa-list"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php
                    
                }
                ?>
            </tbody>
        </table>
    </section>

    <!-- Playlist Modal -->
    <div class="modal fade" id="playlistModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add to Playlist</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="songTitle"></p>
                    <input type="hidden" id="selectedSongId">
                    <input type="hidden" id="selectedArtistId">

                    <select id="playlistSelect" class="form-select">
                        <!-- Option playlist akan load dari server -->
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveToPlaylist()">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Player Bar -->
    <?php include 'resources/html/playbar.php'; ?>

    <?php include 'resources/html/footer.php'; ?>

    <script src="https://www.youtube.com/iframe_api"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <script src="resources/javascript/script.js"></script>
    <script src="resources/javascript/artist-detail.js"></script>
    <script>
        
    </script>
</body>
</html>
</body>
</html>