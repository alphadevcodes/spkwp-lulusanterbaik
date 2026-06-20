<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Criteria extends Model
{
    protected $fillable = ['code', 'name', 'category', 'attribute', 'weight'];

    protected function casts(): array
    {
        return ['weight' => 'integer'];
    }

    /**
     * Semua baris nilai (pivot) milik criteria ini.
     *
     * @return HasMany<AlternativeValue, covariant $this>
     */
    public function values(): HasMany
    {
        return $this->hasMany(AlternativeValue::class);
    }

    /**
     * Relasi many-to-many ke Alternative lewat tabel pivot alternative_values.
     *
     * @return BelongsToMany<Alternative, covariant $this>
     */
    public function alternatives(): BelongsToMany
    {
        return $this->belongsToMany(Alternative::class, 'alternative_values')
            ->withPivot('id', 'value')
            ->withTimestamps();
    }

    /**
     * Scope pencarian by code atau name.
     *
     * @param  Builder<Criteria>  $query
     * @return Builder<Criteria>
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
     *
     * @param  Builder<Criteria>  $query
     * @return Builder<Criteria>
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
     *
     * @param  Builder<Criteria>  $query
     * @return Builder<Criteria>
     */
    public function scopeAttribute(Builder $query, ?string $attribute): Builder
    {
        if (blank($attribute)) {
            return $query;
        }

        return $query->where('attribute', $attribute);
    }
}
