<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Company settings
        $companySettings = [
            ['group' => 'company', 'key' => 'name', 'value' => 'Psajj Toko'],
            ['group' => 'company', 'key' => 'tagline', 'value' => 'Tempat belanja terbaik'],
            ['group' => 'company', 'key' => 'address', 'value' => 'Jln Wangon'],
            ['group' => 'company', 'key' => 'phone', 'value' => '08319078158'],
            ['group' => 'company', 'key' => 'email', 'value' => 'info@psajjtoko.com'],
            ['group' => 'company', 'key' => 'website', 'value' => 'www.psajjtoko.com'],
            ['group' => 'company', 'key' => 'logo', 'value' => 'assets/images/logo.png'],
        ];
        
        // Invoice settings
        $invoiceSettings = [
            ['group' => 'invoice', 'key' => 'prefix', 'value' => 'INV'],
            ['group' => 'invoice', 'key' => 'suffix', 'value' => date('Y')],
            ['group' => 'invoice', 'key' => 'signature', 'value' => 'Barang yang sudah dibeli tidak dapat dikembalikan. Terima Kasih'],
        ];
        
        // Application settings
        $appSettings = [
            ['group' => 'app', 'key' => 'name', 'value' => 'GudangX POS'],
            ['group' => 'app', 'key' => 'version', 'value' => '1.0.0'],
            ['group' => 'app', 'key' => 'footer', 'value' => 'Psajj Sehat'],
            ['group' => 'app', 'key' => 'session_time', 'value' => '3000'],
            ['group' => 'app', 'key' => 'login_background', 'value' => 'assets/images/login-bg.jpg'],
        ];
        
        foreach (array_merge($companySettings, $invoiceSettings, $appSettings) as $setting) {
            Setting::create($setting);
        }
    }
}