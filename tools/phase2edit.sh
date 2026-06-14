#!/usr/bin/env bash
cd ~/laravelProjects/volamani || exit 1
BASE="http://localhost:8000"
JAR=/tmp/pixel.jar
rm -f "$JAR"

# id of a physical product owned by pixel-forge-studio
PID=$(docker compose exec -T app php artisan tinker --execute='echo \App\Models\Product::physical()->whereHas("vendor",fn($q)=>$q->where("slug","pixel-forge-studio"))->value("id");' | tr -dc '0-9')
echo "physical product id = $PID"

TOKEN=$(curl -s -c "$JAR" $BASE/login | grep -o 'name="_token" value="[^"]*"' | head -1 | sed 's/.*value="//;s/"//')
curl -s -b "$JAR" -c "$JAR" -o /dev/null -d "_token=$TOKEN" -d "email=pixel@example.com" -d "password=password" $BASE/login

echo "create page      -> $(curl -s -b "$JAR" -o /dev/null -w '%{http_code}' $BASE/vendor/products/create)"
echo "edit physical    -> $(curl -s -b "$JAR" -o /dev/null -w '%{http_code}' $BASE/vendor/products/$PID/edit)"
echo "vendor products  -> $(curl -s -b "$JAR" -o /dev/null -w '%{http_code}' $BASE/vendor/products)"
