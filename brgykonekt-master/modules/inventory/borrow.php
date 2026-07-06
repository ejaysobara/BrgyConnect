<?php
include "../../includes/auth_check.php";
include "../../config/database.php";

requireRoles([8]);

$user_id = (int)$_SESSION["user_id"];
$message = "";
$error = "";

$resident_result = mysqli_query($conn, "SELECT * FROM residents WHERE user_id = '$user_id'");
$resident = $resident_result ? mysqli_fetch_assoc($resident_result) : null;
$resident_id = $resident ? (int)$resident["id"] : 0;

if (isset($_POST["borrow_item"])) {
    if (!$resident || $resident["status"] !== "Verified") {
        $error = "Your resident profile must be verified before borrowing equipment.";
    } else {
        $inventory_id = (int)$_POST["inventory_id"];
        $quantity = max(1, (int)$_POST["quantity"]);
        $date_borrowed = mysqli_real_escape_string($conn, $_POST["date_borrowed"]);
        $return_date = mysqli_real_escape_string($conn, $_POST["return_date"]);

        $check_item = mysqli_query($conn, "SELECT * FROM inventory WHERE id = '$inventory_id'");
        $item = $check_item ? mysqli_fetch_assoc($check_item) : null;

        if ($item && $quantity <= (int)$item["available_quantity"]) {
            $sql = "INSERT INTO borrowed_items
                    (inventory_id, resident_id, quantity, date_borrowed, return_date, status)
                    VALUES
                    ('$inventory_id', '$resident_id', '$quantity', '$date_borrowed', '$return_date', 'Pending')";

            if (mysqli_query($conn, $sql)) {
                $message = "Borrow request submitted successfully.";
            } else {
                $error = "Unable to submit request: " . mysqli_error($conn);
            }
        } else {
            $error = "Requested quantity is not available.";
        }
    }
}

$items = mysqli_query($conn, "SELECT * FROM inventory WHERE available_quantity > 0 AND condition_status = 'Functional' ORDER BY asset_name ASC");
$my_requests = false;

if ($resident_id > 0) {
    $my_requests = mysqli_query($conn, "SELECT borrowed_items.*, inventory.asset_name
                                        FROM borrowed_items
                                        INNER JOIN inventory ON borrowed_items.inventory_id = inventory.id
                                        WHERE borrowed_items.resident_id = '$resident_id'
                                        ORDER BY borrowed_items.created_at DESC");
}

include "../../includes/header.php";
renderHeader("Borrow Equipment", "Request available barangay equipment and track approval or return status.", "inventory");
?>

<section class="panel">
    <?php if ($message !== "") { ?>
        <p class="message"><?php echo e($message); ?></p>
    <?php } ?>
    <?php if ($error !== "") { ?>
        <p class="error"><?php echo e($error); ?></p>
    <?php } ?>

    <?php if (!$resident || $resident["status"] !== "Verified") { ?>
        <p class="error">Complete and verify your resident profile before borrowing equipment.</p>
    <?php } else { ?>
        <form method="POST">
            <label for="inventory_id">Equipment</label>
            <select id="inventory_id" name="inventory_id" required>
                <option value="">Select equipment</option>
                <?php if ($items) { ?>
                    <?php while ($item = mysqli_fetch_assoc($items)) { ?>
                        <option value="<?php echo e($item["id"]); ?>">
                            <?php echo e($item["asset_name"]); ?> - Available: <?php echo e($item["available_quantity"]); ?>
                        </option>
                    <?php } ?>
                <?php } ?>
            </select>

            <div class="grid-2">
                <div>
                    <label for="quantity">Quantity</label>
                    <input id="quantity" type="number" name="quantity" min="1" required>
                </div>
                <div>
                    <label for="date_borrowed">Date Borrowed</label>
                    <input id="date_borrowed" type="date" name="date_borrowed" required>
                </div>
                <div>
                    <label for="return_date">Return Date</label>
                    <input id="return_date" type="date" name="return_date" required>
                </div>
            </div>

            <button type="submit" name="borrow_item">Submit Request</button>
        </form>
    <?php } ?>
</section>

<section class="panel">
    <h3>My Borrow Requests</h3>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Equipment</th>
                    <th>Quantity</th>
                    <th>Date Borrowed</th>
                    <th>Return Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($my_requests && mysqli_num_rows($my_requests) > 0) { ?>
                    <?php while ($row = mysqli_fetch_assoc($my_requests)) { ?>
                        <tr>
                            <td><?php echo e($row["asset_name"]); ?></td>
                            <td><?php echo e($row["quantity"]); ?></td>
                            <td><?php echo e($row["date_borrowed"]); ?></td>
                            <td><?php echo e($row["return_date"]); ?></td>
                            <td><span class="status-pill status-<?php echo e(strtolower(str_replace(" ", "-", $row["status"]))); ?>"><?php echo e($row["status"]); ?></span></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr><td colspan="5">No borrow requests yet.</td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</section>

<?php include "../../includes/footer.php"; ?>
