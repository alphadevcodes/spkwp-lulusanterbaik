<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Criteria extends Model
{
    //
    protected $fillable = ['code', 'name', 'category', 'attribute', 'weight'];

    protected function casts(): array
    {
        return ['weight' => 'integer'];
    }

    /**
     * Scope pencarian by code atau name.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('code', 'like', "%{$term}%")
                ->orWhere('name', 'like', "%{$term}%");
        });
    }

    /**
     * Scope filter by category.
     */
    public function scopeCategory(Builder $query, ?string $category): Builder
    {
        if (blank($category)) {
            return $query;
        }

        return $query->where('category', $category);
    }

    /**
     * Scope filter by attribute.
     */
    public function scopeAttribute(Builder $query, ?string $attribute): Builder
    {
        if (blank($attribute)) {
            return $query;
        }

        return $query->where('attribute', $attribute);
    }
}
