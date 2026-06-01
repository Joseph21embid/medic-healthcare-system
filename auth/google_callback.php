<?php

include_once __DIR__ . "/../includes/session.php";
include_once __DIR__ . "/../includes/functions.php";
include_once __DIR__ . "/../config/db.php";

$google_config = include __DIR__ . "/../config/google.php";

if (!isset($_GET["state"]) || !isset($_SESSION["google_auth_state"])) {
    redirect_to("../public/login.php?error=invalid_google_state");
}

if ($_GET["state"] != $_SESSION["google_auth_state"]) {
    redirect_to("../public/login.php?error=invalid_google_state");
}

if (!isset($_GET["code"])) {
    redirect_to("../public/login.php?error=missing_google_code");
}

if (isset($_SESSION["google_auth_role"])) {
    $role = $_SESSION["google_auth_role"];
} else {
    $role = "patient";
}

if (isset($_SESSION["google_auth_mode"])) {
    $mode = $_SESSION["google_auth_mode"];
} else {
    $mode = "signup";
}

if (!in_array($role, array("patient", "hospital"), true)) {
    $role = "patient";
}

$token_payload = array(
    "code" => $_GET["code"],
    "client_id" => $google_config["client_id"],
    "client_secret" => $google_config["client_secret"],
    "redirect_uri" => $google_config["redirect_uri"],
    "grant_type" => "authorization_code",
);

$token_response = google_post_request("https://oauth2.googleapis.com/token", $token_payload);

if (!isset($token_response["access_token"])) {
    redirect_to("../public/login.php?error=google_token_failed");
}

$user_info = google_get_request("https://www.googleapis.com/oauth2/v2/userinfo", $token_response["access_token"]);

if (!isset($user_info["id"]) || !isset($user_info["email"])) {
    redirect_to("../public/login.php?error=google_profile_failed");
}

$google_id = $user_info["id"];
$email = $user_info["email"];

if (isset($user_info["name"])) {
    $name = $user_info["name"];
} else {
    $name = $email;
}

$stmt = $conn->prepare("SELECT id, role, name_tag, status FROM users WHERE email = ? OR google_id = ? LIMIT 1");
$stmt->bind_param("ss", $email, $google_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows == 1) {
    $stmt->bind_result($user_id, $saved_role, $name_tag, $status);
    $stmt->fetch();
    $stmt->close();

    if ($status == "suspended") {
        redirect_to("../public/login.php?error=account_suspended");
    }

    $update_stmt = $conn->prepare("UPDATE users SET google_id = ?, auth_provider = 'google' WHERE id = ?");
    $update_stmt->bind_param("si", $google_id, $user_id);
    $update_stmt->execute();
    $update_stmt->close();

    login_google_user($user_id, $email, $saved_role, $name_tag);
}

$stmt->close();

if ($mode == "login") {
    redirect_to("../public/signup.php?error=google_account_not_found");
}

$name_tag = ucfirst($role) . random_int(1000, 9999);

if ($role == "hospital") {
    $status = "pending";
} else {
    $status = "active";
}

$conn->begin_transaction();

try {
    $insert_user = $conn->prepare("INSERT INTO users (role, name_tag, email, password, auth_provider, google_id, status) VALUES (?, ?, ?, NULL, 'google', ?, ?)");
    $insert_user->bind_param("sssss", $role, $name_tag, $email, $google_id, $status);
    $insert_user->execute();

    $user_id = $conn->insert_id;
    $insert_user->close();

    if ($role == "patient") {
        $health_id = "MED" . date("Y") . random_int(100000, 999999);
        $patient_stmt = $conn->prepare("INSERT INTO patients (user_id, health_id, full_name) VALUES (?, ?, ?)");
        $patient_stmt->bind_param("iss", $user_id, $health_id, $name);
        $patient_stmt->execute();
        $patient_stmt->close();
    } else {
        $hospital_stmt = $conn->prepare("INSERT INTO hospitals (user_id, hospital_name, email) VALUES (?, ?, ?)");
        $hospital_stmt->bind_param("iss", $user_id, $name, $email);
        $hospital_stmt->execute();
        $hospital_stmt->close();
    }

    $conn->commit();
    login_google_user($user_id, $email, $role, $name_tag);
} catch (Exception $e) {
    $conn->rollback();
    redirect_to("../public/signup.php?error=google_signup_failed");
}

function google_post_request($url, $payload)
{
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded"));

    $response = curl_exec($ch);
    curl_close($ch);

    $decoded_response = json_decode($response, true);

    if (is_array($decoded_response)) {
        return $decoded_response;
    }

    return array();
}

function google_get_request($url, $access_token)
{
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $access_token));

    $response = curl_exec($ch);
    curl_close($ch);

    $decoded_response = json_decode($response, true);

    if (is_array($decoded_response)) {
        return $decoded_response;
    }

    return array();
}

function login_google_user($user_id, $email, $role, $name_tag)
{
    $_SESSION["user_id"] = $user_id;
    $_SESSION["email"] = $email;
    $_SESSION["role"] = $role;
    $_SESSION["name_tag"] = $name_tag;

    unset($_SESSION["google_auth_role"]);
    unset($_SESSION["google_auth_mode"]);
    unset($_SESSION["google_auth_state"]);

    if ($role == "patient") {
        redirect_to("../dashboard/patient.php");
    }

    if ($role == "hospital") {
        redirect_to("../dashboard/hospital.php");
    }

    if ($role == "admin") {
        redirect_to("../dashboard/admin.php");
    }

    redirect_to("../public/login.php?error=unknown_role");
}
