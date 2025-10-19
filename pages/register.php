<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create an Account</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <style>
        body, html {
            height: 100%;
        }
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 0;
            background-color: #f8f9fa;
        }
        .login-card {
            width: 100%;
            max-width: 500px; /* Slightly wider for the extra fields */
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
                            <h2 class="card-title text-center mb-4">Create Account</h2>
                            
                            <?php
                                session_start(); 
                                
                                // Check for a success message
                                if (isset($_SESSION['message'])) {
                                    echo '<div class="alert alert-success" role="alert">' . $_SESSION['message'] . '</div>';
                                    unset($_SESSION['message']); // Clear message
                                }
                                
                                // Check for an error message
                                if (isset($_SESSION['error'])) {
                                    echo '<div class="alert alert-danger" role="alert">' . $_SESSION['error'] . '</div>';
                                    unset($_SESSION['error']); // Clear error
                                }
                            ?>

                            <form action="register_process.php" method="POST">

                                <div class="mb-3">
                                    <label for="full_name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" required>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email address</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>

                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" required>
                                </div>

                                <div class="mb-3">
                                    <label for="role" class="form-label">Register as:</label>
                                    <select class="form-select" id="role" name="role" required>
                                        <option value="user" selected>User (Attendee)</option>
                                        <option value="organizer">Event Organizer</option>
                                    </select>
                                </div>

                                <div class="d-grid mb-3">
                                    <button type="submit" class="btn btn-primary">Create Account</button>
                                </div>

                                <div class="text-center">
                                    <p class="mb-0">Already have an account? 
                                        <a href="index.php">Login here</a>
                                    </p>
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