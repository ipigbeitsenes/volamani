#!/usr/bin/env bash
cd ~/laravelProjects/volamani || exit 1
echo "--- paystack config (presence only) ---"
docker compose exec -T app php artisan tinker --execute='
echo "secret_set=".(config("payment.paystack.secret_key") ? "yes(len ".strlen(config("payment.paystack.secret_key")).")" : "NO").PHP_EOL;
echo "public_set=".(config("payment.paystack.public_key") ? "yes" : "NO").PHP_EOL;
echo "default_gateway=".config("payment.default").PHP_EOL;
'
echo "--- recent wallet fundings ---"
docker compose exec -T app php artisan tinker --execute='
foreach(\App\Models\WalletFunding::with("payment")->latest()->take(5)->get() as $f){
  echo $f->reference." amount=".$f->amount." status=".$f->status." payment_status=".($f->payment?->status?->value ?? "none")." | wallet_balance=".(\App\Models\Wallet::find($f->wallet_id)?->balance).PHP_EOL;
}
echo "total_fundings=".\App\Models\WalletFunding::count()." completed=".\App\Models\WalletFunding::where("status","completed")->count()." pending=".\App\Models\WalletFunding::where("status","pending")->count().PHP_EOL;
'
