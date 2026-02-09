<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'label',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function subcategories()
    {
        return $this->hasMany(Subcategory::class)->orderBy('label');
    }

    public function stories()
    {
        return $this->hasMany(Story::class);
    }
}
