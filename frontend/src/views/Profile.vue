<template>
  <MainLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">{{ $t('profile.title') }}</h1>
          <p class="text-gray-600 mt-1">{{ $t('profile.subtitle') }}</p>
        </div>
      </div>
    </template>

    <div class="max-w-2xl mx-auto">
      <div class="card p-6">
        <div class="flex items-center space-x-6 mb-6">
          <div class="w-20 h-20 bg-primary-100 rounded-full flex items-center justify-center">
            <UserIcon class="w-10 h-10 text-primary-600" />
          </div>
          <div>
            <h2 class="text-xl font-semibold text-gray-900">{{ authStore.userName }}</h2>
            <p class="text-gray-600 capitalize">{{ authStore.userRole }}</p>
            <p class="text-sm text-gray-500">{{ authStore.userEmail }}</p>
          </div>
        </div>

        <!-- Formulario editable -->
        <form @submit.prevent="updateProfile" class="space-y-4">
          <div>
            <label class="form-label">{{ $t('profile.form.fullName') }}</label>
            <input 
              v-model="form.name"
              type="text" 
              class="form-input"
              :class="{ 'border-danger-300': errors.name }"
              :disabled="isLoading"
              :placeholder="$t('profile.form.fullNamePlaceholder')"
            />
            <p v-if="errors.name" class="form-error">{{ errors.name }}</p>
          </div>

          <div>
            <label class="form-label">{{ $t('profile.form.email') }}</label>
            <input
              type="email"
              :value="authStore.userEmail"
              disabled
              class="form-input bg-gray-50"
            />
            <p class="text-xs text-gray-500 mt-1">{{ $t('profile.form.emailNotChangeable') }}</p>
          </div>

          <div>
            <label class="form-label">{{ $t('profile.form.phone') }}</label>
            <input
              v-model="form.phone"
              type="tel"
              class="form-input"
              :class="{ 'border-danger-300': errors.phone }"
              :disabled="isLoading"
              :placeholder="$t('profile.form.phonePlaceholder')"
            />
            <p v-if="errors.phone" class="form-error">{{ errors.phone }}</p>
          </div>

          <div>
            <label class="form-label">{{ $t('profile.form.role') }}</label>
            <input
              type="text"
              :value="authStore.userRole"
              disabled
              class="form-input bg-gray-50 capitalize"
            />
            <p class="text-xs text-gray-500 mt-1">{{ $t('profile.form.roleNotChangeable') }}</p>
          </div>

          <!-- Error general -->
          <div v-if="generalError" class="rounded-lg bg-danger-50 border border-danger-200 p-4">
            <div class="flex items-center">
              <ExclamationTriangleIcon class="w-5 h-5 text-danger-600 mr-2" />
              <p class="text-sm text-danger-700">{{ generalError }}</p>
            </div>
          </div>

          <!-- Botones -->
          <div class="mt-6 pt-6 border-t border-gray-200 flex space-x-3">
            <button 
              type="submit"
              class="btn-primary"
              :disabled="isLoading || !hasChanges"
            >
              <div v-if="isLoading" class="flex items-center">
                <div class="spinner w-4 h-4 mr-2"></div>
                {{ $t('profile.form.updating') }}
              </div>
              <span v-else>{{ $t('profile.form.updateProfile') }}</span>
            </button>
            <button 
              type="button"
              @click="resetForm"
              class="btn-secondary"
              :disabled="isLoading"
            >
              {{ $t('profile.form.reset') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </MainLayout>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { UserIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline'
import { useAuthStore } from '@/stores/auth'
import { authAPI, apiHelpers } from '@/services/api'
import MainLayout from '@/components/layout/MainLayout.vue'

export default {
  name: 'Profile',
  components: {
    MainLayout,
    UserIcon,
    ExclamationTriangleIcon,
  },
  setup() {
    const authStore = useAuthStore()
    
    // Form data
    const form = ref({
      name: '',
      phone: ''
    })

    // UI state
    const isLoading = ref(false)
    const errors = ref({})
    const generalError = ref('')

    // Original values for comparison
    const originalValues = ref({
      name: '',
      phone: ''
    })

    // Computed
    const hasChanges = computed(() => {
      return form.value.name !== originalValues.value.name || 
             form.value.phone !== originalValues.value.phone
    })

    /**
     * Initialize form with current user data
     */
    const initializeForm = () => {
      form.value.name = authStore.userName || ''
      form.value.phone = authStore.user?.phone || ''
      
      // Store original values
      originalValues.value.name = form.value.name
      originalValues.value.phone = form.value.phone
    }

    /**
     * Reset form to original values
     */
    const resetForm = () => {
      form.value.name = originalValues.value.name
      form.value.phone = originalValues.value.phone
      errors.value = {}
      generalError.value = ''
    }

    /**
     * Update profile
     */
    const updateProfile = async () => {
      errors.value = {}
      generalError.value = ''

      // Basic validation
      if (!form.value.name?.trim()) {
        errors.value.name = 'Name is required'
        return
      }

      if (form.value.phone && !/^[\+]?[\d\s\-\(\)]{10,}$/.test(form.value.phone)) {
        errors.value.phone = 'Please enter a valid phone number'
        return
      }

      isLoading.value = true

      try {
        const response = await authAPI.updateProfile({
          name: form.value.name.trim(),
          phone: form.value.phone?.trim() || null
        })

        if (apiHelpers.isSuccess(response)) {
          // Update auth store with new data
          await authStore.fetchProfile()
          
          // Update original values
          originalValues.value.name = form.value.name
          originalValues.value.phone = form.value.phone
          
          window.$notify?.success('Profile updated successfully!')
        } else {
          generalError.value = response.data?.message || 'Failed to update profile'
        }
      } catch (error) {
        console.error('Update profile failed:', error)
        generalError.value = apiHelpers.handleError(error)
      } finally {
        isLoading.value = false
      }
    }

    // Initialize on mount
    onMounted(() => {
      initializeForm()
    })

    return { 
      authStore,
      form,
      isLoading,
      errors,
      generalError,
      hasChanges,
      updateProfile,
      resetForm
    }
  },
}
</script>