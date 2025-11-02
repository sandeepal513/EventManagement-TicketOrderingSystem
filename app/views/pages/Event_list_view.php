<?php
    include_once __DIR__ . '/../../../config/constants.php';
    session_start();
    include_once ROOT . '/config/connection.php';

    $categories_result = $conn->query("SELECT category_name FROM categories ORDER BY category_name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/event_list.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/navbar.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/footer.css">
    <title>Event Planning Management</title>
</head>
<body>

    <!-- Include Navbar -->
    <?php include_once ROOT . '/app/views/layouts/navbar.php'; ?>

    <div class="container">
        <div class="sidebar">
            <div class="sort-section">
                <h2>Sort by</h2>
                <div class="sort-buttons">
                    <button id="date">Date</button>
                    <button id="pop">Popularity</button>
                    <button id="a-z">A-Z</button>
                </div>
            </div>
            <div class="filter-section">
                <h2>Filter</h2>
                <label>
                    Date Range (From):
                    <input type="date" id="date-start-input">
                </label>
                <label>
                    Date Range (To):
                    <input type="date" id="date-end-input">
                </label>

                <label>
                    Location:
                    <input type="text" id="location-input" placeholder="Any Location">
                </label>
                <label>
                    Category:
                    <select name="category" id="category-filter">
                        <option value="" selected>All Categories</option>
                        <?php
                        if ($categories_result && $categories_result->num_rows > 0) {
                            while ($category_row = $categories_result->fetch_assoc()) {
                                $cat_name = htmlspecialchars($category_row['category_name']);
                                echo "<option value=\"$cat_name\">$cat_name</option>";
                            }
                        }
                        ?>
                    </select>
                </label>
                <button id="search-button">Search</button>
            </div>
        </div>
        <div class="main">
            <h1>Explore Upcoming Events</h1>
            <div class="search-bar-container">
                <input type="text" placeholder="Search events" id="search-input">
                <button id="main-search">Search</button>
            </div>
            <div class="events" id="event-container">
                <!-- Events will be loaded here by JavaScript -->
            </div>
        </div>
    </div>

    <!-- Include footer -->
    <?php include_once ROOT . '/app/views/layouts/footer.php'; ?>
	

	<script src="<?php echo BASE_URL; ?>public/assets/js/event_list.js"></script>
</body>
</html>
