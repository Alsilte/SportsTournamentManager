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
                  <h3 class="text-lg font-semibold text-gray-900">Register Team</h3>
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
                    <span class="text-gray-600">Sport:</span>
                    <span class="font-medium">{{ tournament?.sport_type }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600">Start Date:</span>
                    <span class="font-medium">{{ formatDate(tournament?.start_date) }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600">Teams:</span>
                    <span class="font-medium"
                      >{{ tournament?.registered_teams_count || 0 }}/{{
                        tournament?.max_teams
                      }}</span
                    >
                  </div>
                  <div v-if="tournament?.location" class="flex justify-between">
                    <span class="text-gray-600">Location:</span>
                    <span class="font-medium">{{ tournament.location }}</span>
                  </div>
                </div>
              </div>

              <!-- Loading State for Teams -->
              <div v-if="isLoadingTeams" class="space-y-4 mb-6">
                <label class="form-label">Select Team</label>
                <div class="animate-pulse">
                  <div class="h-10 bg-gray-200 rounded"></div>
                </div>
                <p class="text-sm text-gray-500">Loading your teams...</p>
              </div>

              <!-- Team Selection Form -->
              <form v-else @submit.prevent="handleSubmit" class="space-y-4">
                <!-- Team Selection -->
                <div>
                  <label for="team" class="form-label"> Select Team </label>
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
                          ? 'No teams available'
                          : 'Choose a team to register'
                      }}
                    </option>
                    <option v-for="team in availableTeams" :key="team.id" :value="team.id">
                      {{ team.name }} ({{ team.players_count || 0 }} players)
                    </option>
                  </select>
                  <p v-if="error" class="form-error">{{ error }}</p>
                  <p v-if="availableTeams.length === 0" class="text-xs text-gray-500 mt-1">
                    You don't have any teams that can be registered for this tournament
                  </p>
                </div>

                <!-- Team Details (if team selected) -->
                <div v-if="selectedTeam" class="bg-primary-50 rounded-lg p-4">
                  <h4 class="font-medium text-primary-900 mb-2">{{ selectedTeam.name }}</h4>
                  <div class="space-y-1 text-sm text-primary-700">
                    <p>Players: {{ selectedTeam.players_count || 0 }}</p>
                    <p v-if="selectedTeam.contact_email">
                      Contact: {{ selectedTeam.contact_email }}
                    </p>
                    <p v-if="selectedTeam.home_venue">Home Venue: {{ selectedTeam.home_venue }}</p>
                  </div>
                </div>

                <!-- Registration Requirements -->
                <div class="bg-warning-50 border border-warning-200 rounded-lg p-4">
                  <div class="flex">
                    <ExclamationTriangleIcon
                      class="w-5 h-5 text-warning-600 mr-2 flex-shrink-0 mt-0.5"
                    />
                    <div class="text-sm">
                      <h4 class="font-medium text-warning-800 mb-1">Registration Requirements</h4>
                      <ul class="text-warning-700 space-y-1">
                        <li>• Team must have at least 8 registered players</li>
                        <li>• All players must be verified and active</li>
                        <li>• Registration is subject to tournament approval</li>
                        <li>
                          • Registration deadline: {{ formatDate(tournament?.registration_end) }}
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
                    Cancel
                  </button>
                  <button
                    type="submit"
                    :disabled="!selectedTeamId || isLoading || availableTeams.length === 0"
                    class="flex-1 btn-primary"
                  >
                    <div v-if="isLoading" class="flex items-center justify-center">
                      <div class="spinner w-4 h-4 mr-2"></div>
                      Registering...
                    </div>
                    <span v-else>Register Team</span>
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
        generalError.value = 'Failed to load your teams'
      } finally {
        isLoadingTeams.value = false
      }
    }

    /**
     * Handle form submission
     */
    const handleSubmit = async () => {
      error.value = ''
      generalError.value = ''

      if (!selectedTeamId.value) {
        error.value = 'Please select a team'
        return
      }

      const team = selectedTeam.value
      if (!team) {
        error.value = 'Selected team not found'
        return
      }

      // Validate team requirements
      if ((team.players_count || 0) < 8) {
        error.value = 'Team must have at least 8 players to register'
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
          generalError.value = response.data?.message || 'Registration failed'
        }
      } catch (err) {
        console.error('Registration failed:', err)
        generalError.value = apiHelpers.handleError(err)
      } finally {
        isLoading.value = false
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
