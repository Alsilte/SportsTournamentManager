<template>
  <MainLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex items-center space-x-4">
          <button @click="$router.go(-1)" class="btn-secondary p-2">
            <ArrowLeftIcon class="w-5 h-5" />
          </button>
          <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ team?.name }}</h1>
            <div class="flex items-center space-x-4 mt-2">
              <span
                :class="[
                  'px-3 py-1 rounded-full text-sm font-medium',
                  team?.is_active ? 'bg-success-100 text-success-800' : 'bg-gray-100 text-gray-800',
                ]"
              >
                {{ team?.is_active ? 'Active' : 'Inactive' }}
              </span>
              <span v-if="team?.short_name" class="text-gray-600">{{ team.short_name }}</span>
            </div>
          </div>
        </div>

        <div class="flex items-center space-x-3">
          <RouterLink v-if="canManageTeam" :to="`/teams/${team?.id}/roster`" class="btn-secondary">
            <UsersIcon class="w-4 h-4 mr-2" />
            Manage Roster
          </RouterLink>

          <button v-if="canManageTeam" @click="showEditModal = true" class="btn-primary">
            <PencilIcon class="w-4 h-4 mr-2" />
            Edit Team
          </button>
        </div>
      </div>
    </template>

    <!-- Loading State -->
    <div v-if="isLoading" class="space-y-6">
      <div class="card p-6">
        <div class="animate-pulse space-y-4">
          <div class="h-4 bg-gray-200 rounded w-3/4"></div>
          <div class="h-4 bg-gray-200 rounded w-1/2"></div>
          <div class="h-32 bg-gray-200 rounded"></div>
        </div>
      </div>
    </div>

    <!-- Team Details -->
    <div v-else-if="team" class="space-y-8">
      <!-- Team Overview -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Description -->
          <div class="card p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">About this Team</h2>
            <p class="text-gray-700 leading-relaxed">
              {{ team.description || 'No description available.' }}
            </p>
          </div>

          <!-- Recent Matches -->
          <div class="card p-6">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-xl font-semibold text-gray-900">Recent Matches</h2>
              <RouterLink
                :to="`/teams/${team.id}/matches`"
                class="text-primary-600 hover:text-primary-700 text-sm font-medium"
              >
                View All
              </RouterLink>
            </div>

            <div v-if="recentMatches.length" class="space-y-4">
              <div
                v-for="match in recentMatches"
                :key="match.id"
                class="flex items-center justify-between p-4 bg-gray-50 rounded-lg"
              >
                <div class="flex items-center space-x-4">
                  <div class="flex items-center space-x-3">
                    <div class="text-center">
                      <div class="font-medium text-gray-900">{{ match.home_team?.name }}</div>
                      <div class="text-xs text-gray-500">Home</div>
                    </div>
                    <div class="text-center">
                      <div class="text-xl font-bold text-gray-900">
                        {{ match.home_score }} - {{ match.away_score }}
                      </div>
                      <div class="text-xs text-gray-500">Final</div>
                    </div>
                    <div class="text-center">
                      <div class="font-medium text-gray-900">{{ match.away_team?.name }}</div>
                      <div class="text-xs text-gray-500">Away</div>
                    </div>
                  </div>
                </div>
                <div class="text-right">
                  <div class="text-sm text-gray-600">{{ formatDate(match.match_date) }}</div>
                  <div class="text-xs text-gray-500">{{ match.tournament?.name }}</div>
                </div>
              </div>
            </div>

            <div v-else class="text-center py-8 text-gray-500">
              <PlayIcon class="w-12 h-12 mx-auto mb-2 text-gray-300" />
              <p>No recent matches</p>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Team Stats -->
          <div class="card p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Team Statistics</h3>
            <div class="space-y-4">
              <div class="flex justify-between">
                <span class="text-gray-600">Founded</span>
                <span class="font-medium">{{ team.founded_year || 'Unknown' }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Players</span>
                <span class="font-medium">{{ team.players_count || 0 }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Tournaments</span>
                <span class="font-medium">{{ activeTournaments }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Win Rate</span>
                <span class="font-medium">{{ team.win_rate || 0 }}%</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Total Goals</span>
                <span class="font-medium">{{ team.total_goals || 0 }}</span>
              </div>
            </div>
          </div>

          <!-- Contact Info -->
          <div class="card p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Contact Information</h3>
            <div class="space-y-3">
              <div class="flex items-center">
                <UserIcon class="w-5 h-5 text-gray-400 mr-3" />
                <div>
                  <div class="font-medium text-gray-900">{{ team.manager?.name }}</div>
                  <div class="text-sm text-gray-600">Team Manager</div>
                </div>
              </div>
              <div class="flex items-center">
                <EnvelopeIcon class="w-5 h-5 text-gray-400 mr-3" />
                <div class="text-sm text-gray-600">{{ team.contact_email }}</div>
              </div>
              <div v-if="team.contact_phone" class="flex items-center">
                <PhoneIcon class="w-5 h-5 text-gray-400 mr-3" />
                <div class="text-sm text-gray-600">{{ team.contact_phone }}</div>
              </div>
              <div v-if="team.home_venue" class="flex items-center">
                <MapPinIcon class="w-5 h-5 text-gray-400 mr-3" />
                <div class="text-sm text-gray-600">{{ team.home_venue }}</div>
              </div>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="card p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
            <div class="space-y-3">
              <RouterLink
                :to="`/teams/${team.id}/roster`"
                class="btn-secondary w-full text-center block"
              >
                <UsersIcon class="w-4 h-4 mr-2 inline" />
                View Roster
              </RouterLink>
              <RouterLink
                :to="`/teams/${team.id}/statistics`"
                class="btn-secondary w-full text-center block"
              >
                <ChartBarIcon class="w-4 h-4 mr-2 inline" />
                View Statistics
              </RouterLink>
              <RouterLink
                :to="`/teams/${team.id}/matches`"
                class="btn-secondary w-full text-center block"
              >
                <PlayIcon class="w-4 h-4 mr-2 inline" />
                Match History
              </RouterLink>
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
            <span
              v-if="tab.count !== undefined"
              class="ml-2 px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-600"
            >
              {{ tab.count }}
            </span>
          </button>
        </nav>
      </div>

      <!-- Tab Content -->
      <div class="min-h-[400px]">
        <!-- Players Tab -->
        <div v-if="activeTab === 'players'" class="space-y-6">
          <div
            v-if="teamPlayers.length"
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
          >
            <div
              v-for="player in teamPlayers"
              :key="player.id"
              class="card p-6 hover:shadow-card-hover transition-shadow"
            >
              <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                  <div
                    class="w-12 h-12 bg-primary-100 rounded-full flex items-center justify-center mr-4"
                  >
                    <UserIcon class="w-6 h-6 text-primary-600" />
                  </div>
                  <div>
                    <h3 class="font-semibold text-gray-900">{{ player.user?.name }}</h3>
                    <p class="text-sm text-gray-600">
                      {{ player.pivot?.position || 'No position' }}
                    </p>
                  </div>
                </div>
                <div class="text-center">
                  <div class="text-lg font-bold text-primary-600">
                    {{ player.pivot?.jersey_number || '-' }}
                  </div>
                  <div class="text-xs text-gray-500">Jersey</div>
                </div>
              </div>

              <div class="space-y-2 text-sm text-gray-600 mb-4">
                <div v-if="player.pivot?.is_captain" class="flex items-center">
                  <StarIcon class="w-4 h-4 mr-2 text-warning-500" />
                  <span class="font-medium text-warning-700">Team Captain</span>
                </div>
                <div class="flex justify-between">
                  <span>Joined:</span>
                  <span class="font-medium">{{ formatDate(player.pivot?.joined_date) }}</span>
                </div>
              </div>

              <RouterLink
                :to="`/players/${player.id}`"
                class="btn-secondary text-xs px-3 py-1 w-full text-center block"
              >
                View Profile
              </RouterLink>
            </div>
          </div>

          <div v-else class="text-center py-12">
            <UsersIcon class="w-12 h-12 text-gray-300 mx-auto mb-4" />
            <h3 class="text-lg font-medium text-gray-900 mb-2">No players registered</h3>
            <p class="text-gray-600">Start building your team by adding players</p>
            <RouterLink
              v-if="canManageTeam"
              :to="`/teams/${team.id}/roster`"
              class="btn-primary mt-4"
            >
              Add Players
            </RouterLink>
          </div>
        </div>

        <!-- Tournaments Tab -->
        <div v-if="activeTab === 'tournaments'" class="space-y-6">
          <div v-if="teamTournaments.length" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div
              v-for="tournament in teamTournaments"
              :key="tournament.id"
              class="card p-6 hover:shadow-card-hover transition-shadow"
            >
              <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-900">{{ tournament.name }}</h3>
                <span
                  :class="getTournamentStatusClass(tournament.status)"
                  class="px-2 py-1 text-xs font-medium rounded-full"
                >
                  {{ formatTournamentStatus(tournament.status) }}
                </span>
              </div>

              <div class="space-y-2 text-sm text-gray-600 mb-4">
                <div class="flex justify-between">
                  <span>Sport:</span>
                  <span class="font-medium">{{ tournament.sport_type }}</span>
                </div>
                <div class="flex justify-between">
                  <span>Format:</span>
                  <span class="font-medium">{{
                    formatTournamentType(tournament.tournament_type)
                  }}</span>
                </div>
                <div class="flex justify-between">
                  <span>Start Date:</span>
                  <span class="font-medium">{{ formatDate(tournament.start_date) }}</span>
                </div>
              </div>

              <RouterLink
                :to="`/tournaments/${tournament.id}`"
                class="btn-secondary text-xs px-3 py-1 w-full text-center block"
              >
                View Tournament
              </RouterLink>
            </div>
          </div>

          <div v-else class="text-center py-12">
            <TrophyIcon class="w-12 h-12 text-gray-300 mx-auto mb-4" />
            <h3 class="text-lg font-medium text-gray-900 mb-2">No tournaments</h3>
            <p class="text-gray-600">This team hasn't participated in any tournaments yet</p>
          </div>
        </div>

        <!-- Statistics Tab -->
        <div v-if="activeTab === 'statistics'" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="card p-6 text-center">
              <div class="text-3xl font-bold text-primary-600">
                {{ teamStats.total_matches || 0 }}
              </div>
              <div class="text-sm text-gray-600">Total Matches</div>
            </div>
            <div class="card p-6 text-center">
              <div class="text-3xl font-bold text-success-600">{{ teamStats.wins || 0 }}</div>
              <div class="text-sm text-gray-600">Wins</div>
            </div>
            <div class="card p-6 text-center">
              <div class="text-3xl font-bold text-warning-600">{{ teamStats.draws || 0 }}</div>
              <div class="text-sm text-gray-600">Draws</div>
            </div>
            <div class="card p-6 text-center">
              <div class="text-3xl font-bold text-danger-600">{{ teamStats.losses || 0 }}</div>
              <div class="text-sm text-gray-600">Losses</div>
            </div>
          </div>

          <!-- More detailed stats -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="card p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Offensive Stats</h3>
              <div class="space-y-3">
                <div class="flex justify-between">
                  <span class="text-gray-600">Clean Sheets</span>
                  <span class="font-medium">{{ teamStats.clean_sheets || 0 }}</span>
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
      <h3 class="text-lg font-medium text-gray-900 mb-2">Team not found</h3>
      <p class="text-gray-600 mb-6">{{ error }}</p>
      <RouterLink to="/teams" class="btn-primary"> Back to Teams </RouterLink>
    </div>
  </MainLayout>
</template>

<script>
/**
 * Team Detail Page Component
 * Shows comprehensive team information with players, tournaments, and statistics
 */

import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import {
  ArrowLeftIcon,
  PencilIcon,
  UsersIcon,
  UserIcon,
  EnvelopeIcon,
  PhoneIcon,
  MapPinIcon,
  PlayIcon,
  ChartBarIcon,
  TrophyIcon,
  StarIcon,
  ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline'
import { useAuthStore } from '@/stores/auth'
import { teamAPI, apiHelpers } from '@/services/api'
import MainLayout from '@/components/layout/MainLayout.vue'

export default {
  name: 'TeamDetail',
  components: {
    MainLayout,
    ArrowLeftIcon,
    PencilIcon,
    UsersIcon,
    UserIcon,
    EnvelopeIcon,
    PhoneIcon,
    MapPinIcon,
    PlayIcon,
    ChartBarIcon,
    TrophyIcon,
    StarIcon,
    ExclamationTriangleIcon,
  },
  setup() {
    const route = useRoute()
    const authStore = useAuthStore()

    // Data
    const team = ref(null)
    const teamPlayers = ref([])
    const teamTournaments = ref([])
    const recentMatches = ref([])
    const teamStats = ref({})
    const isLoading = ref(false)
    const error = ref('')
    const activeTab = ref('players')
    const showEditModal = ref(false)

    // Computed
    const canManageTeam = computed(() => {
      return authStore.isAdmin || team.value?.manager_id === authStore.user?.id
    })

    const activeTournaments = computed(() => {
      return teamTournaments.value.filter((t) =>
        ['registration_open', 'in_progress'].includes(t.status),
      ).length
    })

    const tabs = computed(() => [
      { id: 'players', name: 'Players', count: teamPlayers.value.length },
      { id: 'tournaments', name: 'Tournaments', count: teamTournaments.value.length },
      { id: 'statistics', name: 'Statistics' },
    ])

    /**
     * Fetch team data
     */
    const fetchTeam = async () => {
      isLoading.value = true
      error.value = ''

      try {
        const response = await teamAPI.getById(route.params.id)

        if (apiHelpers.isSuccess(response)) {
          const data = apiHelpers.getData(response)
          team.value = data
          recentMatches.value = data.recent_matches || []
        } else {
          error.value = 'Team not found'
        }
      } catch (err) {
        console.error('Failed to fetch team:', err)
        error.value = apiHelpers.handleError(err)
      } finally {
        isLoading.value = false
      }
    }

    /**
     * Fetch team roster
     */
    const fetchRoster = async () => {
      try {
        const response = await teamAPI.getRoster(route.params.id)

        if (apiHelpers.isSuccess(response)) {
          const data = apiHelpers.getData(response)
          teamPlayers.value = data.players || []
        }
      } catch (err) {
        console.error('Failed to fetch roster:', err)
      }
    }

    /**
     * Fetch team statistics
     */
    const fetchStatistics = async () => {
      try {
        const response = await teamAPI.getStatistics(route.params.id)

        if (apiHelpers.isSuccess(response)) {
          const data = apiHelpers.getData(response)
          teamStats.value = data.overall_statistics || {}
          teamTournaments.value = data.tournament_statistics || []
        }
      } catch (err) {
        console.error('Failed to fetch statistics:', err)
        // Mock data for demo
        teamStats.value = {
          total_matches: 15,
          wins: 8,
          draws: 4,
          losses: 3,
          goals_for: 24,
          goals_against: 18,
          goals_per_match: 1.6,
          goals_conceded_per_match: 1.2,
          clean_sheets: 5,
          top_scorer: 'John Smith (8 goals)',
        }

        teamTournaments.value = [
          {
            id: 1,
            name: 'Spring League',
            status: 'in_progress',
            sport_type: 'Football',
            tournament_type: 'league',
            start_date: '2024-03-15',
          },
          {
            id: 2,
            name: 'Summer Cup',
            status: 'completed',
            sport_type: 'Football',
            tournament_type: 'knockout',
            start_date: '2024-06-01',
          },
        ]
      }
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
     * Get tournament status CSS classes
     */
    const getTournamentStatusClass = (status) => {
      const classMap = {
        draft: 'bg-gray-100 text-gray-800',
        registration_open: 'bg-success-100 text-success-800',
        in_progress: 'bg-primary-100 text-primary-800',
        completed: 'bg-secondary-100 text-secondary-800',
        cancelled: 'bg-danger-100 text-danger-800',
      }
      return classMap[status] || classMap.draft
    }

    /**
     * Format tournament type
     */
    const formatTournamentType = (type) => {
      const typeMap = {
        league: 'League',
        knockout: 'Knockout',
        group_knockout: 'Group + Knockout',
      }
      return typeMap[type] || type
    }

    /**
     * Format date for display
     */
    const formatDate = (dateString) => {
      if (!dateString) return 'N/A'
      return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
      })
    }

    // Initialize
    onMounted(() => {
      fetchTeam()
      fetchRoster()
      fetchStatistics()
    })

    return {
      authStore,
      team,
      teamPlayers,
      teamTournaments,
      recentMatches,
      teamStats,
      isLoading,
      error,
      activeTab,
      showEditModal,
      canManageTeam,
      activeTournaments,
      tabs,
      formatTournamentStatus,
      getTournamentStatusClass,
      formatTournamentType,
      formatDate,
    }
  },
}
</script>
