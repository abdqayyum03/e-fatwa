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
                'authority_name' => 'Jabatan Mufti Wilayah Persekutuan',
                'logo' => 'mufti-wilayah.png',
                'contact_info' => '03-8888 1234',
                'street' => 'Jabatan Mufti, Kompleks Islam Putrajaya',
                'city' => 'Putrajaya',
                'poscode' => '62100',
                'state' => 'Wilayah Persekutuan',
            ],
            [
                'authority_name' => 'Jabatan Mufti Negeri Pahang',
                'logo' => 'mufti-pahang.png',
                'contact_info' => '09-1234 5678',
                'street' => 'Jabatan Mufti Negeri Pahang',
                'city' => 'Kuantan',
                'poscode' => '25000',
                'state' => 'Pahang',
            ],
            [
                'authority_name' => 'Jabatan Mufti Negeri Selangor',
                'contact_info' => '03-5514 3760',
                'street' => 'Jabatan Mufti Selangor, Bangunan Sultan Idris Shah',
                'city' => 'Shah Alam',
                'poscode' => '40000',
                'state' => 'Selangor',
            ],
            [
                'authority_name' => 'Jabatan Mufti Negeri Perak',
                'contact_info' => '05-245 6400',
                'street' => 'Jabatan Mufti Negeri Perak',
                'city' => 'Ipoh',
                'poscode' => '30000',
                'state' => 'Perak',
            ],
            [
                'authority_name' => 'Jabatan Mufti Kerajaan Negeri Sembilan',
                'contact_info' => '06-765 2425',
                'street' => 'Jabatan Mufti Negeri Sembilan',
                'city' => 'Seremban',
                'poscode' => '70000',
                'state' => 'Negeri Sembilan',
            ],
            // Added with their importers; only name, state and city (no unverified contact details).
            ['authority_name' => 'Jabatan Mufti Negeri Kedah Darul Aman', 'city' => 'Alor Setar', 'state' => 'Kedah'],
            ['authority_name' => 'Jabatan Mufti Negeri Perlis', 'city' => 'Kangar', 'state' => 'Perlis'],
            ['authority_name' => 'Jabatan Mufti Negeri Pulau Pinang', 'street' => 'Tingkat 48, KOMTAR', 'city' => 'George Town', 'state' => 'Pulau Pinang'],
            ['authority_name' => 'Jabatan Mufti Negeri Melaka', 'logo' => 'mufti-melaka.png', 'city' => 'Melaka', 'state' => 'Melaka'],
            ['authority_name' => 'Jabatan Mufti Negeri Kelantan', 'city' => 'Kota Bharu', 'state' => 'Kelantan'],
            ['authority_name' => 'Jabatan Mufti Johor', 'city' => 'Johor Bahru', 'state' => 'Johor'],
            ['authority_name' => 'Pejabat Mufti Negeri Sabah', 'city' => 'Kota Kinabalu', 'state' => 'Sabah'],
            ['authority_name' => 'Jabatan Mufti Negeri Sarawak', 'city' => 'Kuching', 'state' => 'Sarawak'],
            // No importer yet: the Terengganu site publishes no fatwa list.
            ['authority_name' => 'Jabatan Mufti Negeri Terengganu', 'city' => 'Kuala Terengganu', 'state' => 'Terengganu'],
        ];

        // Official sites the importers read from (all reachable in Nov 2026). Only filled in
        // where an admin hasn't set a website already at /admin/agensi.
        $websites = [
            'Wilayah Persekutuan' => 'https://www.muftiwp.gov.my',
            'Pahang' => 'https://mufti.pahang.gov.my',
            'Selangor' => 'https://www.muftiselangor.gov.my',
            'Perak' => 'https://mufti.perak.gov.my',
            'Negeri Sembilan' => 'https://muftins.gov.my',
            'Kedah' => 'https://ifatwa.kedah.gov.my',
            'Perlis' => 'https://muftiperlis.gov.my',
            'Pulau Pinang' => 'https://mufti.penang.gov.my',
            'Melaka' => 'https://www.muftimelaka.gov.my',
            'Johor' => 'https://said.johor.gov.my',
            'Sabah' => 'https://mufti.sabah.gov.my',
            'Sarawak' => 'https://muftinegeri.sarawak.gov.my',
            'Terengganu' => 'https://mufti.terengganu.gov.my',
        ];

        // One agency per state, matched by state so a renamed agency is updated, not duplicated.
        foreach ($authorities as $data) {
            $authority = Authority::updateOrCreate(['state' => $data['state']], $data);

            if (! $authority->website && isset($websites[$data['state']])) {
                $authority->update(['website' => $websites[$data['state']]]);
            }
        }
    }
}
