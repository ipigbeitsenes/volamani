#!/bin/bash
cd /home/ipigb/laravelProjects/volamani
docker compose restart app nginx > /dev/null 2>&1
sleep 4
docker compose exec -T app php artisan optimize:clear > /dev/null 2>&1
echo "home: $(curl -s -o /dev/null -w "%{http_code}" http://localhost:8000)"
# mark log position, then sweep
wc -l < storage/logs/laravel.log > /tmp/logmark
bash tools/sweep.sh
echo "=== NEW log errors since sweep start ==="
tail -n +$(cat /tmp/logmark) storage/logs/laravel.log | grep -c "ERROR" || true
