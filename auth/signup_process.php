<?php

include_once __DIR__ . "/../includes/session.php";
include_once __DIR__ . "/../includes/functions.php";
include_once __DIR__ . "/../config/db.php";

$name = "";
$email = "";
$password = "";
$confirm_password = "";
$role = "patient";

$errors = array(
    "name" => "",
    "role" => "",
    "email" => "",
    "password" => "",
    "confirm_password" => "",
    "terms" => "",
);

if (is_post_request() && isset($_POST["signup_submit"])) {
    if (isset($_POST["name"])) {
        $name = clean_input($_POST["name"]);
    }

    if (isset($_POST["email"])) {
        $email = clean_input($_POST["email"]);
    }

    if (isset($_POST["password"])) {
        $password = $_POST["password"];
    }

    if (isset($_POST["confirm_password"])) {
        $confirm_password = $_POST["confirm_password"];
    }

    if (isset($_POST["role"])) {
        $role = $_POST["role"];
    } else {
        $role = "patient";
    }

    if (empty($name)) {
        $errors["name"] = "Enter your full name or hospital name";
    }

    if (!in_array($role, array("patient", "hospital"), true)) {
        $errors["role"] = "Select a valid account type";
    }

    if (empty($email)) {
        $errors["email"] = "Enter an email address";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors["email"] = "Please enter a valid email address";
    }

    if (empty($password)) {
        $errors["password"] = "Enter a password";
    } elseif (strlen($password) < 8 || !preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/", $password)) {
        $errors["password"] = "Password must be at least 8 characters and include uppercase, lowercase, and a number";
    }

    if (empty($confirm_password)) {
        $errors["confirm_password"] = "Confirm your password";
    } elseif ($confirm_password != $password) {
        $errors["confirm_password"] = "Passwords do not match";
    }

    if (!isset($_POST["terms"])) {
        $errors["terms"] = "You must agree before creating an account";
    }

    if (!array_filter($errors)) {
        $check_stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $errors["email"] = "This email already exists";
        }

        $check_stmt->close();
    }

    if (!array_filter($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $name_tag = ucfirst($role) . random_int(1000, 9999);

        if ($role == "hospital") {
            $status = "pending";
        } else {
            $status = "active";
        }

        $conn->begin_transaction();

        try {
            $stmt = $conn->prepare("INSERT INTO users (role, name_tag, email, password, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $role, $name_tag, $email, $password_hash, $status);
            $stmt->execute();

            $user_id = $conn->insert_id;
            $stmt->close();

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

            $_SESSION["user_id"] = $user_id;
            $_SESSION["email"] = $email;
            $_SESSION["role"] = $role;
            $_SESSION["name_tag"] = $name_tag;

            if ($role == "patient") {
                redirect_to("../dashboard/patient.php");
            } else {
                redirect_to("../dashboard/hospital.php");
            }
        } catch (Exception $e) {
            $conn->rollback();
            $errors["email"] = "Account could not be created. Please try again.";
        }
    }
}
