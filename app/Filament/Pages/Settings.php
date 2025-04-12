<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class Settings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-8-tooth';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.settings';
    
    // Company settings
    public $company_name = '';
    public $company_tagline = '';
    public $company_email = '';
    public $company_phone = '';
    public $company_website = '';
    public $company_address = '';
    
    // File uploads - IMPORTANT: Must be initialized as arrays for Filament
    public $company_logo = [];
    public $app_login_background = [];
    
    // Invoice settings
    public $invoice_prefix = '';
    public $invoice_suffix = '';
    public $invoice_signature = '';
    
    // App settings
    public $app_name = '';
    public $app_footer = '';
    public $app_session_time = 3000;

    public function mount(): void
    {
        // Get settings from database
        $settings = Setting::all();
        
        // Group settings by their type
        $settingsMap = [];
        foreach ($settings as $setting) {
            $key = "{$setting->group}_{$setting->key}";
            $settingsMap[$key] = $setting->value;
        }
        
        // Text fields - just assign directly
        $textFields = [
            'company_name', 'company_tagline', 'company_email', 'company_phone', 
            'company_website', 'company_address', 'invoice_prefix', 'invoice_suffix', 
            'invoice_signature', 'app_name', 'app_footer', 'app_session_time'
        ];
        
        foreach ($textFields as $field) {
            if (isset($settingsMap[$field])) {
                $this->{$field} = $settingsMap[$field];
            }
        }
        
        // Special handling for file uploads - must be arrays
        // If we have a company logo stored as a string, convert it to array format
        if (isset($settingsMap['company_logo']) && !empty($settingsMap['company_logo'])) {
            // Convert string path to array format that Filament expects
            $this->company_logo = [$settingsMap['company_logo']];
            Log::info('Mounted company_logo', ['value' => $this->company_logo]);
        }
        
        // Same for login background
        if (isset($settingsMap['app_login_background']) && !empty($settingsMap['app_login_background'])) {
            $this->app_login_background = [$settingsMap['app_login_background']];
            Log::info('Mounted app_login_background', ['value' => $this->app_login_background]);
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Settings')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Company')
                            ->schema([
                                Forms\Components\Section::make('Company Information')
                                    ->schema([
                                        Forms\Components\TextInput::make('company_name')
                                            ->label('Company Name')
                                            ->required()
                                            ->helperText('Your company name (used in application branding)'),
                                        
                                        Forms\Components\TextInput::make('company_tagline')
                                            ->label('Company Tagline')
                                            ->helperText('A short tagline for your company'),
                                        
                                        Forms\Components\TextInput::make('company_email')
                                            ->label('Company Email')
                                            ->email()
                                            ->helperText('Your company contact email'),
                                        
                                        Forms\Components\TextInput::make('company_phone')
                                            ->label('Company Phone')
                                            ->tel()
                                            ->helperText('Your company contact phone number'),
                                        
                                        Forms\Components\TextInput::make('company_website')
                                            ->label('Company Website')
                                            ->url()
                                            ->helperText('Your company website URL'),
                                        
                                        Forms\Components\Textarea::make('company_address')
                                            ->label('Company Address')
                                            ->rows(3)
                                            ->columnSpanFull()
                                            ->helperText('Your company full address'),
                                    ])->columns(2),
                                
                                Forms\Components\Section::make('Company Logo')
                                    ->schema([
                                        Forms\Components\FileUpload::make('company_logo')
                                            ->label('Logo')
                                            ->disk('public')
                                            ->directory('images')
                                            ->visibility('public')
                                            ->image()
                                            ->multiple(false)
                                            ->helperText('This logo will appear in the application header and login page. Refresh after upload to see changes.')
                                            ->maxSize(2048),
                                    ]),
                            ]),
                        
                        Forms\Components\Tabs\Tab::make('Invoice')
                            ->schema([
                                Forms\Components\Section::make('Invoice Settings')
                                    ->schema([
                                        Forms\Components\TextInput::make('invoice_prefix')
                                            ->label('Invoice Prefix')
                                            ->helperText('Prefix added to invoice numbers (e.g. INV-)'),
                                        
                                        Forms\Components\TextInput::make('invoice_suffix')
                                            ->label('Invoice Suffix')
                                            ->helperText('Suffix added to invoice numbers (e.g. -2023)'),
                                        
                                        Forms\Components\Textarea::make('invoice_signature')
                                            ->label('Invoice Signature')
                                            ->helperText('This text will appear at the bottom of receipts and invoices'),
                                    ]),
                            ]),
                        
                        Forms\Components\Tabs\Tab::make('Application')
                            ->schema([
                                Forms\Components\Section::make('Application Settings')
                                    ->schema([
                                        Forms\Components\TextInput::make('app_name')
                                            ->label('Application Name')
                                            ->required()
                                            ->helperText('The name of the application shown in browser title'),
                                        
                                        Forms\Components\TextInput::make('app_footer')
                                            ->label('Footer Text')
                                            ->helperText('Text displayed in the footer of the application'),
                                        
                                        Forms\Components\TextInput::make('app_session_time')
                                            ->label('Session Timeout (seconds)')
                                            ->numeric()
                                            ->default(3000)
                                            ->helperText('How long until users are logged out due to inactivity'),
                                        
                                        Forms\Components\FileUpload::make('app_login_background')
                                            ->label('Login Background')
                                            ->disk('public')
                                            ->directory('images')
                                            ->visibility('public')
                                            ->image()
                                            ->multiple(false)
                                            ->helperText('Background image for the login page')
                                            ->maxSize(5120),
                                    ])->columns(2),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }
    
    public function submit(): void
    {
        try {
            // Log all properties for debugging
            $props = get_object_vars($this);
            Log::info('Submit called with properties:', array_filter($props, function($key) {
                // Filter out internal properties
                return !str_starts_with($key, '_');
            }, ARRAY_FILTER_USE_KEY));
            
            // Process text fields directly
            $textFields = [
                'company_name', 'company_tagline', 'company_email', 'company_phone', 
                'company_website', 'company_address', 'invoice_prefix', 'invoice_suffix', 
                'invoice_signature', 'app_name', 'app_footer', 'app_session_time'
            ];
            
            foreach ($textFields as $field) {
                list($group, $key) = explode('_', $field, 2);
                
                Setting::updateOrCreate(
                    ['group' => $group, 'key' => $key],
                    ['value' => $this->{$field}]
                );
            }
            
            // Handle file uploads specially
            // For company logo
            if (!empty($this->company_logo)) {
                $logoValue = is_array($this->company_logo) ? $this->company_logo[0] : $this->company_logo;
                
                Log::info('Saving company logo', ['value' => $logoValue]);
                
                Setting::updateOrCreate(
                    ['group' => 'company', 'key' => 'logo'],
                    ['value' => $logoValue]
                );
            }
            
            // For app login background
            if (!empty($this->app_login_background)) {
                $backgroundValue = is_array($this->app_login_background) ? $this->app_login_background[0] : $this->app_login_background;
                
                Log::info('Saving app login background', ['value' => $backgroundValue]);
                
                Setting::updateOrCreate(
                    ['group' => 'app', 'key' => 'login_background'],
                    ['value' => $backgroundValue]
                );
            }
            
            // Clear all caches
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            
            // Notify success
            Notification::make()
                ->title('Settings saved successfully')
                ->body('Your changes have been saved. Please refresh the page to see them.')
                ->success()
                ->send();
                
        } catch (\Throwable $e) {
            Log::error('Error saving settings: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            Notification::make()
                ->title('Error saving settings')
                ->body('An error occurred: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    public static function canAccess(): bool
    {
        return \Illuminate\Support\Facades\Auth::user()->role === 'admin';
    }
}