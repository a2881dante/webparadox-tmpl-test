<?php

namespace App\Models;


use Illuminate\Database\Eloquent\{Model, SoftDeletes};

use Spatie\MediaLibrary\HasMedia\{HasMedia, HasMediaTrait};
use App\Traits\Image;

class ShopProduct extends Model implements HasMedia
{
    use SoftDeletes;
    use HasMediaTrait;
    use Image;

    protected $table = 'shop_products';

    protected $fillable = [
        'uri',
        'name',
        'description',
        'price',
        'currency',
        'category_id'
    ];

    public function models()
    {
        return $this->hasMany(ShopProductModel::class, 'product_id', 'id');
    }

    public function category()
    {
        return $this->hasOne(ShopCategory::class, 'id', 'category_id');
    }

    public function modelsProperties()
    {
        return $this->hasMany(ShopModelProperty::class, 'product_id', 'id');
    }

    public function properties()
    {
        return $this->belongsToMany(ShopProperty::class,
            'shop_models_properties', 'property_id', 'product_id');
    }
}
