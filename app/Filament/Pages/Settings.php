<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class Settings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-8-tooth';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = Setting::all()->groupBy('group');
        
        foreach ($settings as $group => $items) {
            foreach ($items as $item) {
                $this->data["{$group}.{$item->key}"] = $item->value;
            }
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Settings')
                    ->tabs([
                        Tabs\Tab::make('Company')
                            ->schema([
                                Section::make('Company Information')
                                    ->schema([
                                        TextInput::make('company.name')
                                            ->label('Company Name')
                                            ->required(),
                                        
                                        TextInput::make('company.tagline')
                                            ->label('Company Tagline'),
                                        
                                        TextInput::make('company.email')
                                            ->label('Company Email')
                                            ->email(),
                                        
                                        TextInput::make('company.phone')
                                            ->label('Company Phone')
                                            ->tel(),
                                        
                                        TextInput::make('company.website')
                                            ->label('Company Website')
                                            ->url(),
                                        
                                        Textarea::make('company.address')
                                            ->label('Company Address')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                    ])->columns(2),
                                
                                Section::make('Company Logo')
                                    ->schema([
                                        FileUpload::make('company.logo')
                                            ->label('Logo')
                                            ->image()
                                            ->directory('company')
                                            ->visibility('public')
                                            ->imagePreviewHeight('150')
                                            ->maxSize(1024),
                                    ]),
                            ]),
                        
                        Tabs\Tab::make('Invoice')
                            ->schema([
                                Section::make('Invoice Settings')
                                    ->schema([
                                        TextInput::make('invoice.prefix')
                                            ->label('Invoice Prefix'),
                                        
                                        TextInput::make('invoice.suffix')
                                            ->label('Invoice Suffix'),
                                        
                                        Textarea::make('invoice.signature')
                                            ->label('Invoice Signature')
                                            ->helperText('This text will appear at the bottom of receipts and invoices'),
                                    ]),
                            ]),
                        
                        Tabs\Tab::make('Application')
                            ->schema([
                                Section::make('Application Settings')
                                    ->schema([
                                        TextInput::make('app.name')
                                            ->label('Application Name')
                                            ->required(),
                                        
                                        TextInput::make('app.footer')
                                            ->label('Footer Text'),
                                        
                                        TextInput::make('app.session_time')
                                            ->label('Session Timeout (seconds)')
                                            ->numeric()
                                            ->default(3000),
                                        
                                        FileUpload::make('app.login_background')
                                            ->label('Login Background')
                                            ->image()
                                            ->directory('backgrounds')
                                            ->visibility('public'),
                                    ])->columns(2),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }
    
    public function submit(): void
    {
        $data = $this->form->getState();
        
        foreach ($data as $key => $value) {
            list($group, $settingKey) = explode('.', $key);
            
            Setting::updateOrCreate(
                ['group' => $group, 'key' => $settingKey],
                ['value' => $value]
            );
        }
        
        Notification::make()
            ->title('Settings saved successfully')
            ->success()
            ->send();
    }
    
    public static function canAccess(): bool
    {
        return \Illuminate\Support\Facades\Auth::user()->role === 'admin';
    }
}