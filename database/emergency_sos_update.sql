ALTER TABLE emergency_requests
ADD COLUMN IF NOT EXISTS request_type VARCHAR(40) DEFAULT 'self' AFTER hospital_id,
ADD COLUMN IF NOT EXISTS victim_name VARCHAR(150) NULL AFTER request_type,
ADD COLUMN IF NOT EXISTS victim_phone VARCHAR(60) NULL AFTER victim_name,
ADD COLUMN IF NOT EXISTS reporter_note TEXT NULL AFTER victim_phone,
ADD COLUMN IF NOT EXISTS scene_photo VARCHAR(255) NULL AFTER reporter_note,
ADD COLUMN IF NOT EXISTS evidence_requested TINYINT(1) DEFAULT 0 AFTER scene_photo;
