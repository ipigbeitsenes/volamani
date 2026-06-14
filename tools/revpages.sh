#!/usr/bin/env bash
cd ~/laravelProjects/volamani || exit 1
BASE="http://localhost:8000"
login() { local jar=$1 email=$2; local t; t=$(curl -s -c "$jar" $BASE/login | grep -o 'name="_token" value="[^"]*"' | head -1 | sed 's/.*value="//;s/"//'); curl -s -b "$jar" -c "$jar" -o /dev/null -d "_token=$t" -d "email=$email" -d "password=password" $BASE/login; }

echo "home (public)            -> $(curl -s -o /dev/null -w '%{http_code}' $BASE/)"
UN=$(docker compose exec -T app php artisan tinker --execute='echo \App\Models\Vendor::where("slug","pixel-forge-studio")->first()->user->username;' | tr -dc 'a-z0-9')
HTML=$(curl -s $BASE/store/$UN)
echo "storefront               -> $(curl -s -o /dev/null -w '%{http_code}' $BASE/store/$UN)  hasRequestBtn=$(echo "$HTML" | grep -c 'Request a Custom Order')"

JARB=/tmp/cb.jar; rm -f "$JARB"; login "$JARB" chioma@example.com >/dev/null
echo "direct request create    -> $(curl -s -b "$JARB" -o /dev/null -w '%{http_code}' "$BASE/requests/create?vendor=1")"
CR=$(curl -s -b "$JARB" "$BASE/requests/create?vendor=1")
echo "  shows 'Sending directly'-> $(echo "$CR" | grep -c 'Sending directly')"

JARV=/tmp/cv.jar; rm -f "$JARV"; login "$JARV" pixel@example.com >/dev/null
DASH=$(curl -s -b "$JARV" $BASE/vendor/dashboard)
echo "vendor dashboard         -> $(curl -s -b "$JARV" -o /dev/null -w '%{http_code}' $BASE/vendor/dashboard)  hasBranding=$(echo "$DASH" | grep -c 'Store Branding')"
PROD=$(curl -s -b "$JARV" $BASE/vendor/products)
echo "vendor products          -> $(curl -s -b "$JARV" -o /dev/null -w '%{http_code}' $BASE/vendor/products)  hasPromote=$(echo "$PROD" | grep -c 'Promote')"
echo "vendor quotations        -> $(curl -s -b "$JARV" -o /dev/null -w '%{http_code}' $BASE/vendor/quotations)"
echo "--- log ---"
tail -n 50 storage/logs/laravel.log | grep -i error | grep -v "20:38:09" | tail -n 5
echo LOGDONE
