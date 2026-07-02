<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlternativeValue extends Model
{
    protected $fillable = ['alternative_id', 'criteria_id', 'value'];

    protected function casts(): array
    {
        return ['value' => 'decimal:2'];
    }

    /**
     * @return BelongsTo<Alternative,  $this>
     */
    public function alternative(): BelongsTo
    {
        return $this->belongsTo(Alternative::class);
    }

    /**
     * @return BelongsTo<Criteria, covariant $this>
     */
    public function criteria(): BelongsTo
    {
        return $this->belongsTo(Criteria::class);
    }
}
