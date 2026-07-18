<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'sku', 'category_id', 'unit_of_measure',
        'packaging_details', 'description', 'selling_price',
        'standard_price', 'image', 'is_active',
    ];

    protected $casts = [
        'selling_price' => 'decimal:2',
        'standard_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function priceSlabs()
    {
        return $this->hasMany(PriceSlab::class);
    }

    public function warehouseInventory()
    {
        return $this->hasOne(WarehouseInventory::class);
    }

    public function franchiseInventories()
    {
        return $this->hasMany(FranchiseInventory::class);
    }

    public function getBestPrice(float $quantity): float
    {
        $slab = $this->priceSlabs()
            ->where('is_active', true)
            ->where('min_quantity', '<=', $quantity)
            ->where(function ($q) use ($quantity) {
                $q->whereNull('max_quantity')
                    ->orWhere('max_quantity', '>=', $quantity);
            })
            ->orderByDesc('min_quantity')
            ->first();

        return $slab ? (float) $slab->slab_price : (float) $this->standard_price;
    }
}
