CREATE USER 'escape_user'@'localhost' IDENTIFIED BY 'escapepass';
GRANT ALL PRIVILEGES ON escape_room.* TO 'escape_user'@'localhost';
FLUSH PRIVILEGES;

CREATE DATABASE IF NOT EXISTS escape_room;
USE escape_room;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255),
  username VARCHAR(255) UNIQUE,
  password VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS teams (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) UNIQUE
);

CREATE TABLE IF NOT EXISTS team_users (
  team_id INT,
  user_id INT,
  PRIMARY KEY (team_id, user_id),
  FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS rooms (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) UNIQUE NOT NULL,
  steps INT NOT NULL,
  time_for_solving TIME NOT NULL
);

CREATE TABLE IF NOT EXISTS games (
  id INT AUTO_INCREMENT PRIMARY KEY,
  room_id INT NOT NULL,
  question TEXT NOT NULL,
  answer TEXT NOT NULL,
  hint TEXT,
  FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS leaderboard (
  id INT AUTO_INCREMENT PRIMARY KEY,
  room_id INT NOT NULL,
  team_id INT NOT NULL,
  time TIME NOT NULL,
  FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
  FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE
);

INSERT INTO rooms (name, steps, time_for_solving)
VALUES ('Mystic Garden', 3, '00:20:00');

SET @room_id = LAST_INSERT_ID();

INSERT INTO games (room_id, question, answer, hint) VALUES
  (@room_id,
   'I’m tall when I’m young and short when I’m old. What am I?',
   'candle',
   'It burns down over time.'),
  (@room_id,
   'What has keys but can’t open locks?',
   'keyboard',
   'You type on it every day.'),
  (@room_id,
   'What can travel around the world while staying in the same spot?',
   'stamp',
   'It moves with letters.');