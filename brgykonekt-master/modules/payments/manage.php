<?php
include "../../includes/auth_check.php";
include "../../config/database.php";

requireStaffLevel(2); // Treasurer and above: payments and financial records

$message = "";
$error = "";

if (isset($_POST["record_payment"])) {
    $request_id = (int)$_POST["request_id"];
    $amount = (float)$_POST["amount"];
    $payment_method = mysqli_real_escape_string($conn, $_POST["payment_method"]);
    $receipt_sequence = getCountValue($conn, "SELECT COUNT(*) AS total FROM payments WHERE DATE(payment_date) = CURDATE()") + 1;
    $receipt_number = "OR-" . date("Ymd") . "-" . str_pad((string)$receipt_sequence, 4, "0", STR_PAD_LEFT);

    $sql = "INSERT INTO payments (document_request_id, receipt_number, amount, payment_method)
            VALUES ('$request_id', '$receipt_number', '$amount', '$payment_method')";

    if (mysqli_query($conn, $sql)) {
        mysqli_query($conn, "UPDATE document_requests SET status = 'Paid' WHERE id = '$request_id'");
        addAuditLog($conn, $_SESSION["user_id"], "record_payment", "request#$request_id", $receipt_number);
        $message = "Payment recorded with receipt $receipt_number.";
    } else {
        $error = "Unable to record payment: " . mysqli_error($conn);
    }
}

if (isset($_GET["ready"])) {
    $request_id = (int)$_GET["ready"];
    mysqli_query($conn, "UPDATE document_requests SET status = 'Ready for Pickup' WHERE id = '$request_id'");
    addAuditLog($conn, $_SESSION["user_id"], "ready_for_pickup", "request#$request_id");
    header("Location: manage.php");
    exit();
}

$requests = mysqli_query($conn, "SELECT document_requests.*, document_types.document_name, document_types.fee,
                                        residents.first_name, residents.middle_name, residents.last_name,
                                        payments.receipt_number, payments.payment_method, payments.payment_date
                                 FROM document_requests
                                 INNER JOIN document_types ON document_requests.document_type_id = document_types.id
                                 INNER JOIN residents ON document_requests.resident_id = residents.id
                                 LEFT JOIN payments ON payments.document_request_id = document_requests.id
                                 WHERE document_requests.status IN ('For Payment', 'Paid', 'Free', 'Ready for Pickup', 'Released')
                                 ORDER BY document_requests.requested_at DESC");

include "../../includes/header.php";
renderHeader("Payment and Official Receipt", "Collect document fees, generate official receipts, and mark paid documents ready for pickup.", "payments");
?>

<section class="panel">
    <?php if ($message !== "") { ?>
        <p class="message"><?php echo e($message); ?></p>
    <?php } ?>
    <?php if ($error !== "") { ?>
        <p class="error"><?php echo e($error); ?></p>
    <?php } ?>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Resident</th>
                    <th>Document</th>
                    <th>Fee</th>
                    <th>Status</th>
                    <th>Receipt</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($requests && mysqli_num_rows($requests) > 0) { ?>
                    <?php while ($row = mysqli_fetch_assoc($requests)) { ?>
                        <tr>
                            <td><?php echo e(trim($row["first_name"] . " " . $row["middle_name"] . " " . $row["last_name"])); ?></td>
                            <td><?php echo e($row["document_name"]); ?></td>
                            <td>PHP <?php echo e(number_format((float)$row["fee"], 2)); ?></td>
                            <td><span class="status-pill status-<?php echo e(strtolower(str_replace(" ", "-", $row["status"]))); ?>"><?php echo e($row["status"]); ?></span></td>
                            <td>
                                <?php if ($row["receipt_number"]) { ?>
                                    <?php echo e($row["receipt_number"]); ?><br>
                                    <?php echo e($row["payment_method"]); ?><br>
                                    <?php echo e($row["payment_date"]); ?>
                                <?php } else { ?>
                                    No receipt yet
                                <?php } ?>
                            </td>
                            <td>
                                <?php if ($row["status"] === "For Payment") { ?>
                                    <form class="inline-form" method="POST">
                                        <input type="hidden" name="request_id" value="<?php echo e($row["id"]); ?>">
                                        <input type="hidden" name="amount" value="<?php echo e($row["fee"]); ?>">
                                        <select name="payment_method" aria-label="Payment method">
                                            <option value="Cash">Cash</option>
                                            <option value="GCash">GCash</option>
                                            <option value="Maya">Maya</option>
                                        </select>
                                        <button type="submit" name="record_payment">Record Payment</button>
                                    </form>
                                <?php } elseif ($row["status"] === "Paid" || $row["status"] === "Free") { ?>
                                    <a class="button" href="manage.php?ready=<?php echo e($row["id"]); ?>">Mark Ready</a>
                                <?php } else { ?>
                                    Done
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr><td colspan="6">No payment records found.</td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</section>

<?php include "../../includes/footer.php"; ?>
