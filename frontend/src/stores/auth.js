/**
 * Authentication Store
 * Manages user authentication state, login, logout, and permissions
 */

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authAPI, apiHelpers } from '@/services/api'
import router from '@/router'

export const useAuthStore = defineStore('auth', () => {
  // State
  const user = ref(null)
  const token = ref(localStorage.getItem('auth_token'))
  const isLoading = ref(false)
  const error = ref(null)

  // Getters
  const isAuthenticated = computed(() => !!token.value && !!user.value)
  const isAdmin = computed(() => user.value?.role === 'admin')
  const isTeamManager = computed(() => user.value?.role === 'team_manager')
  const isPlayer = computed(() => user.value?.role === 'player')
  const isReferee = computed(() => user.value?.role === 'referee')
  const userRole = computed(() => user.value?.role || null)
  const userName = computed(() => user.value?.name || '')
  const userEmail = computed(() => user.value?.email || '')

  // Actions
  
  /**
   * Initialize authentication state on app start
   */
  const initializeAuth = async () => {
    if (token.value) {
      try {
        await fetchProfile()
      } catch (error) {
        console.error('Failed to initialize auth:', error)
        clearAuth()
      }
    }
  }

  /**
   * Login user with credentials
   */
  const login = async (credentials) => {
    isLoading.value = true
    error.value = null

    try {
      const response = await authAPI.login(credentials)
      
      // Verificar si la respuesta es exitosa (status 200-299)
      if (response.status >= 200 && response.status < 300) {
        const data = response.data
        
        // Verificar si el backend indica éxito
        if (data.success === true && data.data) {
          // Store token and user data
          token.value = data.data.token
          user.value = data.data.user
          localStorage.setItem('auth_token', data.data.token)
          
          // Redirect to intended route or dashboard
          const intendedRoute = router.currentRoute.value.query.redirect || '/dashboard'
          router.push(intendedRoute)
          
          return { success: true }
        } else {
          throw new Error(data.message || 'Login failed')
        }
      } else {
        throw new Error(response.data?.message || 'Login failed')
      }
    } catch (err) {
      const errorMessage = err.response?.data?.message || err.message || 'Login failed'
      error.value = errorMessage
      return { success: false, error: errorMessage }
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Register new user
   */
  const register = async (userData) => {
    isLoading.value = true
    error.value = null

    try {
      const response = await authAPI.register(userData)
      
      // Verificar si la respuesta es exitosa (status 200-299)
      if (response.status >= 200 && response.status < 300) {
        const data = response.data
        
        // Verificar si el backend indica éxito
        if (data.success === true && data.data) {
          // Store token and user data
          token.value = data.data.token
          user.value = data.data.user
          localStorage.setItem('auth_token', data.data.token)
          
          // Mostrar mensaje de éxito
          if (window.$notify) {
            window.$notify.success('Account created successfully! Welcome to Tournament Manager.')
          }
          
          // Redirect to dashboard
          router.push('/dashboard')
          
          return { success: true }
        } else {
          throw new Error(data.message || 'Registration failed')
        }
      } else {
        // Manejar errores de validación del backend
        if (response.status === 422 && response.data?.errors) {
          const validationErrors = response.data.errors
          const errorMessages = Object.values(validationErrors).flat()
          throw new Error(errorMessages.join(', '))
        } else {
          throw new Error(response.data?.message || 'Registration failed')
        }
      }
    } catch (err) {
      let errorMessage = 'Registration failed'
      
      if (err.response) {
        // Error del servidor
        if (err.response.status === 422 && err.response.data?.errors) {
          // Errores de validación
          const validationErrors = err.response.data.errors
          const errorMessages = Object.values(validationErrors).flat()
          errorMessage = errorMessages.join(', ')
        } else {
          errorMessage = err.response.data?.message || 'Registration failed'
        }
      } else if (err.message) {
        errorMessage = err.message
      }
      
      error.value = errorMessage
      return { success: false, error: errorMessage }
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Logout user
   */
  const logout = async () => {
    isLoading.value = true

    try {
      // Call logout endpoint to invalidate token on server
      await authAPI.logout()
    } catch (error) {
      console.error('Logout API call failed:', error)
      // Continue with local logout even if API call fails
    } finally {
      clearAuth()
      router.push('/login')
      isLoading.value = false
    }
  }

  /**
   * Fetch user profile
   */
  const fetchProfile = async () => {
    try {
      const response = await authAPI.profile()
      
      if (response.status >= 200 && response.status < 300 && response.data?.success === true) {
        user.value = response.data.data
        return { success: true }
      } else {
        throw new Error('Failed to fetch profile')
      }
    } catch (err) {
      const errorMessage = err.response?.data?.message || err.message || 'Failed to fetch profile'
      error.value = errorMessage
      return { success: false, error: errorMessage }
    }
  }

  /**
   * Update user profile
   */
  const updateProfile = async (profileData) => {
    isLoading.value = true
    error.value = null

    try {
      const response = await authAPI.updateProfile(profileData)
      
      if (response.status >= 200 && response.status < 300 && response.data?.success === true) {
        user.value = response.data.data
        return { success: true }
      } else {
        throw new Error(response.data?.message || 'Profile update failed')
      }
    } catch (err) {
      const errorMessage = err.response?.data?.message || err.message || 'Profile update failed'
      error.value = errorMessage
      return { success: false, error: errorMessage }
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Refresh authentication token
   */
  const refreshToken = async () => {
    try {
      const response = await authAPI.refresh()
      
      if (response.status >= 200 && response.status < 300 && response.data?.success === true) {
        const data = response.data.data
        token.value = data.token
        localStorage.setItem('auth_token', data.token)
        return { success: true }
      } else {
        throw new Error('Token refresh failed')
      }
    } catch (err) {
      console.error('Token refresh failed:', err)
      clearAuth()
      return { success: false }
    }
  }

  /**
   * Clear authentication data
   */
  const clearAuth = () => {
    user.value = null
    token.value = null
    localStorage.removeItem('auth_token')
    error.value = null
  }

  /**
   * Check if user has specific role
   */
  const hasRole = (role) => {
    return user.value?.role === role
  }

  /**
   * Check if user has any of the specified roles
   */
  const hasAnyRole = (roles) => {
    return roles.includes(user.value?.role)
  }

  /**
   * Check if user can manage teams
   */
  const canManageTeams = computed(() => {
    return isAdmin.value || isTeamManager.value
  })

  /**
   * Check if user can manage tournaments
   */
  const canManageTournaments = computed(() => {
    return isAdmin.value
  })

  /**
   * Check if user can referee matches
   */
  const canRefereeMatches = computed(() => {
    return isAdmin.value || isReferee.value
  })

  /**
   * Clear error state
   */
  const clearError = () => {
    error.value = null
  }

  // Return store interface
  return {
    // State
    user,
    token,
    isLoading,
    error,
    
    // Getters
    isAuthenticated,
    isAdmin,
    isTeamManager,
    isPlayer,
    isReferee,
    userRole,
    userName,
    userEmail,
    canManageTeams,
    canManageTournaments,
    canRefereeMatches,
    
    // Actions
    initializeAuth,
    login,
    register,
    logout,
    fetchProfile,
    updateProfile,
    refreshToken,
    clearAuth,
    hasRole,
    hasAnyRole,
    clearError
  }
})