<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Category extends Model
{
    protected $appends = ['image_url'];

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'image',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * القسم الأب.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * الأقسام الفرعية.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')
            ->orderBy('sort_order');
    }

    /**
     * المنتجات التابعة للقسم.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        return filter_var($this->image, FILTER_VALIDATE_URL)
            ? $this->image
            : Storage::disk('public')->url($this->image);
    }
}
