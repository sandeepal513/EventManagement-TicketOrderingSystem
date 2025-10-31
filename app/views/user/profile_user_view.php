<?php

    // Include constants for consistent path handling
    include_once __DIR__ . '/../../../config/constants.php';

    if (!isset($user) || !isset($profile_pic_src)) {
        // Redirect to controller if accessed directly
        header("Location: " . BASE_URL . "app/controllers/profile_controller.php");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Link to the external CSS file -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/profile.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/logout-modal.css">
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

            <form action="<?php echo BASE_URL; ?>app/controllers/profile_details_edit.php" method="post">
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

                <div class="form-group password-group">
                    <label for="current_password">Current Password:</label>
                    <div class="password-wrapper">
                        <input type="password" name="current_pwd" id="current_password" placeholder="Leave blank to keep current password">
                        <button type="button" class="toggle-password" data-target="current_password">&#128065;</button>
                    </div>
                </div>

                <div class="form-group password-group">
                    <label for="new_password">New Password:</label>
                    <div class="password-wrapper">
                        <input type="password" name="new_pwd" id="new_password" placeholder="Leave blank to keep current password">
                        <button type="button" class="toggle-password" data-target="new_password">&#128065;</button>
                    </div>
                </div>

                <div class="form-group password-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <div class="password-wrapper">
                        <input type="password" name="confirm_pwd" id="confirm_password" placeholder="Leave blank to keep current password">
                        <button type="button" class="toggle-password" data-target="confirm_password">&#128065;</button>
                    </div>
                </div>

                <div class="btn-group">
                    <input type="submit" value="Save Changes" class="save-btn">
                </div>

            </form>
        </div>
    </div>

    <!-- 2. Profile Image Upload Modal -->
    <div class="profileimage" id="editProfileimageModal">
        <div class="edit-profile-image-content">
            <div class="modal-header">
                <h2>Change Profile Photo</h2>
                <button class="close-btn" id="closeImageModalBtn">&times;</button>
            </div>

            <form action="<?php echo BASE_URL; ?>app/controllers/profile_pic_update.php" method="post" id="imageUploadForm" enctype="multipart/form-data">
                <div class="image-upload-area">
                    <?php
                    // Check if using default profile picture
                    $is_default = empty($user['profile_pic']) ||
                                  strpos($profile_pic_src, 'default_profile.png') !== false;
                    ?>

                    <?php if ($is_default): ?>
                        <div class="image-preview-container1">
                            <img src="<?php echo $profile_pic_src; ?>" alt="Current Profile" id="imagePreview" class="profile-preview">
                        </div>
                    <?php else: ?>
                        <div class="image-preview-container2">
                            <a href="javascript:void(0)" onclick="deleteProfileImage(event)" title="Click to delete profile picture">
                                <img src="<?php echo $profile_pic_src; ?>" alt="Current Profile" id="imagePreview" class="profile-preview">
                            </a>
                        </div>
                    <?php endif; ?>

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

    <!-- Main Container -->
    <div class="container">

        <!-- Navigation Panel -->
        <div class="nav">
            <!-- Top Section: Profile Picture and Info -->
            <div class="nav-up">
                <div class="profile-pic" id="profilepic">
                    <img src="<?php echo $profile_pic_src; ?>" alt="Profile Picture" class="img-pic">
                </div>
                <div class="profile-info">
                    <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
                    <p>Role: <?php echo ucfirst($user['role']); ?></p>
                </div>
            </div>

            <!-- Bottom Section: Menu Links (User specific) -->
            <div class="nav-down">
                <ul>
                    <li><a href="#" data-view="profileDetailsView" id="profileDetailsLink" class="active-nav">Profile Details</a></li>
                    <!-- User specific links -->
                    <li><a href="#" data-view="myTicketsView" id="myTicketsLink">My Tickets</a></li>
                    <li><a href="#" data-view="notificationSettingView" id="notificationSettingLink">Notification Setting</a></li>
                    <li><a href="#" data-view="settingsView" id="settingsLink">Settings</a></li>
                    <li class="logout-btn"><a href="#" onclick="logoutUser(event)" title="Click to logout">Logout</a></li>
                </ul>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="content">

            <!-- View 1: Profile Details (Active by default) -->
            <div id="profileDetailsView" class="content-view active">
                <h1>Profile Details</h1>
                <p>Welcome to your user profile dashboard. Here you can review and manage your personal account information.</p>

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

            <!-- View 2: My Tickets (User specific) -->
            <div id="myTicketsView" class="content-view">
                <h1>My Tickets</h1>
                <p>View and manage your event tickets and bookings.</p>

                <div class="data-card">
                    <h2>Ticket Overview</h2>
                    <table>
                        <tr><th>Total Tickets</th><td>5</td></tr>
                        <tr><th>Upcoming Events</th><td>3</td></tr>
                        <tr><th>Attended Events</th><td>2</td></tr>
                        <tr><th>Total Spent</th><td>$150.00</td></tr>
                    </table>
                </div>
            </div>

            <!-- View 3: Notification Settings -->
            <div id="notificationSettingView" class="content-view">
                <h1>Notification Settings</h1>
                <p>Control how and when you receive system alerts.</p>
                <div class="data-card">
                    <table>
                        <tr>
                            <th>Email Alerts:</th>
                            <td>
                                <div class="not-b<?php echo $email_alerts ? ' on' : ''; ?>"
                                    id="emailToggle"
                                    data-toggle-type="email_alerts">
                                    <div class="not-o"></div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>SMS Alerts:</th>
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

            <!-- View 4: Settings -->
            <div id="settingsView" class="content-view">
                <h1>Application Settings</h1>
                <p>General application settings are managed here.</p>
                <div class="data-card">
                    <table>
                        <tr><th>Dark Mode:</th><td><div class="not-b" id="themeToggle"><div class="not-o"></div></div></td></tr>
                        <tr><th>Language:</th><td>English (US)</td></tr>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- Link to the external JavaScript files -->
    <script>
        // Make BASE_URL available to JavaScript
        const BASE_URL = '<?php echo BASE_URL; ?>';
    </script>
    <script src="<?php echo BASE_URL; ?>public/assets/js/profile.js"></script>
    <script src="<?php echo BASE_URL; ?>public/assets/js/logout-modal.js"></script>

    <script>
        // Initialize logout functionality
        document.addEventListener('DOMContentLoaded', () => {
            const logoutLink = document.querySelector('.logout-btn a');
            if (logoutLink) {
                logoutLink.addEventListener('click', logoutUser);
            }
        });
    </script>

</body>
</html>