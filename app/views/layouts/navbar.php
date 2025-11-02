<!-- <?php session_start(); ?>    -->
<!-- Navigation Bar -->
    <nav class="navbar">
        <div class="logo">
            <a href="<?php echo BASE_URL; ?>index.php"><h1>EventSphere</h1></a>
        </div>
        <ul class="nav-links">
            <li><a href="<?php echo BASE_URL; ?>app/views/pages/event_list_view.php">Events</a></li>
            <li class="dropdown">
                <a href="#">Categories ▼</a>
                <div class="dropdown-content">
                    <a href="#">Music</a>
                    <a href="#">Sports</a>
                    <a href="#">Tech</a>
                </div>
            </li>
            <li><a href="">Contact Us</a></li>
            <li><a href="<?php echo BASE_URL; ?>app/views/pages/about_us.php">About Us</a></li>
        </ul>
        <div class="nav-right">
            <input type="text" class="search-bar" placeholder="Search events...">

            <?php if (isset($_SESSION['user_id']) && !preg_match('/profile/', $_SERVER['REQUEST_URI'])): ?>
                <?php
                    include_once ROOT . '/config/connection.php';
                    include_once ROOT . '/app/models/user_model.php';
                    $user = getUserById($conn, $_SESSION['user_id']);
                    $profile_pic_src = !empty($user['profile_pic']) ? BASE_URL . 'public/upload/profile_pic/' . htmlspecialchars($user['profile_pic']) : BASE_URL . 'public/assets/images/sys_img/default_profile.png';
                ?>
                <a href="<?php echo BASE_URL; ?>app/controllers/profile_controller.php" class="nav-btn">
                    <img src="<?php echo $profile_pic_src; ?>" alt="Profile" style="width: 30px; height: 30px; border-radius: 50%; margin-right: 5px;">
                </a>
            <?php elseif (!isset($_SESSION['user_id'])): ?>
                <a href="<?php echo BASE_URL; ?>app/views/auth/login_view.php" class="nav-btn">Login / Sign Up</a>
            <?php endif; ?>
        </div>
    </nav>