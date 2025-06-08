/**
 * API Services for Sports Tournament Manager
 * 
 * Configures Axios client with authentication, error handling, and organized API endpoints.
 * Provides centralized API communication layer with automatic token management.
 * 
 * Author: Alejandro Silla Tejero
 */
import axios from 'axios'

const getApiBaseUrl = () => {
  return import.meta.env.VITE_API_URL || 
         import.meta.env.VITE_API_BASE_URL || 
         'https://sportstournamentmanager-production.up.railway.app/api'
}

const API_BASE_URL = getApiBaseUrl()

const api = axios.create({
  baseURL: API_BASE_URL,
  timeout: parseInt(import.meta.env.VITE_API_TIMEOUT) || 15000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: false
})

// Request interceptor for authentication
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Response interceptor for authentication errors
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)
export const authAPI = {
  login: (credentials) => api.post('/auth/login', credentials),
  register: (userData) => api.post('/auth/register', userData),
  logout: () => api.post('/auth/logout'),
  profile: () => api.get('/auth/profile'),
  updateProfile: (data) => api.put('/auth/profile', data),
  refresh: () => api.post('/auth/refresh'),
}

export const tournamentAPI = {
  getAll: (params) => api.get('/tournaments', { params }),
  getById: (id) => api.get(`/tournaments/${id}`),
  create: (data) => api.post('/tournaments', data),
  update: (id, data) => api.put(`/tournaments/${id}`, data),
  delete: (id) => api.delete(`/tournaments/${id}`),
  registerTeam: (id, data) => api.post(`/tournaments/${id}/register-team`, data),
  standings: (id) => api.get(`/tournaments/${id}/standings`),
  matches: (id) => api.get(`/tournaments/${id}/matches`),
}

export const teamAPI = {
  getAll: (params) => api.get('/teams', { params }),
  getById: (id) => api.get(`/teams/${id}`),
  show: (id) => api.get(`/teams/${id}`),
  getRoster: (id) => api.get(`/teams/${id}/roster`),
  getStatistics: (id) => api.get(`/teams/${id}/statistics`),
  create: (data) => api.post('/teams', data),
  update: (id, data) => api.put(`/teams/${id}`, data),
  delete: (id) => api.delete(`/teams/${id}`),
  addPlayer: (teamId, playerData) => api.post(`/teams/${teamId}/add-player`, playerData),
  removePlayer: (teamId, playerId) => api.delete(`/teams/${teamId}/remove-player/${playerId}`),
  
  getAvailablePlayers: async (teamId) => {
    try {
      const response = await api.get(`/teams/${teamId}/available-players`)
      return response
    } catch (error) {
      throw error
    }
  },

  getMyTeams() {
    return api.get('/teams/my-teams')
  },

  getByManager(managerId) {
    return api.get(`/teams?manager_id=${managerId}`)
  },
}

export const playerAPI = {
  getAll: (params) => api.get('/players', { params }),
  getById: (id) => api.get(`/players/${id}`),
  getStatistics: (id) => api.get(`/players/${id}/statistics`),
  getTeamHistory: (id) => api.get(`/players/${id}/team-history`),
  update: (id, data) => api.put(`/players/${id}`, data),
  available: (params) => api.get('/players/available', { params }),
}

export const matchAPI = {
  getAll: (params) => api.get('/matches', { params }),
  getById: (id) => api.get(`/matches/${id}`),
  create: (data) => api.post('/matches', data),
  update: (id, data) => api.put(`/matches/${id}`, data),
  addEvent: (id, data) => api.post(`/matches/${id}/events`, data),
  getEvents: (id) => api.get(`/matches/${id}/events`),
}

export const standingAPI = {
  getByTournament: (tournamentId, params) => api.get(`/standings/${tournamentId}`, { params }),
  recalculate: (tournamentId) => api.post(`/standings/${tournamentId}/recalculate`),
  teamStats: (tournamentId, teamId) => api.get(`/standings/${tournamentId}/teams/${teamId}`),
  topScorers: (tournamentId, params) => api.get(`/standings/${tournamentId}/top-scorers`, { params }),
}

export const apiHelpers = {
  isSuccess: (response) => {
    return response?.data?.success === true || response?.status === 200
  },
  
  getData: (response) => {
    return response?.data?.data || response?.data || response
  },
  
  getMessage: (response) => {
    return response?.data?.message || 'Operation completed'
  },
  
  handleError: (error) => {
    if (error.response?.data?.message) {
      return error.response.data.message
    }
    if (error.response?.data?.error) {
      return error.response.data.error
    }
    if (error.message) {
      return error.message
    }
    return 'An unexpected error occurred'
  }
}

export default api