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

    // Validate lobby ID
    if (isset($data['lobbyId']) && !empty($data['lobbyId'])) {
        $lobbyId = $data['lobbyId'];

        // Get lobbies from JSON file
        $lobbiesFile = 'lobbies.json';

        if (file_exists($lobbiesFile)) {
            $lobbies = json_decode(file_get_contents($lobbiesFile), true);
        } else {
            $lobbies = [];
            echo json_encode(['success' => false, 'message' => 'Lobby not found']);
            exit();
        }

        // Find the lobby
        $lobbyIndex = -1;
        foreach ($lobbies as $index => $lobby) {
            if ($lobby['id'] === $lobbyId) {
                $lobbyIndex = $index;
                break;
            }
        }

        if ($lobbyIndex === -1) {
            echo json_encode(['success' => false, 'message' => 'Lobby not found']);
            exit();
        }

        // Check if lobby is full
        if (count($lobbies[$lobbyIndex]['players']) >= $lobbies[$lobbyIndex]['maxPlayers']) {
            echo json_encode(['success' => false, 'message' => 'Lobby is full']);
            exit();
        }

        // Check if user is already in the lobby
        foreach ($lobbies[$lobbyIndex]['players'] as $player) {
            if ($player['id'] === $_SESSION['user_id']) {
                echo json_encode(['success' => true, 'lobby' => $lobbies[$lobbyIndex]]);
                exit();
            }
        }

        // Add user to the lobby
        $lobbies[$lobbyIndex]['players'][] = [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'status' => 'not-ready'
        ];

        // Save the updated lobby list
        file_put_contents($lobbiesFile, json_encode($lobbies, JSON_PRETTY_PRINT));

        // Return success response
        echo json_encode(['success' => true, 'lobby' => $lobbies[$lobbyIndex]]);
        exit();
    } else {
        echo json_encode(['success' => false, 'message' => 'Lobby ID is required']);
        exit();
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}
?>