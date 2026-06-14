#!/usr/bin/env bash
cd ~/laravelProjects/volamani || exit 1

echo "--- default paginator view ---"
docker compose exec -T app php artisan tinker --execute='echo \Illuminate\Pagination\Paginator::$defaultView.PHP_EOL; echo \Illuminate\Pagination\Paginator::$defaultSimpleView.PHP_EOL;'

BASE="http://localhost:8000"
echo "--- public paginated page markup (admin users) ---"
JAR=/tmp/admp.jar; rm -f "$JAR"
T=$(curl -s -c "$JAR" $BASE/login | grep -o 'name="_token" value="[^"]*"' | head -1 | sed 's/.*value="//;s/"//')
curl -s -b "$JAR" -c "$JAR" -o /dev/null -d "_token=$T" -d "email=superadmin@volamani.com" -d "password=SuperAdmin@123456" $BASE/login

curl -s -b "$JAR" "$BASE/admin/users" -o /tmp/users.html
echo "admin users code     -> $(curl -s -b "$JAR" -o /dev/null -w '%{http_code}' $BASE/admin/users)"
echo "bootstrap pagination -> $(grep -c 'pagination' /tmp/users.html)"
echo "page-link class      -> $(grep -c 'page-link' /tmp/users.html)"

curl -s -b "$JAR" "$BASE/admin/dashboard" -o /tmp/admdash.html
echo "--- admin dashboard ---"
echo "dashboard code       -> $(curl -s -b "$JAR" -o /dev/null -w '%{http_code}' $BASE/admin/dashboard)"
echo "has Returns queue     -> $(grep -c '>Returns<' /tmp/admdash.html)"
echo "has Category requests -> $(grep -c 'Category requests' /tmp/admdash.html)"
