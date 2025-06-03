/**
 * SERVICIOS API - SISTEMA DEPORTIVO
 * 
 * Archivo: src/services/api.js
 * 
 * Servicios para comunicación con el backend Laravel
 * Basado en las rutas definidas en backend/routes/api.php
 */

import axios from 'axios'

// ============================================================================
// CONFIGURACIÓN BASE DE AXIOS
// ============================================================================

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: true,
})

// Interceptor para agregar token automáticamente
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Interceptor para manejar respuestas
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Token expirado o inválido
      localStorage.removeItem('auth_token')
      localStorage.removeItem('user')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

// ============================================================================
// SERVICIOS DE AUTENTICACIÓN
// ============================================================================

export const authAPI = {
  // Registro
  register: (userData) => api.post('/auth/register', userData),
  
  // Login
  login: (credentials) => api.post('/auth/login', credentials),
  
  // Logout
  logout: () => api.post('/auth/logout'),
  
  // Logout de todos los dispositivos
  logoutAll: () => api.post('/auth/logout-all'),
  
  // Obtener usuario actual
  me: () => api.get('/auth/me'),
  
  // Cambiar contraseña
  changePassword: (data) => api.post('/auth/change-password', data),
  
  // Refrescar token
  refresh: () => api.post('/auth/refresh'),
  
  // Recuperar contraseña
  forgotPassword: (email) => api.post('/auth/forgot-password', { email }),
  
  // Restablecer contraseña
  resetPassword: (data) => api.post('/auth/reset-password', data),
  
  // Verificar token
  verifyToken: (token) => api.post('/auth/verify-token', { token }),
  
  // Gestión de tokens
  getActiveTokens: () => api.get('/auth/tokens'),
  revokeToken: (tokenId) => api.delete(`/auth/tokens/${tokenId}`),
  
  // Actualizar perfil
  updateProfile: (data) => api.put('/auth/profile', data),
}

// ============================================================================
// SERVICIOS PÚBLICOS
// ============================================================================

export const publicAPI = {
  // Información de la aplicación
  getAppInfo: () => api.get('/public/app-info'),
  
  // Lista de deportes
  getDeportes: () => api.get('/public/deportes'),
}

// ============================================================================
// SERVICIOS DE DASHBOARD
// ============================================================================

export const dashboardAPI = {
  // Estadísticas del dashboard
  getStats: () => api.get('/dashboard/stats'),
  
  // Actividad reciente
  getActivity: () => api.get('/dashboard/activity'),
}

// ============================================================================
// SERVICIOS DE USUARIOS
// ============================================================================

export const userAPI = {
  // Listar usuarios (solo admin)
  getAll: (params = {}) => api.get('/usuarios', { params }),
  
  // Crear usuario (solo admin)
  create: (userData) => api.post('/usuarios', userData),
  
  // Obtener usuario por ID (solo admin)
  getById: (id) => api.get(`/usuarios/${id}`),
  
  // Actualizar usuario (solo admin)
  update: (id, data) => api.put(`/usuarios/${id}`, data),
  
  // Eliminar usuario (solo admin)
  delete: (id) => api.delete(`/usuarios/${id}`),
  
  // Perfil del usuario actual
  getProfile: () => api.get('/usuarios/me/profile'),
}

// ============================================================================
// SERVICIOS DE TORNEOS
// ============================================================================

export const tournamentAPI = {
  // Listar torneos
  getAll: (params = {}) => api.get('/torneos', { params }),
  
  // Crear torneo
  create: (data) => api.post('/torneos', data),
  
  // Obtener torneo por ID
  getById: (id) => api.get(`/torneos/${id}`),
  
  // Actualizar torneo
  update: (id, data) => api.put(`/torneos/${id}`, data),
  
  // Eliminar torneo
  delete: (id) => api.delete(`/torneos/${id}`),
  
  // Inscribir equipo en torneo
  enrollTeam: (torneoId, data) => api.post(`/torneos/${torneoId}/equipos`, data),
  
  // Obtener clasificación del torneo
  getClassification: (torneoId) => api.get(`/torneos/${torneoId}/clasificacion`),
  
  // Generar fixture/calendario (solo admin)
  generateFixture: (torneoId) => api.post(`/torneos/${torneoId}/fixture`),
}

// ============================================================================
// SERVICIOS DE EQUIPOS
// ============================================================================

