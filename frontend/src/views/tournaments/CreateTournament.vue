<template>
  <MainLayout>
    <template #header>
      <div class="flex items-center space-x-4">
        <button @click="$router.go(-1)" class="btn-secondary p-2">
          <ArrowLeftIcon class="w-5 h-5" />
        </button>
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Crear Torneo</h1>
          <p class="text-gray-600 mt-1">Configura un nuevo torneo deportivo</p>
        </div>
      </div>
    </template>

    <div class="max-w-4xl mx-auto">
      <form @submit.prevent="handleSubmit" class="space-y-8">
        <!-- Basic Information -->
        <div class="card p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-6">Información Básica</h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Tournament Name -->
            <div class="md:col-span-2">
              <label for="name" class="form-label">Nombre del Torneo</label>º
              <input
                id="name"
                v-model="form.name"
                type="text"
                required
                :disabled="isLoading"
                class="form-input"
                :class="{ 'border-danger-300': errors.name }"
                placeholder="Ej: Copa de Fútbol 2024"
              />
              <p v-if="errors.name" class="form-error">{{ errors.name }}</p>
            </div>

            <!-- Sport Type -->
            <div>
              <label for="sport_type" class="form-label">Tipo de Deporte</label>
              <select
                id="sport_type"
                v-model="form.sport_type"
                required
                :disabled="isLoading"
                class="form-input"
                :class="{ 'border-danger-300': errors.sport_type }"
              >
                <option value="">Seleccionar tipo de deporte</option>
                <option value="Football">Fútbol</option>
                <option value="Basketball">Baloncesto</option>
                <option value="Tennis">Tenis</option>
                <option value="Volleyball">Voleibol</option>
                <option value="Other">Otro</option>
              </select>
              <p v-if="errors.sport_type" class="form-error">{{ errors.sport_type }}</p>
            </div>

            <!-- Tournament Type -->
            <div>
              <label for="tournament_type" class="form-label">Formato del Torneo</label>
              <select
                id="tournament_type"
                v-model="form.tournament_type"
                required
                :disabled="isLoading"
                class="form-input"
                :class="{ 'border-danger-300': errors.tournament_type }"
              >
                <option value="">Seleccionar formato</option>
                <option value="league">Liga (todos contra todos)</option>
                <option value="knockout">Eliminación directa</option>
                <option value="group_knockout">Grupos + Eliminación directa</option>
              </select>
              <p v-if="errors.tournament_type" class="form-error">{{ errors.tournament_type }}</p>
            </div>

            <!-- Max Teams -->
            <div>
              <label for="max_teams" class="form-label">Máximo de Equipos</label>
              <input
                id="max_teams"
                v-model="form.max_teams"
                type="number"
                min="2"
                max="64"
                required
                :disabled="isLoading"
                class="form-input"
                :class="{ 'border-danger-300': errors.max_teams }"
                placeholder="16"
              />
              <p v-if="errors.max_teams" class="form-error">{{ errors.max_teams }}</p>
            </div>

            <!-- Location -->
            <div>
              <label for="location" class="form-label">Ubicación</label>
              <input
                id="location"
                v-model="form.location"
                type="text"
                :disabled="isLoading"
                class="form-input"
                :class="{ 'border-danger-300': errors.location }"
                placeholder="Ej: Madrid, España"
              />
              <p v-if="errors.location" class="form-error">{{ errors.location }}</p>
            </div>

            <!-- Description -->
            <div class="md:col-span-2">
              <label for="description" class="form-label">Descripción</label>
              <textarea
                id="description"
                v-model="form.description"
                rows="4"
                :disabled="isLoading"
                class="form-input"
                :class="{ 'border-danger-300': errors.description }"
                placeholder="Describe el torneo, sus objetivos, reglas especiales, etc."
              ></textarea>
              <p v-if="errors.description" class="form-error">{{ errors.description }}</p>
            </div>
          </div>
        </div>

        <!-- Schedule -->
        <div class="card p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-6">Cronograma</h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Registration Start -->
            <div>
              <label for="registration_start" class="form-label">Inicio de Inscripciones</label>
              <input
                id="registration_start"
                v-model="form.registration_start"
                type="datetime-local"
                required
                :disabled="isLoading"
                class="form-input"
                :class="{ 'border-danger-300': errors.registration_start }"
                :min="minDateTime"
              />
              <p v-if="errors.registration_start" class="form-error">
                {{ errors.registration_start }}
              </p>
            </div>

            <!-- Registration End -->
            <div>
              <label for="registration_end" class="form-label">Fin de Inscripciones</label>
              <input
                id="registration_end"
                v-model="form.registration_end"
                type="datetime-local"
                required
                :disabled="isLoading"
                class="form-input"
                :class="{ 'border-danger-300': errors.registration_end }"
                :min="form.registration_start || minDateTime"
              />
              <p v-if="errors.registration_end" class="form-error">{{ errors.registration_end }}</p>
            </div>

            <!-- Tournament Start -->
            <div>
              <label for="start_date" class="form-label">Inicio del Torneo</label>
              <input
                id="start_date"
                v-model="form.start_date"
                type="datetime-local"
                required
                :disabled="isLoading"
                class="form-input"
                :class="{ 'border-danger-300': errors.start_date }"
                :min="form.registration_end || minDateTime"
              />
              <p v-if="errors.start_date" class="form-error">{{ errors.start_date }}</p>
            </div>

            <!-- Tournament End -->
            <div>
              <label for="end_date" class="form-label">Fin del Torneo</label>
              <input
                id="end_date"
                v-model="form.end_date"
                type="datetime-local"
                :disabled="isLoading"
                class="form-input"
                :class="{ 'border-danger-300': errors.end_date }"
                :min="form.start_date || minDateTime"
              />
              <p v-if="errors.end_date" class="form-error">{{ errors.end_date }}</p>
            </div>
          </div>
        </div>

        <!-- Additional Settings -->
        <div class="card p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-6">Configuración Adicional</h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Prize Pool -->
            <div>
              <label for="prize_pool" class="form-label">Premio en Metálico</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <span class="text-gray-500 text-sm">€</span>
                </div>
                <input
                  id="prize_pool"
                  v-model="form.prize_pool"
                  type="number"
                  min="0"
                  step="0.01"
                  :disabled="isLoading"
                  class="form-input pl-7"
                  :class="{ 'border-danger-300': errors.prize_pool }"
                  placeholder="0.00"
                />
              </div>
              <p v-if="errors.prize_pool" class="form-error">{{ errors.prize_pool }}</p>
            </div>

            <!-- Status -->
            <div>
              <label for="status" class="form-label">Estado Inicial</label>
              <select
                id="status"
                v-model="form.status"
                :disabled="isLoading"
                class="form-input"
                :class="{ 'border-danger-300': errors.status }"
              >
                <option value="draft">Borrador</option>
                <option value="registration_open">Inscripciones Abiertas</option>
              </select>
              <p v-if="errors.status" class="form-error">{{ errors.status }}</p>
              <p class="text-xs text-gray-500 mt-1">
                Puedes cambiar el estado después de crear el torneo
              </p>
            </div>
          </div>

          <!-- Rules -->
          <div class="mt-6">
            <label for="rules" class="form-label">Reglas y Regulaciones</label>
            <textarea
              id="rules"
              v-model="form.rules"
              rows="6"
              :disabled="isLoading"
              class="form-input"
              :class="{ 'border-danger-300': errors.rules }"
              placeholder="Define las reglas específicas del torneo, requisitos de participación, formato de partidos, etc."
            ></textarea>
            <p v-if="errors.rules" class="form-error">{{ errors.rules }}</p>
            <p class="text-xs text-gray-500 mt-1">
              Las reglas serán visibles para todos los participantes
            </p>
          </div>
        </div>

        <!-- Preview -->
        <div v-if="form.name" class="card p-6 bg-gray-50">
          <h2 class="text-xl font-semibold text-gray-900 mb-6">Vista Previa</h2>
          <div class="bg-white rounded-lg p-6 border-2 border-dashed border-gray-200">
            <div class="flex justify-between items-start mb-4">
              <div>
                <h3 class="text-lg font-semibold text-gray-900">{{ form.name }}</h3>
                <div class="flex items-center space-x-4 mt-2">
                  <span
                    class="px-3 py-1 bg-primary-100 text-primary-800 rounded-full text-sm font-medium"
                  >
                    {{ formatStatus(form.status) }}
                  </span>
                  <span class="text-gray-600">{{ form.sport_type }}</span>
                </div>
              </div>
              <div class="text-right text-sm text-gray-600">
                <div>{{ formatTournamentType(form.tournament_type) }}</div>
                <div>Máx. {{ form.max_teams }} equipos</div>
              </div>
            </div>

            <p v-if="form.description" class="text-gray-700 mb-4">{{ form.description }}</p>

            <div class="grid grid-cols-2 gap-4 text-sm">
              <div>
                <span class="text-gray-600">Inscripciones</span>
                <div class="font-medium">
                  {{ formatPreviewDate(form.registration_start) }} -
                  {{ formatPreviewDate(form.registration_end) }}
                </div>
              </div>
              <div>
                <span class="text-gray-600">Torneo</span>
                <div class="font-medium">
                  {{ formatPreviewDate(form.start_date)
                  }}{{ form.end_date ? ' - ' + formatPreviewDate(form.end_date) : '' }}
                </div>
              </div>
            </div>

            <div v-if="form.location || form.prize_pool" class="mt-4 pt-4 border-t border-gray-200">
              <div class="flex justify-between text-sm">
                <span v-if="form.location" class="text-gray-600"> 📍 {{ form.location }} </span>
                <span v-if="form.prize_pool" class="text-gray-600">
                  💰 ${{ formatMoney(form.prize_pool) }}
                </span>
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
            Cancelar
          </button>
          <button type="submit" :disabled="isLoading || !isFormValid" class="btn-primary">
            <div v-if="isLoading" class="flex items-center">
              <div class="spinner w-4 h-4 mr-2"></div>
              Creando torneo...
            </div>
            <span v-else>Crear Torneo</span>
          </button>
        </div>
      </form>
    </div>
  </MainLayout>
