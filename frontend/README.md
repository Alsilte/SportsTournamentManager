# Sports Tournament Manager - Frontend

Frontend application for the Sports Tournament Manager system built with Vue 3, Vite, and Tailwind CSS.

**Author: Alejandro Silla Tejero**

## Features

- Modern Vue 3 Composition API
- Responsive design with Tailwind CSS
- Real-time tournament management
- Team and player administration
- Match scheduling and results tracking
- User authentication and authorization
- Progressive Web App capabilities

## Technology Stack

- **Vue 3** - Progressive JavaScript framework
- **Vite** - Fast build tool and development server
- **Tailwind CSS** - Utility-first CSS framework
- **Vue Router** - Official router for Vue.js
- **Pinia** - State management for Vue
- **Axios** - HTTP client for API communication

## Project Setup

```sh
npm install
```

### Development Server

```sh
npm run dev
```

### Production Build

```sh
npm run build
```

### Preview Production Build

```sh
npm run preview
```

## Project Structure

```
src/
├── components/     # Reusable Vue components
├── views/         # Page components
├── router/        # Vue Router configuration
├── stores/        # Pinia state management
├── services/      # API services and utilities
└── assets/        # Static assets
```

## API Integration

The frontend communicates with a Laravel backend API providing:
- User authentication with JWT tokens
- Tournament CRUD operations
- Team and player management
- Match scheduling and results
- Real-time standings calculation
