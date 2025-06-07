<template>
  <MainLayout>
    <template #header>
      <div class="flex items-center space-x-4">
        <button @click="$router.go(-1)" class="btn-secondary p-2">
          <ArrowLeftIcon class="w-5 h-5" />
        </button>
        <div>
          <h1 class="text-3xl font-bold text-gray-900">{{ $t('teams.create.title') }}</h1>
          <p class="text-gray-600 mt-1">{{ $t('teams.create.subtitle') }}</p>
        </div>
      </div>
    </template>

    <div class="max-w-4xl mx-auto">
      <form @submit.prevent="handleSubmit" class="space-y-8">
        <!-- Basic Information -->
        <div class="card p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-6">{{ $t('teams.create.basicInformation') }}</h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Team Name -->
            <div class="md:col-span-2">
              <label for="name" class="form-label">{{ $t('teams.create.teamName') }}</label>
              <input
                id="name"
                v-model="form.name"
                type="text"
                required
                :disabled="isLoading"
                class="form-input"
                :class="{ 'border-danger-300': errors.name }"
                :placeholder="$t('teams.create.teamNamePlaceholder')"
              />
              <p v-if="errors.name" class="form-error">{{ errors.name }}</p>
            </div>

            <!-- Short Name -->
            <div>
              <label for="short_name" class="form-label">{{ $t('teams.create.shortName') }}</label>
              <input
                id="short_name"
                v-model="form.short_name"
                type="text"
                maxlength="10"
                :disabled="isLoading"
                class="form-input"
                :class="{ 'border-danger-300': errors.short_name }"
                :placeholder="$t('teams.create.shortNamePlaceholder')"
              />
              <p v-if="errors.short_name" class="form-error">{{ errors.short_name }}</p>
              <p class="text-xs text-gray-500 mt-1">{{ $t('teams.create.shortNameHint') }}</p>
            </div>

            <!-- Founded Year -->
            <div>
              <label for="founded_year" class="form-label">{{ $t('teams.create.foundedYear') }}</label>
              <input
                id="founded_year"
                v-model="form.founded_year"
                type="number"
                :min="1800"
                :max="currentYear"
                :disabled="isLoading"
                class="form-input"
                :class="{ 'border-danger-300': errors.founded_year }"
                :placeholder="$t('teams.create.foundedYearPlaceholder')"
              />
              <p v-if="errors.founded_year" class="form-error">{{ errors.founded_year }}</p>
            </div>

            <!-- Description -->
            <div class="md:col-span-2">
              <label for="description" class="form-label">{{ $t('teams.create.description') }}</label>
              <textarea
                id="description"
                v-model="form.description"
                rows="4"
                :disabled="isLoading"
                class="form-input"
                :class="{ 'border-danger-300': errors.description }"
                :placeholder="$t('teams.create.descriptionPlaceholder')"
              ></textarea>
              <p v-if="errors.description" class="form-error">{{ errors.description }}</p>
            </div>
          </div>
        </div>

        <!-- Contact Information -->
        <div class="card p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-6">{{ $t('teams.create.contactInformation') }}</h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Contact Email -->
            <div>
              <label for="contact_email" class="form-label">{{ $t('teams.create.contactEmail') }}</label>
              <input
                id="contact_email"
                v-model="form.contact_email"
                type="email"
                required
                :disabled="isLoading"
                class="form-input"
                :class="{ 'border-danger-300': errors.contact_email }"
                :placeholder="$t('teams.create.contactEmailPlaceholder')"
              />
              <p v-if="errors.contact_email" class="form-error">{{ errors.contact_email }}</p>
            </div>

            <!-- Contact Phone -->
            <div>
              <label for="contact_phone" class="form-label">{{ $t('teams.create.contactPhone') }}</label>
              <input
                id="contact_phone"
                v-model="form.contact_phone"
                type="tel"
                :disabled="isLoading"
                class="form-input"
                :class="{ 'border-danger-300': errors.contact_phone }"
                :placeholder="$t('teams.create.contactPhonePlaceholder')"
              />
              <p v-if="errors.contact_phone" class="form-error">{{ errors.contact_phone }}</p>
            </div>

            <!-- Home Venue -->
            <div class="md:col-span-2">
              <label for="home_venue" class="form-label">{{ $t('teams.create.homeVenue') }}</label>
              <input
                id="home_venue"
                v-model="form.home_venue"
                type="text"
                :disabled="isLoading"
                class="form-input"
                :class="{ 'border-danger-300': errors.home_venue }"
                :placeholder="$t('teams.create.homeVenuePlaceholder')"
              />
              <p v-if="errors.home_venue" class="form-error">{{ errors.home_venue }}</p>
            </div>
          </div>
        </div>

        <!-- Team Settings -->
        <div class="card p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-6">{{ $t('teams.create.teamSettings') }}</h2>

          <div class="space-y-6">
            <!-- Active Status -->
            <div class="flex items-start space-x-3">
              <div class="flex items-center h-5">
                <input
                  id="is_active"
                  v-model="form.is_active"
                  type="checkbox"
                  :disabled="isLoading"
                  class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                />
              </div>
              <div class="text-sm">
                <label for="is_active" class="font-medium text-gray-700">{{ $t('teams.create.activeTeam') }}</label>
                <p class="text-gray-500">
                  {{ $t('teams.create.activeTeamDescription') }}
                </p>
              </div>
            </div>

            <!-- Team Logo Upload -->
            <div>
              <label class="form-label">{{ $t('teams.create.teamLogo') }}</label>
              <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md bg-gray-50">
                <div class="space-y-1 text-center">
                  <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                  <div class="text-sm text-gray-600">
                    <span class="font-medium text-gray-400">{{ $t('teams.create.logoUploadFeature') }}</span>
                    <p class="text-gray-400">{{ $t('teams.create.willBeAvailableSoon') }}</p>
                  </div>
                  <p class="text-xs text-gray-400">{{ $t('teams.create.comingInFutureUpdate') }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Preview -->
        <div v-if="form.name" class="card p-6 bg-gray-50">
          <h2 class="text-xl font-semibold text-gray-900 mb-6">{{ $t('teams.create.preview') }}</h2>
          <div class="bg-white rounded-lg p-6 border-2 border-dashed border-gray-200">
            <!-- Team Header Preview -->
            <div
              class="relative h-32 bg-gradient-to-br from-primary-500 to-primary-700 rounded-lg mb-6"
            >
              <div class="absolute inset-0 bg-black opacity-20 rounded-lg"></div>
              <div class="absolute top-4 right-4">
                <span
                  class="px-2 py-1 rounded-full text-xs font-medium bg-success-100 text-success-800"
                >
                  {{ $t('teams.create.active') }}
                </span>
              </div>
              <div class="absolute bottom-4 left-4 right-4">
                <h3 class="text-white text-lg font-bold truncate">{{ form.name }}</h3>
                <p v-if="form.short_name" class="text-primary-100 text-sm">{{ form.short_name }}</p>
              </div>
            </div>

            <!-- Team Info Preview -->
            <div class="space-y-3">
              <p v-if="form.description" class="text-gray-700 text-sm">{{ form.description }}</p>

              <div class="grid grid-cols-2 gap-4 text-sm">
                <div v-if="form.founded_year">
                  <span class="text-gray-600">{{ $t('teams.create.founded') }}:</span>
                  <span class="font-medium ml-2">{{ form.founded_year }}</span>
                </div>
                <div>
                  <span class="text-gray-600">{{ $t('teams.create.manager') }}:</span>
                  <span class="font-medium ml-2">{{ authStore.userName }}</span>
                </div>
                <div v-if="form.home_venue">
                  <span class="text-gray-600">{{ $t('teams.create.homeVenue') }}:</span>
                  <span class="font-medium ml-2">{{ form.home_venue }}</span>
                </div>
                <div>
                  <span class="text-gray-600">{{ $t('teams.create.contact') }}:</span>
                  <span class="font-medium ml-2">{{ form.contact_email }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Error Display -->
        <div v-if="generalError" class="card p-6 bg-danger-50 border border-danger-200">
          <div class="flex items-center">
            <ExclamationTriangleIcon class="w-5 h-5 text-danger-600 mr-2" />
            <p class="text-sm text-danger-700">{{ generalError }}</p>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end space-x-4">
          <button type="button" @click="$router.go(-1)" :disabled="isLoading" class="btn-secondary">
            {{ $t('teams.create.cancel') }}
          </button>
          <button type="submit" :disabled="isLoading || !isFormValid" class="btn-primary">
            <div v-if="isLoading" class="flex items-center">
              <div class="spinner w-4 h-4 mr-2"></div>
              {{ $t('teams.create.creatingTeam') }}
            </div>
            <span v-else>{{ $t('teams.create.createTeam') }}</span>
          </button>
        </div>
      </form>
    </div>
  </MainLayout>
</template>

<script>
/**
 * Create Team Page Component
 * Form for creating a new team with validation and preview
 */

import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { ArrowLeftIcon, ExclamationTriangleIcon, PhotoIcon } from '@heroicons/vue/24/outline'
import { useAuthStore } from '@/stores/auth'
import { teamAPI, apiHelpers } from '@/services/api'
import MainLayout from '@/components/layout/MainLayout.vue'
import { useI18n } from 'vue-i18n'

export default {
  name: 'CreateTeam',
  components: {
    MainLayout,
    ArrowLeftIcon,
    ExclamationTriangleIcon,
    PhotoIcon,
  },
  setup() {
    const router = useRouter()
    const authStore = useAuthStore()
    const { t } = useI18n()

    // Form data
    const form = ref({
      name: '',
      short_name: '',
      description: '',
      contact_email: authStore.userEmail || '',
      contact_phone: '',
      founded_year: '',
      home_venue: '',
      is_active: true,
    })

    // UI state
    const isLoading = ref(false)
    const errors = ref({})
    const generalError = ref('')

    // Computed
    const currentYear = computed(() => new Date().getFullYear())

    const isFormValid = computed(() => {
      return form.value.name && form.value.contact_email && Object.keys(errors.value).length === 0
    })

    /**
     * Handle form submission
     */
    const handleSubmit = async () => {
      errors.value = {}
      generalError.value = ''

      if (!validateForm()) {
        return
      }

      isLoading.value = true

      try {
        const teamData = {
          ...form.value,
          founded_year: form.value.founded_year ? parseInt(form.value.founded_year) : null,
        }

        // Remove empty fields
        Object.keys(teamData).forEach((key) => {
          if (teamData[key] === '' || teamData[key] === null) {
            delete teamData[key]
          }
        })

        const response = await teamAPI.create(teamData)

        if (apiHelpers.isSuccess(response)) {
          const team = apiHelpers.getData(response)
          window.$notify?.success(t('teams.create.success'))
          router.push(`/teams/${team.id}`)
        } else {
          generalError.value = response.data?.message || t('teams.create.error')
        }
      } catch (error) {
        console.error('Team creation failed:', error)
        generalError.value = apiHelpers.handleError(error)
      } finally {
        isLoading.value = false
      }
    }

    /**
     * Validate form data
     */
    const validateForm = () => {
      const newErrors = {}

      // Basic validation
      if (!form.value.name?.trim()) {
        newErrors.name = t('teams.create.validation.nameRequired')
      } else if (form.value.name.length < 2) {
        newErrors.name = t('teams.create.validation.nameMinLength')
      } else if (form.value.name.length > 255) {
        newErrors.name = t('teams.create.validation.nameMaxLength')
      }

      if (form.value.short_name && form.value.short_name.length > 10) {
        newErrors.short_name = t('teams.create.validation.shortNameMaxLength')
      }

      if (!form.value.contact_email?.trim()) {
        newErrors.contact_email = t('teams.create.validation.emailRequired')
      } else {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
        if (!emailRegex.test(form.value.contact_email)) {
          newErrors.contact_email = t('teams.create.validation.emailInvalid')
        }
      }

      if (form.value.contact_phone && !/^[\+]?[\d\s\-\(\)]{10,}$/.test(form.value.contact_phone)) {
        newErrors.contact_phone = t('teams.create.validation.phoneInvalid')
      }

      if (form.value.founded_year) {
        const year = parseInt(form.value.founded_year)
        if (year < 1800 || year > currentYear.value) {
          newErrors.founded_year = t('teams.create.validation.yearRange', { currentYear: currentYear.value })
        }
      }

      if (form.value.description && form.value.description.length > 1000) {
        newErrors.description = t('teams.create.validation.descriptionMaxLength')
      }

      if (form.value.home_venue && form.value.home_venue.length > 255) {
        newErrors.home_venue = t('teams.create.validation.venueMaxLength')
      }

      errors.value = newErrors
      return Object.keys(newErrors).length === 0
    }

    return {
      authStore,
      form,
      isLoading,
      errors,
      generalError,
      currentYear,
      isFormValid,
      handleSubmit,
    }
  },
}
</script>
