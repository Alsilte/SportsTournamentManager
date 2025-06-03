/**
 * STORE DE AUTENTICACIÓN
 * 
 * Archivo: src/stores/auth.js
 * 
 * Manejo global del estado de autenticación con Pinia
 */

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authAPI, apiHelpers } from '@/services/api'
import router from '@/router'

export const useAuthStore = defineStore('auth', () => {
  // ============================================================================
  // ESTADO
  // ============================================================================
  
  const user = ref(null)
  const token = ref(null)
  const isLoading = ref(false)
  const error = ref(null)

  // ============================================================================
  // GETTERS COMPUTADOS
  // ============================================================================
  
  const isAuthenticated = computed(() => {
    return !!(user.value && token.value)
  })

  const userRole = computed(() => {
    return user.value?.tipo_usuario || null
  })

  const userName = computed(() => {
    return user.value?.nombre || ''
  })

  const userEmail = computed(() => {
    return user.value?.email || ''
  })

  const isAdmin = computed(() => {
    return userRole.value === 'administrador'
  })

  const isJugador = computed(() => {
    return userRole.value === 'jugador'
  })

  const isArbitro = computed(() => {
    return userRole.value === 'arbitro'
  })

  // ============================================================================
  // ACCIONES
  // ============================================================================

  /**
   * Inicializar store desde localStorage
   */
  function initializeAuth() {
    const savedToken = localStorage.getItem('auth_token')
    const savedUser = localStorage.getItem('user_data')

    if (savedToken && savedUser) {
      try {
        token.value = savedToken
        user.value = JSON.parse(savedUser)
      } catch (error) {
        console.error('Error al parsear datos de usuario:', error)
        clearAuth()
      }
    }
  }

  /**
   * Limpiar estado de autenticación
   */
  function clearAuth() {
    user.value = null
    token.value = null
    error.value = null
    localStorage.removeItem('auth_token')
    localStorage.removeItem('user_data')
  }

  /**
   * Establecer error
   */
  function setError(errorMessage) {
    error.value = errorMessage
  }

  /**
   * Limpiar error
   */
  function clearError() {
    error.value = null
  }

  /**
   * Registrar nuevo usuario
   */
  async function register(userData) {
    isLoading.value = true
    clearError()

    try {
      const result = await authAPI.register(userData)
      
      if (result.success) {
        // Si el registro incluye login automático
        if (result.data.token) {
          token.value = result.data.token
          user.value = result.data.user
        }
        return { success: true, message: result.message }
      } else {
        setError(result.message)
        return { success: false, message: result.message, errors: result.errors }
      }
    } catch (error) {
      const errorMessage = 'Error de conexión al registrar usuario'
      setError(errorMessage)
      return { success: false, message: errorMessage }
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Iniciar sesión
   */
  async function login(credentials) {
    isLoading.value = true
    clearError()

    try {
      const result = await authAPI.login(credentials)
      
      if (result.success) {
        token.value = result.data.token
        user.value = result.data.user
        
        // Redirigir al dashboard después del login exitoso
        await router.push('/dashboard')
        
        return { success: true, message: result.message }
      } else {
        setError(result.message)
        return { success: false, message: result.message, errors: result.errors }
      }
    } catch (error) {
      const errorMessage = 'Error de conexión al iniciar sesión'
      setError(errorMessage)
      return { success: false, message: errorMessage }
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Cerrar sesión
   */
  async function logout() {
    isLoading.value = true
    
    try {
      await authAPI.logout()
    } catch (error) {
      console.warn('Error al cerrar sesión en el servidor:', error)
    } finally {
      clearAuth()
      isLoading.value = false
      
      // Redirigir al login
      await router.push('/login')
    }
  }

  /**
   * Obtener datos del usuario actual
   */
  async function fetchUser() {
    if (!token.value) {
      return { success: false, message: 'No hay token de autenticación' }
    }

    isLoading.value = true
    clearError()

    try {
      const result = await authAPI.me()
      
      if (result.success) {
        user.value = result.data
        // Actualizar datos en localStorage
        localStorage.setItem('user_data', JSON.stringify(result.data))
        return { success: true, data: result.data }
      } else {
        setError(result.message)
        return { success: false, message: result.message }
      }
    } catch (error) {
      const errorMessage = 'Error al obtener datos del usuario'
      setError(errorMessage)
      return { success: false, message: errorMessage }
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Cambiar contraseña
   */
  async function changePassword(passwordData) {
    isLoading.value = true
    clearError()

    try {
      const result = await authAPI.changePassword(passwordData)
      
      if (result.success) {
        return { success: true, message: result.message }
      } else {
        setError(result.message)
        return { success: false, message: result.message, errors: result.errors }
      }
    } catch (error) {
      const errorMessage = 'Error al cambiar contraseña'
      setError(errorMessage)
      return { success: false, message: errorMessage }
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Verificar si el usuario puede acceder a una ruta
   */
  function canAccess(requiredRole = null) {
    if (!isAuthenticated.value) {
      return false
    }

    if (!requiredRole) {
      return true
    }

    if (Array.isArray(requiredRole)) {
      return requiredRole.includes(userRole.value)
    }

    return userRole.value === requiredRole
  }

  /**
   * Refrescar autenticación
   */
  async function refreshAuth() {
    if (token.value) {
      await fetchUser()
    }
  }

  // ============================================================================
  // RETURN DEL STORE
  // ============================================================================

  return {
    // Estado
    user,
    token,
    isLoading,
    error,
    
    // Getters
    isAuthenticated,
    userRole,
    userName,
    userEmail,
    isAdmin,
    isJugador,
    isArbitro,
    
    // Acciones
    initializeAuth,
    clearAuth,
    setError,
    clearError,
    register,
    login,
    logout,
    fetchUser,
    changePassword,
    canAccess,
    refreshAuth
  }
})