<template>
  <MainLayout>
    <template #header>
      <div class="flex items-center space-x-4">
        <button @click="$router.go(-1)" class="btn-secondary p-2">
          <ArrowLeftIcon class="w-5 h-5" />
        </button>
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Match Details</h1>
          <p class="text-gray-600 mt-1">View match information and events</p>
        </div>
      </div>
    </template>

    <!-- Loading State -->
    <div v-if="isLoading" class="space-y-6">
      <div class="card p-6">
        <div class="animate-pulse space-y-4">
          <div class="h-8 bg-gray-200 rounded w-1/2 mx-auto"></div>
          <div class="h-4 bg-gray-200 rounded w-3/4 mx-auto"></div>
          <div class="h-32 bg-gray-200 rounded"></div>
        </div>
      </div>
    </div>

    <!-- Match Details -->
    <div v-else-if="match" class="space-y-8">
      <!-- Match Header -->
      <div class="card p-8">
        <div class="text-center mb-6">
          <div class="flex items-center justify-center space-x-2 mb-2">
            <span
              :class="getStatusBadgeClass(match.status)"
              class="px-3 py-1 rounded-full text-sm font-medium"
            >
              {{ formatStatus(match.status) }}
            </span>
            <span v-if="match.round" class="text-gray-600 text-sm">{{ match.round }}</span>
          </div>
          <h2 class="text-gray-600 text-lg">{{ match.tournament?.name }}</h2>
        </div>

        <!-- Teams and Score -->
        <div class="flex items-center justify-center space-x-8 mb-8">
          <!-- Home Team -->
          <div class="text-center flex-1 max-w-xs">
            <div class="w-20 h-20 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <span class="text-2xl font-bold text-primary-600">
                {{ match.home_team?.short_name || match.home_team?.name?.charAt(0) }}
              </span>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-1">{{ match.home_team?.name }}</h3>
            <p class="text-sm text-gray-600">Home</p>
          </div>

          <!-- Score -->
          <div class="text-center">
            <div v-if="match.status === 'completed' || match.status === 'in_progress'" class="mb-4">
              <div class="text-6xl font-bold text-gray-900 mb-2">
                {{ match.home_score ?? 0 }} - {{ match.away_score ?? 0 }}
              </div>
              
              <!-- Extra Time -->
              <div v-if="match.extra_time_home !== null && match.extra_time_away !== null" class="text-lg text-gray-600 mb-2">
                Extra Time: {{ match.extra_time_home }} - {{ match.extra_time_away }}
              </div>
              
              <!-- Penalties -->
              <div v-if="match.penalty_home !== null && match.penalty_away !== null" class="text-lg text-gray-600">
                Penalties: {{ match.penalty_home }} - {{ match.penalty_away }}
              </div>
            </div>
            
            <div v-else class="text-4xl font-bold text-gray-400 mb-4">VS</div>
            
            <!-- Match Time -->
            <div class="text-center">
              <div class="text-lg font-medium text-gray-900">
                {{ formatMatchDate(match.match_date) }}
              </div>
              <div class="text-sm text-gray-600">
                {{ formatMatchTime(match.match_date) }}
              </div>
            </div>
          </div>

          <!-- Away Team -->
          <div class="text-center flex-1 max-w-xs">
            <div class="w-20 h-20 bg-secondary-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <span class="text-2xl font-bold text-secondary-600">
                {{ match.away_team?.short_name || match.away_team?.name?.charAt(0) }}
              </span>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-1">{{ match.away_team?.name }}</h3>
            <p class="text-sm text-gray-600">Away</p>
          </div>
        </div>

        <!-- Match Info -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center border-t pt-6">
          <div v-if="match.venue">
            <MapPinIcon class="w-5 h-5 text-gray-400 mx-auto mb-1" />
            <div class="text-sm text-gray-600">Venue</div>
            <div class="font-medium">{{ match.venue }}</div>
          </div>
          <div v-if="match.referee">
            <UserIcon class="w-5 h-5 text-gray-400 mx-auto mb-1" />
            <div class="text-sm text-gray-600">Referee</div>
            <div class="font-medium">{{ match.referee.name }}</div>
          </div>
          <div>
            <ClockIcon class="w-5 h-5 text-gray-400 mx-auto mb-1" />
            <div class="text-sm text-gray-600">Duration</div>
            <div class="font-medium">{{ getMatchDuration() }}</div>
          </div>
        </div>

        <!-- Winner Information -->
        <div v-if="match.status === 'completed' && match.winner_team_id" class="text-center mt-6 pt-6 border-t">
          <div class="text-lg text-gray-600 mb-2">Winner</div>
          <div class="text-2xl font-bold text-success-600">
            {{ getWinnerTeamName() }}
          </div>
        </div>
      </div>

      <!-- Match Events -->
      <div class="card p-6">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-xl font-semibold text-gray-900">Match Events</h2>
          <button 
            v-if="canManageMatch"
            @click="refreshEvents"
            class="btn-secondary text-sm"
          >
            <ArrowPathIcon class="w-4 h-4 mr-2" />
            Refresh
          </button>
        </div>

        <!-- Events Timeline -->
        <div v-if="events.length" class="space-y-4">
          <div
            v-for="event in sortedEvents"
            :key="event.id"
            class="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg"
          >
            <!-- Event Icon -->
            <div :class="getEventIconClass(event.event_type)" class="w-10 h-10 rounded-full flex items-center justify-center">
              <component :is="getEventIcon(event.event_type)" class="w-5 h-5" />
            </div>

            <!-- Event Details -->
            <div class="flex-1">
              <div class="flex items-center space-x-2 mb-1">
                <span class="font-medium text-gray-900">{{ event.player?.user?.name || 'Unknown Player' }}</span>
                <span
                  :class="getEventBadgeClass(event.event_type)"
                  class="px-2 py-1 text-xs font-medium rounded-full"
                >
                  {{ formatEventType(event.event_type) }}
                </span>
              </div>
              <div class="text-sm text-gray-600">
                {{ event.team?.name }} • {{ event.description || 'No description' }}
              </div>
            </div>

            <!-- Event Time -->
            <div class="text-right">
              <div class="font-bold text-gray-900">{{ event.minute }}'</div>
              <div v-if="event.additional_time > 0" class="text-xs text-gray-500">
                +{{ event.additional_time }}
              </div>
            </div>
          </div>
        </div>

        <div v-else class="text-center py-8 text-gray-500">
          <ClockIcon class="w-12 h-12 mx-auto mb-2 text-gray-300" />
          <p>No match events recorded yet</p>
          <p v-if="match.status === 'scheduled'" class="text-sm mt-2">
            Events will appear here once the match starts
          </p>
        </div>
      </div>

      <!-- Match Notes -->
      <div v-if="match.notes" class="card p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Match Notes</h2>
        <div class="bg-gray-50 rounded-lg p-4">
          <p class="text-gray-700 whitespace-pre-line">{{ match.notes }}</p>
        </div>
      </div>

      <!-- Actions -->
      <div v-if="canManageMatch" class="card p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Match Management</h2>
        <div class="flex space-x-4">
          <button 
            v-if="match.status === 'scheduled'"
            @click="startMatch"
            class="btn-success"
          >
            <PlayIcon class="w-4 h-4 mr-2" />
            Start Match
          </button>
          <button 
            v-if="match.status === 'in_progress'"
            @click="completeMatch"
            class="btn-primary"
          >
            <CheckCircleIcon class="w-4 h-4 mr-2" />
            Complete Match
          </button>
          <RouterLink 
            :to="`/matches/${match.id}/edit`"
            class="btn-secondary"
          >
            <PencilIcon class="w-4 h-4 mr-2" />
            Edit Match
          </RouterLink>
        </div>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="text-center py-12">
      <ExclamationTriangleIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
      <h3 class="text-lg font-medium text-gray-900 mb-2">Match not found</h3>
      <p class="text-gray-600 mb-6">{{ error }}</p>
      <RouterLink to="/matches" class="btn-primary">
        Back to Matches
      </RouterLink>
    </div>
  </MainLayout>
