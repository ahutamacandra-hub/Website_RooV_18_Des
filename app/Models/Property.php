<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

class Property extends Model
{
    use HasFactory, Searchable;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'is_hook' => 'boolean',
        'has_pool' => 'boolean',
        'has_carport' => 'boolean', // Ini tetap dipakai untuk centang fasilitas
        'has_garden' => 'boolean',
        'has_canopy' => 'boolean',      // Baru
        'has_smart_home' => 'boolean',  // Baru
        'has_fence' => 'boolean',       // Baru
        'price' => 'integer',
        'gallery' => 'array',
        'garage_size' => 'integer',
        'carport_size' => 'integer',
        'maid_bedrooms' => 'integer',
        'maid_bathrooms' => 'integer',
    ];

    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'address' => $this->address,
            'city' => $this->city,
            'district' => $this->district,
            'listing_type' => $this->listing_type,
            'property_type' => $this->property_type,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
