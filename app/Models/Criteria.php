<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Criteria extends Model
{
    use SoftDeletes;

    protected $table = 'criterias';

    protected $fillable = [
        'code',
        'name',
        'category',
        'attribute',
        'weight',
        'description',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
    ];
}