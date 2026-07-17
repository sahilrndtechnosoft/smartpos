<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        Customer::cashCustomer();

        $customers = [
            [
                'phone' => '9724806960',
                'name' => 'Aniket',
                'email' => 'aniketatworkplace@gmail.com',
                'gender' => 'male',
                'birthday' => '2000-01-05',
                'is_active' => true,
                'phone_verified_at' => now()->subMonths(7),
                'preferences' => [
                    'email_notifications' => true,
                    'sms_notifications' => true,
                    'whatsapp_notifications' => true,
                ],
                'notes' => null,
                'address' => [
                    'title' => 'Home',
                    'receiver_name' => 'Aniket',
                    'receiver_phone' => '9724806960',
                    'street' => "204-A Samir Apartment\nChanod Colony Behind Gurudwara\nVapi\nNear Aarti Bldg",
                    'city' => 'Chanod',
                    'state' => 'Gujarat',
                    'country' => 'India',
                    'latitude' => 20.3441942,
                    'longitude' => 72.9362092,
                    'description' => null,
                    'meta' => [
                        'address_details' => [
                            'address1' => '204-A Samir Apartment',
                            'address2' => 'Chanod Colony Behind Gurudwara',
                            'address3' => 'Vapi',
                            'landmark' => 'Near Aarti Bldg',
                        ],
                        'distance_to_shop_km' => 4.35,
                    ],
                ],
            ],
            [
                'phone' => '9876543210',
                'name' => 'Rahul Sharma',
                'email' => 'rahul.sharma@example.com',
                'gender' => 'male',
                'birthday' => '1992-04-15',
                'is_active' => true,
                'phone_verified_at' => now()->subDays(30),
                'preferences' => [
                    'email_notifications' => true,
                    'sms_notifications' => false,
                    'whatsapp_notifications' => true,
                ],
                'notes' => 'Regular weekend shopper.',
                'address' => [
                    'title' => 'Home',
                    'receiver_name' => 'Rahul Sharma',
                    'receiver_phone' => '9876543210',
                    'street' => '12 MG Road, Near City Mall',
                    'city' => 'Pune',
                    'state' => 'Maharashtra',
                    'country' => 'India',
                    'latitude' => 18.5204300,
                    'longitude' => 73.8567430,
                    'description' => 'Primary delivery address',
                    'meta' => ['distance_to_shop_km' => 2.10],
                ],
            ],
            [
                'phone' => '9123456780',
                'name' => 'Priya Patel',
                'email' => 'priya.patel@example.com',
                'gender' => 'female',
                'birthday' => '1995-09-22',
                'is_active' => true,
                'phone_verified_at' => now()->subDays(10),
                'preferences' => [
                    'email_notifications' => false,
                    'sms_notifications' => true,
                    'whatsapp_notifications' => false,
                ],
                'notes' => 'Prefers dairy and staples.',
                'address' => [
                    'title' => 'Office',
                    'receiver_name' => 'Priya Patel',
                    'receiver_phone' => '9123456780',
                    'street' => '501 Business Park, Sector 18',
                    'city' => 'Ahmedabad',
                    'state' => 'Gujarat',
                    'country' => 'India',
                    'latitude' => 23.0225050,
                    'longitude' => 72.5713620,
                    'description' => 'Office pickup point',
                    'meta' => ['distance_to_shop_km' => 6.80],
                ],
            ],
            [
                'phone' => '9012345678',
                'name' => 'Sneha Reddy',
                'email' => 'sneha.reddy@example.com',
                'gender' => 'female',
                'birthday' => '1999-12-03',
                'is_active' => false,
                'phone_verified_at' => now()->subDays(60),
                'preferences' => [
                    'email_notifications' => false,
                    'sms_notifications' => false,
                    'whatsapp_notifications' => false,
                ],
                'notes' => 'Inactive account — moved cities.',
                'address' => null,
            ],
        ];

        foreach ($customers as $customerData) {
            $addressData = $customerData['address'] ?? null;
            unset($customerData['address']);

            $customer = Customer::query()->updateOrCreate(
                ['phone' => $customerData['phone']],
                $customerData,
            );

            if ($addressData === null) {
                continue;
            }

            $address = Address::query()->updateOrCreate(
                [
                    'title' => $addressData['title'],
                    'receiver_phone' => $addressData['receiver_phone'],
                    'city' => $addressData['city'],
                ],
                $addressData,
            );

            $customer->addresses()->syncWithoutDetaching([$address->id]);
        }
    }
}
