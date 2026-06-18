ALTER TABLE appointments
ADD COLUMN IF NOT EXISTS service_area VARCHAR(120) NULL AFTER hospital_id;
