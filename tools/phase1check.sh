#!/usr/bin/env bash
# Phase 1 verification: category counts + vendor store columns
cd ~/laravelProjects/volamani || exit 1
docker compose exec -T app php artisan tinker --execute='
echo "physical_categories=".\App\Models\PhysicalCategory::count().PHP_EOL;
echo "service_categories=".\App\Models\ServiceCategory::count().PHP_EOL;
echo "physical_roots=".\App\Models\PhysicalCategory::whereNull("parent_id")->count().PHP_EOL;
echo "service_roots=".\App\Models\ServiceCategory::whereNull("parent_id")->count().PHP_EOL;
echo "vendors_with_focus=".\App\Models\Vendor::whereNotNull("store_focus")->count().PHP_EOL;
$v = \App\Models\Vendor::first();
echo "sample_focus=".($v ? $v->store_focus?->value : "none").PHP_EOL;
echo "sample_type=".($v ? $v->store_type?->value : "none").PHP_EOL;
'
