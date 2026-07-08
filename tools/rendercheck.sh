#!/usr/bin/env bash
set -e
cd /var/www/html
export DB_CONNECTION=sqlite
export DB_DATABASE=/tmp/verify.sqlite
export MAIL_MAILER=log
export CACHE_STORE=array
export APP_ENV=local
rm -f /tmp/verify.sqlite && touch /tmp/verify.sqlite
php artisan config:clear >/dev/null
php artisan migrate --seed --force >/dev/null 2>&1
php artisan tinker --execute="
\$k = app(Illuminate\Contracts\Http\Kernel::class);
\$r = \$k->handle(Illuminate\Http\Request::create('/buyer-protection','GET'));
echo 'buyer-protection status=' . \$r->getStatusCode() . PHP_EOL;
echo 'contains-escrow-window=' . (str_contains(\$r->getContent(),'business days') ? 'yes' : 'no') . PHP_EOL;
echo 'contains-support-email=' . (str_contains(\$r->getContent(),'support@volamani.com') ? 'yes' : 'no') . PHP_EOL;
"
