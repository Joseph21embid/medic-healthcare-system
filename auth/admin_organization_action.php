<?php

include_once __DIR__ . "/../includes/session.php";
include_once __DIR__ . "/../includes/functions.php";
include_once __DIR__ . "/../config/db.php";

$allowed_admin_email = "itisadminjay@gmail.com";

if (!isset($_SESSION["role"]) || $_SESSION["role"] != "admin") {
    redirect_to("../public/login.php");
}

if (!isset($_SESSION["email"]) || $_SESSION["email"] != $allowed_admin_email) {
    redirect_to("../public/login.php");
}

if (!is_post_request()) {
    redirect_to("../dashboard/admin.php");
}

if (!isset($_POST["hospital_id"]) || !isset($_POST["action"])) {
    redirect_to("../dashboard/admin.php?message=missing_action");
}

$hospital_id = (int) $_POST["hospital_id"];
$action = $_POST["action"];

if ($hospital_id <= 0) {
    redirect_to("../dashboard/admin.php?message=invalid_organization");
}

$hospital_stmt = $conn->prepare("SELECT user_id FROM hospitals WHERE id = ? LIMIT 1");
$hospital_stmt->bind_param("i", $hospital_id);
$hospital_stmt->execute();
$hospital_stmt->store_result();

if ($hospital_stmt->num_rows != 1) {
    $hospital_stmt->close();
    redirect_to("../dashboard/admin.php?message=organization_not_found");
}

$hospital_stmt->bind_result($organization_user_id);
$hospital_stmt->fetch();
$hospital_stmt->close();

if ($action == "approve") {
    $conn->begin_transaction();

    try {
        $update_user = $conn->prepare("UPDATE users SET status = 'active' WHERE id = ? AND role = 'hospital'");
        $update_user->bind_param("i", $organization_user_id);
        $update_user->execute();
        $update_user->close();

        $column_stmt = $conn->prepare("SHOW COLUMNS FROM hospitals LIKE 'approval_seen'");
        $column_stmt->execute();
        $column_stmt->store_result();

        if ($column_stmt->num_rows > 0) {
            $column_stmt->close();
            $update_hospital = $conn->prepare("UPDATE hospitals SET approval_seen = 0 WHERE id = ?");
            $update_hospital->bind_param("i", $hospital_id);
            $update_hospital->execute();
            $update_hospital->close();
        } else {
            $column_stmt->close();
        }

        $conn->commit();
        redirect_to("../dashboard/admin.php?message=organization_approved");
    } catch (Exception $e) {
        $conn->rollback();
        redirect_to("../dashboard/admin.php?message=approval_failed");
    }
}

if ($action == "reject") {
    $conn->begin_transaction();

    try {
        $delete_hospital = $conn->prepare("DELETE FROM hospitals WHERE id = ?");
        $delete_hospital->bind_param("i", $hospital_id);
        $delete_hospital->execute();
        $delete_hospital->close();

        $delete_user = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'hospital'");
        $delete_user->bind_param("i", $organization_user_id);
        $delete_user->execute();
        $delete_user->close();

        $conn->commit();
        redirect_to("../dashboard/admin.php?message=organization_rejected");
    } catch (Exception $e) {
        $conn->rollback();
        redirect_to("../dashboard/admin.php?message=rejection_failed");
    }
}

redirect_to("../dashboard/admin.php?message=unknown_action");
