<?php
// Include Composer's autoloader (adjust path if needed) 
include "resources/database/func.php";

$connect = new functional();
$isLoggedIn = isset($_SESSION['user']);

// Determine user ID based on session structure
$userId = $_SESSION['user_id'];

if (!$isLoggedIn) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library - MelodyHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="resources/css/main.css">
    <link rel="stylesheet" href="resources/css/library.css">
</head>
<body class="d-flex flex-column min-vh-100">
    <?php include 'resources/html/header.php'; ?>

    <main class="container mt-4">
        <h2 class="gradient-text mb-5">Your Library</h2>

        <!-- Followed Artists Section -->
        <section class="container my-5">
            <h2 class="mb-4 text-information">Followed Artists</h2>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 justify-content-center">
                <?php
                    $followedArtists = $connect->select(
                        "a.artist_id, a.artist_name, a.artist_image", 
                        "artist a JOIN artist_follows af ON a.artist_id = af.artist_id", 
                        "WHERE af.user_id = '$userId'"
                    );

                    if ($followedArtists && mysqli_num_rows($followedArtists) > 0):
                        while ($artist = mysqli_fetch_assoc($followedArtists)):
                            $artistId = $artist['artist_id'];
                            $artistName = htmlspecialchars($artist['artist_name'], ENT_QUOTES, 'UTF-8');
                            $artistImage = !empty($artist['artist_image']) ? htmlspecialchars($artist['artist_image'], ENT_QUOTES, 'UTF-8') : 'resources/images/default.jpg';
                    ?>
                            <div class="col d-flex justify-content-center">
                                <a href="artist_detail.php?artist_name=<?= urlencode($artistName) ?>&artist_id=<?= urlencode($artistId) ?>" class="text-decoration-none">
                                    <div class="text-center artist-card">
                                        <div class="artist-circle mb-2">
                                            <img src="<?= $artistImage ?>" 
                                                alt="<?= $artistName ?>" 
                                                class="img-fluid rounded-circle"
                                                onerror="this.src='resources/images/default.jpg';">
                                        </div>
                                        <h5 class="artist-name mt-2"><?= $artistName ?></h5>
                                    </div>
                                </a>
                            </div>
                    <?php
                        endwhile;
                    else:
                    ?>
                        <p class="text-white text-center py-5">No followed artists yet.</p>
                    <?php endif; ?>
            </div>
        </section>



        <!-- Followed Playlists Section -->
        <section class="container my-5">
            <h2 class="mb-4 text-information">Followed Playlists</h2>
            
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 justify-content-center">
                <?php
                $playlists = $connect->select(
                    "pf.playlist_id, p.playlist_name, p.playlist_image", 
                    "playlist_follows pf JOIN playlist p ON pf.playlist_id = p.playlist_id", 
                    "WHERE pf.user_id = '$userId'"
                );

                if ($playlists && mysqli_num_rows($playlists) > 0):
                    while ($playlist = mysqli_fetch_assoc($playlists)):
                        $playlistId = htmlspecialchars($playlist['playlist_id']);
                        $playlistName = !empty($playlist['playlist_name']) ? htmlspecialchars($playlist['playlist_name']) : "Playlist #$playlistId";
                        $playlistImage = !empty($playlist['playlist_image']) ? htmlspecialchars($playlist['playlist_image']) : 'https://via.placeholder.com/300x300.png?text=Default';
                ?>
                        <div class="col">
                            <a href="playlist_details.php?id=<?= $playlistId ?>" class="card-link" aria-label="View playlist <?= $playlistName ?>">
                                <div class="playlist-circle text-center">
                                    <img src="<?= $playlistImage ?>" alt="Playlist Cover for <?= $playlistName ?>" class="img-fluid rounded-circle">
                                </div>
                                <h5 class="artist-name mt-2 text-center"><?= $playlistName ?></h5>
                            </a>
                        </div>
                <?php
                    endwhile;
                else:
                ?>
                    <p class="text-white text-center py-5">No followed playlists yet.</p>
                <?php endif; ?>
            </div>
        </section>

    </main>

    <?php include 'resources/html/footer.php'; ?>

    <script src="https://www.youtube.com/iframe_api"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
       
</body>
</html>