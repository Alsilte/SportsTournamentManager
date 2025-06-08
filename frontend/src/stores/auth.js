/**
 * Authentication Store for Sports Tournament Manager
 * 
 * Manages user authentication state, login/logout functionality, and role-based permissions.
 * Provides reactive authentication state with local persistence and token management.
 * 
 * Author: Alejandro Silla Tejero
 */
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authAPI, apiHelpers } from '@/services/api'
import router from '@/router'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(null)
  const isLoading = ref(false)
  const error = ref(null)
  const isInitialized = ref(false)

  const isAuthenticated = computed(() => !!token.value && !!user.value)
  const isAdmin = computed(() => user.value?.role === 'admin')
  const isTeamManager = computed(() => user.value?.role === 'team_manager')
  const isPlayer = computed(() => user.value?.role === 'player')
  const isReferee = computed(() => user.value?.role === 'referee')
  const userRole = computed(() => user.value?.role || null)
  const userName = computed(() => user.value?.name || '')
  const userEmail = computed(() => user.value?.email || '')

  const canManageTeams = computed(() => isAdmin.value || isTeamManager.value)
  const canManageTournaments = computed(() => isAdmin.value)
  const canRefereeMatches = computed(() => isAdmin.value || isReferee.value)

  /**
   * Initialize authentication state on app start
   */
  const initializeAuth = async () => {
    try {
      const storedToken = localStorage.getItem('auth_token')
      const storedUser = localStorage.getItem('auth_user')
      
      if (storedToken && storedUser) {
        token.value = storedToken
        user.value = JSON.parse(storedUser)
        
        await fetchProfile()
      } else {
        clearAuth()
      }
    } catch (error) {
      clearAuth()
    } finally {
      isInitialized.value = true
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
      
      if (response.status >= 200 && response.status < 300) {
        const data = response.data
        
        if (data.success === true && data.data) {
          const authToken = data.data.token
          const userData = data.data.user
          
          token.value = authToken
          user.value = userData
          
          localStorage.setItem('auth_token', authToken)
          localStorage.setItem('auth_user', JSON.stringify(userData))
          
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
      clearAuth()
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
      
      if (response.status >= 200 && response.status < 300) {
        const data = response.data
        
        if (data.success === true && data.data) {
          const authToken = data.data.token
          const userInfo = data.data.user
          
          token.value = authToken
          user.value = userInfo
          
          localStorage.setItem('auth_token', authToken)
          localStorage.setItem('auth_user', JSON.stringify(userInfo))
          
          if (window.$notify) {
            window.$notify.success('Account created successfully! Welcome to Tournament Manager.')
          }
          
          router.push('/dashboard')
          
          return { success: true }
        } else {
          throw new Error(data.message || 'Registration failed')
        }
      } else {
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
        if (err.response.status === 422 && err.response.data?.errors) {
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
      clearAuth()
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
      await authAPI.logout()
    } catch (error) {
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
        const userData = response.data.data
        
        user.value = userData
        localStorage.setItem('auth_user', JSON.stringify(userData))
        
        return { success: true }
      } else {
        throw new Error('Failed to fetch profile')
      }
    } catch (err) {
      const errorMessage = err.response?.data?.message || err.message || 'Failed to fetch profile'
      error.value = errorMessage
      
      if (err.response?.status === 401) {
        clearAuth()
      }
      
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
        const userData = response.data.data
        
        // Update state and localStorage
        user.value = userData
        localStorage.setItem('auth_user', JSON.stringify(userData))
        
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
        const newToken = response.data.data.token
        
        token.value = newToken
        localStorage.setItem('auth_token', newToken)
        
        return { success: true }
      } else {
        throw new Error('Token refresh failed')
      }
    } catch (err) {
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
    error.value = null
    
    localStorage.removeItem('auth_token')
    localStorage.removeItem('auth_user')
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
   * Clear error state
   */
  const clearError = () => {
    error.value = null
  }

  return {
    user,
    token,
    isLoading,
    error,
    isInitialized,
    
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