#!/usr/bin/env bash
cd ~/laravelProjects/volamani || exit 1
BASE="http://localhost:8000"; JAR=/tmp/pgm.jar; rm -f "$JAR"
T=$(curl -s -c "$JAR" $BASE/login | grep -o 'name="_token" value="[^"]*"' | head -1 | sed 's/.*value="//;s/"//')
curl -s -b "$JAR" -c "$JAR" -o /dev/null -d "_token=$T" -d "email=superadmin@volamani.com" -d "password=SuperAdmin@123456" $BASE/login
for path in admin/audit-logs admin/users admin/products; do
  curl -s -b "$JAR" "$BASE/$path" -o /tmp/pg.html
  echo "$path -> code $(curl -s -b "$JAR" -o /dev/null -w '%{http_code}' $BASE/$path) | page-item=$(grep -c 'page-item' /tmp/pg.html) page-link=$(grep -c 'page-link' /tmp/pg.html) tailwind(hidden)=$(grep -c 'relative inline-flex' /tmp/pg.html)"
done
