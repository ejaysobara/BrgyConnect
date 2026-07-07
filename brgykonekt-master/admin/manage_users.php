<?php
include "../includes/auth_check.php";
include "../config/database.php";

requireRoles([1]);

$message = "";
$error = "";
$search = trim($_GET["search"] ?? "");
$role_filter = (int)($_GET["role"] ?? 0);
$edit_user = null;

$roles = [];
$role_result = mysqli_query($conn, "SELECT * FROM roles ORDER BY id ASC");
if ($role_result) {
    while ($role = mysqli_fetch_assoc($role_result)) {
        $roles[] = $role;
    }
}

if (isset($_GET["edit"])) {
    $edit_id = (int)$_GET["edit"];
    $edit_result = mysqli_query($conn, "SELECT * FROM users WHERE id = '$edit_id'");
    $edit_user = $edit_result ? mysqli_fetch_assoc($edit_result) : null;
}

if (isset($_GET["delete"])) {
    $delete_id = (int)$_GET["delete"];
    if ($delete_id === (int)$_SESSION["user_id"]) {
        $error = "You cannot delete your own account while logged in.";
    } else {
        $name_result = mysqli_query($conn, "SELECT full_name FROM users WHERE id = '$delete_id'");
        $name_row = $name_result ? mysqli_fetch_assoc($name_result) : null;
        if (mysqli_query($conn, "DELETE FROM users WHERE id = '$delete_id'")) {
            addAuditLog($conn, $_SESSION["user_id"], "delete_user", $name_row["full_name"] ?? "user#$delete_id");
            $message = "User account deleted.";
        } else {
            $error = "Unable to delete user: " . mysqli_error($conn);
        }
    }
}

if (isset($_POST["save_user"])) {
    $user_id = (int)($_POST["user_id"] ?? 0);
    $role_id = (int)$_POST["role_id"];
    $full_name = mysqli_real_escape_string($conn, trim($_POST["full_name"]));
    $username = mysqli_real_escape_string($conn, trim($_POST["username"]));
    $email = mysqli_real_escape_string($conn, trim($_POST["email"]));
    $status = mysqli_real_escape_string($conn, $_POST["status"]);
    $password = trim($_POST["password"] ?? "");

    if ($user_id > 0) {
        $sql = "UPDATE users SET role_id = '$role_id', full_name = '$full_name', username = '$username', email = '$email', status = '$status'";
        if ($password !== "") {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $sql .= ", password = '$hashed'";
        }
        $sql .= " WHERE id = '$user_id'";

        if (mysqli_query($conn, $sql)) {
            $message = "User updated successfully.";
            addAuditLog($conn, $_SESSION["user_id"], "update_user", $full_name);
        } else {
            $error = "Unable to update user: " . mysqli_error($conn);
        }
    } else {
        $hashed = password_hash($password !== "" ? $password : "123", PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (role_id, full_name, username, email, password, status)
                VALUES ('$role_id', '$full_name', '$username', '$email', '$hashed', '$status')";

        if (mysqli_query($conn, $sql)) {
            $message = "User created successfully.";
            addAuditLog($conn, $_SESSION["user_id"], "create_user", $full_name);
        } else {
            $error = "Unable to create user: " . mysqli_error($conn);
        }
    }
}

$query = "SELECT users.*, roles.role_name FROM users LEFT JOIN roles ON users.role_id = roles.id WHERE 1=1";
if ($search !== "") {
    $search_value = mysqli_real_escape_string($conn, $search);
    $query .= " AND (users.full_name LIKE '%$search_value%' OR users.username LIKE '%$search_value%' OR users.email LIKE '%$search_value%')";
}
if ($role_filter > 0) {
    $query .= " AND users.role_id = '$role_filter'";
}
$query .= " ORDER BY users.id ASC";
$users = mysqli_query($conn, $query);

include "../includes/header.php";
renderHeader("User Account Management", "Create, update, filter, and assign role-based access for barangay system accounts.", "users");
?>

<section class="panel">
    <?php if ($message !== "") { ?>
        <p class="message"><?php echo e($message); ?></p>
    <?php } ?>
    <?php if ($error !== "") { ?>
        <p class="error"><?php echo e($error); ?></p>
    <?php } ?>

    <form class="inline-form" method="GET">
        <input type="text" name="search" value="<?php echo e($search); ?>" placeholder="Search name, username, or email">
        <select name="role" aria-label="Role filter">
            <option value="0">All roles</option>
            <?php foreach ($roles as $role) { ?>
                <option value="<?php echo e($role["id"]); ?>" <?php echo $role_filter === (int)$role["id"] ? "selected" : ""; ?>><?php echo e($role["role_name"]); ?></option>
            <?php } ?>
        </select>
        <button type="submit">Filter</button>
        <a class="button secondary" href="manage_users.php">Reset</a>
    </form>
</section>

<section class="panel">
    <h3><?php echo $edit_user ? "Edit User" : "Create User"; ?></h3>
    <form method="POST">
        <input type="hidden" name="user_id" value="<?php echo e($edit_user["id"] ?? ""); ?>">
        <div class="grid-2">
            <div>
                <label for="full_name">Full Name</label>
                <input id="full_name" type="text" name="full_name" required value="<?php echo e($edit_user["full_name"] ?? ""); ?>">
            </div>
            <div>
                <label for="username">Username</label>
                <input id="username" type="text" name="username" required value="<?php echo e($edit_user["username"] ?? ""); ?>">
            </div>
            <div>
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="<?php echo e($edit_user["email"] ?? ""); ?>">
            </div>
            <div>
                <label for="role_id">Role</label>
                <select id="role_id" name="role_id">
                    <?php foreach ($roles as $role) { ?>
                        <option value="<?php echo e($role["id"]); ?>" <?php echo ($edit_user && (int)$edit_user["role_id"] === (int)$role["id"]) ? "selected" : ""; ?>><?php echo e($role["role_name"]); ?></option>
                    <?php } ?>
                </select>
            </div>
            <div>
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="Active" <?php echo (!$edit_user || $edit_user["status"] === "Active") ? "selected" : ""; ?>>Active</option>
                    <option value="Inactive" <?php echo ($edit_user && $edit_user["status"] === "Inactive") ? "selected" : ""; ?>>Inactive</option>
                </select>
            </div>
            <div>
                <label for="password">Password <?php echo $edit_user ? "(leave blank to keep current)" : ""; ?></label>
                <input id="password" type="password" name="password">
            </div>
        </div>
        <button type="submit" name="save_user">Save User</button>
    </form>
</section>

<section class="panel">
    <h3>Existing Accounts</h3>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($users && mysqli_num_rows($users) > 0) { ?>
                    <?php while ($row = mysqli_fetch_assoc($users)) { ?>
                        <tr>
                            <td><?php echo e($row["full_name"]); ?></td>
                            <td><?php echo e($row["username"]); ?></td>
                            <td><?php echo e($row["role_name"] ?? "Unassigned"); ?></td>
                            <td><span class="badge"><?php echo e($row["status"]); ?></span></td>
                            <td>
                                <div class="quick-actions">
                                    <a class="button secondary" href="manage_users.php?edit=<?php echo e($row["id"]); ?>">Edit</a>
                                    <?php if ((int)$row["id"] !== (int)$_SESSION["user_id"]) { ?>
                                        <a class="button secondary" href="manage_users.php?delete=<?php echo e($row["id"]); ?>" onclick="return confirm('Delete this account permanently?');">Delete</a>
                                    <?php } ?>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr><td colspan="5">No user accounts found.</td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</section>

<?php include "../includes/footer.php"; ?>