</template>

<script>
/**
 * Match Detail Page Component
 * Shows comprehensive match information with events and management
 */

import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import {
  ArrowLeftIcon,
  ArrowPathIcon,
  MapPinIcon,
  UserIcon,
  ClockIcon,
  PlayIcon,
  PencilIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline'
import {
  TrophyIcon,
  ExclamationTriangleIcon as WarningIcon,
  XCircleIcon,
  ArrowRightArrowLeftIcon,
} from '@heroicons/vue/24/solid'
import { useAuthStore } from '@/stores/auth'
import { matchAPI, apiHelpers } from '@/services/api'
import MainLayout from '@/components/layout/MainLayout.vue'

export default {
  name: 'MatchDetail',
  components: {
    MainLayout,
    ArrowLeftIcon,
    ArrowPathIcon,
    MapPinIcon,
    UserIcon,
    ClockIcon,
    PlayIcon,
    PencilIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
  },
  setup() {
    const route = useRoute()
    const authStore = useAuthStore()

    // Data
    const match = ref(null)
    const events = ref([])
    const isLoading = ref(false)
    const error = ref('')

    // Computed
    const canManageMatch = computed(() => {
      return authStore.isAdmin || 
             (authStore.isReferee && match.value?.referee_id === authStore.user?.id)
    })

    const sortedEvents = computed(() => {
      return [...events.value].sort((a, b) => {
        if (a.minute !== b.minute) return a.minute - b.minute
        return a.additional_time - b.additional_time
      })
    })

    /**
     * Fetch match data
     */
    const fetchMatch = async () => {
      isLoading.value = true
      error.value = ''

      try {
        const response = await matchAPI.getById(route.params.id)

        if (apiHelpers.isSuccess(response)) {
          const data = apiHelpers.getData(response)
          match.value = data
          events.value = data.events || []
        } else {
          error.value = 'Match not found'
        }
      } catch (err) {
        console.error('Failed to fetch match:', err)
        error.value = apiHelpers.handleError(err)
      } finally {
        isLoading.value = false
      }
    }

    /**
     * Refresh events
     */
    const refreshEvents = async () => {
      try {
        const response = await matchAPI.getEvents(route.params.id)
        if (apiHelpers.isSuccess(response)) {
          const data = apiHelpers.getData(response)
          events.value = data.events || []
        }
      } catch (err) {
        console.error('Failed to refresh events:', err)
      }
    }

    /**
     * Start match (change status to in_progress)
     */
    const startMatch = async () => {
      if (!confirm('Start this match?')) return

      try {
        const response = await matchAPI.update(match.value.id, {
          status: 'in_progress'
        })

        if (apiHelpers.isSuccess(response)) {
          match.value.status = 'in_progress'
          window.$notify?.success('Match started successfully')
        }
      } catch (err) {
        console.error('Failed to start match:', err)
        window.$notify?.error('Failed to start match')
      }
    }

    /**
     * Complete match
     */
    const completeMatch = async () => {
      if (!confirm('Mark this match as completed?')) return

      try {
        const response = await matchAPI.update(match.value.id, {
          status: 'completed'
        })

        if (apiHelpers.isSuccess(response)) {
          match.value.status = 'completed'
          window.$notify?.success('Match completed successfully')
        }
      } catch (err) {
        console.error('Failed to complete match:', err)
        window.$notify?.error('Failed to complete match')
      }
    }

    /**
     * Get winner team name
     */
    const getWinnerTeamName = () => {
      if (!match.value?.winner_team_id) return ''
      
      if (match.value.winner_team_id === match.value.home_team_id) {
        return match.value.home_team?.name
      } else {
        return match.value.away_team?.name
      }
    }

    /**
     * Get match duration
     */
    const getMatchDuration = () => {
      if (match.value?.status === 'completed') {
        return '90 min'
      } else if (match.value?.status === 'in_progress') {
        return 'Live'
      } else {
        return 'N/A'
      }
    }

    /**
     * Format match status
     */
    const formatStatus = (status) => {
      const statusMap = {
        scheduled: 'Scheduled',
        in_progress: 'Live',
        completed: 'Final',
        postponed: 'Postponed',
        cancelled: 'Cancelled',
      }
      return statusMap[status] || status
    }

    /**
     * Get status badge CSS classes
     */
    const getStatusBadgeClass = (status) => {
      const classMap = {
        scheduled: 'bg-primary-100 text-primary-800',
        in_progress: 'bg-success-100 text-success-800',
        completed: 'bg-secondary-100 text-secondary-800',
        postponed: 'bg-warning-100 text-warning-800',
        cancelled: 'bg-danger-100 text-danger-800',
      }
      return classMap[status] || classMap.scheduled
    }

    /**
     * Format event type
     */
    const formatEventType = (eventType) => {
      const eventMap = {
        goal: 'Goal',
        yellow_card: 'Yellow Card',
        red_card: 'Red Card',
        substitution: 'Substitution',
        own_goal: 'Own Goal',
      }
      return eventMap[eventType] || eventType
    }

    /**
     * Get event icon
     */
    const getEventIcon = (eventType) => {
      const iconMap = {
        goal: TrophyIcon,
        yellow_card: WarningIcon,
        red_card: XCircleIcon,
        substitution: ArrowRightArrowLeftIcon,
        own_goal: TrophyIcon,
      }
      return iconMap[eventType] || TrophyIcon
    }

    /**
     * Get event icon background class
     */
    const getEventIconClass = (eventType) => {
      const classMap = {
        goal: 'bg-success-100 text-success-600',
        yellow_card: 'bg-warning-100 text-warning-600',
        red_card: 'bg-danger-100 text-danger-600',
        substitution: 'bg-primary-100 text-primary-600',
        own_goal: 'bg-gray-100 text-gray-600',
      }
      return classMap[eventType] || classMap.goal
    }

    /**
     * Get event badge class
     */
    const getEventBadgeClass = (eventType) => {
      const classMap = {
        goal: 'bg-success-100 text-success-800',
        yellow_card: 'bg-warning-100 text-warning-800',
        red_card: 'bg-danger-100 text-danger-800',
        substitution: 'bg-primary-100 text-primary-800',
        own_goal: 'bg-gray-100 text-gray-800',
      }
      return classMap[eventType] || classMap.goal
    }

    /**
     * Format match date
     */
    const formatMatchDate = (dateString) => {
      if (!dateString) return 'TBD'
      return new Date(dateString).toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
      })
    }

    /**
     * Format match time
     */
    const formatMatchTime = (dateString) => {
      if (!dateString) return 'TBD'
      return new Date(dateString).toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
      })
    }

    // Initialize
    onMounted(() => {
      fetchMatch()
    })

    return {
      authStore,
      match,
      events,
      isLoading,
      error,
      canManageMatch,
      sortedEvents,
      refreshEvents,
      startMatch,
      completeMatch,
      getWinnerTeamName,
      getMatchDuration,
      formatStatus,
      getStatusBadgeClass,
      formatEventType,
      getEventIcon,
      getEventIconClass,
      getEventBadgeClass,
      formatMatchDate,
      formatMatchTime,
    }
  },
}
</script>

<style scoped>
.card-hover:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}
</style>