-- JM PADDY base schema for a new database.
-- Select the jm_paddy database before importing this file.
CREATE TABLE IF NOT EXISTS farmers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  phone VARCHAR(40) NULL,
  address VARCHAR(255) NULL,
  opening_balance DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_farmers_phone (phone),
  INDEX idx_farmers_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bills (
  id INT AUTO_INCREMENT PRIMARY KEY,
  bill_no VARCHAR(80) NOT NULL UNIQUE,
  farmer_id INT NOT NULL,
  bill_date DATE NOT NULL,
  district VARCHAR(80) NOT NULL,
  district_rate DECIMAL(10,2) NOT NULL,
  paddy_type VARCHAR(80) NOT NULL,
  total_weight DECIMAL(12,3) NOT NULL DEFAULT 0,
  net_weight DECIMAL(12,4) NOT NULL DEFAULT 0,
  price DECIMAL(12,2) NOT NULL DEFAULT 0,
  total_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  paid_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  previous_balance DECIMAL(12,2) NOT NULL DEFAULT 0,
  balance_due DECIMAL(12,2) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_bills_farmer_id (farmer_id),
  INDEX idx_bills_bill_date (bill_date),
  CONSTRAINT fk_bills_farmer_base FOREIGN KEY (farmer_id) REFERENCES farmers(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bill_weights (
  id INT AUTO_INCREMENT PRIMARY KEY,
  bill_id INT NOT NULL,
  weight_no INT NOT NULL,
  weight DECIMAL(12,3) NOT NULL,
  INDEX idx_bill_weights_bill_id (bill_id),
  CONSTRAINT fk_bill_weights_bill_base FOREIGN KEY (bill_id) REFERENCES bills(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
