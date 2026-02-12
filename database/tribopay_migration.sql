-- Add TriboPay columns to gateways table
ALTER TABLE gateways ADD COLUMN tribopay_uri VARCHAR(191) NULL;
ALTER TABLE gateways ADD COLUMN tribopay_cliente_id VARCHAR(191) NULL;
ALTER TABLE gateways ADD COLUMN tribopay_cliente_secret VARCHAR(191) NULL;

-- Create tribo_pay_payments table
CREATE TABLE tribo_pay_payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    payment_id VARCHAR(191) NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    withdrawal_id BIGINT UNSIGNED NULL,
    pix_key VARCHAR(191) NOT NULL,
    pix_type VARCHAR(191) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    observation TEXT NULL,
    status TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
