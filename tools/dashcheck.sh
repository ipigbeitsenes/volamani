#!/usr/bin/env bash
cd ~/laravelProjects/volamani || exit 1
docker compose exec -T app php artisan view:clear >/dev/null 2>&1
BASE="http://localhost:8000"
JAR=/tmp/dash.jar; rm -f "$JAR"
T=$(curl -s -c "$JAR" $BASE/login | grep -o 'name="_token" value="[^"]*"' | head -1 | sed 's/.*value="//;s/"//')
curl -s -b "$JAR" -c "$JAR" -o /dev/null -d "_token=$T" -d "email=chioma@example.com" -d "password=password" $BASE/login
H=$(curl -s -b "$JAR" $BASE/dashboard)
echo "dashboard code     -> $(curl -s -b "$JAR" -o /dev/null -w '%{http_code}' $BASE/dashboard)"
echo "has sidebar nav    -> $(echo "$H" | grep -c 'nav-section')"
echo "has sidebar brand  -> $(echo "$H" | grep -c 'sidebar-brand')"
echo "has Returns link   -> $(echo "$H" | grep -c 'Returns')"
echo "main-content shift -> $(echo "$H" | grep -c 'main-content')"
