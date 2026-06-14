#!/usr/bin/env bash
cd ~/laravelProjects/volamani || exit 1
docker compose exec -T app php artisan view:clear >/dev/null 2>&1
UN=$(docker compose exec -T app php artisan tinker --execute='echo \App\Models\Vendor::where("slug","pixel-forge-studio")->first()->user->username;' | tr -dc 'a-z0-9')
echo "pixel username = $UN"
echo "storefront            -> $(curl -s -o /dev/null -w '%{http_code}' http://localhost:8000/store/$UN)"
echo "marketplace products  -> $(curl -s -o /dev/null -w '%{http_code}' http://localhost:8000/marketplace/products)"
echo "home                  -> $(curl -s -o /dev/null -w '%{http_code}' http://localhost:8000/)"
# sanity: storefront HTML contains both buttons
HTML=$(curl -s http://localhost:8000/store/$UN)
echo "storefront has 'View Details'  -> $(echo "$HTML" | grep -c 'View Details')"
echo "storefront has 'Add to Cart'   -> $(echo "$HTML" | grep -c 'Add to Cart')"
