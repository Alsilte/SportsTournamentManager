<?php

// database/seeders/TeamPlayerSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TeamPlayer;
use App\Models\Team;
use App\Models\Player;
use Carbon\Carbon;

class TeamPlayerSeeder extends Seeder
{
    public function run(): void
    {
        $teams = Team::all();
        $players = Player::with('user')->get();

        // Distribución específica de jugadores por equipos
        $teamAssignments = [
            // Manchester City (team 0)
            ['Erling Haaland', 'Kevin De Bruyne', 'Jack Grealish', 'Ederson Moraes', 'Phil Foden', 'Riyad Mahrez'],
            
            // Liverpool (team 1)  
            ['Mohamed Salah', 'Virgil van Dijk', 'Sadio Mané', 'Alisson Becker', 'Luis Díaz', 'Darwin Núñez'],
            
            // Atlético Madrid (team 2)
            ['Antoine Griezmann', 'João Félix', 'Jan Oblak', 'Koke Resurrección', 'Marcos Llorente', 'Álvaro Morata'],
            
            // Real Madrid (team 3)
            ['Karim Benzema', 'Vinícius Jr', 'Luka Modrić', 'Thibaut Courtois', 'Rodrygo Silva', 'Eduardo Camavinga'],
            
            // Barcelona (team 4)
            ['Robert Lewandowski', 'Pedri González', 'Gavi Páez', 'Marc-André ter Stegen', 'Raphinha', 'Frenkie de Jong'],
            
            // Manchester United (team 5)
            ['Marcus Rashford', 'Bruno Fernandes', 'Casemiro', 'David de Gea', 'Jadon Sancho', 'Raphael Varane'],
            
            // Tottenham (team 6)
            ['Harry Kane', 'Son Heung-min', 'Hugo Lloris', 'Dejan Kulusevski', 'Pierre-Emile Højbjerg', 'Cristian Romero'],
            
            // Chelsea (team 7)
            ['Thiago Silva', 'Mason Mount', 'Reece James', 'Kepa Arrizabalaga', 'Raheem Sterling', 'Enzo Fernández']
        ];

        foreach ($teams as $teamIndex => $team) {
            if (isset($teamAssignments[$teamIndex])) {
                $targetPlayers = $teamAssignments[$teamIndex];
                
                foreach ($targetPlayers as $index => $playerName) {
                    $player = $players->first(function ($p) use ($playerName) {
                        return $p->user->name === $playerName;
                    });
                    
                    if ($player) {
                        // Asignar números de camiseta específicos según la posición
                        $jerseyNumber = $this->getJerseyNumber($player->position, $index);
                        
                        TeamPlayer::updateOrCreate(
                            ['team_id' => $team->id, 'jersey_number' => $jerseyNumber],
                            [
                                'player_id'   => $player->id,
                                'position'    => $player->position,
                                'is_captain'  => $index === 0, // El primer jugador es capitán
                                'is_active'   => true,
                                'joined_date' => Carbon::now()->subDays(rand(30, 730)), // Entre 1 mes y 2 años
                            ]
                        );
                    }
                }
            }
        }
    }

    private function getJerseyNumber($position, $index)
    {
        // Números tradicionales según posición
        switch ($position) {
            case 'Goalkeeper':
                return [1, 13, 25][$index % 3];
            case 'Defender':
                return [2, 3, 4, 5, 12, 15, 16, 17][$index % 8];
            case 'Midfielder':
                return [6, 8, 10, 14, 18, 20, 22, 24][$index % 8];
            case 'Forward':
                return [7, 9, 11, 19, 21, 23][$index % 6];
            default:
                return rand(1, 30);
        }
    }
}