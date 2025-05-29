<template>
  <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <div class="flex justify-center">
        <div class="w-12 h-12 bg-primary-600 rounded-xl flex items-center justify-center">
          <TrophyIcon class="w-8 h-8 text-white" />
        </div>
      </div>
      <h2 class="mt-6 text-center text-3xl font-bold text-gray-900">
        Create your account
      </h2>
      <p class="mt-2 text-center text-sm text-gray-600">
        Already have an account?
        <RouterLink 
          to="/login" 
          class="font-medium text-primary-600 hover:text-primary-500 transition-colors"
        >
          Sign in here
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
              Full Name
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
                placeholder="Enter your full name"
                :disabled="isLoading"
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
              Email Address
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
                placeholder="Enter your email"
                :disabled="isLoading"
              />
              <EnvelopeIcon class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
            </div>
            <p v-if="errors.email" class="form-error">
              {{ errors.email }}
            </p>
          </div>

          <!-- Password Field -->
          <div>
            <label for="password" class="form-label">
              Password
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
                placeholder="Create a password"
                :disabled="isLoading"
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
            <!-- Password strength indicator -->
            <div v-if="form.password" class="mt-2">
              <div class="text-xs text-gray-600 mb-1">Password strength:</div>
              <div class="flex space-x-1">
                <div 
                  v-for="i in 4" 
                  :key="i"
                  :class="[
                    'h-1 flex-1 rounded',
                    i <= passwordStrength ? getStrengthColor(passwordStrength) : 'bg-gray-200'
                  ]"
                ></div>
              </div>
              <div class="text-xs mt-1" :class="getStrengthTextColor(passwordStrength)">
                {{ getStrengthText(passwordStrength) }}
              </div>
            </div>
          </div>

          <!-- Confirm Password Field -->
          <div>
            <label for="password_confirmation" class="form-label">
              Confirm Password
            </label>
            <div class="relative">
              <input
                id="password_confirmation"
                v-model="form.password_confirmation"
                :type="showConfirmPassword ? 'text' : 'password'"
                autocomplete="new-password"
                required
                :class="[
                  'form-input pr-10',
                  errors.password_confirmation ? 'border-danger-300 focus:border-danger-500 focus:ring-danger-500' : ''
                ]"
                placeholder="Confirm your password"
                :disabled="isLoading"
              />
              <button
                type="button"
                @click="showConfirmPassword = !showConfirmPassword"
                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
                :disabled="isLoading"
              >
                <EyeIcon v-if="!showConfirmPassword" class="w-5 h-5" />
                <EyeSlashIcon v-else class="w-5 h-5" />
              </button>
            </div>
            <p v-if="errors.password_confirmation" class="form-error">
              {{ errors.password_confirmation }}
            </p>
          </div>

          <!-- Role Selection -->
          <div>
            <label for="role" class="form-label">
              Role
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
            >
              <option value="">Select your role</option>
              <option value="player">Player</option>
              <option value="team_manager">Team Manager</option>
              <option value="referee">Referee</option>
            </select>
            <p v-if="errors.role" class="form-error">
              {{ errors.role }}
            </p>
            <p class="text-xs text-gray-500 mt-1">
              Choose the role that best describes your participation in tournaments
            </p>
          </div>

          <!-- Phone Field (Optional) -->
          <div>
            <label for="phone" class="form-label">
              Phone Number <span class="text-gray-400 text-sm">(Optional)</span>
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
                placeholder="Enter your phone number"
                :disabled="isLoading"
              />
              <PhoneIcon class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
            </div>
            <p v-if="errors.phone" class="form-error">
              {{ errors.phone }}
            </p>
          </div>

          <!-- Terms and Privacy -->
          <div class="flex items-start">
            <div class="flex items-center h-5">
              <input
                id="terms"
                v-model="form.terms"
                type="checkbox"
                required
                class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                :disabled="isLoading"
              />
            </div>
            <div class="ml-2 text-sm">
              <label for="terms" class="text-gray-700">
                I agree to the 
                <a href="#" class="text-primary-600 hover:text-primary-500">Terms of Service</a>
                and 
                <a href="#" class="text-primary-600 hover:text-primary-500">Privacy Policy</a>
              </label>
            </div>
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
                Creating account...
              </div>
              <span v-else>Create Account</span>
            </button>
          </div>
        </form>

        <!-- Social Registration (Future Enhancement) -->
        <div class="mt-6">
          <div class="relative">
            <div class="absolute inset-0 flex items-center">
              <div class="w-full border-t border-gray-300" />
            </div>
            <div class="relative flex justify-center text-sm">
              <span class="px-2 bg-white text-gray-500">Or register with</span>
            </div>
          </div>

          <div class="mt-6 text-center">
            <p class="text-sm text-gray-500">
              Social registration coming soon
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
/**
 * Registration Page Component
 * User registration form with validation and role selection
 */

