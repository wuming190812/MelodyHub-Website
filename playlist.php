<?php
include "resources/database/func.php";
$connect = new functional();
$isLoggedIn = isset($_SESSION['user']);

// Fetch all playlists for the logged-in user
$playlists = $connect->select("*", "playlist", "WHERE creator_id = '{$_SESSION['user_id']}'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Playlists - MelodyHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <link rel="stylesheet" type="text/css" href="resources/css/main.css">
    <link rel="stylesheet" type="text/css" href="resources/css/playlist.css">
</head>
<body class="d-flex flex-column min-vh-100">

    <?php include 'resources/html/header.php'; ?>

    <!-- Modal for Adding Playlist -->
    <div class="modal fade" id="createPlaylistModal" tabindex="-1" aria-labelledby="createPlaylistModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createPlaylistModalLabel">Create a New Playlist</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Playlist Creation Form -->
                    <form action="resources/php/playlist-process.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="name" class="form-label text-information">Playlist Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <button type="submit" name="submit" class="btn btn-warning text-dark">Create Playlist</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- List of Playlists -->
    <section class="container my-5">
        <h2 class="mb-4 gradient-text">Your Playlists</h2>
        
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 justify-content-center">
            <?php
            // Display the playlists for the logged-in user
            if (mysqli_num_rows($playlists) > 0) {
                mysqli_data_seek($playlists, 0); // Reset result pointer
                while ($playlist = mysqli_fetch_assoc($playlists)) {
                    echo '
                        <div class="col">
                            <a href="playlist_details.php?id=' . htmlspecialchars($playlist['playlist_id']) . '" class="card-link" aria-label="View playlist ' . htmlspecialchars($playlist['playlist_name']) . '">
                                <div class="playlist-circle">
                                    <img src="' . htmlspecialchars($playlist['playlist_image'] ?? 'https://via.placeholder.com/300x300.png?text=Default') . '" alt="Playlist Cover for ' . htmlspecialchars($playlist['playlist_name']) . '">
                                </div>
                                <h5 class="artist-name mt-2 text-center">' . htmlspecialchars($playlist['playlist_name']) . '</h5>
                            </a>
                        </div>
                    ';
                }
            } else {
                echo '<p class="text-white text-center">You have no playlists yet. Create one now!</p>';
            }
            ?>
        </div>
    </section>
    <!-- Button to Trigger Modal -->
    <section class="container my-5">
        <button type="button" class="btn btn-warning text-dark" data-bs-toggle="modal" data-bs-target="#createPlaylistModal">Create New Playlist</button>
    </section>

    <?php include 'resources/html/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>