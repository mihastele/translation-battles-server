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

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST data
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Validate username
    if (isset($data['username']) && !empty($data['username'])) {
        $username = trim($data['username']);

        // Simple validation
        if (strlen($username) < 3) {
            echo json_encode(['success' => false, 'message' => 'Username must be at least 3 characters long']);
            exit();
        }

        if (strlen($username) > 20) {
            echo json_encode(['success' => false, 'message' => 'Username must be less than 20 characters']);
            exit();
        }

        // Check if username is already in use
        // In a real app, this would check a database
        // For simplicity, we'll use a JSON file to store usernames
        $usersFile = 'users.json';

        if (file_exists($usersFile)) {
            $users = json_decode(file_get_contents($usersFile), true);
        } else {
            $users = [];
        }

        // Check if username exists
        foreach ($users as $user) {
            if (strtolower($user['username']) === strtolower($username)) {
                echo json_encode(['success' => false, 'message' => 'Username already taken']);
                exit();
            }
        }

        // Generate a unique user ID
        $userId = 'user_' . uniqid();

        // Add user to the list
        $users[] = [
            'id' => $userId,
            'username' => $username,
            'created' => date('Y-m-d H:i:s')
        ];

        // Save the updated user list
        file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));

        // Set session variables
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;
        $_SESSION['logged_in'] = true;

        // Return success response
        echo json_encode([
            'success' => true,
            'user' => [
                'id' => $userId,
                'username' => $username
            ]
        ]);
        exit();
    } else {
        echo json_encode(['success' => false, 'message' => 'Username is required']);
        exit();
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}
?>