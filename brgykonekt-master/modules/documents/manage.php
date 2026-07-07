<?php
include "../../includes/auth_check.php";
include "../../config/database.php";

requireStaffLevel(3); // Secretary and above: document workflow

$message = "";
$statuses = ["Pending", "Verified", "For Payment", "Paid", "Unpaid", "Free", "Ready for Pickup", "Released", "Cancelled", "Rejected"];

if (isset($_POST["update_status"])) {
    $request_id = (int)$_POST["request_id"];
    $status = mysqli_real_escape_string($conn, $_POST["status"]);

    if (in_array($status, $statuses)) {
        mysqli_query($conn, "UPDATE document_requests SET status = '$status' WHERE id = '$request_id'");
        addAuditLog($conn, $_SESSION["user_id"], "update_document_status", "request#$request_id", $status);
        $message = "Document request status updated.";
    }
}

$requests = mysqli_query($conn, "SELECT document_requests.*, document_types.document_name, document_types.fee,
                                        residents.first_name, residents.middle_name, residents.last_name,
                                        payments.receipt_number
                                 FROM document_requests
                                 INNER JOIN document_types ON document_requests.document_type_id = document_types.id
                                 INNER JOIN residents ON document_requests.resident_id = residents.id
                                 LEFT JOIN payments ON payments.document_request_id = document_requests.id
                                 ORDER BY document_requests.requested_at DESC");

include "../../includes/header.php";
renderHeader("Document Requests", "Move requests through verification, approval, payment, release, cancellation, or rejection.", "documents");
?>

<section class="panel">
    <?php if ($message !== "") { ?>
        <p class="message"><?php echo e($message); ?></p>
    <?php } ?>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Resident</th>
                    <th>Document</th>
                    <th>Purpose</th>
                    <th>Fee</th>
                    <th>Current Status</th>
                    <th>Receipt</th>
                    <th>Update Status</th>
                    <th>Date Requested</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($requests && mysqli_num_rows($requests) > 0) { ?>
                    <?php while ($row = mysqli_fetch_assoc($requests)) { ?>
                        <tr>
                            <td><?php echo e(trim($row["first_name"] . " " . $row["middle_name"] . " " . $row["last_name"])); ?></td>
                            <td><?php echo e($row["document_name"]); ?></td>
                            <td><?php echo e($row["purpose"]); ?></td>
                            <td>PHP <?php echo e(number_format((float)$row["fee"], 2)); ?></td>
                            <td><span class="status-pill status-<?php echo e(strtolower(str_replace(" ", "-", $row["status"]))); ?>"><?php echo e($row["status"]); ?></span></td>
                            <td><?php echo e($row["receipt_number"] ?: "No receipt"); ?></td>
                            <td>
                                <form class="inline-form" method="POST">
                                    <input type="hidden" name="request_id" value="<?php echo e($row["id"]); ?>">
                                    <select name="status" aria-label="Status">
                                        <?php foreach ($statuses as $status) { ?>
                                            <option value="<?php echo e($status); ?>" <?php echo $row["status"] === $status ? "selected" : ""; ?>><?php echo e($status); ?></option>
                                        <?php } ?>
                                    </select>
                                    <button type="submit" name="update_status">Update</button>
                                </form>
                            </td>
                            <td><?php echo e($row["requested_at"]); ?></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr><td colspan="8">No document requests found.</td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</section>

<?php include "../../includes/footer.php"; ?>
