# Sports Tournament Manager

A comprehensive web application for managing sports tournaments, teams, players, and matches. Built with modern technologies for scalability and performance.

**Author: Alejandro Silla Tejero**

## Overview

Sports Tournament Manager is a full-stack application that provides complete tournament management capabilities including:

- **Tournament Creation & Management** - Create various tournament formats (league, knockout, group stages)
- **Team & Player Administration** - Manage teams, rosters, and player statistics  
- **Match Scheduling & Results** - Schedule matches, record results, and track events
- **Real-time Standings** - Automatic calculation of tournament standings and statistics
- **User Management** - Role-based access control for admins, managers, and referees
- **Responsive Design** - Modern UI that works on desktop and mobile devices

## Technology Stack

### Backend (Laravel)
- **Laravel 11** - PHP framework with robust features
- **MySQL** - Relational database for data persistence
- **Laravel Sanctum** - API authentication
- **Eloquent ORM** - Database abstraction layer

### Frontend (Vue.js)
- **Vue 3** - Progressive JavaScript framework
- **Vite** - Fast build tool and development server
- **Tailwind CSS** - Utility-first CSS framework
- **Vue Router** - Client-side routing
- **Pinia** - State management

## Project Structure

```
SportsTournamentManager/
├── backend/           # Laravel API backend
│   ├── app/
│   │   ├── Models/    # Eloquent models
│   │   ├── Http/Controllers/Api/  # API controllers
│   │   └── ...
│   ├── database/
│   │   ├── migrations/  # Database schema
│   │   └── seeders/     # Sample data
│   └── routes/api.php   # API endpoints
├── frontend/          # Vue.js frontend
│   ├── src/
│   │   ├── components/  # Vue components
│   │   ├── views/       # Page components
│   │   ├── stores/      # State management
│   │   └── services/    # API integration
│   └── ...
└── README.md
```

## Features

### Tournament Management
- Create tournaments with different formats (league, knockout, mixed)
- Set registration periods and tournament rules
- Manage tournament status and progression
- Generate automatic fixtures and schedules

### Team & Player Management  
- Register and manage teams with detailed information
- Add/remove players from team rosters
- Track player statistics and performance
- Handle player transfers between teams

### Match Management
- Schedule matches with date, time, and venue
- Record match results and events (goals, cards, substitutions)
- Real-time match updates and notifications
- Referee assignment and management

### Standings & Statistics
- Automatic calculation of tournament standings
- Team and player statistics tracking
- Goal scorers and performance rankings
- Historical data and trends analysis

### User Roles & Permissions
- **Admin** - Full system access and tournament management
- **Team Manager** - Manage assigned teams and players
- **Referee** - Update match results and events
- **Player** - View personal statistics and team information

## Installation & Setup

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8.0+

### Backend Setup
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

### Frontend Setup
```bash
cd frontend
npm install
npm run dev
```

## API Documentation

The backend provides a RESTful API with the following main endpoints:

- `POST /api/auth/login` - User authentication
- `GET /api/tournaments` - List tournaments
- `GET /api/teams` - List teams
- `GET /api/players` - List players
- `GET /api/matches` - List matches
- `GET /api/standings` - Tournament standings

## Deployment

The application is configured for deployment on Railway and other cloud platforms with:

- Automated CI/CD pipelines
- Environment-specific configurations
- Production optimizations
- Database migrations on deploy

## Contributing

This project follows clean code principles with:

- Comprehensive documentation
- Consistent code formatting
- Error handling and validation
- Security best practices
- Performance optimizations

## License

This project is developed for educational and portfolio purposes.

---

**Author: Alejandro Silla Tejero**  
**Date: June 2025**
