<?php

require_once __DIR__ . '/../common/headSecure.php';

// 1. Felhasználó lekérése
$DBLIB->where("users.users_userid", $AUTH->data['users_userid']);
$user = $DBLIB->getOne("users", ["users_kimai_username", "users_kimai_passwd"]);

if (!$user) {
    die("Felhasználói adatok nem találhatók.");
}

// 2. Inicializáljuk a cURL sessiont cookie támogatással
$cookieFile = tempnam(sys_get_temp_dir(), 'cookie');

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://attendance.overlab.duckdns.org/hu/login");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$csrfTokenHtml = curl_exec($ch);

// 3. CSRF token kinyerése
preg_match('/name="_csrf_token" value="([^"]+)"/', $csrfTokenHtml, $matches);
$csrfToken = $matches[1] ?? null;

if (!$csrfToken) {
    die("CSRF token nem található.");
}

// 4. Bejelentkezési adatok POST formában
$postData = [
    '_username' => $user['users_kimai_username'],
    '_password' => $user['users_kimai_passwd'],
    '_csrf_token' => $csrfToken
];

// 5. Bejelentkezés ugyanazzal a cURL sessióval + cookiekkal
curl_setopt($ch, CURLOPT_URL, "https://attendance.overlab.duckdns.org/hu/login_check");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Kövesse az átirányítást

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// 6. Eredmény ellenőrzése
if ($httpCode === 302) {
    // Sikeres bejelentkezés és átirányítás
    header('Location: https://attendance.overlab.duckdns.org/');
    exit(); // Itt kilépünk, hogy ne folytatódjon semmi más
} else {
    // Ha a bejelentkezés nem sikerült, az alábbiakat írjuk ki
    die("Bejelentkezés sikertelen\nToken: {$csrfToken}\nUsername: {$user['users_kimai_username']}\nPasswd: {$user['users_kimai_passwd']}\nHTTP Code: $httpCode\nResponse: $response");
}
