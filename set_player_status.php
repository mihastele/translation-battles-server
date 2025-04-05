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

    // Validate data
    if (isset($data['lobbyId']) && !empty($data['lobbyId']) &&
        isset($data['status']) && is_bool($data['status'])) {

        $lobbyId = $data['lobbyId'];
        $isReady = $data['status'];

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

        // Find the player in the lobby
        $playerFound = false;
        foreach ($lobbies[$lobbyIndex]['players'] as $playerIndex => $player) {
            if ($player['id'] === $_SESSION['user_id']) {
                $lobbies[$lobbyIndex]['players'][$playerIndex]['status'] = $isReady ? 'ready' : 'not-ready';
                $playerFound = true;
                break;
            }
        }

        if (!$playerFound) {
            echo json_encode(['success' => false, 'message' => 'Player not in lobby']);
            exit();
        }

        // Save the updated lobby list
        file_put_contents($lobbiesFile, json_encode($lobbies, JSON_PRETTY_PRINT));

        // Return success response
        echo json_encode([
            'success' => true,
            'lobby' => $lobbies[$lobbyIndex],
            'message' => 'Player status updated'
        ]);
        exit();
    } else {
        echo json_encode(['success' => false, 'message' => 'Lobby ID and status are required']);
        exit();
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}
?>