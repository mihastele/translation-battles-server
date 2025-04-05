<?php
// Enable CORS for development
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST data
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Validate lobby data
    if (isset($data['name']) && !empty($data['name']) &&
        isset($data['gameMode']) && !empty($data['gameMode'])) {

        $lobbyName = trim($data['name']);
        $gameMode = $data['gameMode'];
        $maxPlayers = isset($data['maxPlayers']) ? intval($data['maxPlayers']) : 4;

        // Simple validation
        if (strlen($lobbyName) < 3) {
            echo json_encode(['success' => false, 'message' => 'Lobby name must be at least 3 characters long']);
            exit();
        }

        // Get lobbies from JSON file
        $lobbiesFile = 'lobbies.json';

        if (file_exists($lobbiesFile)) {
            $lobbies = json_decode(file_get_contents($lobbiesFile), true);
        } else {
            $lobbies = [];
        }

        // Generate a unique lobby ID
        $lobbyId = 'lobby_' . uniqid();

        // Create new lobby
        $newLobby = [
            'id' => $lobbyId,
            'name' => $lobbyName,
            'host' => $_SESSION['user_id'],
            'players' => [
                [
                    'id' => $_SESSION['user_id'],
                    'username' => $_SESSION['username'],
                    'status' => 'ready'
                ]
            ],
            'gameMode' => $gameMode,
            'maxPlayers' => $maxPlayers,
            'status' => 'waiting',
            'created' => date('Y-m-d H:i:s')
        ];

        // Add lobby to the list
        $lobbies[] = $newLobby;

        // Save the updated lobby list
        file_put_contents($lobbiesFile, json_encode($lobbies, JSON_PRETTY_PRINT));

        // Return success response
        echo json_encode(['success' => true, 'lobby' => $newLobby]);
        exit();
    } else {
        echo json_encode(['success' => false, 'message' => 'Lobby name and game mode are required']);
        exit();
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}
?>