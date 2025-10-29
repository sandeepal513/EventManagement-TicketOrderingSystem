<?php

    session_start();

    include_once './../config/connection.php';

    if (!$conn) {
        // Redirect to a specific error page for database issues
        header("Location: ./../php/error.php?type=db_down");
        exit();
    }
    
    // Remove test session data in production
    $_SESSION["user_id"] = 1; // Simulated user ID for testing
    $_SESSION['role'] = 'user'; // Simulated role for testing

    // Check if user is logged in
    if (empty($_SESSION['user_id'])) {
        header("Location: ./../php/login.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];
    
    // Prepare the SQL statement
    $user_dtl = "SELECT * FROM users WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $user_dtl);
    
    // Bind the user ID parameter (i = integer)
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    
    // Execute the statement
    mysqli_stmt_execute($stmt);
    
    // Get the result set
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    
    // Close the statement
    mysqli_stmt_close($stmt);

    // Get notification preferences (add after $user = mysqli_fetch_assoc($result);)
    $email_alerts = isset($user['email_alerts']) ? (int)$user['email_alerts'] : 0;
    $sms_alerts = isset($user['sms_alerts']) ? (int)$user['sms_alerts'] : 0;

    // If user not found (shouldn't happen if ID is good, but good practice)
    if (!$user) {
        header("Location: ./../php/login.php?error=user_not_found");
        exit();
    }
    
    // Determine profile picture source
    if (!empty($user['profile_pic'])) {
        $profile_pic_src = "./../res/profile_pic/" . $user['profile_pic'];
    } else {
        $profile_pic_src = "./../res/profile_pic/default_profile.png"; // Default profile picture
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Link to the external CSS file -->
    <link rel="stylesheet" href="./../css/profile.css"> 
    <title>Profile - <?php echo ucfirst($user['role']); ?></title>
</head>
<body>

    <!-- Success/Error Message Display -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <?php 
                echo htmlspecialchars($_SESSION['success_message']); 
                unset($_SESSION['success_message']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-error">
            <?php 
                echo htmlspecialchars($_SESSION['error_message']); 
                unset($_SESSION['error_message']);
            ?>
        </div>
    <?php endif; ?>

    <!-- 1. Edit Profile Details Modal -->
    <div class="edit-profile" id="editProfileModal">
        <div class="edit-profile-content">
            <div class="modal-header">
                <h2>Edit Your Profile</h2>
                <button class="close-btn" id="closeModalBtn">&times;</button>
            </div>

            <form action="./../php/profile_details_edit.php" method="post">
                <div class="form-group">
                    <label for="full_name">Full Name:</label>
                    <input type="text" name="full_name" id="full_name" placeholder="Enter your full name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" name="email" id="email" placeholder="Enter your email address" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number:</label>
                    <input type="tel" name="phone" id="phone" placeholder="Enter your mobile no" value="<?php echo htmlspecialchars($user['phone']); ?>">
                </div>
                
                <div class="form-group paassword-group">
                    <label for="current_password">Current Password:</label>
                    <div class="password-wrapper">
                        <input type="password" name="current_pwd" id="current_password" placeholder="Leave blank to keep current password">
                        <button type="button" class="toggle-password" data-target="current_password">👁</button>
                    </div>
                </div>

                <div class="form-group password-group">
                    <label for="new_password">New Password:</label>
                    <div class="password-wrapper">
                        <input type="password" name="new_pwd" id="new_password" placeholder="Leave blank to keep current password">
                        <button type="button" class="toggle-password" data-target="new_password">👁</button>
                    </div>
                </div>

                <div class="form-group password-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <div class="password-wrapper">
                        <input type="password" name="confirm_pwd" id="confirm_password" placeholder="Leave blank to keep current password">
                        <button type="button" class="toggle-password" data-target="confirm_password">👁</button>
                    </div>
                </div>
                
                <div class="btn-group">
                    <input type="submit" value="Save Changes" class="save-btn">
                </div>
                
            </form>
        </div>
    </div>

    <!-- 2. Enhanced Profile Image Upload Modal (identical structure) -->
    <div class="profileimage" id="editProfileimageModal">
        <div class="edit-profile-image-content">
            <div class="modal-header">
                <h2>Change Profile Photo</h2>
                <button class="close-btn" id="closeImageModalBtn">&times;</button>
            </div>

            <form action="./../php/profile_pic_update.php" method="post" id="imageUploadForm" enctype="multipart/form-data">
                <div class="image-upload-area">
                    <?php if ($profile_pic_src === "./../res/profile_pic/default_profile.png"): ?>
                        <div class="image-preview-container1">
                            <img src="<?php echo $profile_pic_src; ?>" alt="Current Profile" id="imagePreview" class="profile-preview">
                    <?php else: ?>
                        <div class="image-preview-container2">
                            <a href="./../php/profile_pic_delete.php">
                                <img src="<?php echo $profile_pic_src; ?>" alt="Current Profile" id="imagePreview" class="profile-preview">
                            </a>
                    <?php endif; ?>
                        
                    </div>
                    <div class="file-drop-zone" id="fileDropZone">
                        <p class="drop-text">Drag & drop image here or</p>
                        <input type="file" name="profile_picture" id="imageFileInput" accept="image/*" style="display: none;">
                        <button type="button" class="upload-select-btn" id="selectImageBtn">Select File</button>
                        <p class="file-hint">JPG or PNG. Max size 5MB.</p>
                    </div>
                </div>
                <div class="btn-group">
                    <button type="submit" class="save-btn">Upload and Save Photo</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Always display containers -->
    <div class="container">
        
        <!-- Navigation Panel -->
        <div class="nav">
            <!-- Top Section: Profile Picture and Info -->
            <div class="nav-up">
                <div class="profile-pic" id="profilepic"> 
                    <img src="<?php echo $profile_pic_src;  ?>" alt="Profile Picture" class="img-pic">
                </div>
                <div class="profile-info">
                    <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
                    <p>Role: <?php echo ucfirst($user['role']); ?></p>
                </div>
            </div>

            <!-- Bottom Section: Menu Links -->
            <div class="nav-down">
                <ul>
                    <li><a href="#" data-view="profileDetailsView" id="profileDetailsLink" class="active-nav">Profile Details</a></li>
                    <li><a href="#" data-view="purchasesTicketsView" id="purchasesTicketsLink">Purchases tickets</a></li>
                    <li><a href="#" data-view="paymentMethodView" id="paymentMethodLink">Payment Method</a></li>
                    <li><a href="#" data-view="notificationSettingView" id="notificationSettingLink">Notification Setting</a></li>
                    <li><a href="#" data-view="settingsView" id="settingsLink">Settings</a></li>
                    <li class="logout-btn"><a href="./../php/logout.php">Logout</a></li>
                </ul>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="content">
            
            <!-- View 1: Profile Details (Active by default) -->
            <div id="profileDetailsView" class="content-view active">
                <h1>Profile Details</h1>
                <p>Welcome to your primary profile dashboard. Here you can review and manage your personal account information.</p>
                
                <div class="data-card">
                    <div class="card-header">
                        <h2>Personal Information</h2>
                        <button id="profiledetails">Edit Personal Information</button>
                    </div>
                    <table>
                        <tr><th>Name</th><td><?php echo htmlspecialchars($user['full_name']); ?></td></tr>
                        <tr><th>Email</th><td><?php echo htmlspecialchars($user['email']); ?></td></tr>
                        <tr><th>Mobile No</th><td><?php echo htmlspecialchars($user['phone']); ?></td></tr>
                        <tr><th>Account Role</th><td><?php echo ucfirst($user['role']); ?></td></tr>
                    </table>
                </div>
            </div>
            
            <!-- View 2: Purchases Tickets -->
            <div id="purchasesTicketsView" class="content-view">
                <h1>Purchased Tickets</h1>
                <p>Below is a list of all the tickets you have purchased for upcoming events.</p>
                
                <div class="data-card ticket-card">
                    <h2>Ticket to Global Tech Summit 2024</h2>
                    <p><strong>Date:</strong> November 15, 2024</p>
                    <p><strong>Location:</strong> Convention Center, City X</p>
                    <p><strong>Status:</strong> Confirmed</p>
                    <button class="view-btn">View E-Ticket</button>
                </div>
            </div>
            
            <!-- View 3: Payment Method (Placeholder) -->
            <div id="paymentMethodView" class="content-view">
                <h1>Payment Method</h1>
                <p>Add and manage your payment methods here.</p>
                <div class="data-card">
                    <h2>Current Methods</h2>
                    <p>No payment methods saved.</p>
                </div>
            </div>
            
            <!-- Placeholder Views for consistency (Notification, Settings) -->
            
            <div id="notificationSettingView" class="content-view">
                <h1>Notification Settings</h1>
                <p>Control how and when you receive system alerts.</p>
                <div class="data-card">
                    <table>
                        <tr>
                            <th>Email Alerts: </th>
                            <td>
                                <div class="not-b<?php echo $email_alerts ? ' on' : ''; ?>" 
                                    id="emailToggle" 
                                    data-toggle-type="email_alerts">
                                    <div class="not-o"></div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>SMS Alerts: </th>
                            <td>
                                <div class="not-b<?php echo $sms_alerts ? ' on' : ''; ?>" 
                                    id="smsToggle" 
                                    data-toggle-type="sms_alerts">
                                    <div class="not-o"></div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- View 5: Settings -->
            <div id="settingsView" class="content-view">
                <h1>Application Settings</h1>
                <p>General application settings are managed here.</p>
                <div class="data-card">
                    <table>
                        <tr><th>Dark Mode: </th><td><div class="not-b" id="themeToggle"><div class="not-o"></div></div></td></tr>
                        <tr><th>Language: </th><td>English (US)</td></tr>
                    </table>
                </div>
            </div>  
        </div>
    </div>

    <!-- Link to the external JavaScript file -->
    <script src="./../js/profile.js"></script> 

</body>
</html>