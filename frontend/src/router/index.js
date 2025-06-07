// frontend/src/router/index.js
import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

// 🚀 Función helper para lazy loading con retry automático
const lazyLoad = (componentPath, retries = 3, delay = 1000) => {
  return () => {
    return import(componentPath).catch(error => {
      console.warn(`Failed to load component: ${componentPath}`, error)
      
      // Si quedan reintentos, esperar y volver a intentar
      if (retries > 0) {
        console.log(`Retrying... ${retries} attempts left`)
        return new Promise((resolve) => {
          setTimeout(() => {
            resolve(lazyLoad(componentPath, retries - 1, delay)())
          }, delay)
        })
      }
      
      // Si no quedan reintentos, lanzar el error
      throw error
    })
  }
}

// 🛡️ Función helper para lazy loading con fallback a recarga
const lazyLoadWithFallback = (componentPath) => {
  return () => {
    return import(componentPath).catch(error => {
      console.error(`Critical error loading ${componentPath}:`, error)
      
      // Verificar si es un error de MIME type específico
      if (error.message.includes('MIME type') || error.message.includes('module script')) {
        console.warn('MIME type error detected, forcing page reload...')
        
        // Mostrar mensaje al usuario antes de recargar
        if (window.confirm('Error loading page. Reload to fix?')) {
          window.location.reload()
        }
        
        // Retornar un componente de error mientras tanto
        return import('@/views/ErrorFallback.vue')
      }
      
      // Para otros errores, intentar un retry
      return lazyLoad(componentPath, 2, 500)()
    })
  }
}

// 📱 Lazy load components con manejo de errores
const Home = lazyLoadWithFallback('@/views/Home.vue')
const Login = lazyLoadWithFallback('@/views/auth/Login.vue')
const Register = lazyLoadWithFallback('@/views/auth/Register.vue')
const Dashboard = lazyLoadWithFallback('@/views/Dashboard.vue')
const Profile = lazyLoadWithFallback('@/views/Profile.vue')

// Tournament views
const Tournaments = lazyLoadWithFallback('@/views/tournaments/Tournaments.vue')
const TournamentDetail = lazyLoadWithFallback('@/views/tournaments/TournamentDetail.vue')
const CreateTournament = lazyLoadWithFallback('@/views/tournaments/CreateTournament.vue')
const EditTournament = lazyLoadWithFallback('@/views/tournaments/EditTournament.vue')

// Team views
const Teams = lazyLoadWithFallback('@/views/teams/Teams.vue')
const TeamDetail = lazyLoadWithFallback('@/views/teams/TeamDetail.vue')
const CreateTeam = lazyLoadWithFallback('@/views/teams/CreateTeam.vue')
const TeamRoster = lazyLoadWithFallback('@/views/teams/TeamRoster.vue')

// Player views
const Players = lazyLoadWithFallback('@/views/players/Players.vue')
const PlayerDetail = lazyLoadWithFallback('@/views/players/PlayerDetail.vue')

// Match views
const Matches = lazyLoadWithFallback('@/views/matches/Matches.vue')
const MatchDetail = lazyLoadWithFallback('@/views/matches/MatchDetail.vue')
const CreateMatch = lazyLoadWithFallback('@/views/matches/CreateMatch.vue')

// Admin views
const AdminPanel = lazyLoadWithFallback('@/views/admin/AdminPanel.vue')
const UserManagement = lazyLoadWithFallback('@/views/admin/UserManagement.vue')

// 🆘 Componente de error (importar directamente, no lazy)
import ErrorFallback from '@/views/ErrorFallback.vue'
import NotFound from '@/views/NotFound.vue'

