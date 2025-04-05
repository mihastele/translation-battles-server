<?php
// Enable CORS for development
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

// Get lobbies from JSON file
$lobbiesFile = 'lobbies.json';

if (file_exists($lobbiesFile)) {
    $lobbies = json_decode(file_get_contents($lobbiesFile), true);
} else {
    $lobbies = [];
}

// Return lobbies
echo json_encode(['success' => true, 'lobbies' => $lobbies]);
?>