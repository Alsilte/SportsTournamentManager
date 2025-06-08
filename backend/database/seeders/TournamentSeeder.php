<?php

/**
 * Tournament Seeder
 * 
 * Seeds the database with sample tournaments of different types and statuses.
 * Creates various tournament configurations including knockout and league formats
 * for testing and development purposes.
 * 
 * Author: Alejandro Silla Tejero
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tournament;
use App\Models\User;
use Carbon\Carbon;

class TournamentSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $admin2 = User::where('role', 'admin')->skip(1)->first();

        $tournaments = [
            // Torneo principal en progreso
            [
                'name' => 'Copa Elite Champions 2024',
                'description' => 'El torneo más prestigioso de la temporada con los mejores equipos europeos. Competición de eliminación directa con premios millonarios.',
                'sport_type' => 'Football',
                'tournament_type' => 'knockout',
                'status' => 'in_progress',
                'max_teams' => 16,
                'registration_start' => Carbon::now()->subDays(90),
                'registration_end' => Carbon::now()->subDays(60),
                'start_date' => Carbon::now()->subDays(30),
                'end_date' => Carbon::now()->addDays(60),
                'location' => 'Estadios de Europa',
                'prize_pool' => 100000.00,
                'rules' => 'Eliminación directa. Prórroga y penales en caso de empate. Cambios ilimitados.',
                'created_by' => $admin->id,
            ],
            
            // Liga regular activa
            [
                'name' => 'Liga Profesional Primavera',
                'description' => 'Liga regular con sistema de todos contra todos. Temporada completa con ascensos y descensos.',
                'sport_type' => 'Football',
                'tournament_type' => 'league',
                'status' => 'in_progress',
                'max_teams' => 8,
                'registration_start' => Carbon::now()->subDays(60),
                'registration_end' => Carbon::now()->subDays(45),
                'start_date' => Carbon::now()->subDays(20),
                'end_date' => Carbon::now()->addDays(90),
                'location' => 'Ciudad Deportiva Central',
                'prize_pool' => 50000.00,
                'rules' => 'Liga regular: 3 puntos por victoria, 1 por empate. Dos tiempos de 45 minutos.',
                'created_by' => $admin->id,
            ],
            
            // Torneo abierto a registraciones
            [
                'name' => 'Copa de Verano Internacional',
                'description' => '¡Inscripciones abiertas! Torneo de verano para equipos de todas las categorías. Ambiente festivo y competitivo.',
                'sport_type' => 'Football',
                'tournament_type' => 'knockout',
                'status' => 'registration_open',
                'max_teams' => 12,
                'registration_start' => Carbon::now()->subDays(5),
                'registration_end' => Carbon::now()->addDays(25),
                'start_date' => Carbon::now()->addDays(40),
                'end_date' => Carbon::now()->addDays(70),
                'location' => 'Complejo Deportivo Mediterráneo',
                'prize_pool' => 25000.00,
                'rules' => 'Torneo eliminatorio con partidos de 90 minutos. Fair play premiado.',
                'created_by' => $admin2->id,
            ],
            
            // Torneo próximo a empezar
            [
                'name' => 'Champions League Juvenil',
                'description' => 'Competición para equipos sub-21. Cantera de futuros campeones con reglas especiales para desarrollo.',
                'sport_type' => 'Football',
                'tournament_type' => 'group_stage',
                'status' => 'draft',
                'max_teams' => 16,
                'registration_start' => Carbon::now()->subDays(40),
                'registration_end' => Carbon::now()->subDays(10),
                'start_date' => Carbon::now()->addDays(15),
                'end_date' => Carbon::now()->addDays(80),
                'location' => 'Academias Deportivas Europeas',
                'prize_pool' => 15000.00,
                'rules' => 'Fase de grupos seguida de eliminatorias. Máximo 3 extranjeros por equipo.',
                'created_by' => $admin->id,
            ],
            
            // Torneo finalizado reciente
            [
                'name' => 'Copa Invernal 2024',
                'description' => 'Torneo de temporada baja completado con gran éxito. Participaron 8 equipos de élite.',
                'sport_type' => 'Football',
                'tournament_type' => 'knockout',
                'status' => 'completed',
                'max_teams' => 8,
                'registration_start' => Carbon::now()->subDays(120),
                'registration_end' => Carbon::now()->subDays(90),
                'start_date' => Carbon::now()->subDays(60),
                'end_date' => Carbon::now()->subDays(10),
                'location' => 'Estadio Central de Invierno',
                'prize_pool' => 30000.00,
                'rules' => 'Eliminación directa con partidos únicos. Sin prórroga en semifinales.',
                'created_by' => $admin2->id,
            ],
            
            // Draft para planificación
            [
                'name' => 'Supercopa Continental 2025',
                'description' => 'Torneo en planificación para la próxima temporada. Solo para campeones de liga.',
                'sport_type' => 'Football',
                'tournament_type' => 'knockout',
                'status' => 'draft',
                'max_teams' => 6,
                'registration_start' => Carbon::now()->addDays(60),
                'registration_end' => Carbon::now()->addDays(90),
                'start_date' => Carbon::now()->addDays(120),
                'end_date' => Carbon::now()->addDays(140),
                'location' => 'Estadio Metropolitano',
                'prize_pool' => 75000.00,
                'rules' => 'Solo campeones de liga. Formato de final four con semifinales y final.',
                'created_by' => $admin->id,
            ],
            
            // Torneo cancelado (para mostrar diferentes estados)
            [
                'name' => 'Copa de Otoño 2024',
                'description' => 'Torneo cancelado debido a circunstancias imprevistas. Los equipos serán reembolsados.',
                'sport_type' => 'Football',
                'tournament_type' => 'league',
                'status' => 'cancelled',
                'max_teams' => 10,
                'registration_start' => Carbon::now()->subDays(80),
                'registration_end' => Carbon::now()->subDays(50),
                'start_date' => Carbon::now()->subDays(30),
                'end_date' => Carbon::now()->addDays(30),
                'location' => 'Centro Deportivo Norte',
                'prize_pool' => 20000.00,
                'rules' => 'Liga regular con playoffs. Cancelado por motivos de fuerza mayor.',
                'created_by' => $admin2->id,
            ],
            
            // Torneo de otro deporte para diversidad
            [
                'name' => 'Torneo de Baloncesto 3x3',
                'description' => 'Competición de baloncesto urbano con equipos de 3 jugadores. Dinámico y espectacular.',
                'sport_type' => 'Basketball',
                'tournament_type' => 'knockout',
                'status' => 'registration_open',
                'max_teams' => 20,
                'registration_start' => Carbon::now()->subDays(10),
                'registration_end' => Carbon::now()->addDays(20),
                'start_date' => Carbon::now()->addDays(35),
                'end_date' => Carbon::now()->addDays(45),
                'location' => 'Polideportivo Central',
                'prize_pool' => 8000.00,
                'rules' => 'Partidos a 21 puntos o 10 minutos. Cancha medio campo.',
                'created_by' => $admin->id,
            ]
        ];

        foreach ($tournaments as $tournament) {
            Tournament::create($tournament);
        }
    }
}