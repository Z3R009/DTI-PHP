-- Add missing columns to the account_name table if they don't exist
-- First check if columns exist and only add them if they don't

-- Add NCA_NO column
ALTER TABLE `account_name` 
ADD COLUMN IF NOT EXISTS `NCA_NO` VARCHAR(50) DEFAULT NULL AFTER `account_number`;

-- Add NCA_DATE column
ALTER TABLE `account_name` 
ADD COLUMN IF NOT EXISTS `NCA_DATE` DATE DEFAULT NULL AFTER `NCA_NO`;

-- Add FUND_SOURCE column
ALTER TABLE `account_name` 
ADD COLUMN IF NOT EXISTS `FUND_SOURCE` VARCHAR(100) DEFAULT NULL AFTER `NCA_DATE`;

-- Add Description column
ALTER TABLE `account_name` 
ADD COLUMN IF NOT EXISTS `Description` TEXT DEFAULT NULL AFTER `FUND_SOURCE`;

-- For MySQL versions that don't support IF NOT EXISTS for ADD COLUMN:
-- Uncomment these lines and comment out the ones above if you encounter errors

/*
-- Check if NCA_NO column exists
SET @existsNCA = (SELECT COUNT(*) FROM information_schema.columns 
                  WHERE table_name = 'account_name' AND column_name = 'NCA_NO');
SET @query = IF(@existsNCA = 0, 
                'ALTER TABLE `account_name` ADD COLUMN `NCA_NO` VARCHAR(50) DEFAULT NULL AFTER `account_number`', 
                'SELECT "NCA_NO column already exists"');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check if NCA_DATE column exists
SET @existsNCADate = (SELECT COUNT(*) FROM information_schema.columns 
                      WHERE table_name = 'account_name' AND column_name = 'NCA_DATE');
SET @query = IF(@existsNCADate = 0, 
                'ALTER TABLE `account_name` ADD COLUMN `NCA_DATE` DATE DEFAULT NULL AFTER `NCA_NO`', 
                'SELECT "NCA_DATE column already exists"');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check if FUND_SOURCE column exists
SET @existsFundSource = (SELECT COUNT(*) FROM information_schema.columns 
                         WHERE table_name = 'account_name' AND column_name = 'FUND_SOURCE');
SET @query = IF(@existsFundSource = 0, 
                'ALTER TABLE `account_name` ADD COLUMN `FUND_SOURCE` VARCHAR(100) DEFAULT NULL AFTER `NCA_DATE`', 
                'SELECT "FUND_SOURCE column already exists"');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check if Description column exists
SET @existsDescription = (SELECT COUNT(*) FROM information_schema.columns 
                          WHERE table_name = 'account_name' AND column_name = 'Description');
SET @query = IF(@existsDescription = 0, 
                'ALTER TABLE `account_name` ADD COLUMN `Description` TEXT DEFAULT NULL AFTER `FUND_SOURCE`', 
                'SELECT "Description column already exists"');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
*/ 