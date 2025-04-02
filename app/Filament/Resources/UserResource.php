<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    
    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 1;
    
    public static function canAccess(): bool
    {
        return \Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::user()->role === 'admin';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('User Information')
                    ->description('Basic user details')
                    ->schema([
                        Forms\Components\TextInput::make('username')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true),
                            
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(100),
                            
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                            
                        Forms\Components\Select::make('role')
                            ->required()
                            ->options([
                                'admin' => 'Administrator',
                                'kasir' => 'Kasir (Cashier)',
                            ])
                            ->default('kasir'),
                            
                        Forms\Components\DatePicker::make('active_date')
                            ->required()
                            ->default(now()),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Contact Information')
                    ->schema([
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(20),
                            
                        Forms\Components\DatePicker::make('birth_date'),
                            
                        Forms\Components\Textarea::make('address')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Security')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->required(fn ($component, $get, $record) => $record === null)
                            ->dehydrateStateUsing(fn ($state) => !empty($state) ? Hash::make($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->same('password_confirmation')
                            ->label(fn ($component, $get, $record) => $record ? 'New Password' : 'Password'),
                                
                        Forms\Components\TextInput::make('password_confirmation')
                            ->password()
                            ->required(fn ($component, $get, $record) => $record === null)
                            ->label('Confirm Password')
                            ->dehydrated(false),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('username')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'kasir' => 'success',
                    }),
                    
                Tables\Columns\TextColumn::make('active_date')
                    ->date()
                    ->sortable(),
                    
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
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'admin' => 'Administrator',
                        'kasir' => 'Kasir (Cashier)',
                    ]),
            ])
            ->actions([
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}