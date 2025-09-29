<?php
include "resources/database/func.php";
$connect = new functional();
$isLoggedIn = isset($_SESSION['user']);

if (isset($_POST['btn'])) {
    $search = $_POST['search'];
    $search = mysqli_real_escape_string($connect->conn, $search);
    header("Location: search_result.php?search=" . urlencode($search));
    exit();
}

// Fetch artists from database
$artists_query = $connect->select("*", "artist", "LIMIT 8"); // Limit to 8 artists for display
$artists = [];
while ($row = mysqli_fetch_assoc($artists_query)) {
    $artists[] = $row;
}
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
    <script src="https://kit.fontawesome.com/a076d05399.js"></script> <!-- For icons -->
    
    <!-- Link to the custom style.css -->
    <link rel="stylesheet" type="text/css" href="resources/css/main.css">
    <link rel="stylesheet" type="text/css" href="resources/css/style.css">
</head>
<body class="d-flex flex-column min-vh-100">
    <!-- Header Section with Black and Pink Theme -->
    <?php include 'resources/html/header.php'?>
    
    <!-- Hero Section with Gradient Pink and Orange -->
    <section class="hero-section text-center py-5">
        <div class="container">
            <h1>Discover Your Soundtrack</h1>
            <p>Find the songs you love, explore new artists, and build the perfect playlist for every mood.</p>

            <div id="search-box" class="mt-3">
                <form action="index.php" method="post" class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search for songs, artists, or playlists..." required>
                    <button class="btn btn-dark" type="submit" name="btn">
                        <i class="fa-solid fa-search"></i>
                    </button>
                </form>
            </div>
        </div>
    </section>

    <!-- Popular Artists Section -->
    <section class="py-5 popular-artists" style="background-color: #111111;">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0 text-information">Popular Artists</h2>
                <a href="artist.php" class="btn view-all-btn">View All Artists</a>
            </div>
            <div class="row justify-content-center">
                <?php if (!empty($artists)): ?>
                    <?php foreach ($artists as $artist): ?>
                        <div class="col-lg-3 col-md-4 col-sm-6 text-center mb-4 artist-card"
                            onclick="window.location.href='artist_detail.php?artist_id=<?php echo $artist['artist_id']; ?>'">
                            <!-- Circle artist image -->
                            <div class="artist-circle mx-auto mb-2">
                                <img src="<?php echo htmlspecialchars($artist['artist_image']); ?>" 
                                    alt="<?php echo htmlspecialchars($artist['artist_name']); ?>" 
                                    onerror="this.src='https://images.unsplash.com/photo-1477118476589-bff2c5c4cfbb?ixlib=rb-4.0.3'">
                            </div>
                            <!-- Artist Name -->
                            <h5 class="artist-name"><?php echo htmlspecialchars($artist['artist_name']); ?></h5>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <p class="text-muted">No artists found in the database.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>



    <?php include 'resources/html/footer.php'?>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>

<script src="resources/javascript/script.js"></script>