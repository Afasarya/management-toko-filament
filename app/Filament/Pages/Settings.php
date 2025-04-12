<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
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
    
    // Form data
    public ?array $formData = [];

    public function mount(): void
    {
        $settings = Setting::all();
        
        // Initialize formData with default values
        $this->formData = [
            'company_name' => '',
            'company_tagline' => '',
            'company_email' => '',
            'company_phone' => '',
            'company_website' => '',
            'company_address' => '',
            'company_logo' => null,
            'invoice_prefix' => '',
            'invoice_suffix' => '',
            'invoice_signature' => '',
            'app_name' => '',
            'app_footer' => '',
            'app_session_time' => '3000',
            'app_login_background' => null,
        ];
        
        // Populate formData with values from database
        foreach ($settings as $setting) {
            $key = "{$setting->group}_{$setting->key}";
            
            if (array_key_exists($key, $this->formData)) {
                // For file uploads, adapt the format
                if ($key === 'company_logo' || $key === 'app_login_background') {
                    // Check if the file exists
                    if (!empty($setting->value) && Storage::disk('public')->exists($setting->value)) {
                        $this->formData[$key] = $setting->value;
                    }
                } else {
                    $this->formData[$key] = $setting->value;
                }
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
                                        TextInput::make('formData.company_name')
                                            ->label('Company Name')
                                            ->required(),
                                        
                                        TextInput::make('formData.company_tagline')
                                            ->label('Company Tagline'),
                                        
                                        TextInput::make('formData.company_email')
                                            ->label('Company Email')
                                            ->email(),
                                        
                                        TextInput::make('formData.company_phone')
                                            ->label('Company Phone')
                                            ->tel(),
                                        
                                        TextInput::make('formData.company_website')
                                            ->label('Company Website')
                                            ->url(),
                                        
                                        Textarea::make('formData.company_address')
                                            ->label('Company Address')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                    ])->columns(2),
                                
                                Section::make('Company Logo')
                                    ->schema([
                                        Forms\Components\FileUpload::make('formData.company_logo')
                                            ->label('Logo')
                                            ->disk('public')
                                            ->directory('logos')
                                            ->visibility('public')
                                            ->image()
                                            ->helperText('This logo will appear in the application header and login page')
                                            ->maxSize(2048)
                                            ->imageResizeMode('cover')
                                            ->imageCropAspectRatio('16:9')
                                            ->imageResizeTargetWidth('400')
                                            ->imageResizeTargetHeight('400'),
                                    ]),
                            ]),
                        
                        Tabs\Tab::make('Invoice')
                            ->schema([
                                Section::make('Invoice Settings')
                                    ->schema([
                                        TextInput::make('formData.invoice_prefix')
                                            ->label('Invoice Prefix'),
                                        
                                        TextInput::make('formData.invoice_suffix')
                                            ->label('Invoice Suffix'),
                                        
                                        Textarea::make('formData.invoice_signature')
                                            ->label('Invoice Signature')
                                            ->helperText('This text will appear at the bottom of receipts and invoices'),
                                    ]),
                            ]),
                        
                        Tabs\Tab::make('Application')
                            ->schema([
                                Section::make('Application Settings')
                                    ->schema([
                                        TextInput::make('formData.app_name')
                                            ->label('Application Name')
                                            ->required(),
                                        
                                        TextInput::make('formData.app_footer')
                                            ->label('Footer Text'),
                                        
                                        TextInput::make('formData.app_session_time')
                                            ->label('Session Timeout (seconds)')
                                            ->numeric()
                                            ->default(3000),
                                        
                                        Forms\Components\FileUpload::make('formData.app_login_background')
                                            ->label('Login Background')
                                            ->disk('public')
                                            ->directory('backgrounds')
                                            ->visibility('public')
                                            ->image()
                                            ->helperText('This image will be used as the login page background')
                                            ->maxSize(5120),
                                    ])->columns(2),
                            ]),
                    ])->columnSpanFull(),
            ])
            ->statePath('formData');
    }
    
    public function submit(): void
    {
        try {
            $data = $this->formData;
            
            foreach ($data as $key => $value) {
                // Skip if the key doesn't contain an underscore
                if (!strpos($key, '_')) {
                    continue;
                }
                
                // Split the key into group and setting key
                list($group, $settingKey) = explode('_', $key, 2);
                
                // Handle file uploads
                if ($key === 'company_logo' || $key === 'app_login_background') {
                    // Skip if empty
                    if (empty($value)) {
                        continue;
                    }
                    
                    // If a real new file was uploaded
                    if (is_array($value)) {
                        // Make sure we're getting the file path
                        $filePath = is_array($value) ? $value : (is_string($value) ? $value : null);
                        
                        // If there's a file path, update the setting
                        if ($filePath) {
                            Setting::updateOrCreate(
                                ['group' => $group, 'key' => $settingKey],
                                ['value' => $filePath]
                            );
                            
                            // If this is the company logo, update Filament configuration
                            if ($key === 'company_logo') {
                                $this->updateFilamentBrandingConfig($filePath);
                            }
                        }
                    } else {
                        // This is an existing file path
                        Setting::updateOrCreate(
                            ['group' => $group, 'key' => $settingKey],
                            ['value' => $value]
                        );
                        
                        // If this is the company logo, update Filament configuration
                        if ($key === 'company_logo') {
                            $this->updateFilamentBrandingConfig($value);
                        }
                    }
                } else {
                    // Regular setting
                    Setting::updateOrCreate(
                        ['group' => $group, 'key' => $settingKey],
                        ['value' => $value]
                    );
                }
            }
            
            Notification::make()
                ->title('Settings saved successfully')
                ->success()
                ->send();
                
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Error saving settings: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error($e->getTraceAsString());
            
            Notification::make()
                ->title('Error saving settings')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    /**
     * Update Filament branding config with the new logo
     */
    protected function updateFilamentBrandingConfig($logoPath): void
    {
        try {
            // Create full URL to the logo
            $logoUrl = asset('storage/' . $logoPath);
            
            // Update app.php config
            $configPath = config_path('filament.php');
            
            if (file_exists($configPath)) {
                $config = include $configPath;
                
                // Create the file if it doesn't exist
                if (!file_exists($configPath)) {
                    file_put_contents($configPath, "<?php\n\nreturn " . var_export($config, true) . ";\n");
                }
                
                // Update the config
                $configContent = file_get_contents($configPath);
                
                // Replace or add logo path
                if (strpos($configContent, "'default_favicon_path'") !== false) {
                    $configContent = preg_replace(
                        "/'default_favicon_path' => '.*?'/",
                        "'default_favicon_path' => '{$logoUrl}'",
                        $configContent
                    );
                }
                
                if (strpos($configContent, "'favicon'") !== false) {
                    $configContent = preg_replace(
                        "/'favicon' => '.*?'/",
                        "'favicon' => '{$logoUrl}'",
                        $configContent
                    );
                }
                
                file_put_contents($configPath, $configContent);
            } else {
                // Create basic config if it doesn't exist
                $config = [
                    'favicon' => $logoUrl,
                    'default_favicon_path' => $logoUrl,
                    'brand' => [
                        'logo' => $logoUrl,
                    ],
                ];
                
                file_put_contents($configPath, "<?php\n\nreturn " . var_export($config, true) . ";\n");
            }
            
            // Clear config cache
            \Illuminate\Support\Facades\Artisan::call('config:clear');
            
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to update Filament branding: ' . $e->getMessage());
        }
    }
    
    public static function canAccess(): bool
    {
        return \Illuminate\Support\Facades\Auth::user()->role === 'admin';
    }
}