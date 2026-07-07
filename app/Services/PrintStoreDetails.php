<?php

namespace App\Services;

class PrintStoreDetails
{
    /**
     * @return array<string, string|null>
     */
    public static function resolve(): array
    {
        $contact = SettingStore::get('contact_details');
        $company = SettingStore::get('company_details');
        $legacyStoreName = data_get(SettingStore::get('store_name'), 'value');
        $legacyStorePhone = data_get(SettingStore::get('store_phone'), 'value');
        $receiptFooter = data_get(SettingStore::get('receipt_footer'), 'text');

        return [
            'name' => filled($contact['shop_name'] ?? null)
                ? (string) $contact['shop_name']
                : ($legacyStoreName ?: 'SmartPOS'),
            'email' => filled($contact['email'] ?? null) ? (string) $contact['email'] : null,
            'phone' => filled($contact['primary_phone'] ?? null)
                ? (string) $contact['primary_phone']
                : $legacyStorePhone,
            'website' => filled($contact['website_url'] ?? null) ? (string) $contact['website_url'] : null,
            'address' => filled($contact['address'] ?? null) ? (string) $contact['address'] : null,
            'gst_number' => filled($company['gst_number'] ?? null) ? (string) $company['gst_number'] : null,
            'pan_number' => filled($company['firm_pan_number'] ?? null) ? (string) $company['firm_pan_number'] : null,
            'fssai_license' => filled($company['fssai_license'] ?? null) ? (string) $company['fssai_license'] : null,
            'footer' => $receiptFooter,
            'logo' => self::logoUrl(),
        ];
    }

    protected static function logoUrl(): ?string
    {
        foreach (['logo.png', 'logo.jpeg', 'logo.jpg'] as $filename) {
            if (file_exists(public_path($filename))) {
                return asset($filename);
            }
        }

        return null;
    }
}
