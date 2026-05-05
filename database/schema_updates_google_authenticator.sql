-- Add Google Authenticator secret storage to users table
ALTER TABLE users
ADD COLUMN google_auth_secret VARCHAR(64) NULL AFTER password;
