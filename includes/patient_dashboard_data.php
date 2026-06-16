<?php

include_once __DIR__ . "/../config/db.php";

$user_id = $_SESSION["user_id"];

$patient = array(
    "id" => 0,
    "health_id" => "Not assigned",
    "full_name" => $_SESSION["name_tag"],
    "blood_group" => "Not provided",
    "genotype" => "Not provided",
    "allergies" => "Not provided",
    "chronic_conditions" => "Not provided",
    "current_medications" => "Not provided",
    "emergency_contact_name" => "",
    "emergency_contact_phone" => "",
    "profile_completed" => 0,
);

$profile_completion = 0;
$active_medications_count = 0;
$next_appointment_text = "No appointment yet";
$next_appointment_hospital = "No hospital selected";
$next_appointment_reason = "Book a consultation when you are ready";
$next_appointment_image = "https://images.unsplash.com/photo-1587351021759-3e566b6af7cc?auto=format&fit=crop&w=300&q=80";

$patient_stmt = $conn->prepare("SELECT id, health_id, full_name, blood_group, genotype, allergies, chronic_conditions, current_medications, emergency_contact_name, emergency_contact_phone, profile_completed FROM patients WHERE user_id = ? LIMIT 1");
$patient_stmt->bind_param("i", $user_id);
$patient_stmt->execute();
$patient_stmt->bind_result($patient_id, $health_id, $full_name, $blood_group, $genotype, $allergies, $chronic_conditions, $current_medications, $emergency_contact_name, $emergency_contact_phone, $profile_completed);

if ($patient_stmt->fetch()) {
    $patient["id"] = $patient_id;

    if (!empty($health_id)) {
        $patient["health_id"] = $health_id;
    }

    if (!empty($full_name)) {
        $patient["full_name"] = $full_name;
    }

    if (!empty($blood_group)) {
        $patient["blood_group"] = $blood_group;
    }

    if (!empty($genotype)) {
        $patient["genotype"] = $genotype;
    }

    if (!empty($allergies)) {
        $patient["allergies"] = $allergies;
    }

    if (!empty($chronic_conditions)) {
        $patient["chronic_conditions"] = $chronic_conditions;
    }

    if (!empty($current_medications)) {
        $patient["current_medications"] = $current_medications;
    }

    if (!empty($emergency_contact_name)) {
        $patient["emergency_contact_name"] = $emergency_contact_name;
    }

    if (!empty($emergency_contact_phone)) {
        $patient["emergency_contact_phone"] = $emergency_contact_phone;
    }

    $patient["profile_completed"] = $profile_completed;
}

$patient_stmt->close();

$completed_fields = 0;
$total_fields = 8;

if (!empty($patient["full_name"]) && $patient["full_name"] != "Not provided") {
    $completed_fields = $completed_fields + 1;
}

if (!empty($patient["blood_group"]) && $patient["blood_group"] != "Not provided") {
    $completed_fields = $completed_fields + 1;
}

if (!empty($patient["genotype"]) && $patient["genotype"] != "Not provided") {
    $completed_fields = $completed_fields + 1;
}

if (!empty($patient["allergies"]) && $patient["allergies"] != "Not provided") {
    $completed_fields = $completed_fields + 1;
}

if (!empty($patient["chronic_conditions"]) && $patient["chronic_conditions"] != "Not provided") {
    $completed_fields = $completed_fields + 1;
}

if (!empty($patient["current_medications"]) && $patient["current_medications"] != "Not provided") {
    $completed_fields = $completed_fields + 1;
}

if (!empty($patient["emergency_contact_name"])) {
    $completed_fields = $completed_fields + 1;
}

if (!empty($patient["emergency_contact_phone"])) {
    $completed_fields = $completed_fields + 1;
}

$profile_completion = round(($completed_fields / $total_fields) * 100);

if ($patient["id"] > 0) {
    $med_stmt = $conn->prepare("SELECT COUNT(*) FROM medications WHERE patient_id = ? AND status = 'active'");
    $med_stmt->bind_param("i", $patient["id"]);
    $med_stmt->execute();
    $med_stmt->bind_result($active_medications_count);
    $med_stmt->fetch();
    $med_stmt->close();

    $appointment_stmt = $conn->prepare("SELECT appointments.appointment_date, appointments.appointment_time, appointments.reason, hospitals.hospital_name FROM appointments INNER JOIN hospitals ON appointments.hospital_id = hospitals.id WHERE appointments.patient_id = ? AND appointments.status IN ('pending', 'approved') AND appointments.appointment_date >= CURDATE() ORDER BY appointments.appointment_date ASC, appointments.appointment_time ASC LIMIT 1");
    $appointment_stmt->bind_param("i", $patient["id"]);
    $appointment_stmt->execute();
    $appointment_stmt->bind_result($appointment_date, $appointment_time, $appointment_reason, $hospital_name);

    if ($appointment_stmt->fetch()) {
        $next_appointment_hospital = $hospital_name;

        if (!empty($appointment_reason)) {
            $next_appointment_reason = $appointment_reason;
        } else {
            $next_appointment_reason = "Consultation";
        }

        if (!empty($appointment_time)) {
            $next_appointment_text = date("M d, Y", strtotime($appointment_date)) . " at " . date("h:i A", strtotime($appointment_time));
        } else {
            $next_appointment_text = date("M d, Y", strtotime($appointment_date));
        }
    }

    $appointment_stmt->close();
}

