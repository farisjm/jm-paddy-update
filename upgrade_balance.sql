-- JM PADDY upgrade migration (run once in phpMyAdmin after selecting jm_paddy)
ALTER TABLE farmers ADD COLUMN opening_balance DECIMAL(12,2) NOT NULL DEFAULT 0.00;
ALTER TABLE bills ADD COLUMN paid_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00;
ALTER TABLE bills ADD COLUMN previous_balance DECIMAL(12,2) NOT NULL DEFAULT 0.00;
ALTER TABLE bills ADD COLUMN balance_due DECIMAL(12,2) NOT NULL DEFAULT 0.00;
CREATE TABLE IF NOT EXISTS bill_payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  bill_id INT NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  payment_date DATE NOT NULL,
  note VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_bill_payments_bill_id (bill_id),
  CONSTRAINT fk_bill_payments_bill FOREIGN KEY (bill_id) REFERENCES bills(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
