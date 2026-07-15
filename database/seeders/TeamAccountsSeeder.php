<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
class TeamAccountsSeeder extends Seeder
{
    
public function run(): void
{
    $team = [
        [
            'firstName' => 'Tasneem',
            'lastName'  => 'Awad',
            'email'     => 'tasrawad1211@gmail.com',
            'dob'       => '2005-05-19',
            'password'  => 'ta0947842155'
        ],
        [
            'firstName' => 'Tasnim',
            'lastName'  => 'Mardini',
            'email'     => 'mardinitasnim35@gmail.com',
            'dob'       => '2004-08-08',
            'password'  => 'Tasnim+++sela7/8/2023'
        ],
        [
            'firstName' => 'Samira',
            'lastName'  => 'Snobar',
            'email'     => 'samirasnobar6@gmail.com',
            'dob'       => '2005-06-03',
            'password'  => '##123samira123##'
        ],
        [
            'firstName' => 'Rama',
            'lastName'  => 'Hijazi-Albasha',
            'email'     => 'Ramaalbasha66@gmail.com',
            'dob'       => '2006-01-01', 
            'password'  => 'RAMA123'
        ],
        [
            'firstName' => 'Sndos',
            'lastName'  => 'AlHomsi',
            'email'     => 'qween7339@gmail.com',
            'dob'       => '2006-02-15', 
            'password'  => 'Sndos2007'
        ],
    ];

    foreach ($team as $member) {
        \App\Models\User::create([
            'firstName'   => $member['firstName'],
            'lastName'    => $member['lastName'],
            'email'       => $member['email'],
            'password'    => \Illuminate\Support\Facades\Hash::make($member['password']),
            'dateOfBirth' => $member['dob'],
            'role'        => 'admin',
        ]);
    }
}
}
