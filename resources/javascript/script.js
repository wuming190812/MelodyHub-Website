    // Add click functionality to artist cards
    document.addEventListener('DOMContentLoaded', function() {
    const artistCards = document.querySelectorAll('.artist-card');
        artistCards.forEach(card => {
            card.addEventListener('click', function() {
                window.location.href = this.getAttribute('onclick').match(/'([^']+)'/)[1];
            });
        });
    });

    window.onload = function() {
        var logoutModal = new bootstrap.Modal(document.getElementById('logoutModal'));
        logoutModal.show();
    };

    // Share Song
    function shareSong(songLink, songName, btn) {

        const facebookUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(songLink)}`;
        const whatsappUrl = `https://api.whatsapp.com/send?text=${encodeURIComponent(songName + ' ' + songLink)}`;

        let shareMenu = document.createElement('div');
        shareMenu.classList.add('share-menu');
        shareMenu.style.position = 'absolute';
        shareMenu.style.background = '#222';
        shareMenu.style.border = 'none';
        shareMenu.style.padding = '8px';
        shareMenu.style.borderRadius = '10px';
        shareMenu.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
        shareMenu.style.zIndex = 9999;

        shareMenu.innerHTML = `
            <p style="margin:0 0 5px 0;">Share "${songName}" via:</p>
            <a href="${facebookUrl}" target="_blank" style="display:flex; align-items:center; gap:5px; margin-bottom:5px; text-decoration:none; color:#1877F2;">
                <i class="fab fa-facebook-square"></i> Facebook
            </a>
            <a href="${whatsappUrl}" target="_blank" style="display:flex; align-items:center; gap:5px; text-decoration:none; color:#25D366;">
                <i class="fab fa-whatsapp"></i> WhatsApp
            </a>
        `;

        document.body.appendChild(shareMenu);

        const rect = btn.getBoundingClientRect();
        shareMenu.style.top = `${rect.bottom + window.scrollY + 5}px`;
        shareMenu.style.left = `${rect.left + window.scrollX}px`;

        function removeMenu(e) {
            if (!shareMenu.contains(e.target) && e.target !== btn) {
                shareMenu.remove();
                document.removeEventListener('click', removeMenu);
            }
        }
        document.addEventListener('click', removeMenu);
    }

    // Record Download
    function recordDownload(songId) {
        fetch('resources/php/download_song.php?song_id=' + songId)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Download recorded!');
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => {
                console.error('Request failed', err);
                alert('An error occurred. Please try again.');
            });
    }

    let currentSongId = null;

    function openPlaylistModal(songId, songName, artistId) {
        // Store song ID in hidden input (artistId is no longer needed in the request)
        const selectedSongIdInput = document.getElementById('selectedSongId');
        if (selectedSongIdInput) {
            selectedSongIdInput.value = songId;
            currentSongId = songId; // Sync with global variable
        } else {
            console.error('selectedSongId input not found');
            return;
        }

        // Update song title in modal
        const songTitle = document.getElementById('songTitle');
        if (songTitle) {
            songTitle.textContent = `Add "${songName}" to playlist`;
        } else {
            console.error('songTitle element not found');
        }

        // Clear dropdown to avoid duplicates
        const dropdown = document.getElementById('playlistSelect');
        if (dropdown) {
            dropdown.innerHTML = '<option disabled selected>Loading...</option>';

            // Fetch user playlists
            fetch('resources/php/get_playlists.php')
                .then(response => response.json())
                .then(data => {
                    dropdown.innerHTML = ''; // Clear options

                    if (data.playlists && data.playlists.length > 0) {
                        data.playlists.forEach(pl => {
                            const opt = document.createElement('option');
                            opt.value = pl.playlist_id;
                            opt.textContent = pl.playlist_name;
                            dropdown.appendChild(opt);
                        });
                    } else {
                        const opt = document.createElement('option');
                        opt.disabled = true;
                        opt.textContent = 'No playlist found';
                        dropdown.appendChild(opt);
                    }
                })
                .catch(err => {
                    console.error('Error fetching playlists:', err);
                    dropdown.innerHTML = '<option disabled>Error loading playlists</option>';
                });
        } else {
            console.error('playlistSelect element not found');
        }

        // Show modal using Bootstrap
        const modal = new bootstrap.Modal(document.getElementById('playlistModal'));
        modal.show();
    }

    function saveToPlaylist() {
        const playlistId = document.getElementById("playlistSelect").value;
        if (!currentSongId) {
            alert('No song selected');
            return;
        }

        fetch(`resources/php/add_to_playlist.php?song_id=${currentSongId}&playlist_id=${playlistId}`)
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if (data.status === "success") {
                    const modal = bootstrap.Modal.getInstance(document.getElementById("playlistModal"));
                    if (modal) {
                        modal.hide();
                    }
                }
            })
            .catch(err => {
                console.error('Error saving to playlist:', err);
                alert('An error occurred while saving to playlist');
            });
    }

    // Debounce function to prevent rapid clicks
    function debounce(func, wait) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    const likeCountElement = document.querySelector('.like-count');
    const followCountElement = document.querySelector('.follow-count');
    
    // Toggle Like
    const toggleLike = debounce(async function (artistId, button) {
        const isLiked = button.classList.contains('liked');
        const action = isLiked ? 'unlike' : 'like';

        try {
            const response = await fetch('resources/php/artist-action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `artist_id=${artistId}&action=${action}`
            });

            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }

            const data = await response.json();

            if (data.success) {
                button.classList.toggle('liked');
                button.setAttribute('aria-label', isLiked ? 'Like artist' : 'Unlike artist');
                
                // Fixed selector for like count
                const likeCountElement = document.querySelector('.artist-stats .stat-item:first-child');
                if (likeCountElement) {
                    likeCountElement.innerHTML = `<i class="fas fa-heart"></i> ${data.like_count} Likes`;
                }
            } else {
                console.error('Server error:', data.message);
                alert(data.message);
            }
        } catch (error) {
            console.error('AJAX error in toggleLike:', error);
            alert(`Failed to ${action} artist: ${error.message}`);
        }
    }, 300);

    // Toggle Follow
    const toggleFollow = debounce(async function (artistId, button) {
        const isFollowed = button.classList.contains('followed');
        const action = isFollowed ? 'unfollow' : 'follow';

        try {
            const response = await fetch('resources/php/artist-action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `artist_id=${artistId}&action=${action}`
            });

            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }

            const data = await response.json();

            if (data.success) {
                button.classList.toggle('followed');
                button.setAttribute('aria-label', isFollowed ? 'Follow artist' : 'Unfollow artist');
                
                // Fixed selector for follow count
                const followCountElement = document.querySelector('.artist-stats .stat-item:last-child');
                if (followCountElement) {
                    followCountElement.innerHTML = `<i class="fas fa-user-plus"></i> ${data.follow_count} Followers`;
                }
            } else {
                console.error('Server error:', data.message);
                alert(data.message);
            }
        } catch (error) {
            console.error('AJAX error in toggleFollow:', error);
            alert(`Failed to ${action} artist: ${error.message}`);
        }
    }, 300);
    