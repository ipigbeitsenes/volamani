#!/usr/bin/env bash
cd ~/laravelProjects/volamani || exit 1

echo "--- media_url (local) ---"
docker compose exec -T app php artisan tinker --execute='echo "media_url=".media_url("vendors/logos/x.png").PHP_EOL; echo "empty_fallback=[".media_url("","none")."]".PHP_EOL; $v=\App\Models\Vendor::first(); echo "vendor_logo=".$v->logo_url.PHP_EOL;'

echo "--- toggle to S3 (separate boot reflects it) ---"
docker compose exec -T app php artisan tinker --execute='\App\Models\Setting::set("storage_driver","s3"); \App\Models\Setting::set("s3_key","AKIA_TEST"); \App\Models\Setting::set("s3_secret","sk"); \App\Models\Setting::set("s3_region","eu-west-1"); \App\Models\Setting::set("s3_bucket","volamani-test"); echo "set".PHP_EOL;' >/dev/null
docker compose exec -T app php artisan tinker --execute='echo "public_driver=".config("filesystems.disks.public.driver")." bucket=".config("filesystems.disks.public.bucket")." region=".config("filesystems.disks.public.region").PHP_EOL; echo "private_driver=".config("filesystems.disks.private.driver").PHP_EOL;'

echo "--- revert to local ---"
docker compose exec -T app php artisan tinker --execute='\App\Models\Setting::set("storage_driver","local"); echo "reverted".PHP_EOL;' >/dev/null
docker compose exec -T app php artisan tinker --execute='echo "public_driver_after_revert=".config("filesystems.disks.public.driver").PHP_EOL;'

echo "--- admin settings page ---"
BASE="http://localhost:8000"; JAR=/tmp/adm.jar; rm -f "$JAR"
T=$(curl -s -c "$JAR" $BASE/login | grep -o 'name="_token" value="[^"]*"' | head -1 | sed 's/.*value="//;s/"//')
curl -s -b "$JAR" -c "$JAR" -o /dev/null -d "_token=$T" -d "email=superadmin@volamani.com" -d "password=SuperAdmin@123456" $BASE/login
curl -s -b "$JAR" $BASE/admin/settings -o /tmp/settings.html
echo "settings code   -> $(curl -s -b "$JAR" -o /dev/null -w '%{http_code}' $BASE/admin/settings)"
echo "has storage tab -> $(grep -c 'tab-storage' /tmp/settings.html)"
echo "has driver select -> $(grep -c 'Amazon S3 (cloud)' /tmp/settings.html)"
