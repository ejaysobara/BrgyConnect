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

if (isset($_POST["submit_request"])) {
    if (!$resident || $resident["status"] !== "Verified") {
        $error = "Your resident profile must be verified before requesting documents.";
    } else {
        $document_type_id = (int)$_POST["document_type_id"];
        $purpose = mysqli_real_escape_string($conn, trim($_POST["purpose"]));

        $sql = "INSERT INTO document_requests (resident_id, document_type_id, purpose, status)
                VALUES ('$resident_id', '$document_type_id', '$purpose', 'Pending')";

        if (mysqli_query($conn, $sql)) {
            $message = "Document request submitted successfully.";
        } else {
            $error = "Unable to submit request: " . mysqli_error($conn);
        }
    }
}

$document_types = mysqli_query($conn, "SELECT * FROM document_types ORDER BY document_name ASC");
$my_requests = mysqli_query($conn, "SELECT document_requests.*, document_types.document_name, document_types.fee, payments.receipt_number, payments.payment_method, payments.payment_date
                                    FROM document_requests
                                    INNER JOIN document_types ON document_requests.document_type_id = document_types.id
                                    LEFT JOIN payments ON payments.document_request_id = document_requests.id
                                    WHERE document_requests.resident_id = '$resident_id'
                                    ORDER BY document_requests.requested_at DESC");

include "../../includes/header.php";
renderHeader("Request Document", "Request clearances and certificates, then track verification, payment, release, or rejection.", "documents");
?>

<section class="panel">
    <?php if ($message !== "") { ?>
        <p class="message"><?php echo e($message); ?></p>
    <?php } ?>
    <?php if ($error !== "") { ?>
        <p class="error"><?php echo e($error); ?></p>
    <?php } ?>

    <?php if (!$resident || $resident["status"] !== "Verified") { ?>
        <p class="error">Complete and verify your resident profile before creating document requests.</p>
    <?php } else { ?>
        <form method="POST">
            <label for="document_type_id">Document Type</label>
            <select id="document_type_id" name="document_type_id" required>
                <option value="">Select document</option>
                <?php if ($document_types) { ?>
                    <?php while ($doc = mysqli_fetch_assoc($document_types)) { ?>
                        <option value="<?php echo e($doc["id"]); ?>">
                            <?php echo e($doc["document_name"]); ?> - PHP <?php echo e(number_format((float)$doc["fee"], 2)); ?>
                        </option>
                    <?php } ?>
                <?php } ?>
            </select>

            <label for="purpose">Purpose</label>
            <input id="purpose" type="text" name="purpose" required placeholder="Employment, scholarship, business permit, travel">

            <button type="submit" name="submit_request">Submit Request</button>
        </form>
    <?php } ?>
</section>

<section class="panel">
    <h3>My Document Requests</h3>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Document</th>
                    <th>Purpose</th>
                    <th>Fee</th>
                    <th>Status</th>
                    <th>Receipt</th>
                    <th>Date Requested</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($my_requests && mysqli_num_rows($my_requests) > 0) { ?>
                    <?php while ($row = mysqli_fetch_assoc($my_requests)) { ?>
                        <tr>
                            <td><?php echo e($row["document_name"]); ?></td>
                            <td><?php echo e($row["purpose"]); ?></td>
                            <td>PHP <?php echo e(number_format((float)$row["fee"], 2)); ?></td>
                            <td><span class="status-pill status-<?php echo e(strtolower(str_replace(" ", "-", $row["status"]))); ?>"><?php echo e($row["status"]); ?></span></td>
                            <td>
                                <?php if (!empty($row["receipt_number"])) { ?>
                                    <?php echo e($row["receipt_number"]); ?><br>
                                    <?php echo e($row["payment_method"]); ?><br>
                                    <?php echo e($row["payment_date"]); ?>
                                <?php } else { ?>
                                    No receipt yet
                                <?php } ?>
                            </td>
                            <td><?php echo e($row["requested_at"]); ?></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr><td colspan="6">No document requests yet.</td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</section>

<?php include "../../includes/footer.php"; ?>
