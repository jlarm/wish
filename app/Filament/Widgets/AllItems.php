<?php

namespace App\Filament\Widgets;

use App\Enums\Role;
use App\Filament\Resources\ItemResource;
use App\Models\Item;
use App\Models\User;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class AllItems extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return in_array(auth()->user()?->role, [Role::PARENT, Role::RELATIVE], true);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Item::query())
            ->columns([
                TextColumn::make('user.name')
                    ->label('Person')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->words(5)
                    ->sortable()
                    ->searchable(),
                ImageColumn::make('image')
                    ->circular(),
                IconColumn::make('purchased'),
                IconColumn::make('delivered'),
                TextColumn::make('size'),
                TextColumn::make('color'),
                TextColumn::make('price')
                    ->prefix('$'),
                TextColumn::make('store')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label('User')
                    ->options(User::pluck('name', 'id'))
                    ->searchable(),
            ])
            ->actions([
                Action::make('edit')
                    ->url(fn (Item $record): string => ItemResource::getUrl('edit', ['record' => $record])),
            ]);
    }
}
