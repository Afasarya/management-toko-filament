<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Models\Expense;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    
    protected static ?string $navigationGroup = 'Transactions';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Expense Information')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true)
                            ->default(function () {
                                $lastExpense = Expense::orderBy('id', 'desc')->first();
                                $lastId = $lastExpense ? $lastExpense->id : 0;
                                $nextId = str_pad($lastId + 1, 6, '0', STR_PAD_LEFT);
                                return '000' . $nextId;
                            }),
                            
                        Forms\Components\TextInput::make('invoice_number')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->default(function () {
                                $lastExpense = Expense::orderBy('id', 'desc')->first();
                                $lastId = $lastExpense ? $lastExpense->id : 0;
                                $nextId = str_pad($lastId + 1, 6, '0', STR_PAD_LEFT);
                                return 'KAS' . $nextId;
                            }),
                            
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(100),
                            
                        Forms\Components\DatePicker::make('expense_date')
                            ->required()
                            ->default(now()),
                            
                        Forms\Components\TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),
                            
                        Forms\Components\Select::make('user_id')
                            ->label('Recorded By')
                            ->relationship('user', 'name')
                            ->required()
                            ->default(fn() => \Illuminate\Support\Facades\Auth::id())
                            ->preload()
                            ->searchable(),
                            
                        Forms\Components\Textarea::make('description')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('invoice_number')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('expense_date')
                    ->date()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('amount')
                    ->money('IDR')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Recorded By')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('expense_date')
                    ->form([
                        Forms\Components\DatePicker::make('date'),
                    ])
                    ->query(function ($query, array $data) {
                        if ($data['date']) {
                            $query->whereDate('expense_date', $data['date']);
                        }
                    })
                    ->indicateUsing(function ($data) {
                        return 'Expense Date: ' . $data['date'];
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'view' => Pages\ViewExpense::route('/{record}'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereDate('expense_date', today())->count() ?: null;
    }
}