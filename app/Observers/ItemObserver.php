<?php

namespace App\Observers;

use App\Enums\Role;
use App\Models\Item;
use App\Models\User;
use Filament\Notifications\Notification;
use Str;

class ItemObserver
{
    public function creating(Item $item): void
    {
        $item->uuid = (string) Str::uuid();
    }

    public function created(Item $item): void
    {
        // Only send notification if the creator is a child
        if ($item->user->role === Role::CHILD) {
            $this->sendNotificationToParentsAndRelatives(
                title: 'New Item Added',
                body: "{$item->user->name} added '{$item->name}' to their wishlist",
                item: $item
            );
        }
    }

    public function updated(Item $item): void
    {
        // Only send notification if the editor is a child
        if ($item->user->role === Role::CHILD) {
            $this->sendNotificationToParentsAndRelatives(
                title: 'Item Updated',
                body: "{$item->user->name} updated '{$item->name}' in their wishlist",
                item: $item
            );
        }
    }

    private function sendNotificationToParentsAndRelatives(string $title, string $body, Item $item): void
    {
        $parentsAndRelatives = User::whereIn('role', [Role::PARENT, Role::RELATIVE])->get();

        foreach ($parentsAndRelatives as $user) {
            Notification::make()
                ->title($title)
                ->body($body)
                ->icon('heroicon-o-gift')
                ->iconColor('success')
                ->sendToDatabase($user);
        }
    }
}
