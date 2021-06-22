<?php

namespace App\Repositories\Competition;

use App\Helpers\Date;
use App\Models\{ CompetitionPrice, CompetitionRegistration };
use App\Repositories\BaseRepository;

class PriceRepository extends BaseRepository
{
    const DEFAULT_CURRENCY = 'UAH';

    public function model()
    {
        return CompetitionPrice::class;
    }

    public function query(int $competitionId, ?int $regId = null)
    {
        $query = CompetitionRegistration::where('competition_id', $competitionId);
        
        if ($regId) {
            $query = $query->where('id', $regId);
        }

        return $query->with('prices')
            ->orderby('id')
            ->get();
    }

    public function getAll(int $competitionId, ?int $regId = null)
    {
        $now = Date::now();

        $registrations = $this->query($competitionId, $regId);

        return $registrations->map(function($item, $key) use (&$now) {
            // Free registration
            if (!$item->prices->count()) {
                return $item;
            }
            // Paid registration
            $sortedPrices = $item->prices->sortBy('finish');

            $prices = $sortedPrices->filter(function ($value, $key) use (&$now) {
                return $value->finish > $now;
            });

            if (!$prices->count()) {
                $prices = $sortedPrices->last();
            }

            // Need to delete then set property prices, otherwise doesn't work
            unset($item->prices);
            $item->prices = $prices;

            return $item;
        });
    }

    public function getCurrent(int $competitionId, int $regId)
    {
        $prices = $this->getAll($competitionId, $regId);
        return $prices->first();
    }
}
