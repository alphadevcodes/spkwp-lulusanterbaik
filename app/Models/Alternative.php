<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Alternative extends Model
{
    protected $fillable = ['code', 'student_name'];
 
    /**
     * Semua baris nilai (pivot) milik alternative ini.
     *
     * @return HasMany<AlternativeValue, covariant $this>
     */
    public function values(): HasMany
    {
        return $this->hasMany(AlternativeValue::class);
    }
 
    /**
     * Relasi many-to-many ke Criteria lewat tabel pivot alternative_values,
     * dengan kolom tambahan 'value' (nilai siswa untuk criteria tsb).
     *
     * @return BelongsToMany<Criteria, covariant $this>
     */
    public function criterias(): BelongsToMany
    {
        return $this->belongsToMany(Criteria::class, 'alternative_values')
            ->withPivot('id', 'value')
            ->withTimestamps();
    }
 
    /**
     * Scope pencarian by code atau student_name.
     *
     * @param  Builder<Alternative>  $query
     * @return Builder<Alternative>
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }
 
        return $query->where(function (Builder $q) use ($term) {
            $q->where('code', 'like', "%{$term}%")
                ->orWhere('student_name', 'like', "%{$term}%");
        });
    }
}
