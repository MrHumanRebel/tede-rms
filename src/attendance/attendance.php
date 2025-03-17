<?php

require_once __DIR__ . '/../common/headSecure.php';

// Irányítás az új URL-re
header('Location: https://attendance.overlab.duckdns.org/');
exit();  // Fontos, hogy az exit() után már ne fusson le további kód
