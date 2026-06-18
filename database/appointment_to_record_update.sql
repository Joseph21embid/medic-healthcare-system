ALTER TABLE medical_records
ADD COLUMN IF NOT EXISTS appointment_id INT NULL AFTER hospital_id,
ADD INDEX IF NOT EXISTS idx_medical_records_appointment_id (appointment_id);
