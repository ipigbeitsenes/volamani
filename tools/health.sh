#!/bin/bash
cd /home/ipigb/laravelProjects/volamani
code=000
for i in $(seq 1 40); do
  code=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8000)
  if [ "$code" = "200" ]; then echo "APP UP (attempt $i): $code"; break; fi
  sleep 3
done
echo "final-home: $code"
docker compose exec -T app php artisan migrate:status 2>&1 | tail -4
echo "--- pending migrations? ---"
docker compose exec -T app php artisan migrate:status 2>&1 | grep -ci "Pending" || echo 0
echo "--- log tail ---"
tail -5 storage/logs/laravel.log 2>/dev/null || echo "(no log)"
