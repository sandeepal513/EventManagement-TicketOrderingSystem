<?php
include 'ticket_add.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Event Tickets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: rgba(208, 225, 244, 1); padding: 20px; }
        .container { max-width: 1200px; } 
        .form-container-width { max-width: 900px; margin-left: auto; margin-right: auto; } 
        .card { border: 2px solid <?php echo $edit_mode ? '#ffc107' : '#17a2b8'; ?>; }
        .btn-submit { 
            background-color: <?php echo $edit_mode ? '#dfbb4eff' : '#17a2b8'; ?>; 
            border-color: <?php echo $edit_mode ? '#ffc107' : '#17a2b8'; ?>; 
            color: <?php echo $edit_mode ? '#333' : 'white'; ?>;
        }
        .table thead th { background-color: #17a2b8; color: white; }
        .status-Pending { color: orange; font-weight: bold; }
        .status-Approved { color: green; font-weight: bold; }
        .status-Rejected { color: red; font-weight: bold; }
        .is-invalid { border-color: #dc3545 !important; }
    </style>
</head>
<body>

<div class="container py-5">
    
    <?php if (!empty($messages)): ?>
        <?php foreach ($messages as $msg): ?>
            <div class="alert alert-<?php echo $msg['type']; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($msg['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="card shadow-lg rounded-4 mb-5 bg-light form-container-width">
        <div class="card-body p-5"> 
            <h2 class="text-center mb-4 fw-bold" style="color: <?php echo $edit_mode ? '#a27d00ff' : '#17a2b8'; ?>;"><?= $form_title ?></h2>
            
            <form method="POST" action="<?= $_SERVER['PHP_SELF'] ?>" class="row g-3">
                
                <?php if ($edit_mode): ?>
                    <input type="hidden" name="ticket_type_id" value="<?= htmlspecialchars($ticket_id) ?>">
                <?php endif; ?>

                <div class="col-md-6">
                    <label class="form-label">Event <span class="text-danger">:</span></label>
                    <select name="event_id" class="form-select <?php echo isset($errors['event_id']) ? 'is-invalid' : ''; ?>" >
                        <option value="" disabled selected>Select Event</option>
                        <?php 
                        if ($eventResult->num_rows > 0) {
                            $eventResult->data_seek(0);
                            while ($event = $eventResult->fetch_assoc()) {
                                $selected = ($formData['event_id'] == $event['event_id']) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($event['event_id']) . "' $selected>" . htmlspecialchars($event['event_name']) . "</option>";
                            }
                        }
                        ?>
                    </select>
                    <div class="invalid-feedback"><?php echo $errors['event_id'] ?? ''; ?></div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Ticket Type <span class="text-danger">:</span></label>
                    <select name="ticket_type" class="form-select <?php echo isset($errors['ticket_type']) ? 'is-invalid' : ''; ?>" >
                        <option value="" disabled selected>Select Type</option>
                        <option value="GA" <?php echo $formData['ticket_type'] == 'GA' ? 'selected' : ''; ?>>General Admission (GA)</option>
                        <option value="VIP" <?php echo $formData['ticket_type'] == 'VIP' ? 'selected' : ''; ?>>VIP</option>
                    </select>
                    <div class="invalid-feedback"><?php echo $errors['ticket_type'] ?? ''; ?></div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Price (LKR) <span class="text-danger">:</span></label>
                    <input type="number" name="price" class="form-control <?php echo isset($errors['price']) ? 'is-invalid' : ''; ?>" step="0.01" value="<?= htmlspecialchars($formData['price']) ?>" >
                    <div class="invalid-feedback"><?php echo $errors['price'] ?? ''; ?></div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Total Quantity <span class="text-danger">:</span></label>
                    <input type="number" name="total_quantity" class="form-control <?php echo isset($errors['total_quantity']) ? 'is-invalid' : ''; ?>" value="<?= htmlspecialchars($formData['total_quantity']) ?>" >
                    <div class="invalid-feedback"><?php echo $errors['total_quantity'] ?? ''; ?></div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Available Quantity <span class="text-danger">:</span></label>
                    <input type="number" name="available_quantity" class="form-control <?php echo isset($errors['available_quantity']) ? 'is-invalid' : ''; ?>" value="<?= htmlspecialchars($formData['available_quantity']) ?>" >
                    <div class="invalid-feedback"><?php echo $errors['available_quantity'] ?? ''; ?></div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Sale Start Date <span class="text-danger">:</span></label>
                    <input type="datetime-local" name="sale_start" class="form-control <?php echo isset($errors['sale_start']) ? 'is-invalid' : ''; ?>" value="<?= htmlspecialchars($formData['sale_start']) ?>" >
                    <div class="invalid-feedback"><?php echo $errors['sale_start'] ?? ''; ?></div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Sale End Date <span class="text-danger">:</span></label>
                    <input type="datetime-local" name="sale_end" class="form-control <?php echo isset($errors['sale_end']) ? 'is-invalid' : ''; ?>" value="<?= htmlspecialchars($formData['sale_end']) ?>" >
                    <div class="invalid-feedback"><?php echo $errors['sale_end'] ?? ''; ?></div>
                </div>

                <div class="col-12 text-center">
                    <button type="submit" name="<?= $edit_mode ? 'update_ticket' : 'add_ticket' ?>" class="btn btn-submit btn-lg mt-3 w-50"><?= $submit_button_text ?></button>
                    
                    <a href="ticket.php" class="btn btn-outline-secondary btn-lg w-20 mt-3">
                        Cancel
                    </a>
                    
                </div>
            </form>
        </div>
    </div>

    <h3 class="mb-3 text-primary fw-bold"> Existing Ticket Types</h3>
    <div class="table-responsive">
        <table class="table table-hover table-striped table-bordered align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Event</th>
                    <th>Type</th>
                    <th>Price</th>
                    <th>Total/Avail</th>
                    <th>Sale Start</th>
                    <th>Sale End</th>
                    <th>Edit</th>
                    <th>Delete</th>
                    
                </tr>
            </thead>
            <tbody>
                <?php
                if ($ticketsResult->num_rows > 0) {
                    while ($ticket = $ticketsResult->fetch_assoc()) {
                        
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($ticket['ticket_type_id']) . "</td>";
                        echo "<td>" . htmlspecialchars($ticket['event_name']) . "</td>";
                        echo "<td><strong>" . htmlspecialchars($ticket['ticket_type']) . "</strong></td>";
                        echo "<td>LKR " . number_format($ticket['price'], 2) . "</td>";
                        echo "<td>" . htmlspecialchars($ticket['total_quantity']) . "/" . htmlspecialchars($ticket['available_quantity']) . "</td>";
                        echo "<td>" . date('M j, Y H:i', strtotime($ticket['sale_start'])) . "</td>";
                        echo "<td>" . date('M j, Y H:i', strtotime($ticket['sale_end'])) . "</td>";
                       echo "<td><a href='" . $_SERVER['PHP_SELF'] . "?id=" . htmlspecialchars($ticket['ticket_type_id']) . "' class='btn btn-warning btn-sm'><i class='fas fa-edit'></i> Edit</a></td>";
echo "<td>
        <form method='POST' onsubmit='return confirm(\"Delete ticket type ID " . htmlspecialchars($ticket['ticket_type_id']) . "? This cannot be undone.\");'>
            <input type='hidden' name='ticket_type_id' value='" . htmlspecialchars($ticket['ticket_type_id']) . "'>
            <button type='submit' name='delete_ticket' class='btn btn-danger btn-sm'><i class='fas fa-trash'></i> Delete</button>
        </form>
      </td>";
echo "</tr>";

                        
                    }
                } else {
                    echo "<tr><td colspan='9' class='text-center'>No ticket types have been added yet.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>