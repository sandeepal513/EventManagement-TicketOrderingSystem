<?php
    include_once __DIR__ . '/../../../config/constants.php';
    session_start();
    // The event_add.php controller runs *first* to process any form submissions
    include ROOT . '/app/controllers/event_add.php'; 

    $errors = $_SESSION['errors'] ?? [];
    $form_data = $_SESSION['form_data'] ?? [];
    unset($_SESSION['errors']);
    unset($_SESSION['form_data']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $edit_id ? 'Edit Event' : 'Add New Event' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo  BASE_URL; ?>public/assets/css/event.css">
</head>
<body style="background-color: rgba(208, 225, 244, 1);"> <div class="container py-5">

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?= $_SESSION['message_type'] ?> alert-dismissible fade show text-center" role="alert">
            <?= htmlspecialchars($_SESSION['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php
        // Clear the session messages after displaying them
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        ?>
    <?php endif; ?>
    

    <div class="card shadow-lg border-0 rounded-4 mb-5 mx-auto" style="max-width: 900px;">
        <div class="card-body p-md-5 p-4">
            <h2 class="text-center mb-5 text-dark fw-bold font-poppins">
                <?= $edit_id ? 'Update Event Details' : ' Create New Event' ?>
            </h2>

            <form method="POST" enctype="multipart/form-data" class="row g-4">
                <div class="col-md-6">
                    <label class="form-label text-muted fw-high">Category:</label>
                    <select name="category_id" class="form-select form-control <?= isset($errors['category_id']) ? 'is-invalid' : '' ?>" >
                            <option value="">Select Category</option>
                            <?php 
                            $categories_res = $conn->query("SELECT * FROM categories");
                            while ($category = $categories_res->fetch_assoc()):
                            ?>
                            <option value="<?= $category['category_id'] ?>" 
                                <?= isset($edit_row['category_id']) && $edit_row['category_id'] == $category['category_id'] ? 'selected' : '' ?>>
                                
                                <?= htmlspecialchars($category['category_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                                <?php if (isset($errors['category_id'])): ?>
                            <div class="invalid-feedback">
                                <?= htmlspecialchars($errors['category_id']) ?>
                            </div>
                        <?php endif; ?>
                  
                </div>
                                                

                <div class="col-12">
                    <label class="form-label text-muted fw-high">Event Name:</label>
                    <input type="text" name="event_name" class="form-control form-control <?= isset($errors['event_name']) ? 'is-invalid' : '' ?>" 
                    placeholder="Enter the event's name" value="<?= htmlspecialchars($edit_row['event_name'] ?? '') ?>" >
                    <?php if (isset($errors['event_name'])): ?>
                        <div class="invalid-feedback">
                            <?= htmlspecialchars($errors['event_name']) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-12">
                    <label class="form-label text-muted fw-high">Description:</label>
                    <textarea name="description" class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>" 
                    rows="4" placeholder="Briefly describe the event..."><?= htmlspecialchars($edit_row['description'] ?? '') ?></textarea>
                    <?php if (isset($errors['description'])): ?>
                        <div class="invalid-feedback">
                            <?= htmlspecialchars($errors['description']) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted fw-high">Venue:</label>
                    <input type="text" name="venue" class="form-control form-control <?= isset($errors['venue']) ? 'is-invalid' : '' ?>" 
                    placeholder="Location Name" value="<?= htmlspecialchars($edit_row['venue'] ?? '') ?>">
                    <?php if (isset($errors['venue'])): ?>
                        <div class="invalid-feedback">
                            <?= htmlspecialchars($errors['venue']) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-md-3">
                    <label class="form-label text-muted fw-high">Date:</label>
                    <input type="date" name="event_date" class="form-control <?= isset($errors['event_date']) ? 'is-invalid' : '' ?>" 
                    value="<?= $edit_row['event_date'] ?? '' ?>" >
                    <?php if (isset($errors['event_date'])): ?>
                        <div class="invalid-feedback">
                            <?= htmlspecialchars($errors['event_date']) ?>
                        </div>
                    <?php endif; ?>
                    
                </div>

                <div class="col-md-3">
                    <label class="form-label text-muted fw-high">Time:</label>
                    <?php
                    
                    $form_data = $_SESSION['form_data'] ?? [];
                    $posted_time = $form_data['event_time'] ?? ($edit_row['event_time'] ?? '');
                    $time_clean = preg_replace('/\s+/u', '', trim($posted_time));
                    $form_time = (strlen($time_clean) >= 5 && strpos($time_clean, ':') !== false) 
                                ? substr($time_clean, 0, 5)  // e.g., "14:30:00" → "14:30"
                                : '';
                    unset($_SESSION['form_data']['event_time']); 
                    if (empty($_SESSION['form_data'])) unset($_SESSION['form_data']);
                    ?>
                        <input type="time" 
                            name="event_time" 
                            class="form-control <?= isset($errors['event_time']) ? 'is-invalid' : '' ?>" 
                            value="<?= htmlspecialchars($form_time) ?>" 
                            pattern="([01]?[0-9]|2[0-3]):[0-5][0-9]" 
                            title="Enter time in HH:MM (24-hour) format."
                            step="60" 
                            min="00:00" 
                            max="23:59">
                            <?php if (isset($errors['event_time'])): ?>
                        <div class="invalid-feedback">
                            <?= htmlspecialchars($errors['event_time']) ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="col-12">
                    <label class="form-label text-muted fw-high">Google Map Link:</label>
                    <input type="text" name="map_link" class="form-control form-control <?= isset($errors['map_link']) ? 'is-invalid' : '' ?>" placeholder="Paste the full map URL here" value="<?= htmlspecialchars($edit_row['location_map_link'] ?? '') ?>">
                    <?php if (isset($errors['map_link'])): ?>
                        <div class="invalid-feedback">
                            <?= htmlspecialchars($errors['map_link']) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-12">
                    <label class="form-label text-muted fw-high">Event Image:</label>
                    <input type="file" name="image" class="form-control form-control <?= isset($errors['image']) ? 'is-invalid' : '' ?>">
                    <?php if (isset($errors['image'])): ?>
                        <div class="invalid-feedback">
                            <?= htmlspecialchars($errors['image']) ?>
                        </div>
                    <?php endif; ?>

                    <?php if(!empty($edit_row['image_url'])): ?>
                        <div class="mt-3">
                            <p class="mb-2 text-sm text-dark">Current Image:</p>
                            <img src="<?= BASE_URL . "public/upload/event_imgs/" . $edit_row['image_url'] ?>" class="img-thumbnail rounded-3" alt="Event Image Preview" style="max-width: 200px;">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-12 d-flex justify-content-center gap-3 pt-3">
                    <button type="submit" name="<?= $edit_id ? 'update_event' : 'add_event' ?>" class="btn btn-primary btn px-5 shadow-sm-hover">
                        <?= $edit_id ? 'Save Changes' : 'Publish Event' ?>
                    </button>
                    <a href="<?php echo BASE_URL; ?>app/views/pages/event_view.php" class="btn btn-outline-secondary btn px-5">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <div class="text-center pt-4 mb-4">
        <h3 class="text-dark fw-bold font-poppins">Your Event Dashboard</h3>
    </div>

    <div class="card shadow-sm border-0 rounded-4 mb-5 p-4">
        <h4 class="mb-4 text-dark-emphasis fw-semibold">Filter Events</h4>
        <form method="POST" class="row g-3">
            <div class="col-md-4">
                <label class="form-label text-muted">Category</label>
                <select name="search_category" class="form-select form-select">
                    <option value="">All Categories</option>
                    <?php 
                    $categories_res = $conn->query("SELECT * FROM categories");
                    while ($category = $categories_res->fetch_assoc()):
                    ?>
                        <option value="<?= $category['category_id'] ?>" <?= $search_category == $category['category_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['category_name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label text-muted">Date</label>
                <input type="date" name="search_date" class="form-control form-control" value="<?= $search_date ?? '' ?>">
            </div>

            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-secondary btn w-100">Apply Filters</button>
            </div>
        </form>
    </div>

<div class="row g-4">
    <?php if($events_res->num_rows > 0): ?>
        <?php while($row = $events_res->fetch_assoc()): ?>
            <div class="col-lg-4 col-md-6">
                
                <a href="<?= !empty($row['location_map_link']) ? htmlspecialchars($row['location_map_link']) : '#' ?>" 
                   target="_blank" 
                   class="text-decoration-none text-dark card-link-wrapper">
                    
                    <div class="card event-card shadow-md border-0 rounded-4 overflow-hidden h-100">
                        <div class="event-image-container">
                            <img src="<?= BASE_URL . "public/upload/event_imgs/" . $row['image_url'] ?>" class="card-img-top" alt="Event Image">
                            <span class="event-status badge bg-primary"><?= $row['status'] ?></span>
                        </div>
                        <div class="card-body p-4">
                            <h5 class="card-title fw-bold text-dark font-poppins"><?= htmlspecialchars($row['event_name']) ?></h5>
                            <div class="event-meta my-3 small text-muted">
                                <div><strong>Date:</strong> <?= $row['event_date'] ?> @ <?= $row['event_time'] ?></div>
                                <div><strong>Venue:</strong> <?= htmlspecialchars($row['venue']) ?></div>
                                <div><strong>Category:</strong> <?= $row['category_name'] ?></div>
                                </div>
                            <p class="card-text text-truncate-3 mb-4"><?= htmlspecialchars($row['description']) ?></p>
                            
                            <a href="<?php echo BASE_URL; ?>app/views/pages/event_view.php?edit=<?= $row['event_id'] ?>" 
                               class="btn btn-warning w-100 fw-semibold internal-link-fix">
                                Edit Event
                            </a>
                        </div>
                    </div>
                    
                </a>
                </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="alert alert-info text-center rounded-4 p-4">
                🎉 No events found! Try adjusting your filters or click Publish Event above to create one.
            </div>
        </div>
    <?php endif; ?>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
