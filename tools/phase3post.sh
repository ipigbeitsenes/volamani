#!/usr/bin/env bash
cd ~/laravelProjects/volamani || exit 1
BASE="http://localhost:8000"
JAR=/tmp/chioma3.jar
rm -f "$JAR"
PID=13  # 4k-webcam-pro (no variants, in stock)

ORDERS_BEFORE=$(docker compose exec -T app php artisan tinker --execute='echo \App\Models\Order::count();' | tr -dc '0-9')

TOKEN=$(curl -s -c "$JAR" $BASE/login | grep -o 'name="_token" value="[^"]*"' | head -1 | sed 's/.*value="//;s/"//')
curl -s -b "$JAR" -c "$JAR" -o /dev/null -d "_token=$TOKEN" -d "email=chioma@example.com" -d "password=password" $BASE/login

# grab a fresh CSRF token from the checkout page
FORM_TOKEN=$(curl -s -b "$JAR" $BASE/checkout/physical/$PID | grep -o 'name="_token" value="[^"]*"' | head -1 | sed 's/.*value="//;s/"//')

# POST with MISSING ship_to_address -> should fail validation (302 back), create no order
CODE=$(curl -s -b "$JAR" -o /dev/null -w '%{http_code}' \
  -d "_token=$FORM_TOKEN" -d "quantity=1" -d "ship_to_name=Test" -d "ship_to_phone=08030000000" -d "gateway=wallet" \
  $BASE/checkout/physical/$PID)
echo "invalid POST (missing address) -> HTTP $CODE (expect 302)"

ORDERS_AFTER=$(docker compose exec -T app php artisan tinker --execute='echo \App\Models\Order::count();' | tr -dc '0-9')
echo "orders before=$ORDERS_BEFORE after=$ORDERS_AFTER (expect equal — no order created on invalid submit)"
