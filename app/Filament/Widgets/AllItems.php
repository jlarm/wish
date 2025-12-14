<?php

namespace App\Filament\Widgets;

use App\Enums\Role;
use App\Models\Item;
use App\Models\User;
use Filament\Forms;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;

class AllItems extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return in_array(auth()->user()?->role, [Role::PARENT, Role::RELATIVE], true);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Item::query()->whereNot('user_id', auth()->id()))
            ->defaultSort('purchased', 'asc')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Person')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('name')
                    ->label('Name')
                    ->formatStateUsing(fn (string $state): string => \Illuminate\Support\Str::title($state))
                    ->limit(30)
                    ->tooltip(fn (Model $record): string => \Illuminate\Support\Str::title($record->name))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                ImageColumn::make('image')
                    ->circular()
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->getStateUsing(fn (Model $record): string => match (true) {
                        $record->delivered => 'Delivered',
                        $record->purchased => 'Purchased',
                        default => 'Available',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Delivered' => 'success',
                        'Purchased' => 'warning',
                        'Available' => 'gray',
                    })
                    ->tooltip(fn (Model $record): ?string => match (true) {
                        $record->delivered && $record->delivered_date => "Delivered on {$record->delivered_date->format('M j, Y')}",
                        $record->purchased && $record->purchased_date => "Purchased on {$record->purchased_date->format('M j, Y')}",
                        default => null,
                    })
                    ->toggleable(),
                TextColumn::make('size')
                    ->toggleable(),
                TextColumn::make('color')
                    ->toggleable(),
                TextColumn::make('price')
                    ->prefix('$')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('store')
                    ->label('Store')
                    ->formatStateUsing(fn (?string $state): ?string => $state ? \Illuminate\Support\Str::title($state) : null)
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('link')
                    ->label('Link')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn ($record) => $record->link)
                    ->openUrlInNewTab()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label('User')
                    ->multiple()
                    ->options(User::whereNot('id', auth()->id())->pluck('name', 'id'))
                    ->searchable(),
                TernaryFilter::make('purchased'),
                SelectFilter::make('purchased_by')
                    ->label('Purchased By')
                    ->multiple()
                    ->options(User::pluck('name', 'id'))
                    ->searchable(),
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
