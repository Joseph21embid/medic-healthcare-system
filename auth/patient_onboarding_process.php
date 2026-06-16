<?php

include_once __DIR__ . "/../includes/session.php";
include_once __DIR__ . "/../includes/functions.php";
include_once __DIR__ . "/../config/db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "patient") {
    header("Location: ../public/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

$full_name = "";
$phone = "";
$gender = "";
$date_of_birth = "";
$address = "";
$blood_group = "";
$genotype = "";
$allergies = "";
$chronic_conditions = "";
$current_medications = "";
$emergency_contact_name = "";
$emergency_contact_phone = "";

$errors = array(
    "full_name" => "",
    "phone" => "",
    "gender" => "",
    "date_of_birth" => "",
    "address" => "",
    "blood_group" => "",
    "emergency_contact_name" => "",
    "emergency_contact_phone" => "",
    "general" => "",
);

$patient_stmt = $conn->prepare("SELECT full_name, phone, gender, date_of_birth, address, blood_group, genotype, allergies, chronic_conditions, current_medications, emergency_contact_name, emergency_contact_phone FROM patients WHERE user_id = ? LIMIT 1");
$patient_stmt->bind_param("i", $user_id);
$patient_stmt->execute();
$patient_stmt->bind_result($saved_full_name, $saved_phone, $saved_gender, $saved_date_of_birth, $saved_address, $saved_blood_group, $saved_genotype, $saved_allergies, $saved_chronic_conditions, $saved_current_medications, $saved_emergency_contact_name, $saved_emergency_contact_phone);

if ($patient_stmt->fetch()) {
    $full_name = $saved_full_name;
    $phone = $saved_phone;
    $gender = $saved_gender;
    $date_of_birth = $saved_date_of_birth;
    $address = $saved_address;
    $blood_group = $saved_blood_group;
    $genotype = $saved_genotype;
    $allergies = $saved_allergies;
    $chronic_conditions = $saved_chronic_conditions;
    $current_medications = $saved_current_medications;
    $emergency_contact_name = $saved_emergency_contact_name;
    $emergency_contact_phone = $saved_emergency_contact_phone;
}

$patient_stmt->close();

if (is_post_request() && isset($_POST["save_onboarding"])) {
    if (isset($_POST["full_name"])) {
        $full_name = clean_input($_POST["full_name"]);
    }

    if (isset($_POST["phone"])) {
        $phone = clean_input($_POST["phone"]);
    }

    if (isset($_POST["gender"])) {
        $gender = clean_input($_POST["gender"]);
    }

    if (isset($_POST["date_of_birth"])) {
        $date_of_birth = clean_input($_POST["date_of_birth"]);
    }

    if (isset($_POST["address"])) {
        $address = clean_input($_POST["address"]);
    }

    if (isset($_POST["blood_group"])) {
        $blood_group = clean_input($_POST["blood_group"]);
    }

    if (isset($_POST["genotype"])) {
        $genotype = clean_input($_POST["genotype"]);
    }

    if (isset($_POST["allergies"])) {
        $allergies = clean_input($_POST["allergies"]);
    }

    if (isset($_POST["chronic_conditions"])) {
        $chronic_conditions = clean_input($_POST["chronic_conditions"]);
    }

    if (isset($_POST["current_medications"])) {
        $current_medications = clean_input($_POST["current_medications"]);
    }

    if (isset($_POST["emergency_contact_name"])) {
        $emergency_contact_name = clean_input($_POST["emergency_contact_name"]);
    }

    if (isset($_POST["emergency_contact_phone"])) {
        $emergency_contact_phone = clean_input($_POST["emergency_contact_phone"]);
    }

    if (empty($full_name)) {
        $errors["full_name"] = "Enter your full name";
    }

    if (empty($phone)) {
        $errors["phone"] = "Enter your phone number";
    }

    if (empty($gender)) {
        $errors["gender"] = "Select your gender";
    }

    if (empty($date_of_birth)) {
        $errors["date_of_birth"] = "Select your date of birth";
    }

    if (empty($address)) {
        $errors["address"] = "Enter your home address";
    }

    if (empty($blood_group)) {
        $errors["blood_group"] = "Select your blood group";
    }

    if (empty($emergency_contact_name)) {
        $errors["emergency_contact_name"] = "Enter emergency contact name";
    }

    if (empty($emergency_contact_phone)) {
        $errors["emergency_contact_phone"] = "Enter emergency contact phone";
    }

    if (!array_filter($errors)) {
        $update_stmt = $conn->prepare("UPDATE patients SET full_name = ?, phone = ?, gender = ?, date_of_birth = ?, address = ?, blood_group = ?, genotype = ?, allergies = ?, chronic_conditions = ?, current_medications = ?, emergency_contact_name = ?, emergency_contact_phone = ?, profile_completed = 1 WHERE user_id = ?");
        $update_stmt->bind_param("ssssssssssssi", $full_name, $phone, $gender, $date_of_birth, $address, $blood_group, $genotype, $allergies, $chronic_conditions, $current_medications, $emergency_contact_name, $emergency_contact_phone, $user_id);

        if ($update_stmt->execute()) {
            $_SESSION["name_tag"] = $full_name;
            $update_stmt->close();
            redirect_to("../dashboard/patient.php");
        } else {
            $errors["general"] = "Profile could not be saved. Please try again.";
            $update_stmt->close();
        }
    }
}

