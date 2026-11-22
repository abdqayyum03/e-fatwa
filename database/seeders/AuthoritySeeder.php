<?php

namespace Database\Seeders;

use App\Models\Authority;
use Illuminate\Database\Seeder;

class AuthoritySeeder extends Seeder
{
    public function run(): void
    {
        $authorities = [
            [
                'authority_name' => 'Mufti Wilayah Persekutuan',
                'logo' => 'mufti-wilayah.png',
                'contact_info' => '03-8888 1234',
                'street' => 'Jabatan Mufti, Kompleks Islam Putrajaya',
                'city' => 'Putrajaya',
                'poscode' => '62100',
                'state' => 'Wilayah Persekutuan',
            ],
            [
                'authority_name' => 'Mufti Pahang',
                'logo' => 'mufti-pahang.png',
                'contact_info' => '09-1234 5678',
                'street' => 'Jabatan Mufti Negeri Pahang',
                'city' => 'Kuantan',
                'poscode' => '25000',
                'state' => 'Pahang',
            ],
            [
                'authority_name' => 'Mufti Selangor',
                'contact_info' => '03-5514 3760',
                'street' => 'Jabatan Mufti Selangor, Bangunan Sultan Idris Shah',
                'city' => 'Shah Alam',
                'poscode' => '40000',
                'state' => 'Selangor',
            ],
            [
                'authority_name' => 'Mufti Perak',
                'contact_info' => '05-245 6400',
                'street' => 'Jabatan Mufti Negeri Perak',
                'city' => 'Ipoh',
                'poscode' => '30000',
                'state' => 'Perak',
            ],
            [
                'authority_name' => 'Mufti Negeri Sembilan',
                'contact_info' => '06-765 2425',
                'street' => 'Jabatan Mufti Negeri Sembilan',
                'city' => 'Seremban',
                'poscode' => '70000',
                'state' => 'Negeri Sembilan',
            ],
        ];

        foreach ($authorities as $data) {
            Authority::updateOrCreate(['authority_name' => $data['authority_name']], $data);
        }
    }
}
