<?php
    session_start(); 

    $token = isset($_GET['token']) ? $_GET['token'] : '';
    if (empty($token)) {
        die("Invalid reset link.");
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body, html { height: 100%; }
        body { display: flex; align-items: center; justify-content: center; background-color: #f8f9fa; }
        .login-card { width: 100%; max-width: 450px; }
    </style>
</head>
<body>
    <main class="login-card">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body p-4 p-md-5">
                            <h2 class="card-title text-center mb-4">Set New Password</h2>
                            
                            <?php
                                if (isset($_SESSION['error'])) {
                                    echo '<div class="alert alert-danger text-center" role="alert">' . $_SESSION['error'] . '</div>';
                                    unset($_SESSION['error']);
                                }
                            ?>

                            <form action="./../../controllers/update_password_controller.php" method="POST">
                                
                                <input type="hidden" name="reset_token" value="<?php echo htmlspecialchars($token); ?>">

                                <div class="mb-3">
                                    <label for="password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>

                                <div class="d-grid mb-3">
                                    <button type="submit" class="btn btn-primary">Update Password</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>