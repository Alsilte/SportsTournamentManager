<template>
  <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <div class="flex justify-center">
        <div class="w-12 h-12 bg-primary-600 rounded-xl flex items-center justify-center">
              <img src="../../../public/logo.png" alt="Logo">
        </div>
      </div>
      <h2 class="mt-6 text-center text-3xl font-bold text-gray-900">
        Crear tu cuenta
      </h2>
      <p class="mt-2 text-center text-sm text-gray-600">
        O
        <RouterLink 
          to="/login" 
          class="font-medium text-primary-600 hover:text-primary-500 transition-colors"
        >
          iniciar sesión en cuenta existente
        </RouterLink>
      </p>
    </div>

    <!-- Registration Form -->
    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="card px-4 py-8 sm:px-10">
        <form @submit.prevent="handleRegister" class="space-y-6">
          <!-- Name Field -->
          <div>
            <label for="name" class="form-label">
              Nombre completo
            </label>
            <div class="relative">
              <input
                id="name"
                v-model="form.name"
                type="text"
                autocomplete="name"
                required
                :class="[
                  'form-input',
                  errors.name ? 'border-danger-300 focus:border-danger-500 focus:ring-danger-500' : ''
                ]"
                placeholder="Ingresa tu nombre completo"
                :disabled="isLoading"
                @input="clearFieldError('name')"
              />
              <UserIcon class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
            </div>
            <p v-if="errors.name" class="form-error">
              {{ errors.name }}
            </p>
          </div>

          <!-- Email Field -->
          <div>
            <label for="email" class="form-label">
              Dirección de correo electrónico
            </label>
            <div class="relative">
              <input
                id="email"
                v-model="form.email"
                type="email"
                autocomplete="email"
                required
                :class="[
                  'form-input',
                  errors.email ? 'border-danger-300 focus:border-danger-500 focus:ring-danger-500' : ''
                ]"
                placeholder="Ingresa tu correo electrónico"
                :disabled="isLoading"
                @input="clearFieldError('email')"
              />
              <EnvelopeIcon class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
            </div>
            <p v-if="errors.email" class="form-error">
              {{ errors.email }}
            </p>
          </div>

          <!-- Role Field -->
          <div>
            <label for="role" class="form-label">
              Tipo de cuenta
            </label>
            <select
              id="role"
              v-model="form.role"
              required
              :class="[
                'form-input',
                errors.role ? 'border-danger-300 focus:border-danger-500 focus:ring-danger-500' : ''
              ]"
              :disabled="isLoading"
              @change="clearFieldError('role')"
            >
              <option value="">Seleccionar rol</option>
              <option value="player">Jugador</option>
              <option value="team_manager">Gestor de Equipo</option>
              <option value="referee">Árbitro</option>
            </select>
            <p v-if="errors.role" class="form-error">
              {{ errors.role }}
            </p>
          </div>

          <!-- Phone Field -->
          <div>
            <label for="phone" class="form-label">
              Teléfono (opcional)
            </label>
            <div class="relative">
              <input
                id="phone"
                v-model="form.phone"
                type="tel"
                autocomplete="tel"
                :class="[
                  'form-input',
                  errors.phone ? 'border-danger-300 focus:border-danger-500 focus:ring-danger-500' : ''
                ]"
                placeholder="Ingresa tu teléfono"
                :disabled="isLoading"
                @input="clearFieldError('phone')"
              />
              <PhoneIcon class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
            </div>
            <p v-if="errors.phone" class="form-error">
              {{ errors.phone }}
            </p>
          </div>

          <!-- Password Field -->
          <div>
            <label for="password" class="form-label">
              Contraseña
            </label>
            <div class="relative">
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="new-password"
                required
                :class="[
                  'form-input pr-10',
                  errors.password ? 'border-danger-300 focus:border-danger-500 focus:ring-danger-500' : ''
                ]"
                placeholder="Ingresa tu contraseña"
                :disabled="isLoading"
                @input="clearFieldError('password')"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
                :disabled="isLoading"
              >
                <EyeIcon v-if="!showPassword" class="w-5 h-5" />
                <EyeSlashIcon v-else class="w-5 h-5" />
              </button>
            </div>
            <p v-if="errors.password" class="form-error">
              {{ errors.password }}
            </p>
          </div>

          <!-- Password Confirmation Field -->
          <div>
            <label for="password_confirmation" class="form-label">
              Confirmar contraseña
            </label>
            <div class="relative">
              <input
                id="password_confirmation"
                v-model="form.password_confirmation"
                :type="showPasswordConfirmation ? 'text' : 'password'"
                autocomplete="new-password"
                required
                :class="[
                  'form-input pr-10',
                  errors.password_confirmation ? 'border-danger-300 focus:border-danger-500 focus:ring-danger-500' : ''
                ]"
                placeholder="Confirma tu contraseña"
                :disabled="isLoading"
                @input="clearFieldError('password_confirmation')"
              />
              <button
                type="button"
                @click="showPasswordConfirmation = !showPasswordConfirmation"
                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
                :disabled="isLoading"
              >
                <EyeIcon v-if="!showPasswordConfirmation" class="w-5 h-5" />
                <EyeSlashIcon v-else class="w-5 h-5" />
              </button>
            </div>
            <p v-if="errors.password_confirmation" class="form-error">
              {{ errors.password_confirmation }}
            </p>
          </div>

          <!-- Terms and Conditions -->
          <div class="flex items-center">
            <input
              id="terms"
              v-model="form.terms"
              type="checkbox"
              class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
              :disabled="isLoading"
              @change="clearFieldError('terms')"
            />
            <label for="terms" class="ml-2 block text-sm text-gray-700">
              Acepto los 
              <a href="#" class="text-primary-600 hover:text-primary-500">Términos del Servicio</a>
              y la 
              <a href="#" class="text-primary-600 hover:text-primary-500">Política de Privacidad</a>
            </label>
          </div>
          <p v-if="errors.terms" class="form-error">
            {{ errors.terms }}
          </p>

          <!-- General Error -->
          <div v-if="generalError" class="rounded-lg bg-danger-50 border border-danger-200 p-4">
            <div class="flex items-center">
              <ExclamationTriangleIcon class="w-5 h-5 text-danger-600 mr-2" />
              <p class="text-sm text-danger-700">{{ generalError }}</p>
            </div>
          </div>

          <!-- Submit Button -->
          <div>
            <button
              type="submit"
              :disabled="isLoading || !isFormValid"
              class="w-full btn-primary py-3 text-base"
            >
              <div v-if="isLoading" class="flex items-center justify-center">
                <div class="spinner w-5 h-5 mr-2"></div>
                Creando cuenta...
              </div>
              <span v-else>Crear cuenta</span>
            </button>
          </div>
        </form>

        <!-- Home Button -->
        <div class="mt-6 text-center">
          <RouterLink 
            to="/" 
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-primary-600 bg-primary-50 hover:bg-primary-100 transition-colors"
          >
            Ir al Inicio
          </RouterLink>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
