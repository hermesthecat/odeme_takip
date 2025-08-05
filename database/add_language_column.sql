-- Add language column to users table if it doesn't exist
-- This allows storing user language preferences in the database

-- Check if language column exists, if not add it
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS language VARCHAR(5) DEFAULT 'tr' 
COMMENT 'User preferred language (tr, en, etc.)';

-- Update existing users to have default language based on their base_currency
UPDATE users 
SET language = CASE 
    WHEN base_currency = 'TRY' THEN 'tr'
    ELSE 'en'
END 
WHERE language IS NULL OR language = '';

-- Create index for better performance on language queries
CREATE INDEX IF NOT EXISTS idx_users_language ON users(language);