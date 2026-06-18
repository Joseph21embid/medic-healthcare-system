<?php

include_once __DIR__ . "/../includes/session.php";
include_once __DIR__ . "/../includes/functions.php";
include_once __DIR__ . "/../config/db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "hospital") {
    header("Location: ../public/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

$hospital_name = "";
$organization_type = "";
$email = "";
$phone = "";
$address = "";
$state = "";
$lga = "";
$license_number = "";
$registration_number = "";
$contact_person = "";
$contact_role = "";
$contact_phone = "";
$database_ready = true;

$errors = array(
    "hospital_name" => "",
    "organization_type" => "",
    "phone" => "",
    "address" => "",
    "state" => "",
    "lga" => "",
    "license_number" => "",
    "contact_person" => "",
    "contact_phone" => "",
    "general" => "",
);

$required_columns = array(
    "organization_type",
    "phone",
    "address",
    "state",
    "lga",
    "license_number",
    "registration_number",
    "contact_person",
    "contact_role",
    "contact_phone",
    "onboarding_completed",
);

foreach ($required_columns as $column_name) {
    $column_stmt = $conn->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'hospitals' AND COLUMN_NAME = ?");
    $column_stmt->bind_param("s", $column_name);
    $column_stmt->execute();
    $column_stmt->bind_result($column_count);
    $column_stmt->fetch();

    if ($column_count == 0) {
        $database_ready = false;
    }

    $column_stmt->close();
}

if ($database_ready) {
    $hospital_stmt = $conn->prepare("SELECT hospital_name, organization_type, email, phone, address, state, lga, license_number, registration_number, contact_person, contact_role, contact_phone FROM hospitals WHERE user_id = ? LIMIT 1");
    $hospital_stmt->bind_param("i", $user_id);
    $hospital_stmt->execute();
    $hospital_stmt->bind_result($saved_hospital_name, $saved_organization_type, $saved_email, $saved_phone, $saved_address, $saved_state, $saved_lga, $saved_license_number, $saved_registration_number, $saved_contact_person, $saved_contact_role, $saved_contact_phone);

    if ($hospital_stmt->fetch()) {
        $hospital_name = $saved_hospital_name;
        $organization_type = $saved_organization_type;
        $email = $saved_email;
        $phone = $saved_phone;
        $address = $saved_address;
        $state = $saved_state;
        $lga = $saved_lga;
        $license_number = $saved_license_number;
        $registration_number = $saved_registration_number;
        $contact_person = $saved_contact_person;
        $contact_role = $saved_contact_role;
        $contact_phone = $saved_contact_phone;
    }

    $hospital_stmt->close();
} else {
    $hospital_stmt = $conn->prepare("SELECT hospital_name, email FROM hospitals WHERE user_id = ? LIMIT 1");
    $hospital_stmt->bind_param("i", $user_id);
    $hospital_stmt->execute();
    $hospital_stmt->bind_result($saved_hospital_name, $saved_email);

    if ($hospital_stmt->fetch()) {
        $hospital_name = $saved_hospital_name;
        $email = $saved_email;
    }

    $hospital_stmt->close();
    $errors["general"] = "Run the hospital onboarding database update before saving organization details.";
}

if (is_post_request() && isset($_POST["save_hospital_onboarding"])) {
    if (isset($_POST["hospital_name"])) {
        $hospital_name = clean_input($_POST["hospital_name"]);
    }

    if (isset($_POST["organization_type"])) {
        $organization_type = clean_input($_POST["organization_type"]);
    }

    if (isset($_POST["phone"])) {
        $phone = clean_input($_POST["phone"]);
    }

    if (isset($_POST["address"])) {
        $address = clean_input($_POST["address"]);
    }

    if (isset($_POST["state"])) {
        $state = clean_input($_POST["state"]);
    }

    if (isset($_POST["lga"])) {
        $lga = clean_input($_POST["lga"]);
    }

    if (isset($_POST["license_number"])) {
        $license_number = clean_input($_POST["license_number"]);
    }

    if (isset($_POST["registration_number"])) {
        $registration_number = clean_input($_POST["registration_number"]);
    }

    if (isset($_POST["contact_person"])) {
        $contact_person = clean_input($_POST["contact_person"]);
    }

    if (isset($_POST["contact_role"])) {
        $contact_role = clean_input($_POST["contact_role"]);
    }

    if (isset($_POST["contact_phone"])) {
        $contact_phone = clean_input($_POST["contact_phone"]);
    }

    if (!$database_ready) {
        $errors["general"] = "Run the hospital onboarding database update before saving organization details.";
    }

    if (empty($hospital_name)) {
        $errors["hospital_name"] = "Enter organization name";
    }

    if (empty($organization_type)) {
        $errors["organization_type"] = "Select organization type";
    }

    if (empty($phone)) {
        $errors["phone"] = "Enter organization phone number";
    }

    if (empty($address)) {
        $errors["address"] = "Enter organization address";
    }

    if (empty($state)) {
        $errors["state"] = "Enter state";
    }

    if (empty($lga)) {
        $errors["lga"] = "Enter LGA";
    }

    if (empty($license_number)) {
        $errors["license_number"] = "Enter license number";
    }

    if (empty($contact_person)) {
        $errors["contact_person"] = "Enter contact person name";
    }

    if (empty($contact_phone)) {
        $errors["contact_phone"] = "Enter contact person phone";
    }

    if (!array_filter($errors)) {
        $update_stmt = $conn->prepare("UPDATE hospitals SET hospital_name = ?, organization_type = ?, phone = ?, address = ?, state = ?, lga = ?, license_number = ?, registration_number = ?, contact_person = ?, contact_role = ?, contact_phone = ?, onboarding_completed = 1 WHERE user_id = ?");
        $update_stmt->bind_param("sssssssssssi", $hospital_name, $organization_type, $phone, $address, $state, $lga, $license_number, $registration_number, $contact_person, $contact_role, $contact_phone, $user_id);

        if ($update_stmt->execute()) {
            $_SESSION["name_tag"] = $hospital_name;
            $update_stmt->close();
            redirect_to("../public/hospital-pending.php");
        } else {
            $errors["general"] = "Organization details could not be saved. Please try again.";
            $update_stmt->close();
        }
    }
}
