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
                    <span class="font-medium">{{ tournament?.registered_teams_count || 0 }}/{{ tournament?.max_teams }}</span>
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

                <!-- Registration Requirements -->
                <div class="bg-warning-50 border border-warning-200 rounded-lg p-4">
                  <div class="flex">
                    <ExclamationTriangleIcon class="w-5 h-5 text-warning-600 mr-2 flex-shrink-0 mt-0.5" />
                    <div class="text-sm">
                      <h4 class="font-medium text-warning-800 mb-1">Requisitos de Registro</h4>
                      <ul class="text-warning-700 space-y-1">
                        <li>• El equipo debe tener al menos 8 jugadores registrados</li>
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
                    <span v-else>Registrar Equipo</span>
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
 * Team Registration Modal Component
 * Modal for registering teams to tournaments
 */

import { ref, computed, onMounted, watch } from 'vue'
import { 
  XMarkIcon,
  ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'
import { useAuthStore } from '@/stores/auth'
import { teamAPI, tournamentAPI, apiHelpers } from '@/services/api'

export default {
  name: 'TeamRegistrationModal',
  components: {
    XMarkIcon,
    ExclamationTriangleIcon
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
    
    // Data
    const availableTeams = ref([])
    const selectedTeamId = ref('')
    const isLoading = ref(false)
    const error = ref('')

    // Computed
    const selectedTeam = computed(() => {
      return availableTeams.value.find(team => team.id == selectedTeamId.value)
    })

    /**
     * Fetch user's teams
     */
    const fetchAvailableTeams = async () => {
      try {
        // Mock implementation - would fetch user's managed teams
        const response = await teamAPI.getAll({ 
          manager_id: authStore.user?.id,
          is_active: true 
        })
        
        if (apiHelpers.isSuccess(response)) {
          availableTeams.value = apiHelpers.getData(response).data || []
        }
      } catch (err) {
        console.error('Failed to fetch teams:', err)
        // Mock data for demo
        availableTeams.value = [
          {
            id: 1,
            name: 'Eagles FC',
            players_count: 15,
            contact_email: 'manager@eagles.com',
            home_venue: 'Eagles Stadium'
          },
          {
            id: 2,
            name: 'Lions United',
            players_count: 12,
            contact_email: 'contact@lions.com',
            home_venue: 'City Sports Complex'
          }
        ]
      }
    }

    /**
     * Handle form submission
     */
    const handleSubmit = async () => {
      error.value = ''
      
      if (!selectedTeamId.value) {
        error.value = 'Por favor selecciona un equipo'
        return
      }

      const team = selectedTeam.value
      if (!team) {
        error.value = 'Equipo seleccionado no encontrado'
        return
      }

      // Validate team requirements
      if ((team.players_count || 0) < 8) {
        error.value = 'El equipo debe tener al menos 8 jugadores para registrarse'
        return
      }

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

    // Clear error when team selection changes
    watch(selectedTeamId, () => {
      error.value = ''
    })

    // Initialize
    onMounted(() => {
      fetchAvailableTeams()
    })

    return {
      availableTeams,
      selectedTeamId,
      selectedTeam,
      isLoading,
      error,
      handleSubmit,
      formatDate
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
</style>