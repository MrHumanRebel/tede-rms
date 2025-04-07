#!/bin/bash

# Bejelentkezési adatok
USERNAME="test_username"
PASSWORD="Test2025@"

# URL-ek
LOGIN_URL="https://attendance.overlab.duckdns.org/hu/login"
LOGIN_CHECK_URL="https://attendance.overlab.duckdns.org/hu/login_check"

# 1. Kérjük le a CSRF tokent
echo "Lekérjük a CSRF tokent..."
csrf_token=$(curl -s "$LOGIN_URL" | grep -oP 'name="_csrf_token" value="\K[^"]+')

if [ -z "$csrf_token" ]; then
    echo "Hiba: Nem található CSRF token"
    exit 1
fi

echo "CSRF token: $csrf_token"

# 2. POST kérés a bejelentkezéshez
echo "Próbálkozunk a bejelentkezéssel..."
response=$(curl -s -w "%{http_code}" -o response.html -X POST "$LOGIN_CHECK_URL" \
    -d "_username=$USERNAME" \
    -d "_password=$PASSWORD" \
    -d "_csrf_token=$csrf_token" \
    -c cookies.txt)

# 3. Ellenőrizzük a válasz státuszkódját
echo "HTTP válasz státuszkódja: $response"

if [ "$response" -eq 302 ]; then
    echo "Sikeres bejelentkezés! Redirecting..."
    location=$(grep -i "Location" response.html | awk '{print $2}' | tr -d '\r')
    echo "Redirect location: $location"
elif [ "$response" -eq 200 ]; then
    echo "Bejelentkezés sikertelen! A válasz tartalma: $(cat response.html)"
else
    echo "HTTP hiba: $response"
    cat response.html
fi
