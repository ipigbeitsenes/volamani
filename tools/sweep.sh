#!/bin/bash
# Full-route smoke sweep: hits every parameterless GET route as guest + 3 roles.
# Usage: bash tools/sweep.sh   (run from repo root; app must be up on :8000)
cd /home/ipigb/laravelProjects/volamani
BASE="http://localhost:8000"
OUT=/tmp/sweep
mkdir -p $OUT
rm -f $OUT/*

docker compose exec -T app php artisan route:list --json 2>/dev/null \
  | php -r '
    $routes = json_decode(stream_get_contents(STDIN), true);
    foreach ($routes as $r) {
        if (strpos($r["method"], "GET") === false) continue;
        if (strpos($r["uri"], "{") !== false) continue;
        if (strpos($r["uri"], "_ignition") === 0 || strpos($r["uri"], "_debugbar") === 0 || strpos($r["uri"], "storage/") === 0) continue;
        echo $r["uri"] . "\n";
    }
  ' | sort -u > $OUT/routes.txt

echo "Routes to sweep: $(wc -l < $OUT/routes.txt)"

login () { # $1=jar $2=email $3=pass
  local jar=$1
  local token
  token=$(curl -s -c "$jar" $BASE/login | grep -o 'name="_token" value="[^"]*"' | head -1 | sed 's/.*value="//;s/"//')
  curl -s -b "$jar" -c "$jar" -o /dev/null -w "%{http_code}" \
    -d "_token=$token" -d "email=$2" -d "password=$3" $BASE/login
}

sweep () { # $1=jar(or "none") $2=label
  local jar=$1 label=$2 fails=0
  while read -r uri; do
    if [ "$jar" = "none" ]; then
      code=$(curl -s -o /dev/null -w "%{http_code}" "$BASE/$uri")
    else
      code=$(curl -s -b "$jar" -o /dev/null -w "%{http_code}" "$BASE/$uri")
    fi
    echo "$code $uri" >> "$OUT/$label.txt"
    if [ "$code" -ge 500 ]; then fails=$((fails+1)); fi
  done < $OUT/routes.txt
  echo "$label: $(grep -c '^5' $OUT/$label.txt 2>/dev/null || echo 0) x 5xx of $(wc -l < $OUT/routes.txt)"
}

sweep none guest

for role in "admin superadmin@volamani.com SuperAdmin@123456" \
            "vendor pixel@example.com password" \
            "buyer chioma@example.com password" \
            "support support@volamani.com Support@123456" \
            "finance finance@volamani.com Finance@123456"; do
  set -- $role
  jar=$OUT/$1.jar
  rc=$(login "$jar" "$2" "$3")
  echo "login $1 -> $rc"
  sweep "$jar" "$1"
done

echo "=== 5xx detail (all roles) ==="
grep -h '^5' $OUT/guest.txt $OUT/admin.txt $OUT/vendor.txt $OUT/buyer.txt $OUT/support.txt $OUT/finance.txt 2>/dev/null | sort -u || echo "NONE"
