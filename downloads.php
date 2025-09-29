<?php
include "resources/database/func.php";
include "resources/php/youtube_url.php"; 
$connect = new functional();
$isLoggedIn = isset($_SESSION['user']);
$user_id = intval($_SESSION['user_id']);

// Fetch downloaded songs with artist info
$downloadsQuery = $connect->select(
    "sd.downloaded_at, s.songs_id, s.songs_name, s.songs_image, s.songs_link, a.artist_name",
    "song_downloads sd 
     INNER JOIN songs s ON sd.song_id = s.songs_id
     LEFT JOIN artist a ON s.artist_from_id = a.artist_id",
    "WHERE sd.user_id = '$user_id' ORDER BY sd.downloaded_at DESC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Downloads - MelodyHub</title>
    <!-- Add Poppins Font Link -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="resources/css/main.css">
    <link rel="stylesheet" href="resources/css/downloads.css">
</head>
<body class="d-flex flex-column min-vh-100" data-page="download-page">
    <?php include 'resources/html/header.php'; ?>
    
    <!-- Main Content -->
    <main class="flex-grow-1" style="padding-top: 80px;">
        <div class="container">
            <div class="downloads-container">
                <!-- Header Section -->
                <div class="downloads-header">
                    <h1 class="gradient-text">My Downloads</h1>
                    <p class="text-muted" style="color:white;">All your downloaded songs in one place</p>
                </div>
                
                <?php
                $downloadCount = mysqli_num_rows($downloadsQuery);
                $totalSize = $downloadCount * 3.5; // Approximate size in MB
                ?>
                
                <!-- Statistics Cards -->
                <div class="stats-cards">
                    <div class="stat-card">
                        <i class="fas fa-download"></i>
                        <h3><?php echo $downloadCount; ?></h3>
                        <p>Total Downloads</p>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-music"></i>
                        <h3><?php echo $downloadCount; ?></h3>
                        <p>Songs Downloaded</p>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-hdd"></i>
                        <h3><?php echo number_format($totalSize, 1); ?> MB</h3>
                        <p>Estimated Storage</p>
                    </div>
                </div>
                
                <!-- Downloads Table -->
                <div class="downloads-table">
                    <?php if ($downloadCount > 0): ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Song</th>
                                    <th>Artist</th>
                                    <th>Duration</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($download = mysqli_fetch_assoc($downloadsQuery)): ?>
                                    <tr data-song-id="<?php echo $download['songs_id']; ?>">
                                        <td>
                                            <div class="song-info">
                                                <img src="<?php echo htmlspecialchars($download['songs_image'] ?? 'resources/images/default-song.jpg'); ?>" 
                                                     alt="<?php echo htmlspecialchars($download['songs_name']); ?>" 
                                                     class="song-image"
                                                     onerror="this.src='resources/images/default-song.jpg'">
                                                <div class="song-details">
                                                    <h6><?php echo htmlspecialchars($download['songs_name']); ?></h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="artist"><?php echo htmlspecialchars($download['artist_name'] ?? 'Unknown Artist'); ?></span>
                                        </td>
                                        <td>
                                            <?php
                                            // Extract video ID from songs_link
                                            $videoId = extractVideoId($download['songs_link']);
                                            // Get duration using YouTube API
                                            $duration = $videoId ? getYouTubeDuration($youtube, $videoId) : 'N/A';
                                            // Create songData object
                                            $songData = [
                                                'videoId' => $videoId ?? '',
                                                'title' => htmlspecialchars($download['songs_name']),
                                                'image' => htmlspecialchars($download['songs_image'] ?? 'resources/images/default-song.jpg'),
                                                'duration' => $duration,
                                            ];
                                            ?>
                                            <span class="download-date"><?php echo htmlspecialchars($songData['duration']); ?></span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-play play-btn" 
                                                        data-song='<?php echo json_encode($songData); ?>'
                                                        <?php echo $videoId ? '' : 'disabled'; ?>>
                                                    <i class="fas fa-play"></i>
                                                </button>
                                                <button class="btn btn-remove remove-btn" 
                                                        data-song-id="<?php echo $download['songs_id']; ?>"
                                                        data-user-id="<?php echo $user_id; ?>"
                                                        title="Remove from Downloads">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="no-downloads">
                            <i class="fas fa-download"></i>
                            <h3>No Downloads Yet</h3>
                            <p>Start exploring music and download your favorite songs!</p>
                            <a href="index.php" class="btn btn-download mt-3">Browse Music</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'resources/html/playbar.php'; ?>
    <?php include 'resources/html/footer.php'; ?>
    
    <script src="https://www.youtube.com/iframe_api"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <script src="resources/javascript/downloads.js"></script>
    <script>
        
    </script>
</body>
</html>