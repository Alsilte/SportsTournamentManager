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
        {{ $t('auth.createYourAccount') }}
      </h2>
      <p class="mt-2 text-center text-sm text-gray-600">
        {{ $t('auth.or') }}
        <RouterLink 
          to="/login" 
          class="font-medium text-primary-600 hover:text-primary-500 transition-colors"
        >
          {{ $t('auth.signInToExisting') }}
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
              {{ $t('auth.fullName') }}
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
                :placeholder="$t('auth.enterFullName')"
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
              {{ $t('auth.emailAddress') }}
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
                :placeholder="$t('auth.enterEmail')"
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
              {{ $t('auth.accountType') }}
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
              <option value="">{{ $t('auth.selectRole') }}</option>
              <option value="player">{{ $t('auth.player') }}</option>
              <option value="team_manager">{{ $t('auth.teamManager') }}</option>
              <option value="referee">{{ $t('auth.referee') }}</option>
            </select>
            <p v-if="errors.role" class="form-error">
              {{ errors.role }}
            </p>
          </div>

          <!-- Phone Field -->
          <div>
            <label for="phone" class="form-label">
              {{ $t('auth.phoneOptional') }}
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
                :placeholder="$t('auth.enterPhone')"
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
              {{ $t('auth.password') }}
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
                :placeholder="$t('auth.enterPassword')"
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
              {{ $t('auth.confirmPassword') }}
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
                :placeholder="$t('auth.confirmYourPassword')"
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
              {{ $t('auth.agreeToTerms') }} 
              <a href="#" class="text-primary-600 hover:text-primary-500">{{ $t('auth.termsOfService') }}</a>
              {{ $t('auth.and') }} 
              <a href="#" class="text-primary-600 hover:text-primary-500">{{ $t('auth.privacyPolicy') }}</a>
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
                {{ $t('auth.creatingAccount') }}
              </div>
              <span v-else>{{ $t('auth.createAccount') }}</span>
            </button>
          </div>
        </form>

        <!-- Demo Accounts Info -->
        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
          <h3 class="text-sm font-medium text-gray-900 mb-2">{{ $t('auth.demoRolesAvailable') }}</h3>
          <div class="text-xs text-gray-600 space-y-1">
            <div><strong>{{ $t('auth.playerRole') }}:</strong> {{ $t('auth.playerDescription') }}</div>
            <div><strong>{{ $t('auth.teamManagerRole') }}:</strong> {{ $t('auth.teamManagerDescription') }}</div>
            <div><strong>{{ $t('auth.refereeRole') }}:</strong> {{ $t('auth.refereeDescription') }}</div>
          </div>
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
import { useI18n } from 'vue-i18n'

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
    const { t } = useI18n()
    
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
        errors.value.name = t('auth.fullNameRequired')
      } else if (form.value.name.length < 2) {
        errors.value.name = t('auth.nameMinLength')
      }

      // Email validation
      if (!form.value.email) {
        errors.value.email = t('auth.emailRequired')
      } else {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
        if (!emailRegex.test(form.value.email)) {
          errors.value.email = t('auth.emailInvalid')
        }
      }

      // Role validation
      if (!form.value.role) {
        errors.value.role = t('auth.selectAccountType')
      }

      // Password validation
      if (!form.value.password) {
        errors.value.password = t('auth.passwordRequired')
      } else if (form.value.password.length < 8) {
        errors.value.password = t('auth.passwordTooShort')
      }

      // Password confirmation validation
      if (!form.value.password_confirmation) {
        errors.value.password_confirmation = t('auth.passwordConfirmationRequired')
      } else if (form.value.password !== form.value.password_confirmation) {
        errors.value.password_confirmation = t('auth.passwordsNotMatch')
      }

      // Terms validation
      if (!form.value.terms) {
        errors.value.terms = t('auth.agreeToTermsRequired')
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
          generalError.value = result?.message || t('auth.registrationFailed')
        }
      } catch (error) {
        console.error('Registration error:', error)
        if (error.response?.data) {
          generalError.value = error.response.data.message || t('auth.registrationUnexpectedError')
        } else {
          generalError.value = t('auth.registrationUnexpectedError')
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