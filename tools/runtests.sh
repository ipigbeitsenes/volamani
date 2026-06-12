#!/bin/bash
cd /home/ipigb/laravelProjects/volamani
docker compose exec -T app php artisan test --without-tty 2>&1 | tail -40
