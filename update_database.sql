-- Add OTP and reset token columns to users table
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS otp VARCHAR(6) NULL,
ADD COLUMN IF NOT EXISTS otp_created_at TIMESTAMP NULL,
ADD COLUMN IF NOT EXISTS reset_token VARCHAR(64) NULL,
ADD COLUMN IF NOT EXISTS reset_token_created_at TIMESTAMP NULL;
