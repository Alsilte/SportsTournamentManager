<template>
  <MainLayout>
    <template #header>
      <div class="flex items-center space-x-4">
        <button @click="$router.go(-1)" class="btn-secondary p-2">
          <ArrowLeftIcon class="w-5 h-5" />
        </button>
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Create Match</h1>
          <p class="text-gray-600 mt-1">Schedule a new match between teams</p>
        </div>
      </div>
    </template>

    <div class="max-w-4xl mx-auto">
      <!-- Debug Info (remove in production) -->
      <div v-if="process.env.NODE_ENV === 'development'" class="card p-4 mb-4 bg-yellow-50 border border-yellow-200">
        <h3 class="text-sm font-medium text-yellow-800 mb-2">Debug Info:</h3>
        <p class="text-xs text-yellow-700">Tournaments loaded: {{ tournaments.length }}</p>
        <p class="text-xs text-yellow-700">Loading state: {{ isLoading }}</p>
        <p class="text-xs text-yellow-700">Selected tournament: {{ form.tournament_id }}</p>
        <p class="text-xs text-yellow-700">Available teams: {{ availableTeams.length }}</p>
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-8">
        <!-- Tournament Selection -->
        <div class="card p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-6">Tournament</h2>
          
          <div class="grid grid-cols-1 gap-6">
            <!-- Tournament -->
            <div>
              <label for="tournament_id" class="form-label">Tournament</label>
              
              <!-- Loading State for Tournaments -->
              <div v-if="isLoadingTournaments" class="form-input bg-gray-100 flex items-center">
                <div class="spinner w-4 h-4 mr-2"></div>
                Loading tournaments...
              </div>
              
              <!-- Tournament Select -->
              <select
                v-else
                id="tournament_id"
                v-model="form.tournament_id"
                required
                :disabled="isLoading"
                class="form-input"
                :class="{ 'border-danger-300': errors.tournament_id }"
                @change="onTournamentChange"
              >
                <option value="">Select a tournament</option>
                <option 
                  v-for="tournament in tournaments" 
                  :key="tournament.id" 
                  :value="tournament.id"
                >
                  {{ tournament.name }} ({{ tournament.sport_type || 'Unknown Sport' }})
                </option>
              </select>
              
              <!-- No tournaments message -->
              <div v-if="!isLoadingTournaments && tournaments.length === 0" class="text-sm text-amber-600 mt-1">
                No tournaments available. Only tournaments that are "Registration Open" or "In Progress" can have matches created.
              </div>
              
              <p v-if="errors.tournament_id" class="form-error">{{ errors.tournament_id }}</p>
              <p v-if="selectedTournament" class="text-sm text-gray-600 mt-1">
                Status: {{ formatTournamentStatus(selectedTournament.status) }} • 
                {{ selectedTournament.registered_teams_count || 0 }} teams registered
              </p>
            </div>
          </div>
        </div>

        <!-- Team Selection -->
        <div class="card p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-6">Teams</h2>
          
          <div v-if="!form.tournament_id" class="text-center py-8 text-gray-500">
            <p>Please select a tournament first to see available teams</p>
          </div>
          
          <div v-else-if="isLoadingTeams" class="text-center py-8">
            <div class="spinner w-6 h-6 mx-auto mb-2"></div>
            <p class="text-gray-500">Loading teams...</p>
          </div>
          
          <div v-else-if="availableTeams.length === 0" class="text-center py-8 text-amber-600">
            <p>No teams are registered for this tournament yet.</p>
            <p class="text-sm mt-2">Teams must be registered in the tournament before matches can be created.</p>
          </div>
          
          <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Home Team -->
            <div>
              <label for="home_team_id" class="form-label">Home Team</label>
              <select
                id="home_team_id"
                v-model="form.home_team_id"
                required
                :disabled="isLoading || !availableTeams.length"
                class="form-input"
                :class="{ 'border-danger-300': errors.home_team_id }"
              >
                <option value="">Select home team</option>
                <option 
                  v-for="team in availableTeams" 
                  :key="`home-${team.id}`" 
                  :value="team.id"
                  :disabled="team.id == form.away_team_id"
                >
                  {{ team.name }}
                </option>
              </select>
              <p v-if="errors.home_team_id" class="form-error">{{ errors.home_team_id }}</p>
            </div>

            <!-- Away Team -->
            <div>
              <label for="away_team_id" class="form-label">Away Team</label>
              <select
                id="away_team_id"
                v-model="form.away_team_id"
                required
                :disabled="isLoading || !availableTeams.length"
                class="form-input"
                :class="{ 'border-danger-300': errors.away_team_id }"
              >
                <option value="">Select away team</option>
                <option 
                  v-for="team in availableTeams" 
                  :key="`away-${team.id}`" 
                  :value="team.id"
                  :disabled="team.id == form.home_team_id"
                >
                  {{ team.name }}
                </option>
              </select>
              <p v-if="errors.away_team_id" class="form-error">{{ errors.away_team_id }}</p>
            </div>
          </div>

          <!-- Team Preview -->
          <div v-if="form.home_team_id && form.away_team_id" class="mt-6 p-4 bg-gray-50 rounded-lg">
            <div class="flex items-center justify-center space-x-8">
              <div class="text-center">
                <h3 class="font-semibold text-gray-900">{{ getTeamName(form.home_team_id) }}</h3>
                <p class="text-sm text-gray-600">Home</p>
              </div>
              <div class="text-2xl font-bold text-gray-400">VS</div>
              <div class="text-center">
                <h3 class="font-semibold text-gray-900">{{ getTeamName(form.away_team_id) }}</h3>
                <p class="text-sm text-gray-600">Away</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Match Details -->
        <div class="card p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-6">Match Details</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Match Date -->
            <div>
              <label for="match_date" class="form-label">Match Date & Time</label>
              <input
                id="match_date"
                v-model="form.match_date"
                type="datetime-local"
                required
                :disabled="isLoading"
                class="form-input"
                :class="{ 'border-danger-300': errors.match_date }"
                :min="minDateTime"
              />
              <p v-if="errors.match_date" class="form-error">{{ errors.match_date }}</p>
            </div>

            <!-- Round -->
            <div>
              <label for="round" class="form-label">Round/Stage</label>
              <input
                id="round"
                v-model="form.round"
                type="text"
                :disabled="isLoading"
                class="form-input"
                :class="{ 'border-danger-300': errors.round }"
                placeholder="e.g., Group Stage, Quarter Final, Round 1"
              />
              <p v-if="errors.round" class="form-error">{{ errors.round }}</p>
            </div>

            <!-- Venue -->
            <div>
              <label for="venue" class="form-label">Venue</label>
              <input
                id="venue"
                v-model="form.venue"
                type="text"
                :disabled="isLoading"
                class="form-input"
                :class="{ 'border-danger-300': errors.venue }"
                placeholder="e.g., Stadium Name, Sports Complex"
              />
              <p v-if="errors.venue" class="form-error">{{ errors.venue }}</p>
            </div>

            <!-- Referee -->
            <div>
              <label for="referee_id" class="form-label">Referee (Optional)</label>
              <select
                id="referee_id"
                v-model="form.referee_id"
                :disabled="isLoading"
                class="form-input"
                :class="{ 'border-danger-300': errors.referee_id }"
              >
                <option value="">No referee assigned</option>
                <option 
                  v-for="referee in referees" 
                  :key="referee.id" 
                  :value="referee.id"
                >
                  {{ referee.name }}
                </option>
              </select>
              <p v-if="errors.referee_id" class="form-error">{{ errors.referee_id }}</p>
              <p v-if="referees.length === 0" class="text-xs text-gray-500 mt-1">
                No referees available. Referees can be assigned later.
              </p>
            </div>
          </div>
        </div>

        <!-- Match Preview -->
        <div v-if="isFormValid" class="card p-6 bg-gray-50">
          <h2 class="text-xl font-semibold text-gray-900 mb-6">Match Preview</h2>
          <div class="bg-white rounded-lg p-6 border-2 border-dashed border-gray-200">
            <!-- Tournament Info -->
            <div class="text-center mb-6">
              <h3 class="text-lg font-semibold text-gray-900">{{ selectedTournament?.name }}</h3>
              <p v-if="form.round" class="text-gray-600">{{ form.round }}</p>
            </div>

            <!-- Teams -->
            <div class="flex items-center justify-center space-x-8 mb-6">
              <div class="text-center">
                <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-2">
                  <span class="text-xl font-bold text-primary-600">
                    {{ getTeamInitials(form.home_team_id) }}
                  </span>
                </div>
                <h4 class="font-semibold text-gray-900">{{ getTeamName(form.home_team_id) }}</h4>
                <p class="text-sm text-gray-600">Home</p>
              </div>
              
              <div class="text-3xl font-bold text-gray-400">VS</div>
              
              <div class="text-center">
                <div class="w-16 h-16 bg-secondary-100 rounded-full flex items-center justify-center mx-auto mb-2">
                  <span class="text-xl font-bold text-secondary-600">
                    {{ getTeamInitials(form.away_team_id) }}
                  </span>
                </div>
                <h4 class="font-semibold text-gray-900">{{ getTeamName(form.away_team_id) }}</h4>
                <p class="text-sm text-gray-600">Away</p>
              </div>
            </div>

            <!-- Match Details -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center border-t pt-6">
              <div>
                <CalendarIcon class="w-5 h-5 text-gray-400 mx-auto mb-1" />
                <div class="text-sm text-gray-600">Date & Time</div>
                <div class="font-medium">{{ formatPreviewDateTime(form.match_date) }}</div>
              </div>
              <div v-if="form.venue">
                <MapPinIcon class="w-5 h-5 text-gray-400 mx-auto mb-1" />
                <div class="text-sm text-gray-600">Venue</div>
                <div class="font-medium">{{ form.venue }}</div>
              </div>
              <div v-if="form.referee_id">
                <UserIcon class="w-5 h-5 text-gray-400 mx-auto mb-1" />
                <div class="text-sm text-gray-600">Referee</div>
                <div class="font-medium">{{ getRefereeName(form.referee_id) }}</div>
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
          <button 
            type="button" 
            @click="$router.go(-1)" 
            :disabled="isLoading" 
            class="btn-secondary"
          >
            Cancel
          </button>
          <button 
            type="submit" 
            :disabled="isLoading || !isFormValid" 
            class="btn-primary"
          >
            <div v-if="isLoading" class="flex items-center">
              <div class="spinner w-4 h-4 mr-2"></div>
              Creating Match...
            </div>
            <span v-else>Create Match</span>
          </button>
        </div>
      </form>
    </div>
  </MainLayout>
