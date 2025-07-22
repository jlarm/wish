<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Observers\ItemObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use phpDocumentor\Reflection\Types\Boolean;

#[ObservedBy(ItemObserver::class)]
class Item extends Model
{
    /** @use HasFactory<\Database\Factories\ItemFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'price' => MoneyCast::class,
            'purchased' => 'boolean',
            'purchased_date' => 'date',
            'delivered' => 'boolean',
            'delivered_date' => 'date',
            'hidden' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
