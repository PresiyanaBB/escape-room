<?php
class Database {
    private $pdo;

    public function __construct() {
        $this->pdo = new PDO("mysql:host=localhost;dbname=escape_room;charset=utf8mb4", "escape_user", "escapepass");
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    public function commit() {
        return $this->pdo->commit();
    }

    public function rollBack() {
        return $this->pdo->rollBack();
    }

    public function findUserByUsername($username) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public function createUser($email, $username, $password) {
        $stmt = $this->pdo->prepare("INSERT INTO users (email, username, password) VALUES (?, ?, ?)");
        return $stmt->execute([$email, $username, $password]);
    }

    public function findTeamByName($teamName) {
        $stmt = $this->pdo->prepare("SELECT id FROM teams WHERE name = ?");
        $stmt->execute([$teamName]);
        return $stmt->fetchColumn();
    }

    public function createTeam($teamName) {
        $stmt = $this->pdo->prepare("INSERT INTO teams (name) VALUES (?)");
        $stmt->execute([$teamName]);
        return $this->pdo->lastInsertId();
    }

    public function addUserToTeam($teamId, $userId) {
        $stmt = $this->pdo->prepare("INSERT IGNORE INTO team_users (team_id, user_id) VALUES (?, ?)");
        return $stmt->execute([$teamId, $userId]);
    }

    public function removeUserFromTeam($userId) {
        $stmt = $this->pdo->prepare("DELETE FROM team_users WHERE user_id = ?");
        return $stmt->execute([$userId]);
    }

    public function getTeamForUser($userId) {
        $stmt = $this->pdo->prepare("SELECT team_id FROM team_users WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }

    public function getTeamName($teamId) {
        $stmt = $this->pdo->prepare("SELECT name FROM teams WHERE id = ?");
        $stmt->execute([$teamId]);
        return $stmt->fetchColumn();
    }

    public function getRoom() {
        return $this->pdo->query("SELECT * FROM rooms LIMIT 1")->fetch();
    }

    public function getRoomById($roomId) {
        $stmt = $this->pdo->prepare("SELECT * FROM rooms WHERE id = ?");
        $stmt->execute([$roomId]);
        return $stmt->fetch();
    }

    public function getQuestionsForRoom($roomId) {
        $stmt = $this->pdo->prepare("SELECT * FROM games WHERE room_id = ? ORDER BY id ASC");
        $stmt->execute([$roomId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLeaderboard($roomId = null, $teamId = null) {
        $query = "SELECT r.name, t.name AS team_name, l.time
                FROM leaderboard l 
                JOIN rooms r ON r.id = l.room_id
                JOIN teams t ON t.id = l.team_id
                WHERE 1=1";
        $params = [];

        if ($roomId) {
            $query .= " AND l.room_id = ?";
            $params[] = $roomId;
        }

        if ($teamId) {
            $query .= " AND l.team_id = ?";
            $params[] = $teamId;
        }

        $query .= " ORDER BY l.time ASC";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateLeaderboard($roomId, $teamId, $time) {
        $checkStmt = $this->pdo->prepare("SELECT id FROM leaderboard WHERE room_id = ? AND team_id = ?");
        $checkStmt->execute([$roomId, $teamId]);
        $existing = $checkStmt->fetchColumn();

        if ($existing) {
            $stmt = $this->pdo->prepare("UPDATE leaderboard SET time = ? WHERE id = ?");
            return $stmt->execute([$time, $existing]);
        } else {
            $stmt = $this->pdo->prepare("INSERT INTO leaderboard (room_id, team_id, time) VALUES (?, ?, ?)");
            return $stmt->execute([$roomId, $teamId, $time]);
        }
    }

    public function importRoom($name, $steps, $timeForSolving) {
        $stmt = $this->pdo->prepare("INSERT INTO rooms (name, steps, time_for_solving) VALUES (?, ?, ?)");
        $stmt->execute([$name, $steps, $timeForSolving]);
        return $this->pdo->lastInsertId();
    }

    public function importGame($roomId, $question, $answer, $hint = null) {
        $stmt = $this->pdo->prepare("INSERT INTO games (room_id, question, answer, hint) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$roomId, $question, $answer, $hint]);
    }

    // Export methods
    public function getAllRooms() {
        return $this->pdo->query("SELECT id, name, steps, time_for_solving FROM rooms")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getGamesForRoom($roomId) {
        $stmt = $this->pdo->prepare("SELECT question, answer, hint FROM games WHERE room_id = ? ORDER BY id ASC");
        $stmt->execute([$roomId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLeaderboardForRoom($roomId, $limit = 5) {
        $stmt = $this->pdo->prepare("SELECT t.name, l.time FROM leaderboard as l 
            JOIN teams as t ON t.id = l.team_id 
            WHERE room_id = ? 
            ORDER BY l.time ASC 
            LIMIT " . (int)$limit);
        $stmt->execute([$roomId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllTeams() {
        return $this->pdo->query("SELECT id, name FROM teams")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUsersForTeam($teamId) {
        $stmt = $this->pdo->prepare("SELECT u.username 
            FROM team_users AS tu 
            JOIN users u ON u.id = tu.user_id 
            JOIN teams t ON t.id = tu.team_id 
            WHERE t.id = ? 
            ORDER BY u.username ASC");
        $stmt->execute([$teamId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllUsers() {
        return $this->pdo->query("SELECT id, email, username FROM users")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRoomByName($name) {
        $stmt = $this->pdo->prepare("SELECT * FROM rooms WHERE name = ?");
        $stmt->execute([$name]);
        return $stmt->fetch();
    }

    public function getGameByQuestion($roomId, $question) {
        $stmt = $this->pdo->prepare("SELECT * FROM games WHERE room_id = ? AND question = ?");
        $stmt->execute([$roomId, $question]);
        return $stmt->fetch();
    }
} 