export const teamAPI = {
  // Listar equipos
  getAll: (params = {}) => api.get('/equipos', { params }),
  
  // Crear equipo
  create: (data) => api.post('/equipos', data),
  
  // Obtener equipo por ID
  getById: (id) => api.get(`/equipos/${id}`),
  
  // Actualizar equipo
  update: (id, data) => api.put(`/equipos/${id}`, data),
  
  // Eliminar equipo
  delete: (id) => api.delete(`/equipos/${id}`),
  
  // Obtener jugadores del equipo
  getPlayers: (equipoId) => api.get(`/equipos/${equipoId}/jugadores`),
  
  // Agregar jugador al equipo
  addPlayer: (equipoId, data) => api.post(`/equipos/${equipoId}/jugadores`, data),
  
  // Actualizar jugador en el equipo
  updatePlayer: (equipoId, jugadorId, data) => api.put(`/equipos/${equipoId}/jugadores/${jugadorId}`, data),
  
  // Remover jugador del equipo
  removePlayer: (equipoId, jugadorId) => api.delete(`/equipos/${equipoId}/jugadores/${jugadorId}`),
}

// ============================================================================
// SERVICIOS DE PARTIDOS
// ============================================================================

export const matchAPI = {
  // Listar partidos
  getAll: (params = {}) => api.get('/partidos', { params }),
  
  // Crear partido
  create: (data) => api.post('/partidos', data),
  
  // Obtener partido por ID
  getById: (id) => api.get(`/partidos/${id}`),
  
  // Actualizar partido
  update: (id, data) => api.put(`/partidos/${id}`, data),
  
  // Eliminar partido
  delete: (id) => api.delete(`/partidos/${id}`),
  
  // Iniciar partido
  start: (partidoId) => api.post(`/partidos/${partidoId}/iniciar`),
  
  // Finalizar partido
  finish: (partidoId, data) => api.post(`/partidos/${partidoId}/finalizar`, data),
  
  // Obtener eventos del partido
  getEvents: (partidoId) => api.get(`/partidos/${partidoId}/eventos`),
  
  // Agregar evento al partido
  addEvent: (partidoId, data) => api.post(`/partidos/${partidoId}/eventos`, data),
}

// ============================================================================
// SERVICIOS ESPECÍFICOS POR ROL
// ============================================================================

// SERVICIOS PARA ADMINISTRADORES
export const adminAPI = {
  // Estadísticas administrativas
  getStats: () => api.get('/admin/estadisticas'),
  
  // Logs del sistema
  getLogs: () => api.get('/admin/logs'),
}

// SERVICIOS PARA JUGADORES
export const playerAPI = {
  // Mis equipos
  getMyTeams: () => api.get('/jugador/equipos'),
  
  // Mis estadísticas
  getMyStats: () => api.get('/jugador/estadisticas'),
}

// SERVICIOS PARA ÁRBITROS
export const refereeAPI = {
  // Mis partidos asignados
  getMyMatches: () => api.get('/arbitro/partidos'),
}

// ============================================================================
// SERVICIOS DE DESARROLLO (solo en desarrollo)
// ============================================================================

export const devAPI = {
  // Generar datos de prueba
  seedData: () => api.post('/dev/seed-data'),
  
  // Obtener usuarios de prueba
  getTestUsers: () => api.get('/dev/test-users'),
}

// ============================================================================
// HELPERS Y UTILIDADES
// ============================================================================

export const apiHelpers = {
  // Manejar errores de API
  handleError: (error) => {
    if (error.response) {
      // Error de respuesta del servidor
      const { status, data } = error.response
      return {
        status,
        message: data.message || 'Error del servidor',
        errors: data.errors || null,
      }
    } else if (error.request) {
      // Error de red
      return {
        status: 0,
        message: 'Error de conexión. Verifica tu conexión a internet.',
        errors: null,
      }
    } else {
      // Error inesperado
      return {
        status: 0,
        message: 'Error inesperado: ' + error.message,
        errors: null,
      }
    }
  },

  // Extraer datos de respuesta
  extractData: (response) => {
    return response.data?.data || response.data
  },

  // Verificar si la respuesta es exitosa
  isSuccess: (response) => {
    return response.data?.success !== false && response.status >= 200 && response.status < 300
  },

  // Formatear parámetros de consulta
  formatParams: (params) => {
    const formatted = {}
    Object.keys(params).forEach(key => {
      if (params[key] !== null && params[key] !== undefined && params[key] !== '') {
        formatted[key] = params[key]
      }
    })
    return formatted
  },
}

// ============================================================================
// EXPORTACIONES POR DEFECTO
// ============================================================================

export default api