const routes = [
  // Public routes
  {
    path: '/',
    name: 'home',
    component: Home,
    meta: { 
      title: 'Sports Tournament Manager',
      preload: true // Marcar rutas importantes para preload
    }
  },
  {
    path: '/login',
    name: 'login',
    component: Login,
    meta: { 
      title: 'Login',
      requiresGuest: true,
      preload: true
    }
  },
  {
    path: '/register',
    name: 'register',
    component: Register,
    meta: { 
      title: 'Register',
      requiresGuest: true 
    }
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: Dashboard,
    meta: { 
      title: 'Dashboard',
      requiresAuth: true,
      preload: true
    }
  },
  {
    path: '/profile',
    name: 'profile',
    component: Profile,
    meta: { 
      title: 'Profile',
      requiresAuth: true 
    }
  },

  // Tournament routes
  {
    path: '/tournaments',
    name: 'tournaments',
    component: Tournaments,
    meta: { 
      title: 'Tournaments',
      preload: true
    }
  },
  {
    path: '/tournaments/create',
    name: 'create-tournament',
    component: CreateTournament,
    meta: { 
      title: 'Create Tournament',
      requiresAuth: true,
      requiresRole: ['admin', 'manager']
    }
  },
  {
    path: '/tournaments/:id',
    name: 'tournament-detail',
    component: TournamentDetail,
    meta: { 
      title: 'Tournament Details'
    }
  },
  {
    path: '/tournaments/:id/edit',
    name: 'edit-tournament',
    component: EditTournament,
    meta: { 
      title: 'Edit Tournament',
      requiresAuth: true,
      requiresRole: ['admin', 'manager']
    }
  },

  // Team routes
  {
    path: '/teams',
    name: 'teams',
    component: Teams,
    meta: { 
      title: 'Teams',
      preload: true
    }
  },
  {
    path: '/teams/create',
    name: 'create-team',
    component: CreateTeam,
    meta: { 
      title: 'Create Team',
      requiresAuth: true,
      requiresRole: ['admin', 'manager']
    }
  },
  {
    path: '/teams/:id',
    name: 'team-detail',
    component: TeamDetail,
    meta: { 
      title: 'Team Details'
    }
  },
  {
    path: '/teams/:id/roster',
    name: 'team-roster',
    component: TeamRoster,
    meta: { 
      title: 'Team Roster',
      requiresAuth: true
    }
  },

  // Player routes
  {
    path: '/players',
    name: 'players',
    component: Players,
    meta: { 
      title: 'Players'
    }
  },
  {
    path: '/players/:id',
    name: 'player-detail',
    component: PlayerDetail,
    meta: { 
      title: 'Player Details'
    }
  },

  // Match routes
  {
    path: '/matches',
    name: 'matches',
    component: Matches,
    meta: { 
      title: 'Matches'
    }
  },
  {
    path: '/matches/create',
    name: 'create-match',
    component: CreateMatch,
    meta: { 
      title: 'Create Match',
      requiresAuth: true,
      requiresRole: ['admin', 'referee']
    }
  },
  {
    path: '/matches/:id',
    name: 'match-detail',
    component: MatchDetail,
    meta: { 
      title: 'Match Details'
    }
  },

  // Admin routes
  {
    path: '/admin',
    name: 'admin',
    component: AdminPanel,
    meta: { 
      title: 'Admin Panel',
      requiresAuth: true,
      requiresRole: ['admin']
    }
  },
  {
    path: '/admin/users',
    name: 'admin-users',
    component: UserManagement,
    meta: { 
      title: 'User Management',
      requiresAuth: true,
      requiresRole: ['admin']
    }
  },

  // Error handling routes
  {
    path: '/error',
    name: 'error',
    component: ErrorFallback,
    meta: { 
      title: 'Error'
    }
  },

  // 404 catch-all route
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: NotFound,
    meta: { 
      title: 'Page Not Found'
    }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    // 🔝 Scroll behavior mejorado
    if (savedPosition) {
      return savedPosition
    } else if (to.hash) {
      return {
        el: to.hash,
        behavior: 'smooth'
      }
    } else {
      return { top: 0 }
    }
  }
})

// 🛡️ Global error handler para errores de navegación
router.onError((error, to, from) => {
  console.error('Router error:', error)
  
  // Manejo específico de errores de lazy loading
  if (error.message.includes('Loading chunk') || 
      error.message.includes('MIME type') ||
      error.message.includes('module script')) {
    
    console.warn('Lazy loading error detected, attempting recovery...')
    
    // Intentar navegar a la ruta de error o recargar
    if (to.name !== 'error') {
      router.push('/error')
    } else {
      // Si ya estamos en error, forzar recarga
      window.location.reload()
    }
  }
})

// 🔐 Navigation guards
router.beforeEach(async (to, from, next) => {
  console.log('🧭 Router guard:', to.path, 'requiresAuth:', to.meta.requiresAuth)
  
  const authStore = useAuthStore()
  
  // Wait for auth initialization
  if (!authStore.isInitialized) {
    console.log('⏳ Waiting for auth initialization...')
    await authStore.initializeAuth()
  }
  
  console.log('✅ Auth initialized. isAuthenticated:', authStore.isAuthenticated)
  
  // Check authentication requirements
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    console.log('🔒 Route requires auth, redirecting to login')
    next({ name: 'login', query: { redirect: to.fullPath } })
    return
  }
  
  // Check guest-only routes
  if (to.meta.requiresGuest && authStore.isAuthenticated) {
    console.log('👤 User already authenticated, redirecting to dashboard')
    next({ name: 'dashboard' })
    return
  }
  
  // Check role-based access
  if (to.meta.requiresRole && authStore.isAuthenticated) {
    const userRole = authStore.userRole
    const requiredRoles = Array.isArray(to.meta.requiresRole) 
      ? to.meta.requiresRole 
      : [to.meta.requiresRole]
    
    if (!requiredRoles.includes(userRole)) {
      console.log('🚫 Insufficient permissions')
      next({ name: 'dashboard' })
      return
    }
  }
  
  next()
})

// 🏷️ Update page title
router.afterEach((to) => {
  const defaultTitle = 'Tournament Manager'
  document.title = to.meta.title ? `${to.meta.title} | ${defaultTitle}` : defaultTitle
})

// 🚀 Preload important routes
router.beforeEach(async (to, from, next) => {
  // Preload rutas marcadas como importantes
  if (to.meta.preload && from.name) {
    try {
      // Darle un poco de tiempo para que se establezca la navegación
      setTimeout(() => {
        // Preload rutas relacionadas comunes
        const preloadRoutes = ['/dashboard', '/tournaments', '/teams']
        preloadRoutes.forEach(route => {
          if (route !== to.path) {
            router.resolve(route)
          }
        })
      }, 100)
    } catch (error) {
      console.warn('Preload failed:', error)
    }
  }
  
  next()
})

export default router