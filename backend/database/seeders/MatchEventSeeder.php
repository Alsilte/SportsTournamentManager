<?php

// database/seeders/MatchEventSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MatchEvent;
use App\Models\GameMatch;
use App\Models\Player;
use App\Models\TeamPlayer;

class MatchEventSeeder extends Seeder
{
    public function run(): void
    {
        $completedMatches = GameMatch::where('status', 'completed')->with(['homeTeam', 'awayTeam'])->get();

        foreach ($completedMatches as $match) {
            $this->createMatchEvents($match);
        }
    }

    private function createMatchEvents($match)
    {
        // Obtener jugadores de ambos equipos
        $homePlayers = TeamPlayer::where('team_id', $match->home_team_id)
            ->where('is_active', true)
            ->with(['player.user'])
            ->get();

        $awayPlayers = TeamPlayer::where('team_id', $match->away_team_id)
            ->where('is_active', true)
            ->with(['player.user'])
            ->get();

        if ($homePlayers->isEmpty() || $awayPlayers->isEmpty()) {
            return; // No crear eventos si no hay jugadores
        }

        // Crear eventos de gol basados en el marcador
        $homeGoals = $match->home_score ?? 0;
        $awayGoals = $match->away_score ?? 0;

        // Goles del equipo local con más realismo
        $this->createGoalEvents($match, $homePlayers, $homeGoals, $match->home_team_id, true);
        
        // Goles del equipo visitante
        $this->createGoalEvents($match, $awayPlayers, $awayGoals, $match->away_team_id, false);

        // Agregar eventos adicionales (tarjetas, sustituciones)
        $this->createAdditionalEvents($match, $homePlayers, $awayPlayers);
    }

    private function createGoalEvents($match, $players, $goalCount, $teamId, $isHome)
    {
        // Priorizar delanteros y centrocampistas ofensivos para los goles
        $forwards = $players->filter(function ($tp) {
            return $tp->position === 'Forward';
        });
        
        $midfielders = $players->filter(function ($tp) {
            return $tp->position === 'Midfielder';
        });
        
        $potentialScorers = $forwards->merge($midfielders);
        if ($potentialScorers->isEmpty()) {
            $potentialScorers = $players;
        }

        for ($i = 0; $i < $goalCount; $i++) {
            $scorer = $potentialScorers->random();
            $minute = $this->getRealisticGoalMinute($i, $goalCount);
            
            // Determinar tipo de gol
            $goalTypes = ['regular', 'penalty', 'free_kick', 'header', 'volley'];
            $goalType = $goalTypes[array_rand($goalTypes)];
            
            $description = $this->getGoalDescription($scorer->player->user->name, $goalType, $minute);

            MatchEvent::create([
                'game_match_id' => $match->id,
                'team_id' => $teamId,
                'player_id' => $scorer->player->id,
                'event_type' => 'goal',
                'minute' => $minute,
                'additional_time' => $minute > 90 ? rand(1, 5) : 0,
                'description' => $description,
            ]);

            // Posible asistencia (70% de probabilidad)
            if (rand(1, 10) <= 7 && $goalType !== 'penalty') {
                $possibleAssisters = $players->reject(function ($tp) use ($scorer) {
                    return $tp->player->id === $scorer->player->id || $tp->position === 'Goalkeeper';
                });
                
                if ($possibleAssisters->isNotEmpty()) {
                    $assister = $possibleAssisters->random();
                    
                    MatchEvent::create([
                        'game_match_id' => $match->id,
                        'team_id' => $teamId,
                        'player_id' => $assister->player->id,
                        'event_type' => 'assist',
                        'minute' => $minute,
                        'additional_time' => $minute > 90 ? rand(1, 5) : 0,
                        'description' => "Asistencia de {$assister->player->user->name} para el gol de {$scorer->player->user->name}",
                    ]);
                }
            }
        }
    }

    private function createAdditionalEvents($match, $homePlayers, $awayPlayers)
    {
        $allPlayers = $homePlayers->merge($awayPlayers);
        
        // Tarjetas amarillas (1-4 por partido)
        $yellowCards = rand(1, 4);
        for ($i = 0; $i < $yellowCards; $i++) {
            $player = $allPlayers->random();
            $minute = rand(15, 90);
            
            $reasons = [
                'Juego peligroso',
                'Conducta antideportiva',
                'Protestar al árbitro',
                'Retardar el juego',
                'Entrada imprudente',
                'No respetar la distancia en tiro libre'
            ];
            
            MatchEvent::create([
                'game_match_id' => $match->id,
                'team_id' => $player->team_id,
                'player_id' => $player->player->id,
                'event_type' => 'yellow_card',
                'minute' => $minute,
                'additional_time' => 0,
                'description' => $reasons[array_rand($reasons)] . " - {$player->player->user->name}",
            ]);
        }

        // Tarjeta roja (10% de probabilidad)
        if (rand(1, 10) === 1) {
            $player = $allPlayers->random();
            $minute = rand(20, 85);
            
            $reasons = [
                'Doble amarilla',
                'Juego violento',
                'Agresión',
                'Escupir a un adversario',
                'Lenguaje ofensivo'
            ];
            
            MatchEvent::create([
                'game_match_id' => $match->id,
                'team_id' => $player->team_id,
                'player_id' => $player->player->id,
                'event_type' => 'red_card',
                'minute' => $minute,
                'additional_time' => 0,
                'description' => $reasons[array_rand($reasons)] . " - {$player->player->user->name}",
            ]);
        }

        // Sustituciones (2-6 por equipo)
        $this->createSubstitutions($match, $homePlayers, $match->home_team_id);
        $this->createSubstitutions($match, $awayPlayers, $match->away_team_id);
    }

