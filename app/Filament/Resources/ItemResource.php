<?php

namespace App\Filament\Resources;

use App\Enums\Role;
use App\Filament\Resources\ItemResource\Pages;
use App\Filament\Resources\ItemResource\RelationManagers;
use App\Models\Item;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;

class ItemResource extends Resource
{
    protected static ?string $model = Item::class;

    protected static ?string $navigationLabel = 'Your List';

    protected static ?string $navigationIcon = 'heroicon-o-gift';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->columnSpanFull()
                            ->required(),
                        Forms\Components\TextInput::make('size')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('color')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('link')
                            ->url()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('price')
                            ->numeric()
                            ->prefix('$')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('store')
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('image')
                            ->image()
                            ->imageEditor()
                    ]),
                Forms\Components\Section::make('Purchase Status')
                    ->schema([
                        Forms\Components\Toggle::make('purchased')
                            ->label('Purchased')
                            ->live()
                            ->columnSpanFull(),
                        Forms\Components\Select::make('purchased_by')
                            ->label('Purchased By')
                            ->options(User::pluck('name', 'id'))
                            ->searchable()
                            ->columnSpanFull()
                            ->visible(fn (Forms\Get $get) => $get('purchased')),
                        Forms\Components\DatePicker::make('purchased_date')
                            ->label('Purchase Date')
                            ->columnSpanFull()
                            ->visible(fn (Forms\Get $get) => $get('purchased')),
                        Forms\Components\Toggle::make('delivered')
                            ->label('Delivered')
                            ->live()
                            ->columnSpanFull()
                            ->visible(fn (Forms\Get $get) => $get('purchased')),
                        Forms\Components\DatePicker::make('delivered_date')
                            ->label('Delivery Date')
                            ->columnSpanFull()
                            ->visible(fn (Forms\Get $get) => $get('delivered')),
                    ])
                    ->visible(fn (?Item $record) =>
                        in_array(auth()->user()?->role, [Role::PARENT, Role::RELATIVE], true) &&
                        $record?->user_id !== auth()->id()
                    ),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->formatStateUsing(fn (string $state): string => \Illuminate\Support\Str::title($state))
                    ->limit(30)
                    ->tooltip(fn (Model $record): string => \Illuminate\Support\Str::title($record->name))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('size')
                    ->sortable(),
                Tables\Columns\TextColumn::make('color')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->prefix('$')
                    ->sortable(),
                Tables\Columns\TextColumn::make('store')
                    ->label('Store')
                    ->formatStateUsing(fn (?string $state): ?string => $state ? \Illuminate\Support\Str::title($state) : null)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('link')
                    ->label('Link')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn ($record) => $record->link)
                    ->openUrlInNewTab(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth()->id())
            ->where('hidden', false);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListItems::route('/'),
            'create' => Pages\CreateItem::route('/create'),
            'edit' => Pages\EditItem::route('/{record}/edit'),
        ];
    }
}
