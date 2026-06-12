#!/bin/bash
cd /home/ipigb/laravelProjects/volamani
docker compose restart app nginx > /dev/null 2>&1
sleep 4
docker compose exec -T app php artisan optimize:clear > /dev/null 2>&1
echo "=== home status + security headers ==="
curl -s -D - -o /dev/null http://localhost:8000 | grep -iE "HTTP/|x-frame|x-content|referrer-policy|permissions-policy"
echo "=== login throttle: 12 bad POSTs, expect 429 by the 11th ==="
token_page=$(curl -s -c /tmp/th.jar http://localhost:8000/login)
token=$(echo "$token_page" | grep -o 'name="_token" value="[^"]*"' | head -1 | sed 's/.*value="//;s/"//')
for i in $(seq 1 12); do
  code=$(curl -s -b /tmp/th.jar -o /dev/null -w "%{http_code}" -d "_token=$token" -d "email=throttle-test@example.com" -d "password=wrongpass" http://localhost:8000/login)
  printf "%s " "$code"
done
echo ""
