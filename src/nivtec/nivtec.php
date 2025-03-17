<?php

require_once __DIR__ . '/../common/headSecure.php';

// Prepare the data to be passed into the template
$PAGEDATA = [
    'title' => 'Nivtec Színpad Kalkulátor',
];

// Render the template with the data
echo $TWIG->render('nivtec/nivtec.twig', $PAGEDATA);