</template>

<script>
/**
 * Create Match Page Component
 * Form for creating a new match with validation and preview
 */

import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { 
  ArrowLeftIcon, 
  ExclamationTriangleIcon,
  CalendarIcon,
  MapPinIcon,
  UserIcon
} from '@heroicons/vue/24/outline'
import { useAuthStore } from '@/stores/auth'
import { matchAPI, tournamentAPI, teamAPI, apiHelpers } from '@/services/api'
import MainLayout from '@/components/layout/MainLayout.vue'

export default {
  name: 'CreateMatch',
  components: {
    MainLayout,
    ArrowLeftIcon,
    ExclamationTriangleIcon,
    CalendarIcon,
    MapPinIcon,
    UserIcon,
  },
  setup() {
    const router = useRouter()
    const authStore = useAuthStore()

    // Form data
    const form = ref({
      tournament_id: '',
      home_team_id: '',
      away_team_id: '',
      referee_id: '',
      round: '',
      match_date: '',
      venue: '',
    })

    // Data
    const tournaments = ref([])
    const availableTeams = ref([])
    const referees = ref([])
    const isLoading = ref(false)
    const isLoadingTournaments = ref(false)
    const isLoadingTeams = ref(false)
    const errors = ref({})
    const generalError = ref('')

    // Computed
    const minDateTime = computed(() => {
      const now = new Date()
      now.setHours(now.getHours() + 1) // At least 1 hour from now
      return now.toISOString().slice(0, 16)
    })

    const selectedTournament = computed(() => {
      return tournaments.value.find(t => t.id == form.value.tournament_id)
    })

    const isFormValid = computed(() => {
      return form.value.tournament_id && 
             form.value.home_team_id && 
             form.value.away_team_id && 
             form.value.match_date &&
             form.value.home_team_id !== form.value.away_team_id &&
             Object.keys(errors.value).length === 0
    })

    /**
     * Fetch tournaments
     */
    const fetchTournaments = async () => {
      isLoadingTournaments.value = true
      try {
        console.log('Fetching tournaments...')
        
        // Try different approaches to get tournaments
        let response
        try {
          // First try with status filter
          response = await tournamentAPI.getAll({ 
            status: 'registration_open,in_progress',
            per_page: 100 
          })
        } catch (error) {
          console.log('First attempt failed, trying without status filter...')
          // If that fails, try without status filter
          response = await tournamentAPI.getAll({ per_page: 100 })
        }
        
        console.log('Tournament API response:', response)
        
        if (apiHelpers.isSuccess(response)) {
          const data = apiHelpers.getData(response)
          console.log('Tournament data:', data)
          
          // Handle both paginated and non-paginated responses
          const tournamentList = data.data || data || []
          
          // Filter tournaments that can have matches created
          tournaments.value = tournamentList.filter(tournament => 
            ['registration_open', 'in_progress'].includes(tournament.status)
          )
          
          console.log('Filtered tournaments:', tournaments.value)
        } else {
          console.error('Tournament API failed:', response)
          tournaments.value = []
        }
      } catch (err) {
        console.error('Failed to fetch tournaments:', err)
        tournaments.value = []
        window.$notify?.error('Failed to load tournaments')
      } finally {
        isLoadingTournaments.value = false
      }
    }

    /**
     * Fetch teams for selected tournament
     */
    const fetchTeamsForTournament = async (tournamentId) => {
      if (!tournamentId) {
        availableTeams.value = []
        return
      }

      isLoadingTeams.value = true
      try {
        console.log('Fetching teams for tournament:', tournamentId)
        const response = await tournamentAPI.getById(tournamentId)
        
        console.log('Tournament details response:', response)
        
        if (apiHelpers.isSuccess(response)) {
          const data = apiHelpers.getData(response)
          console.log('Tournament detail data:', data)
          
          // The teams should be in the teams property
          availableTeams.value = data.teams || []
          console.log('Available teams:', availableTeams.value)
        } else {
          console.error('Failed to get tournament details:', response)
          availableTeams.value = []
        }
      } catch (err) {
        console.error('Failed to fetch tournament teams:', err)
        availableTeams.value = []
        window.$notify?.error('Failed to load teams for tournament')
      } finally {
        isLoadingTeams.value = false
      }
    }

    /**
     * Fetch available referees
     */
    const fetchReferees = async () => {
      try {
        // This would be a specific endpoint for users with referee role
        // For now, we'll leave it empty as this endpoint might not exist
        referees.value = []
        
        // TODO: Implement when referee endpoint is available
        // const response = await userAPI.getAll({ role: 'referee' })
        // if (apiHelpers.isSuccess(response)) {
        //   referees.value = apiHelpers.getData(response).data || []
        // }
      } catch (err) {
        console.error('Failed to fetch referees:', err)
        referees.value = []
      }
    }

    /**
     * Handle tournament selection change
     */
    const onTournamentChange = () => {
      console.log('Tournament changed to:', form.value.tournament_id)
      form.value.home_team_id = ''
      form.value.away_team_id = ''
      
      if (form.value.tournament_id) {
        fetchTeamsForTournament(form.value.tournament_id)
      } else {
        availableTeams.value = []
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

      isLoading.value = true

      try {
        const matchData = {
          tournament_id: parseInt(form.value.tournament_id),
          home_team_id: parseInt(form.value.home_team_id),
          away_team_id: parseInt(form.value.away_team_id),
          match_date: form.value.match_date,
          referee_id: form.value.referee_id ? parseInt(form.value.referee_id) : null,
          round: form.value.round || null,
          venue: form.value.venue || null,
        }

        console.log('Submitting match data:', matchData)

        const response = await matchAPI.create(matchData)

        if (apiHelpers.isSuccess(response)) {
          const match = apiHelpers.getData(response)
          window.$notify?.success('Match created successfully!')
          router.push(`/matches/${match.id}`)
        } else {
          generalError.value = response.data?.message || 'Failed to create match'
        }
      } catch (error) {
        console.error('Match creation failed:', error)
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

      // Tournament validation
      if (!form.value.tournament_id) {
        newErrors.tournament_id = 'Tournament is required'
      }

      // Teams validation
      if (!form.value.home_team_id) {
        newErrors.home_team_id = 'Home team is required'
      }

      if (!form.value.away_team_id) {
        newErrors.away_team_id = 'Away team is required'
      }

      if (form.value.home_team_id === form.value.away_team_id) {
        newErrors.away_team_id = 'Away team must be different from home team'
      }

      // Date validation
      if (!form.value.match_date) {
        newErrors.match_date = 'Match date and time is required'
      } else {
        const matchDate = new Date(form.value.match_date)
        const now = new Date()
        
        if (matchDate <= now) {
          newErrors.match_date = 'Match date must be in the future'
        }
      }

      errors.value = newErrors
      return Object.keys(newErrors).length === 0
    }

    /**
     * Get team name by ID
     */
    const getTeamName = (teamId) => {
      const team = availableTeams.value.find(t => t.id == teamId)
      return team?.name || 'Unknown Team'
    }

    /**
     * Get team initials by ID
     */
    const getTeamInitials = (teamId) => {
      const team = availableTeams.value.find(t => t.id == teamId)
      return team?.short_name || team?.name?.charAt(0) || '?'
    }

    /**
     * Get referee name by ID
     */
    const getRefereeName = (refereeId) => {
      const referee = referees.value.find(r => r.id == refereeId)
      return referee?.name || 'Unknown Referee'
    }

    /**
     * Format tournament status
     */
    const formatTournamentStatus = (status) => {
      const statusMap = {
        draft: 'Draft',
        registration_open: 'Registration Open',
        in_progress: 'In Progress',
        completed: 'Completed',
        cancelled: 'Cancelled',
      }
      return statusMap[status] || status
    }

    /**
     * Format date and time for preview
     */
    const formatPreviewDateTime = (dateString) => {
      if (!dateString) return 'TBD'
      return new Date(dateString).toLocaleString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      })
    }

    // Watch for form changes to clear errors
    watch(() => form.value.tournament_id, () => {
      if (errors.value.tournament_id) {
        delete errors.value.tournament_id
      }
    })

    watch(() => form.value.home_team_id, () => {
      if (errors.value.home_team_id) {
        delete errors.value.home_team_id
      }
    })

    watch(() => form.value.away_team_id, () => {
      if (errors.value.away_team_id) {
        delete errors.value.away_team_id
      }
    })

    watch(() => form.value.match_date, () => {
      if (errors.value.match_date) {
        delete errors.value.match_date
      }
    })

    // Initialize
    onMounted(() => {
      console.log('CreateMatch component mounted')
      fetchTournaments()
      fetchReferees()
      
      // Set default date to tomorrow
      const tomorrow = new Date()
      tomorrow.setDate(tomorrow.getDate() + 1)
      tomorrow.setHours(15, 0, 0, 0) // 3:00 PM
      form.value.match_date = tomorrow.toISOString().slice(0, 16)
    })

    return {
      authStore,
      form,
      tournaments,
      availableTeams,
      referees,
      isLoading,
      isLoadingTournaments,
      isLoadingTeams,
      errors,
      generalError,
      minDateTime,
      selectedTournament,
      isFormValid,
      onTournamentChange,
      handleSubmit,
      getTeamName,
      getTeamInitials,
      getRefereeName,
      formatTournamentStatus,
      formatPreviewDateTime,
    }
  },
}
</script>

<style scoped>
/* Form styling improvements */
.form-section {
  @apply space-y-6;
}

.team-preview {
  @apply flex items-center justify-center space-x-8 p-4 bg-gray-50 rounded-lg;
}

.team-avatar {
  @apply w-16 h-16 rounded-full flex items-center justify-center font-bold text-xl;
}

.preview-card {
  @apply bg-white rounded-lg p-6 border-2 border-dashed border-gray-200;
}

/* Animation for smooth transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Loading spinner */
.spinner {
  border: 2px solid #f3f3f3;
  border-top: 2px solid #3498db;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Custom select styling */
select.form-input {
  @apply bg-white cursor-pointer;
}

select.form-input:disabled {
  @apply bg-gray-100 cursor-not-allowed;
}

/* Preview section styling */
.match-preview {
  @apply transform transition-all duration-300;
}

.match-preview:hover {
  @apply scale-105;
}
</style>