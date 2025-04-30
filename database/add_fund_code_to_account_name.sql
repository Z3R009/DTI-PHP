-- Add a fund_code column to the account_name table
ALTER TABLE `account_name` 
ADD COLUMN `fund_code` VARCHAR(20) NOT NULL DEFAULT '01101101' AFTER `type`;

-- Update EMDS account types with default fund code
UPDATE `account_name` SET `fund_code` = '01101101' WHERE `type` = 'EMDS';

-- Update REGULAR LCCA account types with different fund code
UPDATE `account_name` SET `fund_code` = '01091201' WHERE `type` = 'REGULAR LCCA';

-- You can update specific accounts with different fund codes if needed
-- For example:
-- UPDATE `account_name` SET `fund_code` = '01201101' WHERE `account_id` = 3; 