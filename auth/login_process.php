<?php

include_once __DIR__ . "/../includes/session.php";
include_once __DIR__ . "/../includes/functions.php";
include_once __DIR__ . "/../config/db.php";

$email = "";
$password = "";

$errors = array(
    "email" => "",
    "password" => "",
);

if (is_post_request() && isset($_POST["login_submit"])) {
    if (isset($_POST["email"])) {
        $email = clean_input($_POST["email"]);
    }

    if (isset($_POST["password"])) {
        $password = $_POST["password"];
    }

    if (empty($email)) {
        $errors["email"] = "Enter your email address";
    }

    if (empty($password)) {
        $errors["password"] = "Enter your password";
    }

    if (!array_filter($errors)) {
        $stmt = $conn->prepare("SELECT id, role, name_tag, password, status FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($user_id, $role, $name_tag, $saved_password, $status);
            $stmt->fetch();

            if ($status == "suspended") {
                $errors["email"] = "This account has been suspended";
            } elseif (password_verify($password, $saved_password)) {
                $_SESSION["user_id"] = $user_id;
                $_SESSION["email"] = $email;
                $_SESSION["role"] = $role;
                $_SESSION["name_tag"] = $name_tag;

                if ($role == "patient") {
                    redirect_to("../dashboard/patient.php");
                } elseif ($role == "hospital") {
                    redirect_to("../dashboard/hospital.php");
                } elseif ($role == "admin") {
                    redirect_to("../dashboard/admin.php");
                } else {
                    $errors["email"] = "Unknown account role";
                }
            } else {
                $errors["password"] = "Incorrect password";
            }
        } else {
            $errors["email"] = "No account found with this email";
        }

        $stmt->close();
    }
}
