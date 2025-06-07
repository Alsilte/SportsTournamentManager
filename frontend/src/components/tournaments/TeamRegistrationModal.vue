<template>
  <!-- Modal Overlay -->
  <Teleport to="body">
    <Transition name="modal-overlay">
      <div v-if="true" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
          <!-- Backdrop -->
          <div
            class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
            @click="$emit('close')"
          ></div>

          <!-- Modal Content -->
          <Transition name="modal-content">
            <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
              <!-- Header -->
              <div class="flex items-center justify-between mb-6">
                <div>
                  <h3 class="text-lg font-semibold text-gray-900">Registrar Equipo</h3>
                  <p class="text-sm text-gray-600">{{ tournament?.name }}</p>
                </div>
                <button
                  @click="$emit('close')"
                  class="p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors"
                >
                  <XMarkIcon class="w-5 h-5" />
                </button>
              </div>

              <!-- Tournament Info -->
              <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <div class="space-y-2 text-sm">
                  <div class="flex justify-between">
                    <span class="text-gray-600">Deporte:</span>
                    <span class="font-medium">{{ tournament?.sport_type }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600">Fecha de inicio:</span>
                    <span class="font-medium">{{ formatDate(tournament?.start_date) }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600">Equipos:</span>
                    <span class="font-medium"
                      >{{ tournament?.registered_teams_count || 0 }}/{{
                        tournament?.max_teams
                      }}</span
                    >
                  </div>
                  <div v-if="tournament?.location" class="flex justify-between">
                    <span class="text-gray-600">Ubicación:</span>
                    <span class="font-medium">{{ tournament.location }}</span>
                  </div>
                </div>
              </div>

              <!-- Loading State for Teams -->
              <div v-if="isLoadingTeams" class="space-y-4 mb-6">
                <label class="form-label">Seleccionar equipo</label>
                <div class="animate-pulse">
                  <div class="h-10 bg-gray-200 rounded"></div>
                </div>
                <p class="text-sm text-gray-500">Cargando equipos...</p>
              </div>

              <!-- Team Selection Form -->
              <form v-else @submit.prevent="handleSubmit" class="space-y-4">
                <!-- Team Selection -->
                <div>
                  <label for="team" class="form-label">Seleccionar equipo</label>
                  <select
                    id="team"
                    v-model="selectedTeamId"
                    required
                    :disabled="isLoading || availableTeams.length === 0"
                    class="form-input"
                  >
                    <option value="">
                      {{
                        availableTeams.length === 0
                          ? 'No hay equipos disponibles'
                          : 'Elegir equipo'
                      }}
                    </option>
                    <option v-for="team in availableTeams" :key="team.id" :value="team.id">
                      {{ team.name }} ({{ team.players_count || 0 }} jugadores)
                    </option>
                  </select>
                  <p v-if="error" class="form-error">{{ error }}</p>
                  <p v-if="availableTeams.length === 0" class="text-xs text-gray-500 mt-1">
                    Necesitas crear un equipo antes de poder registrarte en un torneo
                  </p>
                </div>

                <!-- Team Details (if team selected) -->
                <div v-if="selectedTeam" class="bg-primary-50 rounded-lg p-4">
                  <h4 class="font-medium text-primary-900 mb-2">{{ selectedTeam.name }}</h4>
                  <div class="space-y-1 text-sm text-primary-700">
                    <p>Jugadores: {{ selectedTeam.players_count || 0 }}</p>
                    <p v-if="selectedTeam.contact_email">
                      Contacto: {{ selectedTeam.contact_email }}
                    </p>
                    <p v-if="selectedTeam.home_venue">Sede local: {{ selectedTeam.home_venue }}</p>
                  </div>
                </div>

                <!-- Registration Requirements -->
                <div class="bg-warning-50 border border-warning-200 rounded-lg p-4">
                  <div class="flex">
                    <ExclamationTriangleIcon
                      class="w-5 h-5 text-warning-600 mr-2 flex-shrink-0 mt-0.5"
                    />
                    <div class="text-sm">
                      <h4 class="font-medium text-warning-800 mb-1">Requisitos de registro</h4>
                      <ul class="text-warning-700 space-y-1">
                        <li>• Mínimo 8 jugadores registrados</li>
                        <li>• Equipo verificado por administradores</li>
                        <li>• Registro sujeto a aprobación</li>
                        <li>
                          • Fecha límite de registro: {{ formatDate(tournament?.registration_end) }}
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>

                <!-- Error Display -->
                <div
                  v-if="generalError"
                  class="bg-danger-50 border border-danger-200 rounded-lg p-4"
                >
                  <div class="flex items-center">
                    <ExclamationTriangleIcon class="w-5 h-5 text-danger-600 mr-2" />
                    <p class="text-sm text-danger-700">{{ generalError }}</p>
                  </div>
                </div>

                <!-- Actions -->
                <div class="flex space-x-3 pt-4">
                  <button
                    type="button"
                    @click="$emit('close')"
                    :disabled="isLoading"
                    class="flex-1 btn-secondary"
                  >
                    Cancelar
                  </button>
                  <button
                    type="submit"
                    :disabled="!selectedTeamId || isLoading || availableTeams.length === 0"
                    class="flex-1 btn-primary"
                  >
                    <div v-if="isLoading" class="flex items-center justify-center">
                      <div class="spinner w-4 h-4 mr-2"></div>
                      Registrando...
                    </div>
                    <span v-else>Registrar equipo</span>
                  </button>
                </div>
              </form>
            </div>
          </Transition>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script>
/**
 * Team Registration Modal Component - API ONLY VERSION
 * Modal for registering teams to tournaments using REAL data only
 */

import { ref, computed, onMounted, watch } from 'vue'
import { XMarkIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline'
import { useAuthStore } from '@/stores/auth'
import { teamAPI, tournamentAPI, apiHelpers } from '@/services/api'

export default {
  name: 'TeamRegistrationModal',
  components: {
    XMarkIcon,
    ExclamationTriangleIcon,
  },
  props: {
    tournament: {
      type: Object,
      required: true,
    },
  },
  emits: ['close', 'success'],
  setup(props, { emit }) {
    const authStore = useAuthStore()

    // Data
    const availableTeams = ref([])
    const selectedTeamId = ref('')
    const isLoading = ref(false)
    const isLoadingTeams = ref(false)
    const error = ref('')
    const generalError = ref('')

    // Computed
    const selectedTeam = computed(() => {
      return availableTeams.value.find((team) => team.id == selectedTeamId.value)
    })

    /**
     * Handle form submission
     */
    const handleSubmit = async () => {
      error.value = ''
      generalError.value = ''

      if (!selectedTeamId.value) {
        error.value = 'Debe seleccionar un equipo'
        return
      }

      const team = selectedTeam.value
      if (!team) {
        error.value = 'Equipo no encontrado'
        return
      }

      // Validate team requirements
      if ((team.players_count || 0) < 5) {
        error.value = 'El equipo debe tener al menos 8 jugadores'
        return
      }

      isLoading.value = true

      try {
        const response = await tournamentAPI.registerTeam(props.tournament.id, {
          team_id: selectedTeamId.value,
        })

        if (apiHelpers.isSuccess(response)) {
          emit('success', {
            team: team,
            tournament: props.tournament,
            registration: apiHelpers.getData(response),
          })
        } else {
          generalError.value = response.data?.message || 'Error al registrar el equipo'
        }
      } catch (err) {
        console.error('Registration failed:', err)
        generalError.value = apiHelpers.handleError(err)
      } finally {
        isLoading.value = false
      }
    }

    /**
     * Fetch user's teams - ONLY REAL DATA
     */
    const fetchAvailableTeams = async () => {
      isLoadingTeams.value = true

      try {
        // Fetch teams managed by current user
        const response = await teamAPI.getAll({
          manager_id: authStore.user?.id,
          is_active: true,
        })

        if (apiHelpers.isSuccess(response)) {
          const data = apiHelpers.getData(response)
          availableTeams.value = data.data || []
        } else {
          console.warn('Failed to fetch user teams:', response)
          availableTeams.value = []
        }
      } catch (err) {
        console.error('Failed to fetch teams:', err)
        availableTeams.value = []
        generalError.value = 'Error al cargar los equipos'
      } finally {
        isLoadingTeams.value = false
      }
    }

    /**
     * Format date for display
     */
    const formatDate = (dateString) => {
      if (!dateString) return 'TBD'
      return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
      })
    }

    // Clear error when team selection changes
    watch(selectedTeamId, () => {
      error.value = ''
      generalError.value = ''
    })

    // Initialize - Fetch real teams
    onMounted(() => {
      fetchAvailableTeams()
    })

    return {
      availableTeams,
      selectedTeamId,
      selectedTeam,
      isLoading,
      isLoadingTeams,
      error,
      generalError,
      handleSubmit,
      formatDate,
    }
  },
}
</script>

<style scoped>
/* Modal transitions */
.modal-overlay-enter-active,
.modal-overlay-leave-active {
  transition: opacity 0.3s ease;
}

.modal-overlay-enter-from,
.modal-overlay-leave-to {
  opacity: 0;
}

.modal-content-enter-active {
  transition: all 0.3s ease-out;
}

.modal-content-leave-active {
  transition: all 0.2s ease-in;
}

.modal-content-enter-from {
  transform: scale(0.95) translateY(-20px);
  opacity: 0;
}

.modal-content-leave-to {
  transform: scale(0.95) translateY(-20px);
  opacity: 0;
}
</style>
