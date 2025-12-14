<?php

namespace App\Filament\Widgets;

use App\Enums\Role;
use App\Models\Item;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PurchaseStats extends BaseWidget
{
    public static function canView(): bool
    {
        return in_array(auth()->user()?->role, [Role::PARENT, Role::RELATIVE], true);
    }

    protected function getStats(): array
    {
        $items = Item::query()->whereNot('user_id', auth()->id());

        $totalItems = $items->count();
        $purchasedCount = (clone $items)->where('purchased', true)->count();
        $unpurchasedCount = $totalItems - $purchasedCount;

        $totalSpent = (clone $items)->where('purchased', true)->sum('price') / 100;
        $totalRemaining = (clone $items)->where('purchased', false)->sum('price') / 100;

        $deliveredCount = (clone $items)->where('delivered', true)->count();
        $awaitingDelivery = (clone $items)->where('purchased', true)->where('delivered', false)->count();

        return [
            Stat::make('Total Items', $totalItems)
                ->description('Items on wishlists')
                ->descriptionIcon('heroicon-o-gift')
                ->color('gray'),

            Stat::make('Purchased', $purchasedCount)
                ->description($unpurchasedCount.' remaining to buy')
                ->descriptionIcon('heroicon-o-shopping-bag')
                ->color('success'),

            Stat::make('Total Spent', '$'.number_format($totalSpent, 2))
                ->description('$'.number_format($totalRemaining, 2).' remaining')
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('warning'),

            Stat::make('Delivered', $deliveredCount)
                ->description($awaitingDelivery.' awaiting delivery')
                ->descriptionIcon('heroicon-o-truck')
                ->color('info'),
        ];
    }
}
