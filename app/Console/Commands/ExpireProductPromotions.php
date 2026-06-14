<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class ExpireProductPromotions extends Command
{
    protected $signature = 'products:expire-promotions';

    protected $description = 'Un-feature products whose paid promotion window has elapsed';

    public function handle(): int
    {
        $count = Product::where('is_featured', true)
            ->whereNotNull('featured_until')
            ->where('featured_until', '<=', now())
            ->update(['is_featured' => false]);

        $this->info("Expired {$count} product promotion(s).");

        return self::SUCCESS;
    }
}
