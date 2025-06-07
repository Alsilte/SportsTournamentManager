<?php

// database/seeders/TeamSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;
use App\Models\User;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $managers = User::where('role', 'team_manager')->get();

        $teams = [
            [
                'name' => 'Manchester City FC',
                'short_name' => 'MCI',
                'description' => 'Los Citizens. Juego ofensivo espectacular bajo la dirección de Pep Guardiola. Conocidos por su posesión de balón y presión alta.',
                'contact_email' => 'info@mancity.com',
                'founded_year' => 1880,
                'home_venue' => 'Etihad Stadium',
                'manager_id' => $managers[0]->id,
                'contact_phone' => '+44161234567',
                'is_active' => true,
            ],
            [
                'name' => 'Liverpool FC',
                'short_name' => 'LFC',
                'description' => 'Los Reds de Anfield. Intensidad y pasión en cada jugada. Famosos por su pressing y el apoyo incondicional de su afición.',
                'contact_email' => 'info@liverpool.com',
                'founded_year' => 1892,
                'home_venue' => 'Anfield Stadium',
                'manager_id' => $managers[1]->id,
                'contact_phone' => '+44151234567',
                'is_active' => true,
            ],
            [
                'name' => 'Atlético de Madrid',
                'short_name' => 'ATM',
                'description' => 'Los Colchoneros. Garra, corazón y nunca rendirse. Especialistas en defender y contraatacar con precisión letal.',
                'contact_email' => 'info@atleticomadrid.com',
                'founded_year' => 1903,
                'home_venue' => 'Cívitas Metropolitano',
                'manager_id' => $managers[2]->id,
                'contact_phone' => '+34915678901',
                'is_active' => true,
            ],
            [
                'name' => 'Real Madrid CF',
                'short_name' => 'RMA',
                'description' => 'El club más laureado del mundo. Los Blancos que nunca se rinden. Tradición, elegancia y magia en el Santiago Bernabéu.',
                'contact_email' => 'info@realmadrid.com',
                'founded_year' => 1902,
                'home_venue' => 'Santiago Bernabéu',
                'manager_id' => $managers[3]->id,
                'contact_phone' => '+34912345678',
                'is_active' => true,
            ],
            [
                'name' => 'FC Barcelona',
                'short_name' => 'BAR',
                'description' => 'Més que un club. Los Culés del Camp Nou. Filosofía de juego basada en la posesión, el toque y la cantera.',
                'contact_email' => 'info@barcelona.com',
                'founded_year' => 1899,
                'home_venue' => 'Spotify Camp Nou',
                'manager_id' => $managers[4]->id,
                'contact_phone' => '+34934567890',
                'is_active' => true,
            ],
            [
                'name' => 'Manchester United',
                'short_name' => 'MUN',
                'description' => 'Los Red Devils de Old Trafford. Tradición, historia y pasión. El teatro de los sueños donde nacen las leyendas.',
                'contact_email' => 'info@manutd.com',
                'founded_year' => 1878,
                'home_venue' => 'Old Trafford',
                'manager_id' => $managers[5]->id,
                'contact_phone' => '+44161234567',
                'is_active' => true,
            ],
            [
                'name' => 'Tottenham Hotspur',
                'short_name' => 'TOT',
                'description' => 'Los Spurs del norte de Londres. Juego vistoso y atacante. Su nuevo estadio es una joya arquitectónica del fútbol moderno.',
                'contact_email' => 'info@tottenham.com',
                'founded_year' => 1882,
                'home_venue' => 'Tottenham Hotspur Stadium',
                'manager_id' => $managers[6]->id,
                'contact_phone' => '+44208765432',
                'is_active' => true,
            ],
            [
                'name' => 'Chelsea FC',
                'short_name' => 'CHE',
                'description' => 'Los Blues de Stamford Bridge. Ambición, calidad y recursos ilimitados. Expertos en fichajes y desarrollar talento joven.',
                'contact_email' => 'info@chelsea.com',
                'founded_year' => 1905,
                'home_venue' => 'Stamford Bridge',
                'manager_id' => $managers[7]->id,
                'contact_phone' => '+44207890123',
                'is_active' => true,
            ]
        ];

        foreach ($teams as $team) {
            Team::create($team);
        }
    }
}