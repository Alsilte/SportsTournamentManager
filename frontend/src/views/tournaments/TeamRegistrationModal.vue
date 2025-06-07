<template>
  <Teleport to="body">
    <Transition name="modal-overlay" appear>
      <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <Transition name="modal-content" appear>
          <div class="bg-white rounded-xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
            <!-- Header -->
            <div class="p-6 border-b border-gray-200">
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-lg font-semibold text-gray-900">
                    Registrar Equipo
                  </h3>
                  <p class="text-sm text-gray-600 mt-1">
                    {{ tournament?.name }}
                  </p>
                </div>
                <button
                  @click="$emit('close')"
                  class="text-gray-400 hover:text-gray-600 transition-colors"
                >
                  <XMarkIcon class="w-6 h-6" />
                </button>
              </div>
            </div>

            <!-- Content -->
            <div class="p-6">
              <!-- Tournament Details -->
              <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <h4 class="font-medium text-gray-900 mb-3">Detalles del Torneo</h4>
                <div class="grid grid-cols-2 gap-4 text-sm">
                  <div class="flex justify-between">
                    <span class="text-gray-600">Deporte:</span>
                    <span class="font-medium">{{ tournament?.sport_type }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600">Formato:</span>
                    <span class="font-medium">{{ formatTournamentType(tournament?.tournament_type) }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600">Equipos:</span>
                    <span class="font-medium">{{ tournament?.registered_teams_count || 0 }}/{{ tournament?.max_teams }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600">Estado:</span>
                    <span class="font-medium">{{ formatStatus(tournament?.status) }}</span>
                  </div>
                  <div v-if="tournament?.prize_pool" class="flex justify-between">
                    <span class="text-gray-600">Premio:</span>
                    <span class="font-medium">${{ formatMoney(tournament.prize_pool) }}</span>
                  </div>
                  <div v-if="tournament?.location" class="flex justify-between">
                    <span class="text-gray-600">Ubicación:</span>
                    <span class="font-medium">{{ tournament.location }}</span>
                  </div>
                </div>
              </div>

              <!-- Team Selection Form -->
              <form @submit.prevent="handleSubmit" class="space-y-4">
                <!-- Team Selection -->
                <div>
                  <label for="team" class="form-label">
                    Seleccionar Equipo
                  </label>
                  <select
                    id="team"
                    v-model="selectedTeamId"
                    required
                    :disabled="isLoading"
                    class="form-input"
                  >
                    <option value="">Elige un equipo para registrar</option>
                    <option 
                      v-for="team in availableTeams" 
                      :key="team.id"
                      :value="team.id"
                    >
                      {{ team.name }} ({{ team.players_count || 0 }} jugadores)
                    </option>
                  </select>
                  <p v-if="error" class="form-error">{{ error }}</p>
                </div>

                <!-- Team Details (if team selected) -->
                <div v-if="selectedTeam" class="bg-primary-50 rounded-lg p-4">
                  <h4 class="font-medium text-primary-900 mb-2">{{ selectedTeam.name }}</h4>
                  <div class="space-y-1 text-sm text-primary-700">
                    <p>Jugadores: {{ selectedTeam.players_count || 0 }}</p>
                    <p v-if="selectedTeam.contact_email">Contacto: {{ selectedTeam.contact_email }}</p>
                    <p v-if="selectedTeam.home_venue">Sede local: {{ selectedTeam.home_venue }}</p>
                  </div>
                </div>

                <!-- Registration Requirements/Info -->
                <!-- Admin Message -->
                <div v-if="authStore.user?.isAdmin" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                  <div class="flex">
                    <ShieldCheckIcon class="w-5 h-5 text-blue-600 mr-2 flex-shrink-0 mt-0.5" />
                    <div class="text-sm">
                      <h4 class="font-medium text-blue-800 mb-1">Privilegios de Administrador</h4>
                      <ul class="text-blue-700 space-y-1">
                        <li>• Puedes registrar cualquier equipo sin restricciones de jugadores</li>
                        <li>• Los registros se aprueban automáticamente</li>
                        <li>• Puedes registrar equipos incluso si el período de registro ha cerrado</li>
                        <li>• No hay límite de equipos máximos para admins</li>
                      </ul>
                    </div>
                  </div>
                </div>

                <!-- Manager/User Message -->
                <div v-else class="bg-warning-50 border border-warning-200 rounded-lg p-4">
                  <div class="flex">
                    <ExclamationTriangleIcon class="w-5 h-5 text-warning-600 mr-2 flex-shrink-0 mt-0.5" />
                    <div class="text-sm">
                      <h4 class="font-medium text-warning-800 mb-1">Requisitos de Registro</h4>
                      <ul class="text-warning-700 space-y-1">
                        <li>• El equipo debe tener al menos 1 jugador activo</li>
                        <li>• Todos los jugadores deben estar verificados y activos</li>
                        <li>• El registro está sujeto a la aprobación del torneo</li>
                        <li>• Fecha límite de registro: {{ formatDate(tournament?.registration_end) }}</li>
                      </ul>
                    </div>
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
                    :disabled="!selectedTeamId || isLoading"
                    class="flex-1 btn-primary"
                  >
                    <div v-if="isLoading" class="flex items-center justify-center">
                      <div class="spinner w-4 h-4 mr-2"></div>
                      Registrando...
                    </div>
                    <span v-else>
                      {{ authStore.user?.isAdmin ? 'Registrar y Aprobar' : 'Registrar Equipo' }}
                    </span>
                  </button>
                </div>
              </form>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script>
/**
 * Team Registration Modal Component
 * Modal for registering teams to tournaments
 */

import { ref, computed, watch, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { tournamentAPI, teamAPI } from '@/services/api'
import { apiHelpers } from '@/utils/api'
import { ExclamationTriangleIcon, ShieldCheckIcon, XMarkIcon } from '@heroicons/vue/24/outline'

export default {
  name: 'TeamRegistrationModal',
  components: {
    ExclamationTriangleIcon,
    ShieldCheckIcon,
    XMarkIcon
  },
  props: {
    tournament: {
      type: Object,
      required: true
    }
  },
  emits: ['close', 'success'],
  setup(props, { emit }) {
    const authStore = useAuthStore()
    
    // State
    const availableTeams = ref([])
    const selectedTeamId = ref('')
    const isLoading = ref(false)
    const error = ref('')

    // Computed
    const selectedTeam = computed(() => {
      if (!selectedTeamId.value) return null
      return availableTeams.value.find(team => team.id === selectedTeamId.value)
    })

    /**
     * Fetch available teams for registration
     */
    const fetchAvailableTeams = async () => {
      try {
        isLoading.value = true
        
        // Get teams based on user role
        const response = authStore.user?.isAdmin 
          ? await teamAPI.getAll() // Admins can register any team
          : await teamAPI.getMyTeams() // Managers only their teams

        if (apiHelpers.isSuccess(response)) {
          const teams = apiHelpers.getData(response) || []
          
          // Filter out teams already registered
          const registeredTeamIds = props.tournament.teams?.map(t => t.id) || []
          availableTeams.value = teams.filter(team => 
            !registeredTeamIds.includes(team.id)
          )
        }
      } catch (err) {
        console.error('Failed to fetch teams:', err)
        error.value = 'Error al cargar equipos disponibles'
      } finally {
        isLoading.value = false
      }
    }

    /**
     * Handle form submission
     */
    const handleSubmit = async () => {
      if (!selectedTeamId.value) {
        error.value = 'Por favor selecciona un equipo'
        return
      }

      const team = selectedTeam.value
      if (!team) {
        error.value = 'Equipo seleccionado no encontrado'
        return
      }

      // Validación condicional según rol
      if (!authStore.user?.isAdmin) {
        if ((team.players_count || 0) < 1) {
          error.value = 'El equipo debe tener al menos 1 jugador para registrarse'
          return
        }
      }
      // Los administradores no tienen restricciones de jugadores

      isLoading.value = true

      try {
        const response = await tournamentAPI.registerTeam(props.tournament.id, {
          team_id: selectedTeamId.value
        })

        if (apiHelpers.isSuccess(response)) {
          emit('success')
        } else {
          error.value = response.data?.message || 'Falló el registro'
        }
      } catch (err) {
        console.error('Registration failed:', err)
        error.value = apiHelpers.handleError(err)
      } finally {
        isLoading.value = false
      }
    }

    /**
     * Format date for display
     */
    const formatDate = (dateString) => {
      if (!dateString) return 'Por determinar'
      return new Date(dateString).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      })
    }

    /**
     * Format tournament type
     */
    const formatTournamentType = (type) => {
      const types = {
        'knockout': 'Eliminación',
        'league': 'Liga',
        'group_stage': 'Fase de Grupos'
      }
      return types[type] || type
    }

    /**
     * Format status
     */
    const formatStatus = (status) => {
      const statusMap = {
        'draft': 'Borrador',
        'registration_open': 'Registro Abierto',
        'registration_closed': 'Registro Cerrado',
        'in_progress': 'En Progreso',
        'completed': 'Completado',
        'cancelled': 'Cancelado'
      }
      return statusMap[status] || status
    }

    /**
     * Format money
     */
    const formatMoney = (amount) => {
      return new Intl.NumberFormat('es-ES').format(amount)
    }

    // Clear error when team selection changes
    watch(selectedTeamId, () => {
      error.value = ''
    })

    // Initialize
    onMounted(() => {
      fetchAvailableTeams()
    })

    return {
      authStore,
      availableTeams,
      selectedTeamId,
      selectedTeam,
      isLoading,
      error,
      handleSubmit,
      formatDate,
      formatTournamentType,
      formatStatus,
      formatMoney
    }
  }
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

/* Form styles */
.form-label {
  @apply block text-sm font-medium text-gray-700 mb-2;
}

.form-input {
  @apply w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500;
}

.form-error {
  @apply text-sm text-red-600 mt-1;
}

.btn-primary {
  @apply px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed;
}

.btn-secondary {
  @apply px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50;
}

.spinner {
  border: 2px solid transparent;
  border-top: 2px solid currentColor;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>