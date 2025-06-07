<template>
  <MainLayout>
    <div class="max-w-4xl mx-auto">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center gap-4 mb-4">
          <button 
            @click="$router.back()"
            class="btn-ghost p-2"
            :title="$t('common.back')"
          >
            <ArrowLeftIcon class="h-5 w-5" />
          </button>
          <div>
            <h1 class="text-3xl font-bold text-gray-900">
              {{ $t('tournaments.edit.title') }}
            </h1>
            <p class="text-gray-600 mt-1">
              {{ $t('tournaments.edit.subtitle') }}
            </p>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <span class="ml-3 text-gray-600">{{ $t('common.loading') }}</span>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
        <div class="flex items-center">
          <ExclamationTriangleIcon class="h-5 w-5 text-red-500 mr-3" />
          <p class="text-red-700">{{ error }}</p>
        </div>
        <button 
          @click="fetchTournament"
          class="mt-4 btn-secondary"
        >
          {{ $t('common.retry') }}
        </button>
      </div>

      <!-- Edit Form -->
      <div v-else-if="tournament" class="bg-white rounded-lg shadow-md p-6">
        <!-- General Error -->
        <div v-if="generalError" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
          <div class="flex items-center">
            <ExclamationTriangleIcon class="h-5 w-5 text-red-500 mr-3" />
            <p class="text-red-700">{{ generalError }}</p>
          </div>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-6">
          <!-- Tournament Name -->
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
              {{ $t('tournaments.form.name') }} *
            </label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              :class="{ 'border-red-500': errors.name }"
              :placeholder="$t('tournaments.form.namePlaceholder')"
            />
            <p v-if="errors.name" class="mt-1 text-sm text-red-600">
              {{ errors.name }}
            </p>
          </div>

          <!-- Location -->
          <div>
            <label for="location" class="block text-sm font-medium text-gray-700 mb-2">
              {{ $t('tournaments.form.location') }} *
            </label>
            <div class="relative">
              <MapPinIcon class="absolute left-3 top-3 h-5 w-5 text-gray-400" />
              <input
                id="location"
                v-model="form.location"
                type="text"
                required
                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                :class="{ 'border-red-500': errors.location }"
                :placeholder="$t('tournaments.form.locationPlaceholder')"
              />
            </div>
            <p v-if="errors.location" class="mt-1 text-sm text-red-600">
              {{ errors.location }}
            </p>
          </div>

          <!-- Prize Pool -->
          <div>
            <label for="prize_pool" class="block text-sm font-medium text-gray-700 mb-2">
              {{ $t('tournaments.form.prizePool') }}
            </label>
            <div class="relative">
              <span class="absolute left-3 top-3 text-gray-500">$</span>
              <input
                id="prize_pool"
                v-model.number="form.prize_pool"
                type="number"
                min="0"
                step="100"
                class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                :class="{ 'border-red-500': errors.prize_pool }"
                :placeholder="$t('tournaments.form.prizePoolPlaceholder')"
              />
            </div>
            <p v-if="errors.prize_pool" class="mt-1 text-sm text-red-600">
              {{ errors.prize_pool }}
            </p>
          </div>

          <!-- Description -->
          <div>
            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
              {{ $t('tournaments.form.description') }}
            </label>
            <textarea
              id="description"
              v-model="form.description"
              rows="4"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              :class="{ 'border-red-500': errors.description }"
              :placeholder="$t('tournaments.form.descriptionPlaceholder')"
            ></textarea>
            <p v-if="errors.description" class="mt-1 text-sm text-red-600">
              {{ errors.description }}
            </p>
          </div>

          <!-- Form Actions -->
          <div class="flex flex-col sm:flex-row gap-4 pt-6">
            <button
              type="submit"
              :disabled="isSubmitting || !isFormValid"
              class="btn-primary flex-1 sm:flex-none"
              :class="{ 'opacity-50 cursor-not-allowed': isSubmitting || !isFormValid }"
            >
              <span v-if="isSubmitting" class="flex items-center">
                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                {{ $t('tournaments.form.updating') }}
              </span>
              <span v-else>{{ $t('tournaments.form.updateTournament') }}</span>
            </button>
            
            <button
              type="button"
              @click="$router.back()"
              class="btn-secondary flex-1 sm:flex-none"
              :disabled="isSubmitting"
            >
              {{ $t('common.cancel') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </MainLayout>
</template>

<script>
/**
 * Edit Tournament Component
 * Allows editing tournament information with API integration
 */

import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {
  ArrowLeftIcon,
  ExclamationTriangleIcon,
  MapPinIcon
} from '@heroicons/vue/24/outline'
import { useAuthStore } from '@/stores/auth'
import { tournamentAPI, apiHelpers } from '@/services/api'
import MainLayout from '@/components/layout/MainLayout.vue'

export default {
  name: 'EditTournament',
  components: {
    MainLayout,
    ArrowLeftIcon,
    ExclamationTriangleIcon,
    MapPinIcon
  },
  setup() {
    const route = useRoute()
    const router = useRouter()
    const authStore = useAuthStore()

    // Data
    const tournament = ref(null)
    const isLoading = ref(false)
    const isSubmitting = ref(false)
    const error = ref('')
    const generalError = ref('')
    const errors = ref({})

    // Form data
    const form = ref({
      name: '',
      location: '',
      prize_pool: 0,
      description: ''
    })

    // Computed
    const isFormValid = computed(() => {
      return form.value.name.trim() !== '' && 
             form.value.location.trim() !== '' &&
             Object.keys(errors.value).length === 0
    })

    /**
     * Validate form fields
     */
    const validateForm = () => {
      errors.value = {}
      
      if (!form.value.name.trim()) {
        errors.value.name = 'Tournament name is required'
      } else if (form.value.name.length < 3) {
        errors.value.name = 'Tournament name must be at least 3 characters'
      } else if (form.value.name.length > 100) {
        errors.value.name = 'Tournament name must be less than 100 characters'
      }

      if (!form.value.location.trim()) {
        errors.value.location = 'Location is required'
      } else if (form.value.location.length < 3) {
        errors.value.location = 'Location must be at least 3 characters'
      } else if (form.value.location.length > 100) {
        errors.value.location = 'Location must be less than 100 characters'
      }

      if (form.value.prize_pool < 0) {
        errors.value.prize_pool = 'Prize pool cannot be negative'
      }

      if (form.value.description && form.value.description.length > 1000) {
        errors.value.description = 'Description must be less than 1000 characters'
      }

      return Object.keys(errors.value).length === 0
    }

    /**
     * Fetch tournament data
     */
    const fetchTournament = async () => {
      isLoading.value = true
      error.value = ''

      try {
        const response = await tournamentAPI.getById(route.params.id)

        if (apiHelpers.isSuccess(response)) {
          const data = apiHelpers.getData(response)
          tournament.value = data
          
          // Populate form with existing data
          form.value = {
            name: data.name || '',
            location: data.location || '',
            prize_pool: data.prize_pool || 0,
            description: data.description || ''
          }
        } else {
          error.value = 'Tournament not found'
        }
      } catch (err) {
        console.error('Failed to fetch tournament:', err)
        error.value = apiHelpers.handleError(err)
      } finally {
        isLoading.value = false
      }
    }

    /**
     * Handle form submission
     */
    const handleSubmit = async () => {
      errors.value = {}
      generalError.value = ''

      if (!validateForm()) {
        return
      }

      isSubmitting.value = true

      try {
        const updateData = {
          name: form.value.name.trim(),
          location: form.value.location.trim(),
          prize_pool: form.value.prize_pool || 0
        }

        // Only include description if it's not empty
        if (form.value.description && form.value.description.trim()) {
          updateData.description = form.value.description.trim()
        }

        const response = await tournamentAPI.update(route.params.id, updateData)

        if (apiHelpers.isSuccess(response)) {
          window.$notify?.success('Tournament updated successfully!')
          
          // Navigate back to tournament detail or list
          router.push(`/tournaments/${route.params.id}`)
        } else {
          // Handle validation errors from API
          const errorData = apiHelpers.getErrorData(response)
          if (errorData && typeof errorData === 'object') {
            errors.value = errorData
          } else {
            generalError.value = apiHelpers.handleError(response)
          }
        }
      } catch (err) {
        console.error('Failed to update tournament:', err)
        generalError.value = apiHelpers.handleError(err)
      } finally {
        isSubmitting.value = false
      }
    }

    // Lifecycle
    onMounted(() => {
      fetchTournament()
    })

    return {
      // Data
      tournament,
      isLoading,
      isSubmitting,
      error,
      generalError,
      errors,
      form,
      
      // Computed
      isFormValid,
      
      // Methods
      fetchTournament,
      handleSubmit
    }
  }
}
</script>

<style scoped>
.btn-primary {
  @apply bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center;
}

.btn-secondary {
  @apply bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 px-4 rounded-lg transition-colors duration-200;
}

.btn-ghost {
  @apply hover:bg-gray-100 text-gray-600 rounded-lg transition-colors duration-200;
}
</style>