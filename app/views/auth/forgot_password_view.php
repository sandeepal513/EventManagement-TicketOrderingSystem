<?php include_once './../../../config/constants.php'; ?>
<?php session_start(); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <style>
        body, html {
            height: 100%;
        }
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
        }
        .login-card {
            width: 100%;
            max-width: 450px;
        }
        
    </style>
</head>
<body>

    <main class="login-card">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body p-4 p-md-5">

                            <?php
                                // Check for an error message
                                if (isset($_SESSION['error'])) {
                                    // Give the alert an ID so JavaScript can find it
                                    echo '<div id="alert-message" class="alert alert-danger" role="alert">' . htmlspecialchars($_SESSION['error']) . '</div>';
                                    unset($_SESSION['error']); // Unset after displaying
                                }

                                // *** FIX: Check for 'message' instead of 'success' ***
                                if (isset($_SESSION['message'])) {
                                    // Give the alert an ID so JavaScript can find it
                                    echo '<div id="alert-message" class="alert alert-success" role="alert" style="text-align: center">'
                                        . htmlspecialchars($_SESSION['message']) .
                                        '</div>';
                                    unset($_SESSION['message']); // Unset after displaying

                            ?>
                                <script>
                                    setTimeout(function() {
                                        window.location.href = "<?php echo BASE_URL; ?>app/views/auth/login_view.php";
                                    }, 3000); // 3000 ms = 3 seconds
                                </script>
                            <?php
                                }
                            ?>

                            <h2 class="card-title text-center mb-3">Reset Password</h2>
                            
                            <p class="text-center text-muted mb-4">
                                Enter the email address associated with your account, and we'll send you a link to reset your password.
                            </p>

                            <form action="./../../controllers/send_reset_link_controller.php" method="POST">

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email address</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>

                                <div class="d-grid mb-3">
                                    <button type="submit" class="btn btn-primary">Send Password Reset Link</button>
                                </div>

                                <div class="text-center">
                                    <a href="<?php echo BASE_URL; ?>app/views/auth/login_view.php">Back to Login</a>
                                </div>

                            </form>

                            

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- *** ADDED: JavaScript to hide the alert after 3.5 seconds *** -->
    <script src="<?php echo BASE_URL; ?>app/public/assets/js/forgot_password.js"></script>
</body>
</html>
