<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tool extends Model
{
    use HasFactory, HasUuids, LogsActivity;

    protected $fillable = [
        'code',
        'name',
        'category_id',
        'brand',
        'series',
        'description',
        'total_stock',
        'available_stock',
        'price_per_day',
        'image',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'total_stock' => 'integer',
            'available_stock' => 'integer',
            'price_per_day' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function category()
    {
        return $this->belongsTo(ToolCategory::class, 'category_id');
    }

    public function images()
    {
        return $this->hasMany(ToolImage::class)->orderBy('sort_order');
    }

    public function borrowingItems()
    {
        return $this->hasMany(BorrowingItem::class);
    }

    /**
     * Ambil gambar utama (sort_order pertama) atau null.
     */
    public function getPrimaryImageAttribute(): ?ToolImage
    {
        return $this->images->first();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('available_stock', '>', 0);
    }

    /**
     * Format harga sewa per hari sebagai Rupiah.
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp '.number_format($this->price_per_day, 0, ',', '.');
    }

    public function getCategoryNameAttribute(): string
    {
        return $this->category?->name ?? '-';
    }
}
