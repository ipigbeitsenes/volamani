#!/usr/bin/env bash
cd ~/laravelProjects/volamani || exit 1
docker compose exec -T app php artisan view:clear >/dev/null 2>&1
curl -s "http://localhost:8000/marketplace/products" -o /tmp/prod.html
echo "products code      -> $(curl -s -o /dev/null -w '%{http_code}' http://localhost:8000/marketplace/products)"
echo "filterCollapse     -> $(grep -c 'filterCollapse' /tmp/prod.html)"
echo "collapse d-lg-block-> $(grep -c 'collapse d-lg-block' /tmp/prod.html)"
echo "mobile chevron     -> $(grep -c 'bi-chevron-down d-lg-none' /tmp/prod.html)"