/**
 * Registration Page Component
 * User registration form with validation
 */

import { ref, computed, onMounted } from 'vue'
import { 
  TrophyIcon,
  UserIcon,
  EnvelopeIcon,
  PhoneIcon,
  EyeIcon,
  EyeSlashIcon,
  ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'
import { useAuthStore } from '@/stores/auth'

export default {
  name: 'Register',
  components: {
    TrophyIcon,
    UserIcon,
    EnvelopeIcon,
    PhoneIcon,
    EyeIcon,
    EyeSlashIcon,
    ExclamationTriangleIcon
  },
  setup() {
    const authStore = useAuthStore()
    
    // Form state
    const form = ref({
      name: '',
      email: '',
      role: '',
      phone: '',
      password: '',
      password_confirmation: '',
      terms: false
    })

    // UI state
    const showPassword = ref(false)
    const showPasswordConfirmation = ref(false)
    const isLoading = ref(false)
    const errors = ref({})
    const generalError = ref('')

    // Computed properties
    const isFormValid = computed(() => {
      return form.value.name && 
             form.value.email && 
             form.value.role && 
             form.value.password && 
             form.value.password_confirmation && 
             form.value.terms && 
             !isLoading.value
    })

    /**
     * Clear error for specific field
     */
    const clearFieldError = (field) => {
      if (errors.value[field]) {
        delete errors.value[field]
      }
      if (generalError.value) {
        generalError.value = ''
      }
    }

    /**
     * Validate form fields
     */
    const validateForm = () => {
      errors.value = {}

      // Name validation
      if (!form.value.name) {
        errors.value.name = 'El nombre completo es requerido'
      } else if (form.value.name.length < 2) {
        errors.value.name = 'El nombre debe tener al menos 2 caracteres'
      }

      // Email validation
      if (!form.value.email) {
        errors.value.email = 'El correo electrónico es requerido'
      } else {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
        if (!emailRegex.test(form.value.email)) {
          errors.value.email = 'Formato de correo electrónico inválido'
        }
      }

      // Role validation
      if (!form.value.role) {
        errors.value.role = 'Debes seleccionar un tipo de cuenta'
      }

      // Password validation
      if (!form.value.password) {
        errors.value.password = 'La contraseña es requerida'
      } else if (form.value.password.length < 8) {
        errors.value.password = 'La contraseña debe tener al menos 8 caracteres'
      }

      // Password confirmation validation
      if (!form.value.password_confirmation) {
        errors.value.password_confirmation = 'La confirmación de contraseña es requerida'
      } else if (form.value.password !== form.value.password_confirmation) {
        errors.value.password_confirmation = 'Las contraseñas no coinciden'
      }

      // Terms validation
      if (!form.value.terms) {
        errors.value.terms = 'Debes aceptar los términos y condiciones'
      }

      return Object.keys(errors.value).length === 0
    }

    /**
     * Handle form submission
     */
    const handleRegister = async () => {
      // Clear previous errors
      generalError.value = ''
      
      // Validate form
      if (!validateForm()) {
        return
      }

      isLoading.value = true

      try {
        const result = await authStore.register({
          name: form.value.name,
          email: form.value.email,
          role: form.value.role,
          phone: form.value.phone || null,
          password: form.value.password,
          password_confirmation: form.value.password_confirmation
        })

        if (result && result.success === true) {
          console.log('Registration successful:', result.data)
        } else {
          generalError.value = result?.message || 'Error en el registro'
        }
      } catch (error) {
        console.error('Registration error:', error)
        if (error.response?.data) {
          generalError.value = error.response.data.message || 'Error inesperado durante el registro'
        } else {
          generalError.value = 'Error inesperado durante el registro'
        }
      } finally {
        isLoading.value = false
      }
    }

    // Clear any previous auth errors
    onMounted(() => {
      authStore.clearError()
    })

    return {
      form,
      showPassword,
      showPasswordConfirmation,
      isLoading,
      errors,
      generalError,
      isFormValid,
      handleRegister,
      clearFieldError
    }
  }
}
</script>