<?php

namespace App\Repositories\Shop;

use App\Facades\AppCart;
use App\Models\ShopProductModel;
use App\Repositories\BaseRepository;

class ProductModelRepository extends BaseRepository
{
    public function model()
    {
        return ShopProductModel::class;
    }

    public function findWithValue($id)
    {
        return $this->model
            ->with([
                'properties.property',
                'properties.propertyValue'
            ])
            ->where('id', $id)
            ->first();
    }

    public function findByProperties(int $id, array $properties)
    {
        $query = $this->model->query();
        foreach ($properties as $value) {
            $query->whereHas('properties', function ($query) use ($id, $value) {
                $query->where('value_id', '=', $value)
                    ->where('product_id', '=', $id);
            });
        }
        return $query->first();
    }

    public function fromCart()
    {
        $query = $this->model->query();
        $cart = AppCart::get();
        $modelsIds = array_keys($cart ?? []);
        return $query->whereIn('id', $modelsIds)
            ->with([
                'product',
                'values',
                'values.property'
            ])
            ->get();
    }

    public function subStock($id, $count)
    {
        $model = $this->model->where('id', $id)->first();
        $model->in_stock = $model->in_stock - $count;
        return $model->save();
    }
}