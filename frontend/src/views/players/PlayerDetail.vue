<template>
  <MainLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex items-center space-x-4">
          <button @click="$router.go(-1)" class="btn-secondary p-2">
            <ArrowLeftIcon class="w-5 h-5" />
          </button>
          <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ player?.user?.name || 'Player Profile' }}</h1>
            <div class="flex items-center space-x-4 mt-2">
              <span
                v-if="player?.position"
                class="px-3 py-1 rounded-full text-sm font-medium bg-primary-100 text-primary-800"
              >
                {{ player.position }}
              </span>
              <span v-if="player?.nationality" class="text-gray-600">{{ player.nationality }}</span>
            </div>
          </div>
        </div>

        <div class="flex items-center space-x-3">
          <button
            v-if="canEditProfile"
            @click="showEditModal = true"
            class="btn-primary"
          >
            <PencilIcon class="w-4 h-4 mr-2" />
            Edit Profile
          </button>
        </div>
      </div>
    </template>

    <!-- Loading State -->
    <div v-if="isLoading" class="space-y-6">
      <div class="card p-6">
        <div class="animate-pulse">
          <div class="flex items-center space-x-6 mb-6">
            <div class="w-24 h-24 bg-gray-200 rounded-full"></div>
            <div class="space-y-2">
              <div class="h-6 bg-gray-200 rounded w-48"></div>
              <div class="h-4 bg-gray-200 rounded w-32"></div>
              <div class="h-4 bg-gray-200 rounded w-40"></div>
            </div>
          </div>
          <div class="grid grid-cols-4 gap-4 mb-6">
            <div class="h-16 bg-gray-200 rounded"></div>
            <div class="h-16 bg-gray-200 rounded"></div>
            <div class="h-16 bg-gray-200 rounded"></div>
            <div class="h-16 bg-gray-200 rounded"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Player Profile -->
    <div v-else-if="player" class="space-y-8">
      <!-- Profile Overview -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Player Details Card -->
          <div class="card p-6">
            <div class="flex items-center space-x-6 mb-6">
              <!-- Avatar -->
              <div class="relative">
                <div class="w-24 h-24 bg-primary-100 rounded-full flex items-center justify-center">
                  <UserIcon class="w-12 h-12 text-primary-600" />
                </div>
                <div 
                  v-if="player.active_teams?.length"
                  class="absolute -bottom-2 -right-2 w-8 h-8 bg-success-500 rounded-full flex items-center justify-center"
                >
                  <span class="text-white text-xs font-bold">{{ player.active_teams.length }}</span>
                </div>
              </div>

              <!-- Basic Info -->
              <div class="flex-1">
                <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ player.user?.name }}</h2>
                <div class="grid grid-cols-2 gap-4 text-sm">
                  <div v-if="player.age" class="flex items-center text-gray-600">
                    <CalendarIcon class="w-4 h-4 mr-2" />
                    <span>{{ player.age }} years old</span>
                  </div>
                  <div v-if="player.nationality" class="flex items-center text-gray-600">
                    <FlagIcon class="w-4 h-4 mr-2" />
                    <span>{{ player.nationality }}</span>
                  </div>
                  <div v-if="player.height" class="flex items-center text-gray-600">
                    <ArrowUpIcon class="w-4 h-4 mr-2" />
                    <span>{{ player.height }}cm</span>
                  </div>
                  <div v-if="player.weight" class="flex items-center text-gray-600">
                    <ScaleIcon class="w-4 h-4 mr-2" />
                    <span>{{ player.weight }}kg</span>
                  </div>
                  <div v-if="player.preferred_foot" class="flex items-center text-gray-600">
                    <HandRaisedIcon class="w-4 h-4 mr-2" />
                    <span class="capitalize">{{ player.preferred_foot }} footed</span>
                  </div>
                  <div v-if="player.user?.email" class="flex items-center text-gray-600">
                    <EnvelopeIcon class="w-4 h-4 mr-2" />
                    <span>{{ player.user.email }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Biography -->
            <div v-if="player.biography" class="pt-6 border-t border-gray-200">
              <h3 class="text-lg font-semibold text-gray-900 mb-3">About</h3>
              <p class="text-gray-700 leading-relaxed">{{ player.biography }}</p>
            </div>
          </div>

          <!-- Current Teams -->
          <div class="card p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Current Teams</h3>
            
            <div v-if="player.active_teams?.length" class="space-y-4">
              <div 
                v-for="team in player.active_teams" 
                :key="team.id"
                class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
              >
                <div class="flex items-center space-x-4">
                  <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center">
                    <UserGroupIcon class="w-6 h-6 text-primary-600" />
                  </div>
                  <div>
                    <h4 class="font-semibold text-gray-900">{{ team.name }}</h4>
                    <div class="flex items-center space-x-4 text-sm text-gray-600">
                      <span v-if="team.pivot?.position">{{ team.pivot.position }}</span>
                      <span v-if="team.pivot?.jersey_number">#{{ team.pivot.jersey_number }}</span>
                      <span v-if="team.pivot?.is_captain" class="text-warning-600 font-medium">
                        <StarIcon class="w-4 h-4 inline mr-1" />
                        Captain
                      </span>
                    </div>
                  </div>
                </div>
                <RouterLink 
                  :to="`/teams/${team.id}`"
                  class="btn-secondary text-xs px-3 py-1"
                >
                  View Team
                </RouterLink>
              </div>
            </div>

            <div v-else class="text-center py-8 text-gray-500">
              <UserGroupIcon class="w-12 h-12 mx-auto mb-2 text-gray-300" />
              <p>No active teams</p>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Statistics Card -->
          <div class="card p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistics</h3>
            <div class="space-y-4">
              <div class="text-center p-4 bg-primary-50 rounded-lg">
                <div class="text-2xl font-bold text-primary-600">{{ playerStats.total_matches || 0 }}</div>
                <div class="text-sm text-gray-600">Matches Played</div>
              </div>
              <div class="text-center p-4 bg-success-50 rounded-lg">
                <div class="text-2xl font-bold text-success-600">{{ playerStats.goals || 0 }}</div>
                <div class="text-sm text-gray-600">Goals Scored</div>
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div class="text-center p-3 bg-warning-50 rounded-lg">
                  <div class="text-lg font-bold text-warning-600">{{ playerStats.yellow_cards || 0 }}</div>
                  <div class="text-xs text-gray-600">Yellow Cards</div>
                </div>
                <div class="text-center p-3 bg-danger-50 rounded-lg">
                  <div class="text-lg font-bold text-danger-600">{{ playerStats.red_cards || 0 }}</div>
                  <div class="text-xs text-gray-600">Red Cards</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="card p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
            <div class="space-y-3">
              <RouterLink
                :to="`/players/${player.id}/statistics`"
                class="btn-secondary w-full text-center block"
              >
                <ChartBarIcon class="w-4 h-4 mr-2 inline" />
                Detailed Stats
              </RouterLink>
              <RouterLink
                :to="`/players/${player.id}/team-history`"
                class="btn-secondary w-full text-center block"
              >
                <ClockIcon class="w-4 h-4 mr-2 inline" />
                Team History
              </RouterLink>
              <RouterLink
                :to="`/players/${player.id}/matches`"
                class="btn-secondary w-full text-center block"
              >
                <PlayIcon class="w-4 h-4 mr-2 inline" />
                Match History
              </RouterLink>
            </div>
          </div>

          <!-- Achievement Highlights -->
          <div class="card p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Achievements</h3>
            <div class="space-y-3">
              <div 
                v-if="playerStats.goals && playerStats.goals > 0"
                class="flex items-center p-3 bg-gradient-to-r from-primary-50 to-primary-100 rounded-lg"
              >
                <div class="w-10 h-10 bg-primary-600 rounded-full flex items-center justify-center mr-3">
                  <TrophyIcon class="w-5 h-5 text-white" />
                </div>
                <div>
                  <div class="font-medium text-primary-900">Goal Scorer</div>
                  <div class="text-sm text-primary-700">{{ playerStats.goals }} goals scored</div>
                </div>
              </div>
              
              <div 
                v-if="player.active_teams?.some(t => t.pivot?.is_captain)"
                class="flex items-center p-3 bg-gradient-to-r from-warning-50 to-warning-100 rounded-lg"
              >
                <div class="w-10 h-10 bg-warning-600 rounded-full flex items-center justify-center mr-3">
                  <StarIcon class="w-5 h-5 text-white" />
                </div>
                <div>
                  <div class="font-medium text-warning-900">Team Captain</div>
                  <div class="text-sm text-warning-700">Leadership role</div>
                </div>
              </div>

              <div 
                v-if="playerStats.total_matches && playerStats.total_matches >= 10"
                class="flex items-center p-3 bg-gradient-to-r from-success-50 to-success-100 rounded-lg"
              >
                <div class="w-10 h-10 bg-success-600 rounded-full flex items-center justify-center mr-3">
                  <CheckCircleIcon class="w-5 h-5 text-white" />
                </div>
                <div>
                  <div class="font-medium text-success-900">Experienced Player</div>
                  <div class="text-sm text-success-700">{{ playerStats.total_matches }}+ matches</div>
                </div>
              </div>

              <div v-if="!hasAnyAchievements" class="text-center py-4 text-gray-500">
                <TrophyIcon class="w-8 h-8 mx-auto mb-2 text-gray-300" />
                <p class="text-sm">No achievements yet</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabs Navigation -->
      <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              'py-2 px-1 border-b-2 font-medium text-sm transition-colors',
              activeTab === tab.id
                ? 'border-primary-500 text-primary-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
            ]"
          >
            {{ tab.name }}
          </button>
        </nav>
      </div>

      <!-- Tab Content -->
      <div class="min-h-[300px]">
        <!-- Recent Matches Tab -->
        <div v-if="activeTab === 'matches'" class="space-y-4">
          <!-- Estado vacío - no hay endpoint para historial de partidos -->
          <div class="text-center py-12 text-gray-500">
            <PlayIcon class="w-12 h-12 mx-auto mb-2 text-gray-300" />
            <h3 class="text-lg font-medium text-gray-900 mb-2">Match History</h3>
            <p>Match history feature will be available once the backend endpoint is implemented.</p>
            <p class="text-sm text-gray-400 mt-2">
              Endpoint needed: <code class="bg-gray-100 px-2 py-1 rounded">GET /api/players/{id}/matches</code>
            </p>
          </div>
        </div>

        <!-- Performance Tab -->
        <div v-if="activeTab === 'performance'" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Performance Metrics -->
            <div class="card p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Performance Metrics</h3>
              <div class="space-y-4">
                <div class="flex justify-between items-center">
                  <span class="text-gray-600">Goals per Match</span>
                  <span class="font-medium">{{ goalsPerMatch }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-gray-600">Cards per Match</span>
                  <span class="font-medium">{{ cardsPerMatch }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-gray-600">Active Teams</span>
                  <span class="font-medium">{{ player.active_teams?.length || 0 }}</span>
                </div>
              </div>
            </div>

            <!-- Team History -->
            <div class="card p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Career Info</h3>
              <div class="space-y-3">
                <div v-if="player.user?.created_at" class="flex justify-between items-center">
                  <span class="text-gray-600">Joined Platform</span>
                  <span class="font-medium">{{ formatDate(player.user.created_at) }}</span>
                </div>
                <div v-if="player.date_of_birth" class="flex justify-between items-center">
                  <span class="text-gray-600">Date of Birth</span>
                  <span class="font-medium">{{ formatDate(player.date_of_birth) }}</span>
                </div>
                <div class="text-center py-4 text-gray-500">
                  <p class="text-sm">More career statistics will be available as the player participates in tournaments.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="text-center py-12">
      <ExclamationTriangleIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
      <h3 class="text-lg font-medium text-gray-900 mb-2">Player not found</h3>
      <p class="text-gray-600 mb-6">{{ error }}</p>
      <RouterLink to="/players" class="btn-primary">
        Back to Players
      </RouterLink>
    </div>
  </MainLayout>
</template>

<script>
/**
 * Player Detail Page Component
 * Shows comprehensive player profile with REAL statistics and match history only
 */

import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import {
  ArrowLeftIcon,
  PencilIcon,
  UserIcon,
  CalendarIcon,
  FlagIcon,
  ArrowUpIcon,
  ScaleIcon,
  HandRaisedIcon,
  EnvelopeIcon,
  UserGroupIcon,
  StarIcon,
  ChartBarIcon,
  ClockIcon,
  PlayIcon,
  TrophyIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline'
import { useAuthStore } from '@/stores/auth'
import { playerAPI, apiHelpers } from '@/services/api'
import MainLayout from '@/components/layout/MainLayout.vue'

export default {
  name: 'PlayerDetail',
  components: {
    MainLayout,
    ArrowLeftIcon,
    PencilIcon,
    UserIcon,
    CalendarIcon,
    FlagIcon,
    ArrowUpIcon,
    ScaleIcon,
    HandRaisedIcon,
    EnvelopeIcon,
    UserGroupIcon,
    StarIcon,
    ChartBarIcon,
    ClockIcon,
    PlayIcon,
    TrophyIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
  },
  setup() {
    const route = useRoute()
    const authStore = useAuthStore()

    // Data - Solo datos reales de la API
    const player = ref(null)
    const playerStats = ref({})
    const playerMatches = ref([])
    const isLoading = ref(false)
    const error = ref('')
    const activeTab = ref('matches')
    const showEditModal = ref(false)

    // Computed
    const canEditProfile = computed(() => {
      return authStore.isAdmin || player.value?.user_id === authStore.user?.id
    })

    const hasAnyAchievements = computed(() => {
      return (playerStats.value.goals > 0) || 
             (player.value?.active_teams?.some(t => t.pivot?.is_captain)) ||
             (playerStats.value.total_matches >= 10)
    })

    const goalsPerMatch = computed(() => {
      const matches = playerStats.value.total_matches || 0
      const goals = playerStats.value.goals || 0
      return matches > 0 ? (goals / matches).toFixed(2) : '0.00'
    })

    const cardsPerMatch = computed(() => {
      const matches = playerStats.value.total_matches || 0
      const cards = (playerStats.value.yellow_cards || 0) + (playerStats.value.red_cards || 0)
      return matches > 0 ? (cards / matches).toFixed(2) : '0.00'
    })

    const totalEvents = computed(() => {
      // Sin endpoint de eventos, retornamos 0
      return 0
    })

    const tabs = [
      { id: 'matches', name: 'Match History' },
      { id: 'performance', name: 'Performance' },
    ]

    /**
     * Fetch player data - SOLO DATOS REALES
     */
    const fetchPlayer = async () => {
      isLoading.value = true
      error.value = ''

      try {
        const response = await playerAPI.getById(route.params.id)

        if (apiHelpers.isSuccess(response)) {
          const data = apiHelpers.getData(response)
          player.value = data
          
          // Fetch additional real data (solo estadísticas)
          await fetchPlayerStats()
          // No llamamos fetchPlayerMatches() porque no existe el endpoint
        } else {
          error.value = 'Player not found'
        }
      } catch (err) {
        console.error('Failed to fetch player:', err)
        error.value = apiHelpers.handleError(err)
      } finally {
        isLoading.value = false
      }
    }

    /**
     * Fetch player statistics - SOLO DATOS REALES
     */
    const fetchPlayerStats = async () => {
      try {
        const response = await playerAPI.getStatistics(route.params.id)
        
        if (apiHelpers.isSuccess(response)) {
          const data = apiHelpers.getData(response)
          playerStats.value = data.statistics || {}
        } else {
          // Si no hay estadísticas, dejamos el objeto vacío
          playerStats.value = {}
        }
      } catch (err) {
        console.error('Failed to fetch player stats:', err)
        playerStats.value = {}
      }
    }

    /**
     * Fetch player match events - NO DISPONIBLE EN API
     * Mantenemos el array vacío hasta que se implemente el endpoint
     */
    const fetchPlayerMatches = async () => {
      // No hay endpoint específico para historial de partidos del jugador
      // Solo mostramos mensaje de "no data available"
      playerMatches.value = []
    }

    /**
     * Format event type for display
     */
    const formatEventType = (eventType) => {
      const eventMap = {
        'goal': 'Goal',
        'yellow_card': 'Yellow Card',
        'red_card': 'Red Card',
        'substitution': 'Substitution',
        'own_goal': 'Own Goal'
      }
      return eventMap[eventType] || eventType
    }

    /**
     * Get event type color classes
     */
    const getEventTypeColor = (eventType) => {
      const colorMap = {
        'goal': 'bg-success-100 text-success-800',
        'yellow_card': 'bg-warning-100 text-warning-800',
        'red_card': 'bg-danger-100 text-danger-800',
        'substitution': 'bg-primary-100 text-primary-800',
        'own_goal': 'bg-gray-100 text-gray-800'
      }
      return colorMap[eventType] || 'bg-gray-100 text-gray-800'
    }

    /**
     * Format date for display
     */
    const formatDate = (dateString) => {
      if (!dateString) return 'N/A'
      return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric'
      })
    }

    /**
     * Format time for display
     */
    const formatTime = (dateString) => {
      if (!dateString) return 'N/A'
      return new Date(dateString).toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit'
      })
    }

    // Initialize
    onMounted(() => {
      fetchPlayer()
    })

    return {
      authStore,
      player,
      playerStats,
      playerMatches,
      isLoading,
      error,
      activeTab,
      showEditModal,
      canEditProfile,
      hasAnyAchievements,
      goalsPerMatch,
      cardsPerMatch,
      totalEvents,
      tabs,
      formatEventType,
      getEventTypeColor,
      formatDate,
      formatTime
    }
  }
}
</script>