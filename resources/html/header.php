<header class="bg-dark text-white py-3">
        <div class="container">
            <nav class="d-flex justify-content-between">
                <a href="index.php" class="text-decoration-none d-flex align-items-center">
                    MelodyHub
                </a>

                <div>
                    <ul class="nav">
                        <li class="nav-item">
                            <a href="index.php" class="nav-link text-white">Home</a>
                        </li>
                        <li class="nav-item">
                            <a href="artist.php" class="nav-link text-white">Artist</a>
                        </li>
                        <li class="nav-item">
                            <a href="library.php" class="nav-link text-white">Library</a>
                        </li>
                        
                        <?php if ($isLoggedIn): ?>
                            <!-- Logged in: Show username dropdown with Logout and Profile -->
                            <li class="nav-item dropdown">
                                <a href="#" class="nav-link dropdown-toggle text-white" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <?php echo htmlspecialchars($_SESSION['user']); ?>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                    <li><a class="dropdown-item" href="downloads.php">Download</a></li>
                                    <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                                    <li><a class="dropdown-item" href="playlist.php">Playlist</a></li>
                                    <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <!-- Not logged in: Show Account dropdown -->
                            <li class="nav-item dropdown">
                                <a href="#" class="nav-link dropdown-toggle text-white" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Account
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                    <li><a class="dropdown-item" href="login.php">Login</a></li>
                                    <li><a class="dropdown-item" href="register.php">Register</a></li>
                                </ul>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>
        </div>
    </header>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>