import { ref, computed, watch } from 'vue'
import { 
  TrophyIcon,
  UserIcon,
  EnvelopeIcon,
  EyeIcon,
  EyeSlashIcon,
  PhoneIcon,
  ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'
import { useAuthStore } from '@/stores/auth'

export default {
  name: 'Register',
  components: {
    TrophyIcon,
    UserIcon,
    EnvelopeIcon,
    EyeIcon,
    EyeSlashIcon,
    PhoneIcon,
    ExclamationTriangleIcon
  },
  setup() {
    const authStore = useAuthStore()
    
    // Form state
    const form = ref({
      name: '',
      email: '',
      password: '',
      password_confirmation: '',
      role: '',
      phone: '',
      terms: false
    })

    // UI state
    const showPassword = ref(false)
    const showConfirmPassword = ref(false)
    const isLoading = ref(false)
    const errors = ref({})
    const generalError = ref('')

    // Password strength calculation
    const passwordStrength = computed(() => {
      const password = form.value.password
      if (!password) return 0
      
      let strength = 0
      if (password.length >= 8) strength++
      if (/[A-Z]/.test(password)) strength++
      if (/[0-9]/.test(password)) strength++
      if (/[^A-Za-z0-9]/.test(password)) strength++
      
      return strength
    })

    // Form validation
    const isFormValid = computed(() => {
      return form.value.name && 
             form.value.email && 
             form.value.password && 
             form.value.password_confirmation &&
             form.value.role &&
             form.value.terms &&
             !isLoading.value
    })

    // Watch for password confirmation match
    watch([() => form.value.password, () => form.value.password_confirmation], () => {
      if (form.value.password_confirmation && form.value.password !== form.value.password_confirmation) {
        errors.value.password_confirmation = 'Passwords do not match'
      } else {
        delete errors.value.password_confirmation
      }
    })

    /**
     * Handle form submission
     */
    const handleRegister = async () => {
      // Clear previous errors
      errors.value = {}
      generalError.value = ''
      
      // Validation
      if (!validateForm()) {
        return
      }

      isLoading.value = true

      try {
        const result = await authStore.register({
          name: form.value.name,
          email: form.value.email,
          password: form.value.password,
          password_confirmation: form.value.password_confirmation,
          role: form.value.role,
          phone: form.value.phone || null
        })

        if (!result.success) {
          generalError.value = result.error || 'Registration failed'
        }
        // Success case is handled by the store (redirect)
      } catch (error) {
        console.error('Registration error:', error)
        generalError.value = 'An unexpected error occurred'
      } finally {
        isLoading.value = false
      }
    }

    /**
     * Validate form data
     */
    const validateForm = () => {
      const newErrors = {}

      // Name validation
      if (!form.value.name.trim()) {
        newErrors.name = 'Name is required'
      } else if (form.value.name.trim().length < 2) {
        newErrors.name = 'Name must be at least 2 characters'
      }

      // Email validation
      if (!form.value.email) {
        newErrors.email = 'Email is required'
      } else {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
        if (!emailRegex.test(form.value.email)) {
          newErrors.email = 'Please enter a valid email address'
        }
      }

      // Password validation
      if (!form.value.password) {
        newErrors.password = 'Password is required'
      } else if (form.value.password.length < 8) {
        newErrors.password = 'Password must be at least 8 characters'
      } else if (passwordStrength.value < 2) {
        newErrors.password = 'Password is too weak. Include uppercase, numbers, or special characters.'
      }

      // Password confirmation
      if (!form.value.password_confirmation) {
        newErrors.password_confirmation = 'Password confirmation is required'
      } else if (form.value.password !== form.value.password_confirmation) {
        newErrors.password_confirmation = 'Passwords do not match'
      }

      // Role validation
      if (!form.value.role) {
        newErrors.role = 'Please select a role'
      }

      // Terms validation
      if (!form.value.terms) {
        newErrors.terms = 'You must agree to the terms and conditions'
      }

      // Phone validation (optional but if provided, should be valid)
      if (form.value.phone && form.value.phone.length > 0) {
        const phoneRegex = /^[\+]?[\d\s\-\(\)]{10,}$/
        if (!phoneRegex.test(form.value.phone)) {
          newErrors.phone = 'Please enter a valid phone number'
        }
      }

      errors.value = newErrors
      return Object.keys(newErrors).length === 0
    }

    /**
     * Get password strength color
     */
    const getStrengthColor = (strength) => {
      const colors = ['bg-danger-500', 'bg-warning-500', 'bg-warning-400', 'bg-success-500']
      return colors[strength - 1] || 'bg-gray-200'
    }

    /**
     * Get password strength text color
     */
    const getStrengthTextColor = (strength) => {
      const colors = ['text-danger-600', 'text-warning-600', 'text-warning-600', 'text-success-600']
      return colors[strength - 1] || 'text-gray-500'
    }

    /**
     * Get password strength text
     */
    const getStrengthText = (strength) => {
      const texts = ['Weak', 'Fair', 'Good', 'Strong']
      return texts[strength - 1] || 'Too short'
    }

    return {
      form,
      showPassword,
      showConfirmPassword,
      isLoading,
      errors,
      generalError,
      passwordStrength,
      isFormValid,
      handleRegister,
      getStrengthColor,
      getStrengthTextColor,
      getStrengthText
    }
  }
}
</script>