    private function createSubstitutions($match, $players, $teamId)
    {
        $substitutions = rand(2, 5); // Máximo 5 cambios
        $availablePlayers = $players->shuffle();
        
        for ($i = 0; $i < min($substitutions, $availablePlayers->count() - 1); $i++) {
            $playerOut = $availablePlayers[$i];
            $minute = $this->getSubstitutionMinute($i);
            
            MatchEvent::create([
                'game_match_id' => $match->id,
                'team_id' => $teamId,
                'player_id' => $playerOut->player->id,
                'event_type' => 'substitution_out',
                'minute' => $minute,
                'additional_time' => 0,
                'description' => "Sale del campo: {$playerOut->player->user->name}",
            ]);
            
            // El jugador que entra (simulado - no está en la base de datos)
            MatchEvent::create([
                'game_match_id' => $match->id,
                'team_id' => $teamId,
                'player_id' => null, // Jugador suplente no registrado
                'event_type' => 'substitution_in',
                'minute' => $minute,
                'additional_time' => 0,
                'description' => "Entra al campo: Suplente #{" . rand(50, 99) . "}",
            ]);
        }
    }

    private function getRealisticGoalMinute($goalIndex, $totalGoals)
    {
        // Distribución más realista de goles
        $intervals = [
            [1, 15],   // Primeros 15 min
            [16, 30],  // 15-30 min
            [31, 45],  // 30-45 min
            [46, 60],  // 45-60 min
            [61, 75],  // 60-75 min
            [76, 90],  // 75-90 min
            [91, 95]   // Tiempo añadido
        ];
        
        // Los goles tienden a ser más frecuentes en ciertos momentos
        $weights = [10, 15, 20, 20, 15, 15, 5]; // Mayor probabilidad en 30-60 min
        
        $selectedInterval = $this->weightedRandom($intervals, $weights);
        return rand($selectedInterval[0], $selectedInterval[1]);
    }

    private function getSubstitutionMinute($substitutionIndex)
    {
        // Las sustituciones suelen ser en el descanso o después del minuto 60
        $commonTimes = [46, 60, 65, 70, 75, 80, 85];
        return $commonTimes[min($substitutionIndex, count($commonTimes) - 1)] + rand(-2, 5);
    }

    private function weightedRandom($items, $weights)
    {
        $totalWeight = array_sum($weights);
        $random = rand(1, $totalWeight);
        
        $currentWeight = 0;
        foreach ($weights as $index => $weight) {
            $currentWeight += $weight;
            if ($random <= $currentWeight) {
                return $items[$index];
            }
        }
        
        return $items[0]; // Fallback
    }

    private function getGoalDescription($playerName, $goalType, $minute)
    {
        $descriptions = [
            'regular' => [
                "¡Golazo de {$playerName}! Disparo perfecto al ángulo",
                "Gol de {$playerName} tras una jugada brillante",
                "{$playerName} no perdona y marca con clase",
                "Definición magistral de {$playerName}"
            ],
            'penalty' => [
                "Penalti convertido por {$playerName} con sangre fría",
                "{$playerName} no falla desde los once metros",
                "Gol de penalti de {$playerName}, colocado imposible"
            ],
            'free_kick' => [
                "¡Tiro libre espectacular de {$playerName}!",
                "Gol de falta directa por {$playerName}",
                "{$playerName} curva el balón por encima de la barrera"
            ],
            'header' => [
                "Cabezazo letal de {$playerName}",
                "Gol de cabeza de {$playerName} tras centro perfecto",
                "{$playerName} se eleva como un coloso y marca"
            ],
            'volley' => [
                "¡Volea impresionante de {$playerName}!",
                "Gol de volea de {$playerName}, puro instinto",
                "{$playerName} conecta de primera y marca un golazo"
            ]
        ];

        $typeDescriptions = $descriptions[$goalType] ?? $descriptions['regular'];
        return $typeDescriptions[array_rand($typeDescriptions)] . " (min. {$minute})";
    }
}