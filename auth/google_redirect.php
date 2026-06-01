<?php

include_once __DIR__ . "/../includes/session.php";

$google_config = include __DIR__ . "/../config/google.php";

if (isset($_GET["role"])) {
    $role = $_GET["role"];
} else {
    $role = "patient";
}

if (isset($_GET["mode"])) {
    $mode = $_GET["mode"];
} else {
    $mode = "signup";
}

if (!in_array($role, array("patient", "hospital"), true)) {
    $role = "patient";
}

if (!in_array($mode, array("signup", "login"), true)) {
    $mode = "signup";
}

$_SESSION["google_auth_role"] = $role;
$_SESSION["google_auth_mode"] = $mode;
$_SESSION["google_auth_state"] = bin2hex(random_bytes(32));

$params = array(
    "client_id" => $google_config["client_id"],
    "redirect_uri" => $google_config["redirect_uri"],
    "response_type" => "code",
    "scope" => "openid email profile",
    "state" => $_SESSION["google_auth_state"],
    "access_type" => "online",
    "prompt" => "select_account",
);

header("Location: https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query($params));
exit;
