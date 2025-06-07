<?php

// database/seeders/PlayerSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Player;
use App\Models\User;
use Carbon\Carbon;

class PlayerSeeder extends Seeder
{
    public function run(): void
    {
        $playerUsers = User::where('role', 'player')->get();

        // Datos específicos para jugadores reconocibles
        $playerData = [
            // Real Madrid players
            [
                'name' => 'Karim Benzema',
                'position' => 'Forward',
                'jersey_number' => 9,
                'nationality' => 'France',
                'height' => 185,
                'weight' => 81,
                'date_of_birth' => Carbon::createFromDate(1987, 12, 19),
                'preferred_foot' => 'right',
                'biography' => 'Delantero centro francés, Balón de Oro 2022. Líder nato y finalizador letal. Una leyenda del Real Madrid.'
            ],
            [
                'name' => 'Vinícius Jr',
                'position' => 'Forward',
                'jersey_number' => 7,
                'nationality' => 'Brazil',
                'height' => 176,
                'weight' => 73,
                'date_of_birth' => Carbon::createFromDate(2000, 7, 12),
                'preferred_foot' => 'right',
                'biography' => 'Extremo brasileño explosivo. Velocidad, regate y definición. El futuro del fútbol mundial.'
            ],
            [
                'name' => 'Luka Modrić',
                'position' => 'Midfielder',
                'jersey_number' => 10,
                'nationality' => 'Croatia',
                'height' => 172,
                'weight' => 66,
                'date_of_birth' => Carbon::createFromDate(1985, 9, 9),
                'preferred_foot' => 'both',
                'biography' => 'Centrocampista croata, Balón de Oro 2018. Visión, pase y elegancia. Un maestro del mediocampo.'
            ],
            [
                'name' => 'Thibaut Courtois',
                'position' => 'Goalkeeper',
                'jersey_number' => 1,
                'nationality' => 'Belgium',
                'height' => 199,
                'weight' => 96,
                'date_of_birth' => Carbon::createFromDate(1992, 5, 11),
                'preferred_foot' => 'left',
                'biography' => 'Portero belga de élite mundial. Reflejos felinos y seguridad bajo los palos. Muro infranqueable.'
            ],
            [
                'name' => 'Rodrygo Silva',
                'position' => 'Forward',
                'jersey_number' => 11,
                'nationality' => 'Brazil',
                'height' => 174,
                'weight' => 64,
                'date_of_birth' => Carbon::createFromDate(2001, 1, 9),
                'preferred_foot' => 'right',
                'biography' => 'Extremo brasileño versátil. Técnica depurada y olfato goleador. Especialista en momentos decisivos.'
            ],
            [
                'name' => 'Eduardo Camavinga',
                'position' => 'Midfielder',
                'jersey_number' => 12,
                'nationality' => 'France',
                'height' => 182,
                'weight' => 68,
                'date_of_birth' => Carbon::createFromDate(2002, 11, 10),
                'preferred_foot' => 'left',
                'biography' => 'Centrocampista francés prometedor. Energía, recuperación y proyección. El futuro de Francia.'
            ],

            // Barcelona players
            [
                'name' => 'Robert Lewandowski',
                'position' => 'Forward',
                'jersey_number' => 9,
                'nationality' => 'Poland',
                'height' => 185,
                'weight' => 79,
                'date_of_birth' => Carbon::createFromDate(1988, 8, 21),
                'preferred_foot' => 'right',
                'biography' => 'Delantero polaco letal. Máquina de hacer goles con instinto depredador. Leyenda viva del fútbol.'
            ],
            [
                'name' => 'Pedri González',
                'position' => 'Midfielder',
                'jersey_number' => 8,
                'nationality' => 'Spain',
                'height' => 174,
                'weight' => 60,
                'date_of_birth' => Carbon::createFromDate(2002, 11, 25),
                'preferred_foot' => 'right',
                'biography' => 'Centrocampista español elegante. Toque de balón exquisito y visión de juego excepcional. Joya de la cantera.'
            ],
            [
                'name' => 'Gavi Páez',
                'position' => 'Midfielder',
                'jersey_number' => 6,
                'nationality' => 'Spain',
                'height' => 173,
                'weight' => 68,
                'date_of_birth' => Carbon::createFromDate(2004, 8, 5),
                'preferred_foot' => 'right',
                'biography' => 'Centrocampista español dinámico. Intensidad, pressing y llegada al área. El futuro de España.'
            ],
            [
                'name' => 'Marc-André ter Stegen',
                'position' => 'Goalkeeper',
                'jersey_number' => 1,
                'nationality' => 'Germany',
                'height' => 187,
                'weight' => 85,
                'date_of_birth' => Carbon::createFromDate(1992, 4, 30),
                'preferred_foot' => 'right',
                'biography' => 'Portero alemán moderno. Excelente con los pies y paradas espectaculares. Líder silencioso.'
            ],
            [
                'name' => 'Raphinha',
                'position' => 'Forward',
                'jersey_number' => 22,
                'nationality' => 'Brazil',
                'height' => 176,
                'weight' => 68,
                'date_of_birth' => Carbon::createFromDate(1996, 12, 14),
                'preferred_foot' => 'left',
                'biography' => 'Extremo brasileño habilidoso. Regate, velocidad y precisión en el disparo. Desequilibrio puro.'
            ],
            [
                'name' => 'Frenkie de Jong',
                'position' => 'Midfielder',
                'jersey_number' => 21,
                'nationality' => 'Netherlands',
                'height' => 180,
                'weight' => 74,
                'date_of_birth' => Carbon::createFromDate(1997, 5, 12),
                'preferred_foot' => 'right',
                'biography' => 'Centrocampista holandés completo. Técnica, físico y inteligencia táctica. Cerebro del equipo.'
            ],

            // Manchester City players
            [
                'name' => 'Erling Haaland',
                'position' => 'Forward',
                'jersey_number' => 9,
                'nationality' => 'Norway',
                'height' => 194,
                'weight' => 88,
                'date_of_birth' => Carbon::createFromDate(2000, 7, 21),
                'preferred_foot' => 'left',
                'biography' => 'Delantero noruego imparable. Velocidad, potencia y definición brutal. Depredador del área.'
            ],
            [
                'name' => 'Kevin De Bruyne',
                'position' => 'Midfielder',
                'jersey_number' => 17,
                'nationality' => 'Belgium',
                'height' => 181,
                'weight' => 68,
                'date_of_birth' => Carbon::createFromDate(1991, 6, 28),
                'preferred_foot' => 'right',
                'biography' => 'Centrocampista belga sublime. Pase milimétrico y disparo letal. Uno de los mejores del mundo.'
            ],
            [
                'name' => 'Jack Grealish',
                'position' => 'Forward',
                'jersey_number' => 10,
                'nationality' => 'England',
                'height' => 180,
                'weight' => 76,
                'date_of_birth' => Carbon::createFromDate(1995, 9, 10),
                'preferred_foot' => 'right',
                'biography' => 'Extremo inglés creativo. Regate imposible y generación de juego. Espectáculo puro sobre el césped.'
            ],
            [
                'name' => 'Ederson Moraes',
                'position' => 'Goalkeeper',
                'jersey_number' => 31,
                'nationality' => 'Brazil',
                'height' => 188,
                'weight' => 86,
                'date_of_birth' => Carbon::createFromDate(1993, 8, 17),
                'preferred_foot' => 'left',
                'biography' => 'Portero brasileño moderno. Distribución excepcional y reflejos felinos. Revolución entre los palos.'
            ],
            [
                'name' => 'Phil Foden',
                'position' => 'Midfielder',
                'jersey_number' => 47,
                'nationality' => 'England',
                'height' => 171,
                'weight' => 69,
                'date_of_birth' => Carbon::createFromDate(2000, 5, 28),
                'preferred_foot' => 'left',
                'biography' => 'Centrocampista inglés prometedor. Técnica excepcional y visión de juego. Producto de la cantera citizen.'
            ],
            [
                'name' => 'Riyad Mahrez',
                'position' => 'Forward',
                'jersey_number' => 26,
                'nationality' => 'Algeria',
                'height' => 179,
                'weight' => 61,
                'date_of_birth' => Carbon::createFromDate(1991, 2, 21),
                'preferred_foot' => 'left',
                'biography' => 'Extremo argelino mágico. Regate sutil y zurda educada. Especialista en momentos importantes.'
            ],

            // Liverpool players
            [
                'name' => 'Mohamed Salah',
                'position' => 'Forward',
                'jersey_number' => 11,
                'nationality' => 'Egypt',
                'height' => 175,
                'weight' => 71,
                'date_of_birth' => Carbon::createFromDate(1992, 6, 15),
                'preferred_foot' => 'left',
                'biography' => 'Extremo egipcio eléctrico. Velocidad, regate y gol garantizado. El faraón de Anfield.'
            ],
            [
                'name' => 'Virgil van Dijk',
                'position' => 'Defender',
                'jersey_number' => 4,
                'nationality' => 'Netherlands',
                'height' => 193,
                'weight' => 92,
                'date_of_birth' => Carbon::createFromDate(1991, 7, 8),
                'preferred_foot' => 'right',
                'biography' => 'Defensa central holandés imponente. Liderazgo, elegancia y seguridad. El mejor defensa del mundo.'
            ],
            [
                'name' => 'Sadio Mané',
                'position' => 'Forward',
                'jersey_number' => 10,
                'nationality' => 'Senegal',
                'height' => 175,
                'weight' => 69,
                'date_of_birth' => Carbon::createFromDate(1992, 4, 10),
                'preferred_foot' => 'right',
                'biography' => 'Extremo senegalés explosivo. Velocidad pura y definición clínica. Héroe de Liverpool y Senegal.'
            ],
            [
                'name' => 'Alisson Becker',
                'position' => 'Goalkeeper',
                'jersey_number' => 1,
                'nationality' => 'Brazil',
                'height' => 191,
                'weight' => 91,
                'date_of_birth' => Carbon::createFromDate(1993, 10, 2),
                'preferred_foot' => 'right',
                'biography' => 'Portero brasileño excepcional. Paradas imposibles y salida valiente. Muralla de Anfield.'
            ],
            [
                'name' => 'Luis Díaz',
                'position' => 'Forward',
                'jersey_number' => 23,
                'nationality' => 'Colombia',
                'height' => 178,
                'weight' => 67,
                'date_of_birth' => Carbon::createFromDate(1997, 1, 13),
                'preferred_foot' => 'right',
                'biography' => 'Extremo colombiano espectacular. Regate y desequilibrio constante. La nueva joya sudamericana.'
            ],
            [
                'name' => 'Darwin Núñez',
                'position' => 'Forward',
                'jersey_number' => 27,
                'nationality' => 'Uruguay',
                'height' => 187,
                'weight' => 81,
                'date_of_birth' => Carbon::createFromDate(1999, 6, 24),
                'preferred_foot' => 'right',
                'biography' => 'Delantero uruguayo potente. Velocidad, físico y instinto goleador. El futuro charrúa.'
            ]
        ];

        foreach ($playerUsers as $index => $user) {
            if (isset($playerData[$index])) {
                $data = $playerData[$index];
                Player::create([
                    'user_id' => $user->id,
                    'jersey_number' => $data['jersey_number'],
                    'position' => $data['position'],
                    'date_of_birth' => $data['date_of_birth'],
                    'nationality' => $data['nationality'],
                    'height' => $data['height'],
                    'weight' => $data['weight'],
                    'preferred_foot' => $data['preferred_foot'],
                    'biography' => $data['biography'],
                ]);
            } else {
                // Para jugadores adicionales sin datos específicos
                $positions = ['Forward', 'Midfielder', 'Defender', 'Goalkeeper'];
                $nationalities = ['Spain', 'Argentina', 'Brazil', 'France', 'England', 'Portugal', 'Germany', 'Italy'];
                $preferredFeet = ['left', 'right', 'both'];

                Player::create([
                    'user_id' => $user->id,
                    'jersey_number' => rand(1, 99),
                    'position' => $positions[array_rand($positions)],
                    'date_of_birth' => Carbon::now()->subYears(rand(18, 35))->subDays(rand(1, 365)),
                    'nationality' => $nationalities[array_rand($nationalities)],
                    'height' => rand(160, 195),
                    'weight' => rand(60, 90),
                    'preferred_foot' => $preferredFeet[array_rand($preferredFeet)],
                    'biography' => 'Jugador profesional con experiencia en diferentes competiciones.',
                ]);
            }
        }
    }
}