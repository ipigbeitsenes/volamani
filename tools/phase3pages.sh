#!/usr/bin/env bash
cd ~/laravelProjects/volamani || exit 1
docker compose exec -T app php artisan view:clear >/dev/null 2>&1
BASE="http://localhost:8000"
JAR=/tmp/chioma.jar
rm -f "$JAR"

INSTOCK=$(docker compose exec -T app php artisan tinker --execute='echo \App\Models\Product::where("slug","4k-webcam-pro")->value("id");' | tr -dc '0-9')
OOS=$(docker compose exec -T app php artisan tinker --execute='echo \App\Models\Product::where("slug","premium-cotton-brand-tee")->value("id");' | tr -dc '0-9')
echo "in-stock product id=$INSTOCK  out-of-stock id=$OOS"

TOKEN=$(curl -s -c "$JAR" $BASE/login | grep -o 'name="_token" value="[^"]*"' | head -1 | sed 's/.*value="//;s/"//')
curl -s -b "$JAR" -c "$JAR" -o /dev/null -d "_token=$TOKEN" -d "email=chioma@example.com" -d "password=password" $BASE/login

echo "checkout in-stock (expect 200) -> $(curl -s -b "$JAR" -o /dev/null -w '%{http_code}' $BASE/checkout/physical/$INSTOCK)"
echo "checkout out-of-stock (expect 302) -> $(curl -s -b "$JAR" -o /dev/null -w '%{http_code}' $BASE/checkout/physical/$OOS)"
echo "product show keyboard (200) -> $(curl -s -b "$JAR" -o /dev/null -w '%{http_code}' $BASE/marketplace/products/mechanical-keyboard-creator-edition)"
echo "vendor storefront settings (login pixel)..."
JAR2=/tmp/pixel2.jar; rm -f "$JAR2"
T2=$(curl -s -c "$JAR2" $BASE/login | grep -o 'name="_token" value="[^"]*"' | head -1 | sed 's/.*value="//;s/"//')
curl -s -b "$JAR2" -c "$JAR2" -o /dev/null -d "_token=$T2" -d "email=pixel@example.com" -d "password=password" $BASE/login
echo "storefront settings (200) -> $(curl -s -b "$JAR2" -o /dev/null -w '%{http_code}' $BASE/vendor/storefront)"
