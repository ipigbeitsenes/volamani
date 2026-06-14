#!/usr/bin/env bash
# Phase 1 write-flow test: submit -> approve -> verify category created in domain tree
cd ~/laravelProjects/volamani || exit 1
docker compose exec -T app php artisan tinker --execute='
$vendor = \App\Models\Vendor::first();
$admin  = \App\Models\User::role("admin")->first();
$svc    = app(\App\Services\Taxonomy\CategoryRequestService::class);

$before = \App\Models\PhysicalCategory::count();
$req = $svc->submit($vendor, ["domain"=>"physical","name"=>"Drone Footage TEST","parent_id"=>null,"reason"=>"test"]);
echo "submitted_status=".$req->status->value.PHP_EOL;

$approved = $svc->approve($req->fresh(), $admin, "looks good");
echo "approved_status=".$approved->status->value.PHP_EOL;
echo "created_category_id=".$approved->created_category_id.PHP_EOL;

$after = \App\Models\PhysicalCategory::count();
echo "physical_delta=".($after-$before).PHP_EOL;
$cat = \App\Models\PhysicalCategory::find($approved->created_category_id);
echo "new_category_name=".($cat?->name).PHP_EOL;

// reject path on a fresh request
$req2 = $svc->submit($vendor, ["domain"=>"service","name"=>"Reject Me TEST"]);
$rej  = $svc->reject($req2->fresh(), $admin, "duplicate");
echo "rejected_status=".$rej->status->value.PHP_EOL;

// cleanup
$cat?->delete();
\App\Models\CategoryRequest::whereIn("id",[$req->id,$req2->id])->delete();
echo "CLEANED".PHP_EOL;
'
