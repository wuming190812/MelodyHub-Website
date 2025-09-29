<?php
include "resources/database/func.php";
$connect = new functional();
$isLoggedIn = isset($_SESSION['user']);

if (!$isLoggedIn || !isset($_SESSION['user_id'])) {
    echo "Error: User not logged in";
    exit;
}

$id = $_SESSION['user_id'];

// Fetch user data
$get = $connect->select("*", "user", "where id='$id'"); // Ensure this method is secure
if (mysqli_num_rows($get) > 0) {
    $fetch = mysqli_fetch_array($get);
} else {
    echo "Error: No user found with ID '$id'";
    exit;
}

if (isset($_POST['submit']) && isset($_GET['id']) && $_GET['id'] == $id) {
    $fullname = isset($_POST['fullname']) ? $_POST['fullname'] : ($fetch['fullname'] ?? '');
    $email = isset($_POST['email']) ? $_POST['email'] : ($fetch['email'] ?? '');
    $gender = isset($_POST['gender']) ? $_POST['gender'] : ($fetch['gender'] ?? 'Not specified');
    $datebirth = isset($_POST['date_birth']) ? $_POST['date_birth'] : ($fetch['date_birth'] ?? '');
    $updateid = $_GET['id'];

    // Validate inputs (basic example)
    if (empty($fullname) || empty($email)) {
        echo "Error: Fullname and email are required";
        exit;
    }

    // Use prepared statements in your functional class or directly
    $sql = $connect->update("user", "email='$email',fullname='$fullname',gender='$gender',date_birth='$datebirth'", "where id = '$updateid'");
    if ($sql) {
        echo "success";
        header("Location: profile.php?id=$id"); // Redirect to avoid form resubmission
        exit;
    } else {
        echo "Error updating profile";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - MelodyHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>

    <link rel="stylesheet" type="text/css" href="resources/css/main.css">
    <link rel="stylesheet" type="text/css" href="resources/css/profile.css">
</head>
<body class="d-flex flex-column min-vh-100">
    <?php include 'resources/html/header.php'; ?>

    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-4">Your Profile</h2>
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card shadow-lg border-0 text-center">
                        <div class="card-body">
                            <div class="artist-img-container mb-3">
                                <i class="fas fa-user-circle artist-img" style="font-size: 150px; color: #ff7e22;"></i>
                            </div>
                            <p class="card-text">View your account details below.</p>
                            <form method="POST" action="profile.php?id=<?php echo $id; ?>" class="text-start" id="profileForm">
                                <?php if ($fetch) { ?>
                                    <div class="mb-3">
                                        <label for="fullname" class="form-label">Fullname</label>
                                        <input type="text" class="form-control" id="fullname" name="fullname" value="<?php echo htmlspecialchars($fetch['fullname'] ?? ''); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($fetch['email'] ?? ''); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="gender" class="form-label">Gender</label>
                                        <select class="form-control" id="gender" name="gender">
                                            <option value="Not specified" <?php echo (($fetch['gender'] ?? 'Not specified') === 'Not specified') ? 'selected' : ''; ?> <?php echo (in_array($fetch['gender'] ?? 'Not specified', ['Male', 'Female', 'Other'])) ? 'disabled' : ''; ?>>Not specified</option>
                                            <option value="Male" <?php echo (($fetch['gender'] ?? '') === 'Male') ? 'selected' : ''; ?>>Male</option>
                                            <option value="Female" <?php echo (($fetch['gender'] ?? '') === 'Female') ? 'selected' : ''; ?>>Female</option>
                                            <option value="Other" <?php echo (($fetch['gender'] ?? '') === 'Other') ? 'selected' : ''; ?>>Other</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="birth_date" class="form-label">Birth Date</label>
                                        <input type="date" class="form-control" id="birth_date" name="date_birth" value="<?php echo htmlspecialchars($fetch['date_birth'] ?? ''); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Join Date</label>
                                        <p class="form-control plaintext"><?php echo htmlspecialchars($fetch['created_at'] ? date('d F Y', strtotime($fetch['created_at'])) : date('d F Y')); ?></p>
                                    </div>
                                <?php } else { ?>
                                    <p class="text-danger">Error: Unable to load profile data.</p>
                                <?php } ?>
                                <button type="submit" name="submit" id="updateButton" class="btn btn-warning text-dark" style="margin-top: 1rem; display:none;">Save Changes</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    <?php include 'resources/html/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const form = document.getElementById("profileForm");
        const updateButton = document.getElementById("updateButton");

        // Show button when user interacts with any input/select
        form.querySelectorAll("input, select, textarea").forEach(field => {
            field.addEventListener("focus", () => {
                updateButton.style.display = "block";
            });
            field.addEventListener("input", () => {
                updateButton.style.display = "block";
            });
        });
    });
    </script>
</body>
</html>
