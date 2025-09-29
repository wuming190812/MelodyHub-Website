<?php
include "resources/database/func.php";
include "resources/php/youtube_url.php"; 
$connect = new functional();
$isLoggedIn = isset($_SESSION['user']);
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

// Initialize variables
$playlist_results = [];
$song_results = [];
$artist_results = [];
$search_term = '';
$active_tab = 'songs'; // Default active tab

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = mysqli_real_escape_string($connect->conn, $_GET['search']);
    
    // Get active tab from URL or default to songs
    if (isset($_GET['tab']) && in_array($_GET['tab'], ['songs', 'artists', 'playlists'])) {
        $active_tab = $_GET['tab'];
    }
    
    // Search songs
    $song_query = $connect->select("s.*, a.artist_name", "songs s LEFT JOIN artist a ON s.artist_from_id = a.artist_id", 
                                  "WHERE s.songs_name LIKE '%$search_term%' OR a.artist_name LIKE '%$search_term%'");
    if ($song_query) {
        while ($row = mysqli_fetch_assoc($song_query)) {
            $song_results[] = $row;
        }
    }
    
    // Search artists
    $artist_query = $connect->select("*", "artist", "WHERE artist_name LIKE '%$search_term%'");
    if ($artist_query) {
        while ($row = mysqli_fetch_assoc($artist_query)) {
            $artist_results[] = $row;
        }
    }
    
    // Search playlists
    $playlist_query = $connect->select("*", "playlist", "WHERE playlist_name LIKE '%$search_term%'");
    if ($playlist_query) {
        while ($row = mysqli_fetch_assoc($playlist_query)) {
            $playlist_results[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - MelodyHub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Add Poppins Font Link -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js"></script> <!-- For icons -->
    <link rel="stylesheet" type="text/css" href="resources/css/main.css">
    <link rel="stylesheet" type="text/css" href="resources/css/search_detail.css">
</head>
<body class="d-flex flex-column min-vh-100">
    <!-- Header -->
    <?php include 'resources/html/header.php' ?>
    
    <!-- Search Header -->
    <section class="search-header">
        <div class="container">
            <div class="search-container">
                <form method="GET" action="search_result.php" class="d-flex">
                    <input type="text" name="search" class="form-control search-input" 
                           placeholder="Search songs, artists, or playlists..." 
                           value="<?php echo htmlspecialchars($search_term); ?>" required>
                    <button type="submit" class="btn search-btn">
                        <i class="fas fa-search"></i> Search
                    </button>
                </form>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container flex-grow-1">
        <!-- Results Info -->
        <?php if (!empty($search_term)): ?>
            <div class="results-count">
                <?php
                $total_results = count($song_results) + count($artist_results) + count($playlist_results);
                if ($total_results > 0) {
                    echo "Found {$total_results} results for \"{$search_term}\"";
                } else {
                    echo "No results found for \"{$search_term}\"";
                }
                ?>
            </div>
        <?php endif; ?>

        <!-- Tabs Navigation -->
        <ul class="nav nav-tabs" id="searchTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo $active_tab == 'songs' ? 'active' : ''; ?>" 
                        id="songs-tab" data-bs-toggle="tab" data-bs-target="#songs" type="button" role="tab">
                    Songs <span class="badge"><?php echo count($song_results); ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo $active_tab == 'artists' ? 'active' : ''; ?>" 
                        id="artists-tab" data-bs-toggle="tab" data-bs-target="#artists" type="button" role="tab">
                    Artists <span class="badge"><?php echo count($artist_results); ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo $active_tab == 'playlists' ? 'active' : ''; ?>" 
                        id="playlists-tab" data-bs-toggle="tab" data-bs-target="#playlists" type="button" role="tab">
                    Playlists <span class="badge"><?php echo count($playlist_results); ?></span>
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="searchTabContent">
            <!-- Songs Tab -->
            <div class="tab-pane fade <?php echo $active_tab == 'songs' ? 'show active' : ''; ?>" 
                 id="songs" role="tabpanel">
                <?php if (!empty($song_results)): ?>
                    <div class="results-count">
                        Found <span class="badge"><?php echo count($song_results); ?></span> song(s)
                    </div>
                    <div class="song-table">
                        <table class="table">
                            <thead style="color: white !important;">
                                <tr>
                                    <th>Title</th>
                                    <th>Duration</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($song_results as $song): 
                                    $videoId = isset($song['songs_link']) ? extractVideoId($song['songs_link']) : null;
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
                                                <img src="<?php echo htmlspecialchars($song['songs_image'] ?? 'resources/images/default-song.jpg', ENT_QUOTES); ?>" 
                                                     class="song-image" 
                                                     alt="<?php echo htmlspecialchars($song['songs_name'], ENT_QUOTES); ?>">
                                                <div class="song-details">
                                                    <h6><?php echo htmlspecialchars($song['songs_name'], ENT_QUOTES); ?></h6>
                                                    <p class="artist"><?php echo htmlspecialchars($song['artist_name'] ?? 'Unknown Artist', ENT_QUOTES); ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td style="color: white !important;"><?php echo $songData['duration']; ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-sm play-btn" data-song='<?php echo $jsonSongData; ?>' 
                                                        aria-label="Play <?php echo htmlspecialchars($song['songs_name'], ENT_QUOTES); ?>">
                                                    <i class="fas fa-play"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="no-results">
                        <i class="fas fa-music"></i>
                        <h3>No songs found</h3>
                        <p>Try searching with different keywords</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Artists Tab -->
            <div class="tab-pane fade <?php echo $active_tab == 'artists' ? 'show active' : ''; ?>" 
                 id="artists" role="tabpanel">
                <?php if (!empty($artist_results)): ?>
                    <div class="results-count">
                        Found <span class="badge"><?php echo count($artist_results); ?></span> artist(s)
                    </div>
                    <?php foreach ($artist_results as $artist): 
                        $artist_id = htmlspecialchars($artist['artist_id'], ENT_QUOTES, 'UTF-8');
                        $artist_name = htmlspecialchars($artist['artist_name'], ENT_QUOTES, 'UTF-8');
                        $artist_image = htmlspecialchars($artist['artist_image'] ?? 'resources/images/default-artist.jpg', ENT_QUOTES, 'UTF-8');
                        $query = $connect->select(
                            "COUNT(*) as follow_count, SUM(CASE WHEN user_id = '$user_id' THEN 1 ELSE 0 END) as is_followed",
                            "artist_follows",
                            "WHERE artist_id = '$artist_id'"
                        );
                        $result = mysqli_fetch_assoc($query);
                        $is_followed = $isLoggedIn ? $result['is_followed'] > 0 : false;
                        $follow_count = $result['follow_count'];
                    ?>
                        <div class="result-card" role="region" aria-label="Artist: <?php echo $artist_name; ?>">
                            <img src="<?php echo $artist_image; ?>" 
                                 alt="Profile image of <?php echo $artist_name; ?>" 
                                 class="artist-image" 
                                 loading="lazy" 
                                 onerror="this.src='resources/images/default-artist.jpg'">
                            <div class="result-info flex-grow-1">
                                <h5><?php echo $artist_name; ?></h5>
                                <div class="artist-stats"> 
                                    <span class="stat-item follow-count">
                                        <i class="fas fa-user-plus" aria-hidden="true"></i>
                                        <span class="visually-hidden">Followers: </span><?php echo $follow_count; ?> Followers
                                    </span>
                                </div>
                            </div>
                            <button class="action-btn" 
                                    onclick="window.location.href='artist_detail.php?artist_id=<?php echo $artist_id; ?>'"
                                    aria-label="View <?php echo $artist_name; ?>">
                                <i class="fas fa-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-results" role="alert">
                        <i class="fas fa-user"></i>
                        <h3>No artists found</h3>
                        <p>Try searching with different keywords</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Playlists Tab -->
            <div class="tab-pane fade <?php echo $active_tab == 'playlists' ? 'show active' : ''; ?>" 
                 id="playlists" role="tabpanel">
                <?php if (!empty($playlist_results)): ?>
                    <div class="results-count">
                        Found <span class="badge"><?php echo count($playlist_results); ?></span> playlist(s)
                    </div>
                    <?php foreach ($playlist_results as $playlist): ?>
                        <div class="result-card">
                            <div class="playlist-icon">
                                <i class="fas fa-headphones"></i>
                            </div>
                            <div class="result-info flex-grow-1">
                                <h5><?php echo htmlspecialchars($playlist['playlist_name']); ?></h5>
                                <p class="text-muted">Playlist</p>
                            </div>
                            <button class="action-btn" 
                                    onclick="window.location.href='playlist_details.php?playlist_id=<?php echo htmlspecialchars($playlist['playlist_id']); ?>'"
                                    aria-label="View <?php echo htmlspecialchars($playlist['playlist_name']); ?>">
                                <i class="fas fa-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-results">
                        <i class="fas fa-list"></i>
                        <h3>No playlists found</h3>
                        <p>Try searching with different keywords</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bottom Player Bar -->
    <?php include 'resources/html/playbar.php'; ?>

    <!-- Footer -->
    <?php include 'resources/html/footer.php' ?>

    <script src="https://www.youtube.com/iframe_api"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <script>
        let player;
        let currentSong = null;
        let isShuffled = false;
        let songsQueue = [];
        let currentSongIndex = -1;

        function initializePlayer() {
            if (typeof YT === 'undefined' || !YT.loaded) {
                console.log('YouTube API not loaded yet, retrying...');
                setTimeout(initializePlayer, 500);
                return;
            }
            if (!player) {
                player = new YT.Player('youtube-player', {
                    height: '0',
                    width: '0',
                    events: {
                        'onReady': onPlayerReady,
                        'onStateChange': onPlayerStateChange
                    }
                });
                console.log('YouTube Player initialized');
            }
        }

        function onYouTubeIframeAPIReady() {
            initializePlayer();
        }

        function onPlayerReady(event) {
            console.log('Player is ready');
        }

        function onPlayerStateChange(event) {
            const playPauseBtn = document.getElementById('play-pause-btn');
            if (playPauseBtn) {
                if (event.data === YT.PlayerState.PLAYING) {
                    playPauseBtn.innerHTML = '<i class="fas fa-pause"></i>';
                    updateProgress();
                } else if (event.data === YT.PlayerState.PAUSED || event.data === YT.PlayerState.ENDED) {
                    playPauseBtn.innerHTML = '<i class="fas fa-play"></i>';
                }
            }

            if (event.data === YT.PlayerState.ENDED) {
                playNextSong();
            }
        }

        function playSong(songData) {
            if (!songData.videoId) {
                console.error('No video ID available for:', songData.title);
                return;
            }
            if (!player) {
                console.error('Player not initialized. Initializing now...');
                initializePlayer();
                setTimeout(() => playSong(songData), 500);
                return;
            }
            currentSong = songData;
            const playerBar = document.getElementById('player-bar');
            if (playerBar) playerBar.style.display = 'flex';
            const playerImage = document.getElementById('player-image');
            if (playerImage) playerImage.src = songData.image;
            const playerTitle = document.getElementById('player-title');
            if (playerTitle) playerTitle.textContent = songData.title;
            const totalTime = document.getElementById('total-time');
            if (totalTime) totalTime.textContent = songData.duration;

            player.loadVideoById(songData.videoId);
            player.playVideo();

            if (!songsQueue.find(song => song.videoId === songData.videoId)) {
                songsQueue.push(songData);
                currentSongIndex = songsQueue.length - 1;
            }
        }

        const playPauseBtn = document.getElementById('play-pause-btn');
        if (playPauseBtn) {
            playPauseBtn.addEventListener('click', () => {
                if (player && player.getPlayerState) {
                    if (player.getPlayerState() === YT.PlayerState.PLAYING) {
                        player.pauseVideo();
                    } else {
                        player.playVideo();
                    }
                } else {
                    console.error('Player not initialized for play/pause');
                }
            });
        }

        const previousBtn = document.getElementById('prev-btn');
        if (previousBtn) {
            previousBtn.addEventListener('click', () => {
                if (player && player.getPlayerState && currentSongIndex > 0) {
                    currentSongIndex--;
                    console.log('Playing previous song:', songsQueue[currentSongIndex].title);
                    playSong(songsQueue[currentSongIndex]);
                }
            });
        }

        const nextBtn = document.getElementById('next-btn');
        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                playNextSong();
            });
        }

        function playNextSong() {
            if (!player || !player.getPlayerState) {
                console.error('YouTube player not initialized');
                return;
            }

            if (songsQueue.length === 0) {
                const playerBar = document.getElementById('player-bar');
                if (playerBar) playerBar.style.display = 'none';
                currentSong = null;
                return;
            }

            if (isShuffled) {
                currentSongIndex = Math.floor(Math.random() * songsQueue.length);
            } else if (currentSongIndex < songsQueue.length - 1) {
                currentSongIndex++;
            } else {
                currentSongIndex = 0;
            }

            if (songsQueue[currentSongIndex]) {
                playSong(songsQueue[currentSongIndex]);
            } else {
                const playerBar = document.getElementById('player-bar');
                if (playerBar) playerBar.style.display = 'none';
                currentSong = null;
            }
        }

        const shuffleBtn = document.getElementById('shuffle-btn');
        if (shuffleBtn) {
            shuffleBtn.addEventListener('click', () => {
                isShuffled = !isShuffled;
                shuffleBtn.style.color = isShuffled ? '#ff7e22' : '#b3b3b3';
            });
        }

        const closePlayer = document.getElementById('close-player');
        if (closePlayer) {
            closePlayer.addEventListener('click', () => {
                if (player) {
                    player.stopVideo();
                    const playerBar = document.getElementById('player-bar');
                    if (playerBar) playerBar.style.display = 'none';
                    currentSong = null;
                }
            });
        }

        function updateProgress() {
            if (player && player.getCurrentTime) {
                const currentTime = player.getCurrentTime();
                const duration = player.getDuration();
                const progressPercent = duration > 0 ? (currentTime / duration) * 100 : 0;
                const progressFill = document.getElementById('progress-fill');
                const currentTimeEl = document.getElementById('current-time');
                const totalTimeEl = document.getElementById('total-time');

                if (progressFill) progressFill.style.width = `${progressPercent}%`;
                if (currentTimeEl) currentTimeEl.textContent = formatTime(currentTime);
                if (totalTimeEl) totalTimeEl.textContent = formatTime(duration);

                if (player.getPlayerState() === YT.PlayerState.PLAYING) {
                    requestAnimationFrame(updateProgress);
                }
            }
        }

        function seekProgress(event) {
            if (player && player.getDuration) {
                const progressBar = document.getElementById('progress-bar');
                if (!progressBar) return;
                const rect = progressBar.getBoundingClientRect();
                const clickX = event.clientX - rect.left;
                const width = rect.width;
                const seekTime = (clickX / width) * player.getDuration();
                player.seekTo(seekTime, true);
            }
        }

        function formatTime(seconds) {
            if (!isFinite(seconds)) return '0:00';
            const minutes = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60);
            return `${minutes}:${secs < 10 ? '0' : ''}${secs}`;
        }

        document.querySelectorAll('.play-btn').forEach(button => {
            button.addEventListener('click', () => {
                const songData = JSON.parse(button.dataset.song);
                songsQueue = [];
                document.querySelectorAll('.play-btn').forEach(btn => {
                    const data = JSON.parse(btn.dataset.song);
                    if (data.videoId) {
                        songsQueue.push(data);
                    }
                });
                currentSongIndex = songsQueue.findIndex(song => song.videoId === songData.videoId);
                playSong(songData);
            });
        });

        window.onload = function() {
            initializePlayer();
        };
    </script>
</body>
</html>