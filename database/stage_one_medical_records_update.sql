ALTER TABLE medical_records
ADD COLUMN IF NOT EXISTS visit_type VARCHAR(50) DEFAULT 'walk_in' AFTER hospital_id,
ADD COLUMN IF NOT EXISTS heart_rate VARCHAR(30) NULL AFTER visit_type,
ADD COLUMN IF NOT EXISTS blood_pressure VARCHAR(30) NULL AFTER heart_rate,
ADD COLUMN IF NOT EXISTS temperature VARCHAR(30) NULL AFTER blood_pressure,
ADD COLUMN IF NOT EXISTS weight VARCHAR(30) NULL AFTER temperature,
ADD COLUMN IF NOT EXISTS symptoms TEXT NULL AFTER weight,
ADD COLUMN IF NOT EXISTS possible_illness TEXT NULL AFTER symptoms,
ADD COLUMN IF NOT EXISTS result_status VARCHAR(50) DEFAULT 'awaiting_result' AFTER possible_illness,
ADD COLUMN IF NOT EXISTS result_summary TEXT NULL AFTER result_status,
ADD COLUMN IF NOT EXISTS medication_status VARCHAR(50) DEFAULT 'not_started' AFTER prescription_notes;
