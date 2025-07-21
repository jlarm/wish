<?php

namespace App\Observers;

use App\Models\Item;
use Str;

class ItemObserver
{
    public function creating(Item $item): void
    {
        $item->uuid = (string) Str::uuid();
    }
}
