<?php

namespace App\Filament\Widgets;

use App\Enums\Role;
use App\Filament\Resources\ItemResource;
use App\Models\Item;
use App\Models\User;
use Filament\Forms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

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
            ->query(Item::query()->whereNot('user_id', auth()->id()))
            ->columns([
                TextColumn::make('user.name')
                    ->label('Person')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->tooltip(fn (Model $record): string => "{$record->name}")
                    ->words(5)
                    ->sortable()
                    ->searchable(),
                ImageColumn::make('image')
                    ->circular(),
                IconColumn::make('purchased')
                    ->tooltip(fn (Model $record): string => "Purchased On {$record->purchased_date?->format('F j, Y')}"),
                IconColumn::make('delivered')
                    ->tooltip(fn (Model $record): string => "Delivered On {$record->delivered_date?->format('F j, Y')}"),
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
                    ->multiple()
                    ->options(User::whereNot('id', auth()->id())->pluck('name', 'id'))
                    ->searchable(),
                TernaryFilter::make('purchased'),
                TernaryFilter::make('delivered'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add Item for Someone')
                    ->model(Item::class)
                    ->form([
                        Forms\Components\Select::make('user_id')
                            ->label('Person')
                            ->options(User::whereNot('id', auth()->id())->pluck('name', 'id'))
                            ->required()
                            ->searchable(),
                        Forms\Components\Section::make('Item Details')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required(),
                                Forms\Components\TextInput::make('size'),
                                Forms\Components\TextInput::make('color'),
                                Forms\Components\TextInput::make('link')
                                    ->url(),
                                Forms\Components\TextInput::make('price')
                                    ->numeric()
                                    ->prefix('$'),
                                Forms\Components\TextInput::make('store'),
                                Forms\Components\FileUpload::make('image')
                                    ->image()
                                    ->imageEditor(),
                            ]),
                    ])
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['hidden'] = true;
                        return $data;
                    })
                    ->successNotificationTitle('Item added successfully'),
            ])
            ->actions([
                EditAction::make()
                    ->form([
                        Forms\Components\Section::make('Item Details')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required(),
                                Forms\Components\TextInput::make('size'),
                                Forms\Components\TextInput::make('color'),
                                Forms\Components\TextInput::make('link')
                                    ->url(),
                                Forms\Components\TextInput::make('price')
                                    ->numeric()
                                    ->prefix('$'),
                                Forms\Components\TextInput::make('store'),
                                Forms\Components\FileUpload::make('image')
                                    ->image()
                                    ->imageEditor(),
                            ]),
                        Forms\Components\Section::make('Purchase Status')
                            ->schema([
                                Forms\Components\Toggle::make('purchased')
                                    ->label('Purchased')
                                    ->live(),
                                Forms\Components\Select::make('purchased_by')
                                    ->label('Purchased By')
                                    ->options(User::whereNot('id', auth()->id())->pluck('name', 'id'))
                                    ->searchable()
                                    ->visible(fn (Forms\Get $get) => $get('purchased')),
                                Forms\Components\DatePicker::make('purchased_date')
                                    ->label('Purchase Date')
                                    ->visible(fn (Forms\Get $get) => $get('purchased')),
                                Forms\Components\Toggle::make('delivered')
                                    ->label('Delivered')
                                    ->live()
                                    ->visible(fn (Forms\Get $get) => $get('purchased')),
                                Forms\Components\DatePicker::make('delivered_date')
                                    ->label('Delivery Date')
                                    ->visible(fn (Forms\Get $get) => $get('delivered')),
                            ]),
                    ]),
            ]);
    }
}
