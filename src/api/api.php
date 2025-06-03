<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$action = $_GET['action'] ?? '';
$userId = $_GET['userId'] ?? null;

switch ($action) {
    case 'send':
        $data = json_decode(file_get_contents('php://input'), true);
        $senderId = $data['sender_id'];
        $receiverId = $data['receiver_id'];
        $content = $conn->real_escape_string($data['content']);
        $timestamp = $data['timestamp'];

        $sql = "INSERT INTO messages (sender_id, receiver_id, content, timestamp) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiss", $senderId, $receiverId, $content, $timestamp);
        $stmt->execute();
        echo json_encode(['status' => 'success']);
        break;

    case 'get':
        $sql = "SELECT * FROM messages WHERE sender_id = ? OR receiver_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $userId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $messages = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($messages);
        break;

    case 'get_users':
        $adminId = $_GET['adminId'] ?? null;
        $sql = "SELECT id, email FROM users WHERE id != ? AND login_type != 'admin'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $adminId);
        $stmt->execute();
        $result = $stmt->get_result();
        $users = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($users);
        break;
}