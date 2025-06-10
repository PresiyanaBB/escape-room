-- Create Database
IF NOT EXISTS (SELECT * FROM sys.databases WHERE name = 'escape_room')
BEGIN
    CREATE DATABASE escape_room;
END;
GO
USE escape_room;
GO

IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'users')
BEGIN
    CREATE TABLE users (
        id INT IDENTITY(1,1) PRIMARY KEY,
        email VARCHAR(255),
        username VARCHAR(255) UNIQUE,
        password VARCHAR(255)
    );
END;
GO

IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'teams')
BEGIN
    CREATE TABLE teams (
        id INT IDENTITY(1,1) PRIMARY KEY,
        name VARCHAR(255) UNIQUE
    );
END;
GO

IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'team_users')
BEGIN
    CREATE TABLE team_users (
        team_id INT,
        user_id INT,
        PRIMARY KEY (team_id, user_id),
        FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    );
END;
GO

IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'rooms')
BEGIN
    CREATE TABLE rooms (
        id INT IDENTITY(1,1) PRIMARY KEY,
        name VARCHAR(255) UNIQUE NOT NULL,
        steps INT NOT NULL,
        time_for_solving TIME NOT NULL
    );
END;
GO

IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'games')
BEGIN
    CREATE TABLE games (
        id INT IDENTITY(1,1) PRIMARY KEY,
        room_id INT NOT NULL,
        question TEXT NOT NULL,
        answer TEXT NOT NULL,
        hint TEXT,
        FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
    );
END;
GO

IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'leaderboard')
BEGIN
    CREATE TABLE leaderboard (
        id INT IDENTITY(1,1) PRIMARY KEY,
        room_id INT NOT NULL,
        team_id INT NOT NULL,
        time TIME NOT NULL,
        FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
        FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE
    );
END;
GO

INSERT INTO rooms (name, steps, time_for_solving)
VALUES ('Mystic Garden', 3, '00:20:00');
GO

DECLARE @room_id INT = SCOPE_IDENTITY();

INSERT INTO games (room_id, question, answer, hint) VALUES
    (@room_id, 'I’m tall when I’m young and short when I’m old. What am I?', 'candle', 'It burns down over time.'),
    (@room_id, 'What has keys but can’t open locks?', 'keyboard', 'You type on it every day.'),
    (@room_id, 'What can travel around the world while staying in the same spot?', 'stamp', 'It moves with letters.');
GO

INSERT INTO users (email, username, password)
VALUES ('admin@admin.bg', 'admin', '$2y$10$sWhnIQ6.Ad4budtdPsEZ.O4Avr9YjqU6aGcjutEAzj97DmytwSd0S');
GO
