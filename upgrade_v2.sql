-- JM PADDY v2 database upgrade
-- In phpMyAdmin, select the jm_paddy database and import this file.
-- This script expects the existing farmers, bills and bill_weights tables.
ALTER TABLE farmers ADD COLUMN IF NOT EXISTS opening_balance DECIMAL(12,2) NOT NULL DEFAULT 0.00;
ALTER TABLE bills ADD COLUMN IF NOT EXISTS paid_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00;
ALTER TABLE bills ADD COLUMN IF NOT EXISTS previous_balance DECIMAL(12,2) NOT NULL DEFAULT 0.00;
ALTER TABLE bills ADD COLUMN IF NOT EXISTS balance_due DECIMAL(12,2) NOT NULL DEFAULT 0.00;

CREATE TABLE IF NOT EXISTS bill_payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  bill_id INT NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  payment_date DATE NOT NULL,
  note VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_bill_payments_bill_id (bill_id),
  CONSTRAINT fk_bill_payments_bill_v2 FOREIGN KEY (bill_id) REFERENCES bills(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS district_rates (
  district VARCHAR(80) PRIMARY KEY,
  divisor DECIMAL(10,2) NOT NULL DEFAULT 73.00,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO district_rates(district,divisor) VALUES
 ('Vavuniya',72),('Mannar',73),('Mullaitivu',73),('Kilinochchi',73),('Jaffna',73)
ON DUPLICATE KEY UPDATE district=VALUES(district);

CREATE TABLE IF NOT EXISTS paddy_prices (
  paddy_type VARCHAR(80) PRIMARY KEY,
  price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO paddy_prices(paddy_type,price) VALUES
 ('Samba',0),('Keeri Samba',0),('Nadu',0)
ON DUPLICATE KEY UPDATE paddy_type=VALUES(paddy_type);

-- Existing bill totals/payment fields are preserved. Existing bills will keep their recorded values.
