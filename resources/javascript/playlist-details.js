let player;
    let currentSong = null;
    let isShuffled = false;
    let songsQueue = [];
    let currentSongIndex = -1;

    // === Ad system ===
    const adVideoId = "8Vz80Z_iE2A"; // replace with your YouTube ad videoId
    let songsPlayedCount = 0;    // counter of songs played
    let isAdPlaying = false;     // track if ad is currently playing

    // Main "Play All" button
    const playAllBtn = document.getElementById('play-all-btn');
    if (playAllBtn) {
        playAllBtn.addEventListener('click', () => {
            songsQueue = []; 
            currentSongIndex = 0;

            document.querySelectorAll('.play-btn').forEach(btn => {
                const songData = JSON.parse(btn.dataset.song);
                if (songData.videoId) { 
                    songsQueue.push(songData);
                }
            });

            console.log('Play All - songsQueue:', songsQueue);

            if (songsQueue.length > 0) {
                playSong(songsQueue[0]);
            } else {
                console.log('No valid songs to play');
                const playerBar = document.getElementById('player-bar');
                if (playerBar) playerBar.style.display = 'none';
            }
        });
    }

    // Initialize YouTube Player with retry limit
    function initializePlayer(attempts = 10) {
        if (attempts <= 0) {
            console.error('Failed to initialize YouTube player after multiple attempts');
            return;
        }
        if (typeof YT === 'undefined' || !YT.loaded) {
            console.log('YouTube API not loaded yet, retrying...');
            setTimeout(() => initializePlayer(attempts - 1), 500);
            return;
        }
        if (!player) {
            const youtubePlayer = document.getElementById('youtube-player');
            if (!youtubePlayer) {
                console.error('YouTube player container not found');
                return;
            }
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
            if (isAdPlaying) {
                console.log("Ad finished, resume current song...");
                isAdPlaying = false;

                // === Reset counter after ad ===
                songsPlayedCount = 0;

                if (songsQueue[currentSongIndex]) {
                    playSong(songsQueue[currentSongIndex], true); // skip ad check
                }
            } else {
                playNextSong();
            }
        }

    }

    // Play a song with ad check
    function playSong(songData, skipAdCheck = false) {
        if (!songData.videoId) {
            console.error('Invalid videoId for song:', songData.title);
            return;
        }

        // === Ad logic ===
        if (!skipAdCheck && !isAdPlaying) {
            if (songsPlayedCount > 0 && songsPlayedCount % 7 === 0) {
                console.log("Playing advertisement before next song...");
                isAdPlaying = true;
                player.loadVideoById(adVideoId);
                player.playVideo();
                return; // wait until ad ends
            }
        }

        // === Normal song playback ===
        currentSong = songData;
        const playerBar = document.getElementById('player-bar');
        const playerImage = document.getElementById('player-image');
        const playerTitle = document.getElementById('player-title');
        const totalTime = document.getElementById('total-time');

        if (playerBar) playerBar.style.display = 'flex';
        if (playerImage) playerImage.src = songData.image;
        if (playerTitle) playerTitle.textContent = songData.title;
        if (totalTime) totalTime.textContent = songData.duration;

        player.loadVideoById(songData.videoId);
        player.playVideo();

        if (!skipAdCheck) {
            // increase counter only for real songs
            songsPlayedCount++;
        }
    }

    // Play/Pause button handler
    const playPauseBtn = document.getElementById('play-pause-btn');
    if (playPauseBtn) {
        playPauseBtn.addEventListener('click', () => {
            if (player && player.getPlayerState) {
                if (player.getPlayerState() === YT.PlayerState.PLAYING) {
                    player.pauseVideo();
                } else {
                    player.playVideo();
                }
            }
        });
    }

    // Next song handler
    function playNextSong() {
        if (!player || !player.getPlayerState) {
            console.error('YouTube player not initialized');
            return;
        }

        if (songsQueue.length === 0) {
            console.log('No songs in queue');
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
            currentSongIndex = 0; // Loop back
        }

        if (songsQueue[currentSongIndex]) {
            console.log('Playing next song:', songsQueue[currentSongIndex].title);
            playSong(songsQueue[currentSongIndex]);
        }
    }

    // Previous button handler
    const previousBtn = document.getElementById('prev-btn');
    if (previousBtn) {
        previousBtn.addEventListener('click', () => {
            if (!player || !player.getPlayerState) return;

            if (songsQueue.length === 0) return;

            let prevIndex = currentSongIndex - 1;
            if (prevIndex >= 0) {
                currentSongIndex = prevIndex;
            } else {
                currentSongIndex = songsQueue.length - 1; 
            }

            if (songsQueue[currentSongIndex]) {
                playSong(songsQueue[currentSongIndex]);
            }
        });
    }

    // Next button handler
    const nextBtn = document.getElementById('next-btn');
    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            playNextSong();
        });
    }

    // Shuffle button
    const shuffleBtn = document.getElementById('shuffle-btn');
    if (shuffleBtn) {
        shuffleBtn.addEventListener('click', () => {
            isShuffled = !isShuffled;
            shuffleBtn.style.color = isShuffled ? '#1db954' : '#b3b3b3';
        });
    }

    // Close player
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

    // Progress bar update
    function updateProgress() {
        if (player && player.getCurrentTime) {
            const currentTime = player.getCurrentTime();
            const duration = player.getDuration();
            const progressPercent = (currentTime / duration) * 100;
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

    // Seek progress bar
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

    // Format time in MM:SS
    function formatTime(seconds) {
        const minutes = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return `${minutes}:${secs < 10 ? '0' : ''}${secs}`;
    }

    // Update play buttons to trigger playbar
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

    // Initialize player on page load
    window.onload = function() {
        initializePlayer();
    };

    function debounce(func, wait) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // Toggle Playlist Like
    const togglePlaylistLike = debounce(async function (playlistId, button) {
        const isLiked = button.classList.contains('liked');
        const action = isLiked ? 'unlike' : 'like';

        try {
            const response = await fetch('resources/php/playlist-action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `playlist_id=${playlistId}&action=${action}`
            });

            if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);

            const data = await response.json();

            if (data.success) {
                button.classList.toggle('liked');
                button.setAttribute('aria-label', isLiked ? 'Like playlist' : 'Unlike playlist');

                const likeCountElement = document.querySelector('.playlist-stats .stat-item:first-child');
                if (likeCountElement) {
                    likeCountElement.innerHTML = `<i class="fas fa-heart"></i> ${data.like_count} Likes`;
                }
            } else {
                alert(data.message);
            }
        } catch (error) {
            console.error('AJAX error in togglePlaylistLike:', error);
            alert(`Failed to ${action} playlist: ${error.message}`);
        }
    }, 300);

    // Toggle Playlist Follow
    const togglePlaylistFollow = debounce(async function (playlistId, button) {
        const isFollowed = button.classList.contains('followed');
        const action = isFollowed ? 'unfollow' : 'follow';

        try {
            const response = await fetch('resources/php/playlist-action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `playlist_id=${playlistId}&action=${action}`
            });

            if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);

            const data = await response.json();

            if (data.success) {
                button.classList.toggle('followed');
                button.setAttribute('aria-label', isFollowed ? 'Follow playlist' : 'Unfollow playlist');

                const followCountElement = document.querySelector('.playlist-stats .stat-item:last-child');
                if (followCountElement) {
                    followCountElement.innerHTML = `<i class="fas fa-user-plus"></i> ${data.follow_count} Followers`;
                }
            } else {
                alert(data.message);
            }
        } catch (error) {
            console.error('AJAX error in togglePlaylistFollow:', error);
            alert(`Failed to ${action} playlist: ${error.message}`);
        }
    }, 300);

    // Delete entire playlist
    async function deletePlaylist(playlistId) {
        if (!confirm("Are you sure you want to delete this playlist?")) return;

        try {
            const response = await fetch("resources/php/playlist-action2.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `playlist_id=${playlistId}&action=delete_playlist`
            });

            const data = await response.json();

            if (data.success) {
                alert("Playlist deleted successfully!");
                window.location.href = "playlist.php"; // redirect back
            } else {
                alert(data.message || "Failed to delete playlist");
            }
        } catch (err) {
            console.error("Delete playlist error:", err);
            alert("Error deleting playlist");
        }
    }

    // Remove a track from playlist
    async function removeTrack(trackId, button) {
        if (!confirm("Remove this track from playlist?")) return;

        try {
            const response = await fetch("resources/php/playlist-action2.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `track_id=${trackId}&action=remove_track`
            });

            const data = await response.json();

            if (data.success) {
                // remove row from table
                const row = button.closest("tr");
                if (row) row.remove();
            } else {
                alert(data.message || "Failed to remove track");
            }
        } catch (err) {
            console.error("Remove track error:", err);
            alert("Error removing track");
        }
    }