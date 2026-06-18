<?php

include_once __DIR__ . "/../config/db.php";

$user_id = $_SESSION["user_id"];

$patient = array(
    "id" => 0,
    "health_id" => "Not assigned",
    "nhis_number" => "Not provided",
    "full_name" => $_SESSION["name_tag"],
    "gender" => "",
    "blood_group" => "Not provided",
    "genotype" => "Not provided",
    "allergies" => "Not provided",
    "chronic_conditions" => "Not provided",
    "current_medications" => "Not provided",
    "emergency_contact_name" => "",
    "emergency_contact_phone" => "",
    "profile_photo" => "",
    "profile_completed" => 0,
);

$nhis_number_column_exists = false;
$profile_photo_column_exists = false;
$profile_completion = 0;
$active_medications_count = 0;
$next_appointment_text = "No appointment yet";
$next_appointment_hospital = "No hospital selected";
$next_appointment_reason = "Book a consultation when you are ready";
$next_appointment_image = "https://images.unsplash.com/photo-1587351021759-3e566b6af7cc?auto=format&fit=crop&w=300&q=80";
$latest_checkup_date = "No checkup yet";
$latest_checkup_hospital = "No organization yet";
$latest_checkup_possible_illness = "No recent possible illness";
$latest_checkup_result_status = "No result yet";
$latest_checkup_medication_status = "No medication status yet";
$latest_checkup_vitals = "No vitals recorded yet";
$avatar_type = "emoji";
$avatar_value = "🙂";
$avatar_alt = "Default individual avatar";
$avatar_value = "&#128578;";

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
    $patient_stmt = $conn->prepare("SELECT id, health_id, full_name, gender, blood_group, genotype, allergies, chronic_conditions, current_medications, emergency_contact_name, emergency_contact_phone, profile_photo, profile_completed FROM patients WHERE user_id = ? LIMIT 1");
    $patient_stmt->bind_param("i", $user_id);
    $patient_stmt->execute();
    $patient_stmt->bind_result($patient_id, $health_id, $full_name, $gender, $blood_group, $genotype, $allergies, $chronic_conditions, $current_medications, $emergency_contact_name, $emergency_contact_phone, $profile_photo, $profile_completed);
} else {
    $patient_stmt = $conn->prepare("SELECT id, health_id, full_name, gender, blood_group, genotype, allergies, chronic_conditions, current_medications, emergency_contact_name, emergency_contact_phone, profile_completed FROM patients WHERE user_id = ? LIMIT 1");
    $patient_stmt->bind_param("i", $user_id);
    $patient_stmt->execute();
    $patient_stmt->bind_result($patient_id, $health_id, $full_name, $gender, $blood_group, $genotype, $allergies, $chronic_conditions, $current_medications, $emergency_contact_name, $emergency_contact_phone, $profile_completed);
}

if ($patient_stmt->fetch()) {
    $patient["id"] = $patient_id;

    if (!empty($health_id)) {
        $patient["health_id"] = $health_id;
    }

    if (!empty($full_name)) {
        $patient["full_name"] = $full_name;
    }

    if (!empty($gender)) {
        $patient["gender"] = $gender;
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

    if ($profile_photo_column_exists && !empty($profile_photo)) {
        $patient["profile_photo"] = $profile_photo;
    }

    $patient["profile_completed"] = $profile_completed;
}

$patient_stmt->close();

if ($nhis_number_column_exists) {
    $nhis_stmt = $conn->prepare("SELECT nhis_number FROM patients WHERE user_id = ? LIMIT 1");
    $nhis_stmt->bind_param("i", $user_id);
    $nhis_stmt->execute();
    $nhis_stmt->bind_result($nhis_number);

    if ($nhis_stmt->fetch()) {
        if (!empty($nhis_number)) {
            $patient["nhis_number"] = $nhis_number;
        }
    }

    $nhis_stmt->close();
}

if (!empty($patient["profile_photo"])) {
    $avatar_type = "image";
    $avatar_value = "../public/" . $patient["profile_photo"];
    $avatar_alt = "Individual profile photo";
} else {
    if ($patient["gender"] == "male") {
        $avatar_value = "👨";
        $avatar_alt = "Default male avatar";
    } elseif ($patient["gender"] == "female") {
        $avatar_value = "👩";
        $avatar_alt = "Default female avatar";
    } else {
        $avatar_value = "🙂";
        $avatar_alt = "Default individual avatar";
    }

    if ($patient["gender"] == "male") {
        $avatar_value = "&#128104;";
    } elseif ($patient["gender"] == "female") {
        $avatar_value = "&#128105;";
    } else {
        $avatar_value = "&#128578;";
    }
}

$completed_fields = 0;
$total_fields = 9;

if (!empty($patient["full_name"]) && $patient["full_name"] != "Not provided") {
    $completed_fields = $completed_fields + 1;
}

if (!empty($patient["blood_group"]) && $patient["blood_group"] != "Not provided") {
    $completed_fields = $completed_fields + 1;
}

if (!empty($patient["nhis_number"]) && $patient["nhis_number"] != "Not provided") {
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

    $record_stmt = $conn->prepare("SELECT medical_records.visit_date, medical_records.heart_rate, medical_records.blood_pressure, medical_records.temperature, medical_records.weight, medical_records.possible_illness, medical_records.result_status, medical_records.medication_status, hospitals.hospital_name FROM medical_records LEFT JOIN hospitals ON medical_records.hospital_id = hospitals.id WHERE medical_records.patient_id = ? ORDER BY medical_records.visit_date DESC, medical_records.created_at DESC LIMIT 1");
    $record_stmt->bind_param("i", $patient["id"]);
    $record_stmt->execute();
    $record_stmt->bind_result($record_visit_date, $record_heart_rate, $record_blood_pressure, $record_temperature, $record_weight, $record_possible_illness, $record_result_status, $record_medication_status, $record_hospital_name);

    if ($record_stmt->fetch()) {
        if (!empty($record_visit_date)) {
            $latest_checkup_date = date("M d, Y", strtotime($record_visit_date));
        }

        if (!empty($record_hospital_name)) {
            $latest_checkup_hospital = $record_hospital_name;
        }

        if (!empty($record_possible_illness)) {
            $latest_checkup_possible_illness = $record_possible_illness;
        }

        if ($record_result_status == "awaiting_result") {
            $latest_checkup_result_status = "Awaiting result";
        } elseif ($record_result_status == "result_ready") {
            $latest_checkup_result_status = "Result ready";
        }

        if ($record_medication_status == "not_started") {
            $latest_checkup_medication_status = "Not started";
        } elseif ($record_medication_status == "in_progress") {
            $latest_checkup_medication_status = "In progress";
        } elseif ($record_medication_status == "completed") {
            $latest_checkup_medication_status = "Completed";
        }

        $vital_parts = array();

        if (!empty($record_heart_rate)) {
            $vital_parts[] = "Heart rate: " . $record_heart_rate;
        }

        if (!empty($record_blood_pressure)) {
            $vital_parts[] = "BP: " . $record_blood_pressure;
        }

        if (!empty($record_temperature)) {
            $vital_parts[] = "Temp: " . $record_temperature;
        }

        if (!empty($record_weight)) {
            $vital_parts[] = "Weight: " . $record_weight;
        }

        if (!empty($vital_parts)) {
            $latest_checkup_vitals = implode(" | ", $vital_parts);
        }
    }

    $record_stmt->close();
}
