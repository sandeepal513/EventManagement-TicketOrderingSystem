<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
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
                            <h2 class="card-title text-center mb-3">Reset Password</h2>
                            
                            <p class="text-center text-muted mb-4">
                                Enter the email address associated with your account, and we'll send you a link to reset your password.
                            </p>

                            <form action="send_reset_link.php" method="POST">

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email address</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>

                                <div class="d-grid mb-3">
                                    <button type="submit" class="btn btn-primary">Send Password Reset Link</button>
                                </div>

                                <div class="text-center">
                                    <a href="index.php">Back to Login</a>
                                </div>

                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>