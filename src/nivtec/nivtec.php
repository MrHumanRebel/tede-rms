<?php
require_once __DIR__ . '/../common/headSecure.php';

// Fetch the dark mode preference for the logged-in user
$DBLIB->where("users_userid", $AUTH->data['users_userid']);
$user = $DBLIB->getOne('users', ['users_dark_mode']); // Get the dark mode setting

// Prepare the data to be passed into the template
$PAGEDATA = [
    'title' => 'Nivtec Színpad Kalkulátor',
    'USERDATA' => [
        'users_dark_mode' => $user['users_dark_mode'] ?? 0, // Default to 0 if not set
    ],
];

// Render the template with the data
echo $TWIG->render('nivtec/nivtec.twig', $PAGEDATA);
