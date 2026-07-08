#!/usr/bin/env bash
# Buyer-protection verification on a throwaway sqlite (run inside volamani:latest).
set -e
cd /var/www/html
export DB_CONNECTION=sqlite
export DB_DATABASE=/tmp/verify.sqlite
export MAIL_MAILER=log
export CACHE_STORE=array
export QUEUE_CONNECTION=sync
export APP_ENV=local

rm -f /tmp/verify.sqlite
touch /tmp/verify.sqlite

php artisan config:clear >/dev/null
echo "=== MIGRATE + SEED ==="
php artisan migrate --seed --force 2>&1 | tail -20

echo
echo "=== ROUTES (protection-related) ==="
php artisan route:list 2>&1 | grep -Ei "chargeback|buyer-protection|strikes" || echo "NO PROTECTION ROUTES FOUND"

echo
echo "=== TINKER FLOWS ==="
php artisan tinker --execute="$(sed '1d' tools/protflow.php)"
