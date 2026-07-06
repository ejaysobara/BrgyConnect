<?php
include "../../includes/auth_check.php";
include "../../config/database.php";

requireRoles([1, 2, 3]);

$message = "";
$error = "";

if (isset($_POST["add_item"])) {
    $asset_name = mysqli_real_escape_string($conn, trim($_POST["asset_name"]));
    $category = mysqli_real_escape_string($conn, trim($_POST["category"]));
    $quantity = max(0, (int)$_POST["quantity"]);
    $condition_status = mysqli_real_escape_string($conn, $_POST["condition_status"]);

    $sql = "INSERT INTO inventory
            (asset_name, category, total_quantity, available_quantity, condition_status)
            VALUES
            ('$asset_name', '$category', '$quantity', '$quantity', '$condition_status')";

    if (mysqli_query($conn, $sql)) {
        addAuditLog($conn, $_SESSION["user_id"], "add_inventory", $asset_name);
        $message = "Inventory item added successfully.";
    } else {
        $error = "Unable to add inventory item: " . mysqli_error($conn);
    }
}

if (isset($_GET["approve"])) {
    $borrow_id = (int)$_GET["approve"];
    $borrow_result = mysqli_query($conn, "SELECT borrowed_items.*, inventory.available_quantity
                                          FROM borrowed_items
                                          INNER JOIN inventory ON borrowed_items.inventory_id = inventory.id
                                          WHERE borrowed_items.id = '$borrow_id'");
    $borrow = $borrow_result ? mysqli_fetch_assoc($borrow_result) : null;

    if ($borrow && (int)$borrow["quantity"] <= (int)$borrow["available_quantity"]) {
        $inventory_id = (int)$borrow["inventory_id"];
        $quantity = (int)$borrow["quantity"];
        mysqli_query($conn, "UPDATE borrowed_items SET status = 'Approved' WHERE id = '$borrow_id'");
        mysqli_query($conn, "UPDATE inventory SET available_quantity = available_quantity - $quantity WHERE id = '$inventory_id'");
        addAuditLog($conn, $_SESSION["user_id"], "approve_borrow", "borrow#$borrow_id");
    }

    header("Location: manage.php");
    exit();
}

if (isset($_GET["return"])) {
    $borrow_id = (int)$_GET["return"];
    $borrow_result = mysqli_query($conn, "SELECT * FROM borrowed_items WHERE id = '$borrow_id'");
    $borrow = $borrow_result ? mysqli_fetch_assoc($borrow_result) : null;

    if ($borrow && $borrow["status"] !== "Returned") {
        $inventory_id = (int)$borrow["inventory_id"];
        $quantity = (int)$borrow["quantity"];
        mysqli_query($conn, "UPDATE borrowed_items SET status = 'Returned' WHERE id = '$borrow_id'");
        mysqli_query($conn, "UPDATE inventory SET available_quantity = available_quantity + $quantity WHERE id = '$inventory_id'");
        addAuditLog($conn, $_SESSION["user_id"], "return_borrow", "borrow#$borrow_id");
    }

    header("Location: manage.php");
    exit();
}

$borrow_requests = mysqli_query($conn, "SELECT borrowed_items.*, inventory.asset_name,
                                              residents.first_name, residents.middle_name, residents.last_name
                                       FROM borrowed_items
                                       INNER JOIN inventory ON borrowed_items.inventory_id = inventory.id
                                       INNER JOIN residents ON borrowed_items.resident_id = residents.id
                                       ORDER BY borrowed_items.created_at DESC");
$items = mysqli_query($conn, "SELECT * FROM inventory ORDER BY created_at DESC");

include "../../includes/header.php";
renderHeader("Inventory Management", "Manage barangay equipment, availability, requests, approvals, and returned assets.", "inventory");
?>

<section class="panel">
    <?php if ($message !== "") { ?>
        <p class="message"><?php echo e($message); ?></p>
    <?php } ?>
    <?php if ($error !== "") { ?>
        <p class="error"><?php echo e($error); ?></p>
    <?php } ?>

    <form method="POST">
        <div class="grid-2">
            <div>
                <label for="asset_name">Asset Name</label>
                <input id="asset_name" type="text" name="asset_name" required>
            </div>
            <div>
                <label for="category">Category</label>
                <input id="category" type="text" name="category" placeholder="Equipment, furniture, medical">
            </div>
            <div>
                <label for="quantity">Quantity</label>
                <input id="quantity" type="number" name="quantity" min="0" required>
            </div>
            <div>
                <label for="condition_status">Condition</label>
                <select id="condition_status" name="condition_status">
                    <option value="Functional">Functional</option>
                    <option value="Damaged">Damaged</option>
                    <option value="Missing">Missing</option>
                </select>
            </div>
        </div>
        <button type="submit" name="add_item">Add Item</button>
    </form>
</section>

<section class="panel">
    <h3>Borrow Requests</h3>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Resident</th>
                    <th>Equipment</th>
                    <th>Quantity</th>
                    <th>Date Borrowed</th>
                    <th>Return Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($borrow_requests && mysqli_num_rows($borrow_requests) > 0) { ?>
                    <?php while ($row = mysqli_fetch_assoc($borrow_requests)) { ?>
                        <tr>
                            <td><?php echo e(trim($row["first_name"] . " " . $row["middle_name"] . " " . $row["last_name"])); ?></td>
                            <td><?php echo e($row["asset_name"]); ?></td>
                            <td><?php echo e($row["quantity"]); ?></td>
                            <td><?php echo e($row["date_borrowed"]); ?></td>
                            <td><?php echo e($row["return_date"]); ?></td>
                            <td><span class="status-pill status-<?php echo e(strtolower(str_replace(" ", "-", $row["status"]))); ?>"><?php echo e($row["status"]); ?></span></td>
                            <td>
                                <?php if ($row["status"] === "Pending") { ?>
                                    <a class="button" href="manage.php?approve=<?php echo e($row["id"]); ?>">Approve</a>
                                <?php } elseif ($row["status"] === "Approved" || $row["status"] === "Borrowed") { ?>
                                    <a class="button secondary" href="manage.php?return=<?php echo e($row["id"]); ?>">Mark Returned</a>
                                <?php } else { ?>
                                    Done
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr><td colspan="7">No borrow requests found.</td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</section>

<section class="panel">
    <h3>Inventory List</h3>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Asset</th>
                    <th>Category</th>
                    <th>Total</th>
                    <th>Available</th>
                    <th>Condition</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($items && mysqli_num_rows($items) > 0) { ?>
                    <?php while ($row = mysqli_fetch_assoc($items)) { ?>
                        <tr>
                            <td><?php echo e($row["asset_name"]); ?></td>
                            <td><?php echo e($row["category"]); ?></td>
                            <td><?php echo e($row["total_quantity"]); ?></td>
                            <td><?php echo e($row["available_quantity"]); ?></td>
                            <td><span class="badge"><?php echo e($row["condition_status"]); ?></span></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr><td colspan="5">No inventory items found.</td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</section>

<?php include "../../includes/footer.php"; ?>
