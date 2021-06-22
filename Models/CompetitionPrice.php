<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompetitionPrice extends Model
{
    protected $table = 'competitions_prices';

    protected $fillable = [
        'name',
        'competition_id',
        'value',
        'currency',
        'finish'
    ];

    protected $dates = [
        'finish'
    ];

    public $timestamps = false;

    public function registrations()
    {
        return $this->belongsToMany(CompetitionRegistration::class
            , 'registrations_prices', 'price_id', 'registration_id');
    }
}