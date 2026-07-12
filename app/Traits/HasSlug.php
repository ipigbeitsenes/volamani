<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasSlug
{
    protected static function bootHasSlug(): void
    {
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = static::generateUniqueSlug($model->getSlugSource());
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty($model->getSlugSourceColumn()) && $model->auto_update_slug ?? false) {
                $model->slug = static::generateUniqueSlug($model->getSlugSource(), $model->getKey());
            }
        });
    }

    protected static function generateUniqueSlug(string $source, mixed $excludeId = null): string
    {
        $slug = Str::slug($source);
        $original = $slug;
        $i = 1;

        $query = static::where('slug', $slug);
        if ($excludeId) {
            $query->where((new static)->getKeyName(), '!=', $excludeId);
        }

        while ($query->clone()->exists()) {
            $slug = $original.'-'.$i++;
            $query = static::where('slug', $slug);
            if ($excludeId) {
                $query->where((new static)->getKeyName(), '!=', $excludeId);
            }
        }

        return $slug;
    }

    abstract public function getSlugSource(): string;

    public function getSlugSourceColumn(): string
    {
        return 'name';
    }
}
