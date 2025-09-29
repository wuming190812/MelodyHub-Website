<?php
include "resources/database/func.php";
$connect = new functional();
$isLoggedIn = isset($_SESSION['user']);

$artistform = $connect->select("artist_image, artist_name, artist_id", "artist", "");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MelodyHub</title>
    <!-- Add Poppins Font Link -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <link rel="stylesheet" type="text/css" href="resources/css/main.css">
    <link rel="stylesheet" type="text/css" href="resources/css/artist.css">
</head>
<body class="d-flex flex-column min-vh-100">
    <?php include 'resources/html/header.php'; ?>

    <!-- Artist Circle Grid Section -->
    <section class="container my-5">
        <h2 class="mb-4 text-information">Artists</h2>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 justify-content-center">
            <?php 
            // Check if there are any artists
            if (mysqli_num_rows($artistform) > 0) {
                // Loop through all artists
                while ($artistdata = mysqli_fetch_assoc($artistform)) { 
            ?>
            <div class="col d-flex justify-content-center">
                <a href="artist_detail.php?artist_name=<?php echo urlencode($artistdata['artist_name']); ?>&artist_id=<?php echo urlencode($artistdata['artist_id']); ?>" class="text-decoration-none">
                    <div class="text-center artist-card">
                        <div class="artist-circle mb-2">
                            <img src="<?php echo htmlspecialchars($artistdata['artist_image'], ENT_QUOTES, 'UTF-8'); ?>" 
                                 alt="<?php echo htmlspecialchars($artistdata['artist_name'], ENT_QUOTES, 'UTF-8'); ?>" 
                                 class="img-fluid rounded-circle"
                                 onerror="this.src='resources/images/default.jpg';">
                        </div>
                        <h5 class="artist-name mt-2"><?php echo htmlspecialchars($artistdata['artist_name'], ENT_QUOTES, 'UTF-8'); ?></h5>
                    </div>
                </a>
            </div>
            <?php 
                }
            } else {
                echo '<p class="text-center">No artists found.</p>';
            }
            ?>
        </div>
    </section>

    <?php include 'resources/html/footer.php'; ?>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>