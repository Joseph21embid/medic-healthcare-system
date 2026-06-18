<?php

include_once __DIR__ . "/../includes/session.php";
include_once __DIR__ . "/../includes/functions.php";
include_once __DIR__ . "/../config/db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "patient") {
    header("Location: ../public/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

$nhis_number = "";
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
$profile_photo = "";
$nhis_number_column_exists = false;
$profile_photo_column_exists = false;

$errors = array(
    "nhis_number" => "",
    "full_name" => "",
    "phone" => "",
    "gender" => "",
    "date_of_birth" => "",
    "address" => "",
    "blood_group" => "",
    "profile_photo" => "",
    "emergency_contact_name" => "",
    "emergency_contact_phone" => "",
    "general" => "",
);

$column_stmt = $conn->prepare("SHOW COLUMNS FROM patients LIKE 'profile_photo'");
$column_stmt->execute();
$column_stmt->store_result();

if ($column_stmt->num_rows > 0) {
    $profile_photo_column_exists = true;
}

$column_stmt->close();

$column_stmt = $conn->prepare("SHOW COLUMNS FROM patients LIKE 'nhis_number'");
$column_stmt->execute();
$column_stmt->store_result();

if ($column_stmt->num_rows > 0) {
    $nhis_number_column_exists = true;
}

$column_stmt->close();

if ($profile_photo_column_exists) {
    $patient_stmt = $conn->prepare("SELECT full_name, phone, gender, date_of_birth, address, blood_group, genotype, allergies, chronic_conditions, current_medications, emergency_contact_name, emergency_contact_phone, profile_photo FROM patients WHERE user_id = ? LIMIT 1");
    $patient_stmt->bind_param("i", $user_id);
    $patient_stmt->execute();
    $patient_stmt->bind_result($saved_full_name, $saved_phone, $saved_gender, $saved_date_of_birth, $saved_address, $saved_blood_group, $saved_genotype, $saved_allergies, $saved_chronic_conditions, $saved_current_medications, $saved_emergency_contact_name, $saved_emergency_contact_phone, $saved_profile_photo);
} else {
    $patient_stmt = $conn->prepare("SELECT full_name, phone, gender, date_of_birth, address, blood_group, genotype, allergies, chronic_conditions, current_medications, emergency_contact_name, emergency_contact_phone FROM patients WHERE user_id = ? LIMIT 1");
    $patient_stmt->bind_param("i", $user_id);
    $patient_stmt->execute();
    $patient_stmt->bind_result($saved_full_name, $saved_phone, $saved_gender, $saved_date_of_birth, $saved_address, $saved_blood_group, $saved_genotype, $saved_allergies, $saved_chronic_conditions, $saved_current_medications, $saved_emergency_contact_name, $saved_emergency_contact_phone);
}

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

    if ($profile_photo_column_exists) {
        $profile_photo = $saved_profile_photo;
    }
}

$patient_stmt->close();

if ($nhis_number_column_exists) {
    $nhis_stmt = $conn->prepare("SELECT nhis_number FROM patients WHERE user_id = ? LIMIT 1");
    $nhis_stmt->bind_param("i", $user_id);
    $nhis_stmt->execute();
    $nhis_stmt->bind_result($saved_nhis_number);

    if ($nhis_stmt->fetch()) {
        $nhis_number = $saved_nhis_number;
    }

    $nhis_stmt->close();
}

if (is_post_request() && isset($_POST["save_onboarding"])) {
    if (isset($_POST["nhis_number"])) {
        $nhis_number = clean_input($_POST["nhis_number"]);
    }

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

    if (!empty($nhis_number)) {
        if (!$nhis_number_column_exists) {
            $errors["nhis_number"] = "Run the NHIS database update before saving NHIS number";
        } elseif (!preg_match("/^[A-Za-z0-9\-\/ ]{4,50}$/", $nhis_number)) {
            $errors["nhis_number"] = "Enter a valid NHIS number";
        }
    }

    if (isset($_FILES["profile_photo"]) && $_FILES["profile_photo"]["error"] != UPLOAD_ERR_NO_FILE) {
        if (!$profile_photo_column_exists) {
            $errors["profile_photo"] = "Run the profile photo database update before uploading a photo";
        } elseif ($_FILES["profile_photo"]["error"] != UPLOAD_ERR_OK) {
            $errors["profile_photo"] = "Profile photo could not upload. Please try again";
        } elseif ($_FILES["profile_photo"]["size"] > 2097152) {
            $errors["profile_photo"] = "Profile photo must not be larger than 2MB";
        } else {
            $temporary_file = $_FILES["profile_photo"]["tmp_name"];
            $file_info = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($file_info, $temporary_file);
            finfo_close($file_info);

            $file_extension = "";

            if ($mime_type == "image/jpeg") {
                $file_extension = "jpg";
            }

            if ($mime_type == "image/png") {
                $file_extension = "png";
            }

            if ($mime_type == "image/webp") {
                $file_extension = "webp";
            }

            if (empty($file_extension)) {
                $errors["profile_photo"] = "Profile photo must be JPG, PNG, or WEBP";
            } else {
                $upload_directory = __DIR__ . "/../public/uploads/profile_photos/";
                $file_name = "profile_" . $user_id . "_" . time() . "_" . random_int(1000, 9999) . "." . $file_extension;
                $destination = $upload_directory . $file_name;

                if (move_uploaded_file($temporary_file, $destination)) {
                    $profile_photo = "uploads/profile_photos/" . $file_name;
                } else {
                    $errors["profile_photo"] = "Profile photo could not be saved. Please try again";
                }
            }
        }
    }

    if (!array_filter($errors)) {
        $update_stmt = $conn->prepare("UPDATE patients SET full_name = ?, phone = ?, gender = ?, date_of_birth = ?, address = ?, blood_group = ?, genotype = ?, allergies = ?, chronic_conditions = ?, current_medications = ?, emergency_contact_name = ?, emergency_contact_phone = ?, profile_completed = 1 WHERE user_id = ?");
        $update_stmt->bind_param("ssssssssssssi", $full_name, $phone, $gender, $date_of_birth, $address, $blood_group, $genotype, $allergies, $chronic_conditions, $current_medications, $emergency_contact_name, $emergency_contact_phone, $user_id);

        if ($update_stmt->execute()) {
            if ($nhis_number_column_exists) {
                $nhis_update_stmt = $conn->prepare("UPDATE patients SET nhis_number = ? WHERE user_id = ?");
                $nhis_update_stmt->bind_param("si", $nhis_number, $user_id);
                $nhis_update_stmt->execute();
                $nhis_update_stmt->close();
            }

            if ($profile_photo_column_exists) {
                $photo_update_stmt = $conn->prepare("UPDATE patients SET profile_photo = ? WHERE user_id = ?");
                $photo_update_stmt->bind_param("si", $profile_photo, $user_id);
                $photo_update_stmt->execute();
                $photo_update_stmt->close();
            }

            $_SESSION["name_tag"] = $full_name;
            $update_stmt->close();
            redirect_to("../dashboard/patient.php");
        } else {
            $errors["general"] = "Profile could not be saved. Please try again.";
            $update_stmt->close();
        }
    }
}
