<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Services\SettingStore;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'group' => 'general',
                'name' => 'site_active',
                'locked' => false,
                'payload' => ['value' => true],
            ],
            [
                'group' => 'general',
                'name' => 'store_name',
                'locked' => false,
                'payload' => ['value' => 'SmartPOS Demo Store'],
            ],
            [
                'group' => 'general',
                'name' => 'store_phone',
                'locked' => false,
                'payload' => ['value' => '+91 98765 43210'],
            ],
            [
                'group' => 'appearance',
                'name' => 'theme',
                'locked' => true,
                'payload' => ['mode' => 'dark', 'primary_color' => '#f59e0b'],
            ],
            [
                'group' => 'pos',
                'name' => 'default_tax_group',
                'locked' => false,
                'payload' => ['hsn' => 'HSN-18'],
            ],
            [
                'group' => 'pos',
                'name' => 'receipt_footer',
                'locked' => false,
                'payload' => ['text' => 'Thank you for shopping with us!'],
            ],
        ];

        foreach ($settings as $setting) {
            Setting::query()->updateOrCreate(
                ['name' => $setting['name']],
                $setting,
            );
        }

        SettingStore::set('contact_details', [
            'shop_name' => 'GharGrocer',
            'email' => 'support@ghargrocer.com',
            'primary_phone' => '9913705841',
            'website_url' => 'https://ghargrocer.com',
            'address' => 'Shop No 3 Desai wad Sun Enclave Building Ground Floor Vapi Town',
            'google_map_address' => '',
            'other_phones' => [
                ['name' => 'Darshan', 'phone' => '9662915733'],
            ],
            'other_emails' => [],
        ], 'contact');

        SettingStore::set('social_links', [
            'whatsapp' => '',
            'instagram' => '',
            'youtube' => '',
            'linkedin' => '',
            'facebook' => '',
            'twitter' => '',
        ], 'contact');

        SettingStore::set('company_details', [
            'firm_pan_number' => 'ABCDE1234F',
            'gst_number' => '24ABCDE1234F1Z5',
            'fssai_license' => '12345678901234',
        ], 'company');
    }
}
