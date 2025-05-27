<?php

// database/seeders/UserSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'System Administrator',
            'email' => 'admin@tournamentmanager.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'phone' => '+1234567890',
            'is_active' => true,
        ]);

        // Create team managers
        $managers = [
            ['name' => 'John Smith', 'email' => 'john@realmadrid.com'],
            ['name' => 'Maria Garcia', 'email' => 'maria@barcelona.com'],
            ['name' => 'David Johnson', 'email' => 'david@manchester.com'],
            ['name' => 'Anna Williams', 'email' => 'anna@arsenal.com'],
        ];

        foreach ($managers as $manager) {
            User::create([
                'name' => $manager['name'],
                'email' => $manager['email'],
                'password' => Hash::make('password123'),
                'role' => 'team_manager',
                'phone' => '+' . rand(1000000000, 9999999999),
                'is_active' => true,
            ]);
        }

        // Create players
        $players = [
            'Lionel Messi',
            'Cristiano Ronaldo',
            'Neymar Jr',
            'Kylian Mbappé',
            'Robert Lewandowski',
            'Kevin De Bruyne',
            'Virgil van Dijk',
            'Mohamed Salah',
            'Sadio Mané',
            'Sergio Ramos',
            'Luka Modric',
            'Toni Kroos',
            'Harry Kane',
            'Raheem Sterling',
            'Jadon Sancho',
            'Marcus Rashford',
            'Pedri González',
            'Gavi',
            'Erling Haaland',
            'Vinicius Jr',
            'Jude Bellingham',
            'Phil Foden',
            'Bruno Fernandes',
            'Casemiro'
        ];

        foreach ($players as $index => $playerName) {
            User::create([
                'name' => $playerName,
                'email' => strtolower(str_replace(' ', '.', $playerName)) . '@player.com',
                'password' => Hash::make('password123'),
                'role' => 'player',
                'phone' => '+' . rand(1000000000, 9999999999),
                'is_active' => true,
            ]);
        }

        // Create referees
        $referees = ['John Referee', 'Mike Official', 'Sarah Judge'];
        foreach ($referees as $referee) {
            User::create([
                'name' => $referee,
                'email' => strtolower(str_replace(' ', '.', $referee)) . '@referee.com',
                'password' => Hash::make('password123'),
                'role' => 'referee',
                'phone' => '+' . rand(1000000000, 9999999999),
                'is_active' => true,
            ]);
        }
    }
}
