<?php
session_start();
include 'approve_event.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Approval Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="approve_event.css">


</head>
<body style="background-color: rgba(208, 225, 244, 1);">
    
<div class="container">
   
 <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?= $_SESSION['message_type'] ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php
        // Clear the session messages after displaying them
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        ?>
    <?php endif; ?>

    <h1 class="mb-4 text-primary fw-bold"><strong>Event Approval Dashboard</strong></h1>

    <!-- Search Form -->
    <form method="GET" class="search-form row g-3 mb-5 p-3 bg-white shadow-sm rounded-3">
        <div class="col-md-4">
            <label class="form-label fw-semibold">Select Category</label>
            <select name="search_category" class="form-select">
                <option value="">All Categories</option>
                <?php
                $categories_res = $conn->query("SELECT * FROM categories");
                while ($category = $categories_res->fetch_assoc()):
                ?>
                    <option value="<?= $category['category_id'] ?>" <?= isset($_GET['search_category']) && $_GET['search_category'] == $category['category_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['category_name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Event Date</label>
            <input type="date" name="search_date" class="form-control" value="<?= $_GET['search_date'] ?? '' ?>">
        </div>
        <div class="col-md-4 d-flex align-items-end">
    <button type="submit" class="btn btn-primary w-75 mt-2 py-2 rounded-3 shadow-sm hover-shadow transition-all">Search</button>
</div>

    </form>

    <!-- Events Table -->
    <table class="table table-hover table-bordered bg-white shadow-sm rounded-3">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Event Name</th>
                <th>Organizer</th>
                <th>Category</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
                <th>Action</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['event_id'] ?></td>
                    <td><?= htmlspecialchars($row['event_name']) ?></td>
                    <td><?= htmlspecialchars($row['organizer']) ?></td>
                    <td><?= htmlspecialchars($row['category_name']) ?></td>
                    <td><?= $row['event_date'] ?></td>
                    <td><?= $row['event_time'] ?></td>
                    <td>
                        <span class="status <?= $row['status'] ?>">
                            <?= ucfirst($row['status']) ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($row['status'] === 'pending'): ?>
                            <a href="?action=approve&id=<?= $row['event_id'] ?>" class="btn btn-approve btn-sm" onclick="return confirm('Approve this event?')">Approve</a>
                            <a href="?action=reject&id=<?= $row['event_id'] ?>" class="btn btn-reject btn-sm" onclick="return confirm('Reject this event?')">Reject</a>
                        <?php else: ?>
                            <span>—</span>
                        <?php endif; ?>
                    </td>
                    <td><a href="event_delete.php?id=<?= $row['event_id'] ?>" class="btn btn-delete btn-sm w-100" onclick="return confirm('Are you sure you want to delete this event?');">Delete</a></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="8" class="text-center">No events found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>

</html>
