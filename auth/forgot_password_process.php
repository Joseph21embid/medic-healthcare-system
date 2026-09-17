<?php

include_once __DIR__ . "/../includes/session.php";
include_once __DIR__ . "/../includes/functions.php";
include_once __DIR__ . "/../config/db.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . "/../vendor/autoload.php";

$email = "";
$otp = "";
$password = "";
$confirm_password = "";
$success_message = "";
$dev_message = "";

$errors = array(
    "email" => "",
    "otp" => "",
    "password" => "",
    "confirm_password" => "",
);

function send_password_reset_otp($email, $otp)
{
    $mail_config_path = __DIR__ . "/../config/mail.php";

    if (!file_exists($mail_config_path)) {
        return false;
    }

    $mail_config = include $mail_config_path;

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $mail_config["host"];
        $mail->SMTPAuth = true;
        $mail->Username = $mail_config["username"];
        $mail->Password = $mail_config["password"];

        if ($mail_config["encryption"] == "ssl") {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }

        $mail->Port = $mail_config["port"];
        $mail->setFrom($mail_config["from_email"], $mail_config["from_name"]);
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = "Medic password reset OTP";
        $mail->Body = "
            <h2>Password reset OTP</h2>
            <p>Your Medic password reset OTP is:</p>
            <h1 style='letter-spacing: 6px;'>{$otp}</h1>
            <p>This code expires in 10 minutes.</p>
            <p>If you did not request this, please ignore this email.</p>
        ";
        $mail->AltBody = "Your Medic password reset OTP is: " . $otp . ". This code expires in 10 minutes.";
        $mail->send();

        return true;
    } catch (Exception $e) {
        return false;
    }
}

if (is_post_request() && isset($_POST["request_otp"])) {
    if (isset($_POST["email"])) {
        $email = clean_input($_POST["email"]);
    }

    if (empty($email)) {
        $errors["email"] = "Enter your email address";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors["email"] = "Enter a valid email address";
    }

    if (!array_filter($errors)) {
        $stmt = $conn->prepare("SELECT id, email, auth_provider FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($user_id, $saved_email, $auth_provider);
            $stmt->fetch();

            if ($auth_provider == "google") {
                $errors["email"] = "This account uses Google sign-in. Please continue with Google.";
            } else {
                $otp = (string) random_int(100000, 999999);
                $otp_expires_at = date("Y-m-d H:i:s", time() + 600);

                $update_stmt = $conn->prepare("UPDATE users SET otp_code = ?, otp_expires_at = ?, otp_verified = 0 WHERE id = ?");
                $update_stmt->bind_param("ssi", $otp, $otp_expires_at, $user_id);

                if ($update_stmt->execute()) {
                    $_SESSION["reset_user_id"] = $user_id;
                    $_SESSION["reset_email"] = $saved_email;

                    if (send_password_reset_otp($saved_email, $otp)) {
                        $success_message = "A password reset OTP has been sent to your email.";
                    } else {
                        $success_message = "OTP generated. Email sending is not configured on this local XAMPP setup.";
                        $dev_message = "Development OTP: " . $otp;
                    }
                } else {
                    $errors["email"] = "OTP could not be generated. Please try again.";
                }

                $update_stmt->close();
            }
        } else {
            $errors["email"] = "No account was found with this email address";
        }

        $stmt->close();
    }
}

if (is_post_request() && isset($_POST["verify_otp"])) {
    if (isset($_POST["otp"])) {
        $otp = clean_input($_POST["otp"]);
    }

    if (empty($otp)) {
        $errors["otp"] = "Enter the OTP";
    } elseif (!preg_match("/^[0-9]{6}$/", $otp)) {
        $errors["otp"] = "OTP must be 6 digits";
    }

    if (!isset($_SESSION["reset_user_id"])) {
        $errors["otp"] = "Start the password reset process again.";
    }

    if (!array_filter($errors)) {
        $user_id = (int) $_SESSION["reset_user_id"];
        $stmt = $conn->prepare("SELECT otp_code, otp_expires_at FROM users WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($saved_otp, $otp_expires_at);

        if ($stmt->fetch()) {
            if (empty($saved_otp) || empty($otp_expires_at)) {
                $errors["otp"] = "No active OTP was found. Request a new code.";
            } elseif (strtotime($otp_expires_at) < time()) {
                $errors["otp"] = "OTP has expired. Request a new code.";
            } elseif ($saved_otp != $otp) {
                $errors["otp"] = "Incorrect OTP";
            } else {
                $stmt->close();

                $update_stmt = $conn->prepare("UPDATE users SET otp_verified = 1 WHERE id = ?");
                $update_stmt->bind_param("i", $user_id);
                $update_stmt->execute();
                $update_stmt->close();

                redirect_to("reset-password.php");
            }
        } else {
            $errors["otp"] = "Account could not be found.";
        }

        if (isset($stmt) && $stmt instanceof mysqli_stmt) {
            $stmt->close();
        }
    }
}

if (is_post_request() && isset($_POST["reset_password"])) {
    if (isset($_POST["password"])) {
        $password = $_POST["password"];
    }

    if (isset($_POST["confirm_password"])) {
        $confirm_password = $_POST["confirm_password"];
    }

    if (!isset($_SESSION["reset_user_id"])) {
        $errors["password"] = "Start the password reset process again.";
    }

    if (empty($password)) {
        $errors["password"] = "Enter a new password";
    } elseif (strlen($password) < 8 || !preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/", $password)) {
        $errors["password"] = "Password must be at least 8 characters and include uppercase, lowercase, and a number";
    }

    if (empty($confirm_password)) {
        $errors["confirm_password"] = "Confirm your new password";
    } elseif ($confirm_password != $password) {
        $errors["confirm_password"] = "Passwords do not match";
    }

    if (!array_filter($errors)) {
        $user_id = (int) $_SESSION["reset_user_id"];
        $verified_stmt = $conn->prepare("SELECT otp_verified FROM users WHERE id = ? LIMIT 1");
        $verified_stmt->bind_param("i", $user_id);
        $verified_stmt->execute();
        $verified_stmt->bind_result($otp_verified);

        if ($verified_stmt->fetch()) {
            $verified_stmt->close();

            if ($otp_verified != 1) {
                $errors["password"] = "Verify your OTP before resetting your password.";
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $update_stmt = $conn->prepare("UPDATE users SET password = ?, otp_code = NULL, otp_expires_at = NULL, otp_verified = 0 WHERE id = ?");
                $update_stmt->bind_param("si", $password_hash, $user_id);

                if ($update_stmt->execute()) {
                    unset($_SESSION["reset_user_id"]);
                    unset($_SESSION["reset_email"]);

                    $_SESSION["password_reset_success"] = "Password reset successful. You can now sign in.";
                    redirect_to("login.php");
                } else {
                    $errors["password"] = "Password could not be reset. Please try again.";
                }

                $update_stmt->close();
            }
        } else {
            $verified_stmt->close();
            $errors["password"] = "Account could not be found.";
        }
    }
}
