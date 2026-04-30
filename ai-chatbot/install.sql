CREATE TABLE chatbot_clients (
  id CHAR(16) PRIMARY KEY,
  public_key CHAR(32) UNIQUE NOT NULL,
  name VARCHAR(190) NOT NULL,
  website_url VARCHAR(255),
  business_email VARCHAR(190),
  notification_email VARCHAR(190),
  bot_name VARCHAR(120) DEFAULT 'AI Assistant',
  welcome_message VARCHAR(255),
  system_prompt MEDIUMTEXT,
  business_context MEDIUMTEXT,
  fallback_message VARCHAR(255),
  lead_capture_enabled TINYINT DEFAULT 1,
  lead_capture_mode ENUM('before_chat','after_interest','manual') DEFAULT 'after_interest',
  status ENUM('active','inactive') DEFAULT 'active',
  monthly_message_limit INT DEFAULT 1000,
  created_at DATETIME(6),
  updated_at DATETIME(6)
);
CREATE TABLE chatbot_conversations (
  id CHAR(16) PRIMARY KEY,
  client_id CHAR(16) NOT NULL,
  visitor_id CHAR(32) NOT NULL,
  visitor_ip_hash CHAR(64),
  user_agent VARCHAR(255),
  page_url VARCHAR(500),
  referrer VARCHAR(500),
  lead_name VARCHAR(190), lead_email VARCHAR(190), lead_phone VARCHAR(80),
  lead_status ENUM('none','captured','sent') DEFAULT 'none',
  created_at DATETIME(6), updated_at DATETIME(6),
  INDEX (client_id), INDEX (visitor_id)
);
CREATE TABLE chatbot_messages (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  conversation_id CHAR(16) NOT NULL,
  role ENUM('user','assistant','system') NOT NULL,
  content MEDIUMTEXT NOT NULL,
  token_input INT DEFAULT 0,
  token_output INT DEFAULT 0,
  created_at DATETIME(6),
  INDEX(conversation_id)
);
CREATE TABLE chatbot_leads (
  id CHAR(16) PRIMARY KEY,
  client_id CHAR(16) NOT NULL,
  conversation_id CHAR(16) NOT NULL,
  name VARCHAR(190), email VARCHAR(190), phone VARCHAR(80),
  message MEDIUMTEXT, page_url VARCHAR(500), sent_to_client_at DATETIME(6), created_at DATETIME(6)
);
CREATE TABLE chatbot_admin_users (
  id CHAR(16) PRIMARY KEY,
  email VARCHAR(190) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at DATETIME(6)
);
