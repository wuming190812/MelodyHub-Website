let player;
        let currentSong = null;
        let isShuffled = false;
        let songsQueue = [];
        let currentSongIndex = -1;

        // Initialize YouTube Player with fallback
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
            if (event.data === YT.PlayerState.PLAYING) {
                playPauseBtn.innerHTML = '<i class="fas fa-pause"></i>';
                updateProgress();
            } else if (event.data === YT.PlayerState.PAUSED || event.data === YT.PlayerState.ENDED) {
                playPauseBtn.innerHTML = '<i class="fas fa-play"></i>';
            }

            if (event.data === YT.PlayerState.ENDED) {
                playNextSong();
            }
        }

        // Play a song
        function playSong(songData) {
            if (!songData.videoId) {
                console.error('No video ID available for:', songData.title);
                return;
            }
            if (!player) {
                console.error('Player not initialized. Initializing now...');
                initializePlayer();
                setTimeout(() => playSong(songData), 500); // Retry after initialization
                return;
            }
            currentSong = songData;
            document.getElementById('player-bar').style.display = 'flex';
            document.getElementById('player-image').src = songData.image;
            document.getElementById('player-title').textContent = songData.title;
            document.getElementById('total-time').textContent = songData.duration;

            player.loadVideoById(songData.videoId);
            player.playVideo();

            if (!songsQueue.find(song => song.videoId === songData.videoId)) {
                songsQueue.push(songData);
                currentSongIndex = songsQueue.length - 1;
            }
        }

        // Play/Pause button handler
        document.getElementById('play-pause-btn').addEventListener('click', () => {
            if (player && player.getPlayerState) {
                if (player.getPlayerState() === YT.PlayerState.PLAYING) {
                    player.pauseVideo();
                } else {
                    player.playVideo();
                }
            }
        });

        // Previous and Next buttons
        document.getElementById('prev-btn').addEventListener('click', () => {
            if (currentSongIndex > 0 && player) {
                currentSongIndex--;
                playSong(songsQueue[currentSongIndex]);
            }
        });

        document.getElementById('next-btn').addEventListener('click', playNextSong);

        function playNextSong() {
            if (player) {
                if (isShuffled) {
                    currentSongIndex = Math.floor(Math.random() * songsQueue.length);
                } else if (currentSongIndex < songsQueue.length - 1) {
                    currentSongIndex++;
                } else {
                    currentSongIndex = 0; // Loop back to start
                }
                if (songsQueue[currentSongIndex]) {
                    playSong(songsQueue[currentSongIndex]);
                }
            }
        }

        // Shuffle button
        document.getElementById('shuffle-btn').addEventListener('click', () => {
            isShuffled = !isShuffled;
            document.getElementById('shuffle-btn').style.color = isShuffled ? '#1db954' : '#b3b3b3';
        });

        // Close player
        document.getElementById('close-player').addEventListener('click', () => {
            if (player) {
                player.stopVideo();
                document.getElementById('player-bar').style.display = 'none';
                currentSong = null;
            }
        });

        // Progress bar update
        function updateProgress() {
            if (player && player.getCurrentTime) {
                const currentTime = player.getCurrentTime();
                const duration = player.getDuration();
                const progressPercent = (currentTime / duration) * 100;
                document.getElementById('progress-fill').style.width = `${progressPercent}%`;
                document.getElementById('current-time').textContent = formatTime(currentTime);
                document.getElementById('total-time').textContent = formatTime(duration);

                if (player.getPlayerState() === YT.PlayerState.PLAYING) {
                    requestAnimationFrame(updateProgress);
                }
            }
        }

        // Seek progress bar
        function seekProgress(event) {
            if (player && player.getDuration) {
                const progressBar = document.getElementById('progress-bar');
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
                    songsQueue.push(JSON.parse(btn.dataset.song));
                });
                currentSongIndex = songsQueue.findIndex(song => song.videoId === songData.videoId);
                playSong(songData);
            });
        });

        // Initialize player on page load
        window.onload = function() {
            initializePlayer();
        };