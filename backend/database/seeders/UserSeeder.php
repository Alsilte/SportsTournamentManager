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
        // Crear o actualizar admins
        User::updateOrCreate(
            ['email' => 'admin@torneos.com'],
            [
                'name'      => 'Carlos Administrador',
                'password'  => Hash::make('admin123'),
                'role'      => 'admin',
                'phone'     => '+34611234567',
                'is_active' => true,
            ]
        );
        User::updateOrCreate(
            ['email' => 'supervisor@torneos.com'],
            [
                'name'      => 'Ana Supervisora',
                'password'  => Hash::make('super123'),
                'role'      => 'admin',
                'phone'     => '+34622345678',
                'is_active' => true,
            ]
        );

        // Crear managers
        $managers = [
            ['name' => 'Pep Guardiola', 'email' => 'pep@cityfc.com', 'phone' => '+34933123456'],
            ['name' => 'Jürgen Klopp', 'email' => 'jurgen@liverpool.com', 'phone' => '+44201234567'],
            ['name' => 'Diego Simeone', 'email' => 'diego@atletico.com', 'phone' => '+34915678901'],
            ['name' => 'Carlo Ancelotti', 'email' => 'carlo@realmadrid.com', 'phone' => '+34912345678'],
            ['name' => 'Xavi Hernández', 'email' => 'xavi@barcelona.com', 'phone' => '+34934567890'],
            ['name' => 'Erik ten Hag', 'email' => 'erik@manchester.com', 'phone' => '+44161234567'],
            ['name' => 'Antonio Conte', 'email' => 'antonio@tottenham.com', 'phone' => '+44208765432'],
            ['name' => 'Thomas Tuchel', 'email' => 'thomas@chelsea.com', 'phone' => '+44207890123']
        ];

        foreach ($managers as $manager) {
            User::updateOrCreate(
                ['email' => $manager['email']],
                [
                    'name'      => $manager['name'],
                    'password'  => Hash::make('manager123'),
                    'role'      => 'team_manager',
                    'phone'     => $manager['phone'],
                    'is_active' => true,
                ]
            );
        }

        // Crear jugadores estrella más realistas
        $starPlayers = [
            // Real Madrid
            ['name' => 'Karim Benzema', 'email' => 'benzema@realmadrid.com'],
            ['name' => 'Vinícius Jr', 'email' => 'vinicius@realmadrid.com'],
            ['name' => 'Luka Modrić', 'email' => 'modric@realmadrid.com'],
            ['name' => 'Thibaut Courtois', 'email' => 'courtois@realmadrid.com'],
            ['name' => 'Rodrygo Silva', 'email' => 'rodrygo@realmadrid.com'],
            ['name' => 'Eduardo Camavinga', 'email' => 'camavinga@realmadrid.com'],
            
            // Barcelona
            ['name' => 'Robert Lewandowski', 'email' => 'lewandowski@barcelona.com'],
            ['name' => 'Pedri González', 'email' => 'pedri@barcelona.com'],
            ['name' => 'Gavi Páez', 'email' => 'gavi@barcelona.com'],
            ['name' => 'Marc-André ter Stegen', 'email' => 'terstegen@barcelona.com'],
            ['name' => 'Raphinha', 'email' => 'raphinha@barcelona.com'],
            ['name' => 'Frenkie de Jong', 'email' => 'dejong@barcelona.com'],
            
            // Manchester City
            ['name' => 'Erling Haaland', 'email' => 'haaland@city.com'],
            ['name' => 'Kevin De Bruyne', 'email' => 'debruyne@city.com'],
            ['name' => 'Jack Grealish', 'email' => 'grealish@city.com'],
            ['name' => 'Ederson Moraes', 'email' => 'ederson@city.com'],
            ['name' => 'Phil Foden', 'email' => 'foden@city.com'],
            ['name' => 'Riyad Mahrez', 'email' => 'mahrez@city.com'],
            
            // Liverpool
            ['name' => 'Mohamed Salah', 'email' => 'salah@liverpool.com'],
            ['name' => 'Virgil van Dijk', 'email' => 'vandijk@liverpool.com'],
            ['name' => 'Sadio Mané', 'email' => 'mane@liverpool.com'],
            ['name' => 'Alisson Becker', 'email' => 'alisson@liverpool.com'],
            ['name' => 'Luis Díaz', 'email' => 'diaz@liverpool.com'],
            ['name' => 'Darwin Núñez', 'email' => 'nunez@liverpool.com'],
            
            // Atlético Madrid
            ['name' => 'Antoine Griezmann', 'email' => 'griezmann@atletico.com'],
            ['name' => 'João Félix', 'email' => 'joaofelix@atletico.com'],
            ['name' => 'Jan Oblak', 'email' => 'oblak@atletico.com'],
            ['name' => 'Koke Resurrección', 'email' => 'koke@atletico.com'],
            ['name' => 'Marcos Llorente', 'email' => 'llorente@atletico.com'],
            ['name' => 'Álvaro Morata', 'email' => 'morata@atletico.com'],
            
            // Manchester United
            ['name' => 'Marcus Rashford', 'email' => 'rashford@united.com'],
            ['name' => 'Bruno Fernandes', 'email' => 'bruno@united.com'],
            ['name' => 'Casemiro', 'email' => 'casemiro@united.com'],
            ['name' => 'David de Gea', 'email' => 'degea@united.com'],
            ['name' => 'Jadon Sancho', 'email' => 'sancho@united.com'],
            ['name' => 'Raphael Varane', 'email' => 'varane@united.com'],
            
            // Tottenham
            ['name' => 'Harry Kane', 'email' => 'kane@tottenham.com'],
            ['name' => 'Son Heung-min', 'email' => 'son@tottenham.com'],
            ['name' => 'Hugo Lloris', 'email' => 'lloris@tottenham.com'],
            ['name' => 'Dejan Kulusevski', 'email' => 'kulusevski@tottenham.com'],
            ['name' => 'Pierre-Emile Højbjerg', 'email' => 'hojbjerg@tottenham.com'],
            ['name' => 'Cristian Romero', 'email' => 'romero@tottenham.com'],
            
            // Chelsea
            ['name' => 'Thiago Silva', 'email' => 'thiago@chelsea.com'],
            ['name' => 'Mason Mount', 'email' => 'mount@chelsea.com'],
            ['name' => 'Reece James', 'email' => 'james@chelsea.com'],
            ['name' => 'Kepa Arrizabalaga', 'email' => 'kepa@chelsea.com'],
            ['name' => 'Raheem Sterling', 'email' => 'sterling@chelsea.com'],
            ['name' => 'Enzo Fernández', 'email' => 'enzo@chelsea.com']
        ];

        foreach ($starPlayers as $player) {
            User::updateOrCreate(
                ['email' => $player['email']],
                [
                    'name'      => $player['name'],
                    'password'  => Hash::make('player123'),
                    'role'      => 'player',
                    'phone'     => '+' . rand(34600000000, 34699999999),
                    'is_active' => true,
                ]
            );
        }

        // Crear árbitros profesionales
        $referees = [
            ['name' => 'Mateu Lahoz', 'email' => 'mateu@referees.com'],
            ['name' => 'Jesús Gil Manzano', 'email' => 'gil@referees.com'],
            ['name' => 'Carlos del Cerro', 'email' => 'cerro@referees.com'],
            ['name' => 'José Sánchez Martínez', 'email' => 'sanchez@referees.com'],
            ['name' => 'Alejandro Hernández', 'email' => 'hernandez@referees.com'],
            ['name' => 'Ricardo de Burgos', 'email' => 'burgos@referees.com']
        ];

        foreach ($referees as $referee) {
            User::updateOrCreate(
                ['email' => $referee['email']],
                [
                    'name'      => $referee['name'],
                    'password'  => Hash::make('referee123'),
                    'role'      => 'referee',
                    'phone'     => '+34' . rand(600000000, 699999999),
                    'is_active' => true,
                ]
            );
        }

        // Crear usuarios regulares (aficionados)
        $fans = [
            ['name' => 'Juan Pérez', 'email' => 'juan.perez@email.com'],
            ['name' => 'María González', 'email' => 'maria.gonzalez@email.com'],
            ['name' => 'Luis Rodríguez', 'email' => 'luis.rodriguez@email.com'],
            ['name' => 'Carmen López', 'email' => 'carmen.lopez@email.com'],
            ['name' => 'Antonio Martín', 'email' => 'antonio.martin@email.com']
        ];

        foreach ($fans as $fan) {
            User::updateOrCreate(
                ['email' => $fan['email']],
                [
                    'name'      => $fan['name'],
                    'password'  => Hash::make('fan123'),
                    'role'      => 'player',
                    'phone'     => '+34' . rand(600000000, 699999999),
                    'is_active' => true,
                ]
            );
        }
    }
}