</template>

<script>
/**
 * Create Tournament Page Component
 * Form for creating a new tournament with validation and preview
 */

import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { ArrowLeftIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline'
import { useAuthStore } from '@/stores/auth'
import { tournamentAPI, apiHelpers } from '@/services/api'
import MainLayout from '@/components/layout/MainLayout.vue'

export default {
  name: 'CreateTournament',
  components: {
    MainLayout,
    ArrowLeftIcon,
    ExclamationTriangleIcon,
  },
  setup() {
    // Initialize router and authentication store
    const router = useRouter()
    const authStore = useAuthStore()

    // Form data - reactive object containing all tournament form fields
    const form = ref({
      name: '',                  
      description: '',           
      sport_type: '',            
      tournament_type: '',       
      max_teams: 16,             
      registration_start: '',    
      registration_end: '',      
      start_date: '',            
      end_date: '',              
      location: '',              
      prize_pool: '',            
      rules: '',                 
      status: 'draft',           
    })

    // UI state management - reactive variables for component state
    const isLoading = ref(false)    // Loading state for API requests
    const errors = ref({})          // Field-specific validation errors
    const generalError = ref('')    // General error messages for display

    // Computed properties for reactive form validation and constraints

    /**
     * Minimum allowed date/time for form inputs
     * 
     * Calculates the earliest allowed date/time (1 hour from now)
     * to prevent scheduling tournaments in the past.
     */
    const minDateTime = computed(() => {
      const now = new Date()
      now.setHours(now.getHours() + 1) // At least 1 hour from now
      return now.toISOString().slice(0, 16)
    })

    /**
     * Form validation status
     * 
     * Determines if the form has all required fields and passes validation.
     * Used to enable/disable the submit button.
     */
    const isFormValid = computed(() => {
      return (
        form.value.name &&
        form.value.sport_type &&
        form.value.tournament_type &&
        form.value.max_teams &&
        form.value.start_date &&
        Object.keys(errors.value).length === 0
      )
    })

    /**
     * Handle form submission
     * 
     * Validates form data, processes the tournament creation request,
     * and handles success/error responses with appropriate user feedback.
     * 
     * @returns {Promise<void>} Async function that handles form submission
     */
    const handleSubmit = async () => {
      // Clear any previous errors
      errors.value = {}
      generalError.value = ''

      // Validate form before submission
      if (!validateForm()) {
        return
      }

      // Set loading state to show progress indicator
      isLoading.value = true

      try {
        // Prepare tournament data with proper data types
        const tournamentData = {
          ...form.value,
          max_teams: parseInt(form.value.max_teams), // Convert to integer
          prize_pool: form.value.prize_pool ? parseFloat(form.value.prize_pool) : null, // Convert to float or null
        }

        // Clean up data by removing empty fields to avoid backend issues
        Object.keys(tournamentData).forEach((key) => {
          if (tournamentData[key] === '' || tournamentData[key] === null) {
            delete tournamentData[key]
          }
        })

        // Send creation request to API
        const response = await tournamentAPI.create(tournamentData)

        // Handle successful response
        if (apiHelpers.isSuccess(response)) {
          const tournament = apiHelpers.getData(response)
          
          // Show success notification if available
          window.$notify?.success('Torneo creado exitosamente')
          
          // Navigate to the newly created tournament details page
          router.push(`/tournaments/${tournament.id}`)
        } else {
          // Handle API error response
          generalError.value = response.data?.message || 'Error al crear el torneo'
        }
      } catch (error) {
        // Handle network/unexpected errors
        console.error('Tournament creation failed:', error)
        generalError.value = apiHelpers.handleError(error)
      } finally {
        // Always reset loading state
        isLoading.value = false
      }
    }

    /**
     * Validate form data
     * 
     * Performs comprehensive validation of tournament form data including
     * required fields, data types, date logic, and business rules.
     * 
     * @returns {boolean} True if validation passes, false otherwise
     */
    const validateForm = () => {
      // Initialize errors object to collect validation issues
      const newErrors = {}

      // Basic required field validation
      // Check if tournament name is provided and not empty
      if (!form.value.name?.trim()) {
        newErrors.name = 'El nombre del torneo es requerido'
      }

      // Validate sport type selection
      if (!form.value.sport_type) {
        newErrors.sport_type = 'El tipo de deporte es requerido'
      }

      // Validate tournament format selection
      if (!form.value.tournament_type) {
        newErrors.tournament_type = 'El formato del torneo es requerido'
      }

      // Team count validation with business rules
      // Minimum 2 teams required for any tournament
      if (!form.value.max_teams || form.value.max_teams < 2) {
        newErrors.max_teams = 'Mínimo 2 equipos requeridos'
      } 
      // Maximum 64 teams to keep tournament manageable
      else if (form.value.max_teams > 64) {
        newErrors.max_teams = 'Máximo 64 equipos permitidos'
      }

      // Date validation logic - now optional but must be logical if provided
      // Validate registration period if both dates are provided
      if (form.value.registration_start && form.value.registration_end) {
        const regStart = new Date(form.value.registration_start)
        const regEnd = new Date(form.value.registration_end)

        // Registration end must be after registration start
        if (regEnd <= regStart) {
          newErrors.registration_end = 'El fin de inscripciones debe ser después del inicio'
        }
      }

      // Tournament start date validation
      if (form.value.start_date) {
        const tournStart = new Date(form.value.start_date)
        
        // If registration end is set, tournament must start after registration closes
        if (form.value.registration_end) {
          const regEnd = new Date(form.value.registration_end)
          if (tournStart <= regEnd) {
            newErrors.start_date = 'El inicio del torneo debe ser después del fin de inscripciones'
          }
        }
      }

      // Tournament end date validation
      // If both start and end dates are provided, validate their relationship
      if (form.value.end_date && form.value.start_date) {
        const tournEnd = new Date(form.value.end_date)
        const tournStart = new Date(form.value.start_date)
        
        // Tournament end must be after tournament start
        if (tournEnd <= tournStart) {
          newErrors.end_date = 'El fin del torneo debe ser después del inicio'
        }
      }

      // Prize pool validation
      // Ensure prize pool is not negative if provided
      if (form.value.prize_pool && form.value.prize_pool < 0) {
        newErrors.prize_pool = 'El premio no puede ser negativo'
      }

      // Update errors state and return validation result
      errors.value = newErrors
      
      // Return true if no validation errors found
      return Object.keys(newErrors).length === 0
    }

    /**
     * Format status for display
     * 
     * Converts internal status codes to user-friendly Spanish labels
     * for display in the preview section.
     * 
     * @param {string} status - Internal status code
     * @returns {string} User-friendly status label
     */
    const formatStatus = (status) => {
      const statusMap = {
        draft: 'Borrador',
        registration_open: 'Inscripciones Abiertas',
      }
      return statusMap[status] || status
    }

    /**
     * Format tournament type for display
     * 
     * Converts tournament type codes to user-friendly Spanish labels
     * for display in the preview section.
     * 
     * @param {string} type - Tournament type code
     * @returns {string} User-friendly tournament type label
     */
    const formatTournamentType = (type) => {
      const typeMap = {
        league: 'Liga',
        knockout: 'Eliminación directa',
        group_knockout: 'Grupos + Eliminación',
      }
      return typeMap[type] || type
    }

    /**
     * Format date for preview display
     * 
     * Converts ISO date string to a readable format for the preview section.
     * Returns 'TBD' if no date is provided.
     * 
     * @param {string} dateString - ISO date string
     * @returns {string} Formatted date string or 'TBD'
     */
    const formatPreviewDate = (dateString) => {
      if (!dateString) return 'TBD'
      return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
      })
    }

    /**
     * Format money amount for display
     * 
     * Formats numerical amount with proper thousands separators
     * for display in the preview section.
     * 
     * @param {number|string} amount - Money amount to format
     * @returns {string} Formatted money string with separators
     */
    const formatMoney = (amount) => {
      return Number(amount).toLocaleString()
    }

    // Set default dates on component mount
    onMounted(() => {
      const now = new Date()

      // Set registration start time to 2 hours from now
      // This gives admins time to review before opening registration
      const regStart = new Date(now.getTime() + 2 * 60 * 60 * 1000)
      form.value.registration_start = regStart.toISOString().slice(0, 16)

      // Set registration end time to 1 week from now
      // Provides reasonable time for teams to register
      const regEnd = new Date(now.getTime() + 7 * 24 * 60 * 60 * 1000)
      form.value.registration_end = regEnd.toISOString().slice(0, 16)

      // Set tournament start time to 2 days after registration ends
      // Allows time for bracket generation and preparation
      const tournStart = new Date(regEnd.getTime() + 2 * 24 * 60 * 60 * 1000)
      form.value.start_date = tournStart.toISOString().slice(0, 16)
    })

    return {
      authStore,
      form,
      isLoading,
      errors,
      generalError,
      minDateTime,
      isFormValid,
      handleSubmit,
      formatStatus,
      formatTournamentType,
      formatPreviewDate,
      formatMoney,
    }
  },
}
</script>
