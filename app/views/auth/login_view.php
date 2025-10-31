<?php

    include_once __DIR__ . '/../../../config/constants.php';

    session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event System Login</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/login.css">
</head>

<body>

    <main class="login-card">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body p-4 p-md-5">
                            <h2 class="card-title text-center mb-4">Login</h2>
                            
                            <?php
                                // Check for an error message
                                if (isset($_SESSION['error'])) {
                                    echo '<div class="alert alert-danger" role="alert">' . $_SESSION['error'] . '</div>';
                                    unset($_SESSION['error']);
                                }

                                if (isset($_SESSION['message'])) {
                                    echo '<div class="alert alert-success" role="alert" style="text-align: center">'
                                        . htmlspecialchars($_SESSION['message']) .
                                        '</div>';

                                    unset($_SESSION['message']); // clear session message
                            ?>

                                <!-- Redirect after 3 seconds -->
                                <script>
                                    setTimeout(function() {
                                        window.location.href = "<?php echo BASE_URL; ?>index.php";
                                    }, 3000); // 3000 ms = 3 seconds
                                </script>

                            <?php
                            
                                }
                                
                                // Check for a success message (e.g., from registration)
                                if (isset($_SESSION['message'])) {
                                    echo '<div class="alert alert-success" role="alert">' . $_SESSION['message'] . '</div>';
                                    unset($_SESSION['message']);
                                }
                            ?>
                            <form action="<?php echo BASE_URL; ?>app/controllers/login_controller.php" method="POST">

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email address</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>

                                <div class="d-grid mb-3">
                                    <button type="submit" class="btn btn-primary">Login</button>
                                </div>

                                <div class="text-center">
                                    <a href="<?php echo BASE_URL; ?>app/views/auth/forgot_password_view.php">Forgot your password?</a>
                                </div>

                            </form>

                            <hr class="my-4">

                            <div class="text-center">
                                <p class="mb-0">Don't have an account?</p>
                                <a href="<?php echo BASE_URL; ?>app/views/auth/register_view.php" class="btn btn-outline-success mt-2">Create an Account</a>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>