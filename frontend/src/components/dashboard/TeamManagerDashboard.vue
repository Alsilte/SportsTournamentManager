<template>
  <div class="space-y-8">
    <!-- My Teams Overview -->
    <div class="card p-6">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-900">My Teams</h2>
        <RouterLink to="/teams/create" class="btn-primary">
          <PlusIcon class="w-4 h-4 mr-2" />
          Create Team
        </RouterLink>
      </div>

      <div
        v-if="managedTeams.length > 0"
        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
      >
        <div
          v-for="team in managedTeams"
          :key="team.id"
          class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
        >
          <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold text-gray-900">{{ team.name }}</h3>
            <span
              :class="[
                'px-2 py-1 text-xs font-medium rounded-full',
                team.is_active ? 'bg-success-100 text-success-800' : 'bg-gray-100 text-gray-800',
              ]"
            >
              {{ team.is_active ? 'Active' : 'Inactive' }}
            </span>
          </div>

          <div class="space-y-2 text-sm text-gray-600 mb-4">
            <div class="flex items-center">
              <UsersIcon class="w-4 h-4 mr-2" />
              <span>{{ team.players_count || 0 }} players</span>
            </div>
            <div class="flex items-center">
              <CalendarIcon class="w-4 h-4 mr-2" />
              <span>{{ team.tournaments_count || 0 }} tournaments</span>
            </div>
            <div class="flex items-center">
              <TrophyIcon class="w-4 h-4 mr-2" />
              <span>{{ team.wins || 0 }} wins</span>
            </div>
          </div>

          <div class="flex space-x-2">
            <RouterLink
              :to="`/teams/${team.id}`"
              class="btn-primary text-xs px-3 py-1 flex-1 text-center"
            >
              View
            </RouterLink>
            <RouterLink
              :to="`/teams/${team.id}/roster`"
              class="btn-secondary text-xs px-3 py-1 flex-1 text-center"
            >
              Roster
            </RouterLink>
          </div>
        </div>

        <!-- Create new team card -->
        <RouterLink
          to="/teams/create"
          class="border-2 border-dashed border-gray-300 rounded-lg p-4 hover:border-primary-300 hover:bg-primary-50 transition-colors flex flex-col items-center justify-center text-center"
        >
          <PlusIcon class="w-8 h-8 text-gray-400 mb-2" />
          <span class="text-sm font-medium text-gray-600">Create New Team</span>
        </RouterLink>
      </div>

      <!-- Empty state for teams -->
      <div v-else class="text-center py-12">
        <UserGroupIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
        <h3 class="text-lg font-medium text-gray-900 mb-2">No teams yet</h3>
        <p class="text-gray-600 mb-6">
          Create your first team to start managing players and participating in tournaments.
        </p>
        <RouterLink to="/teams/create" class="btn-primary">
          <PlusIcon class="w-4 h-4 mr-2" />
          Create Your First Team
        </RouterLink>
      </div>
    </div>

    <!-- Upcoming Matches -->
    <div class="card p-6">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-900">Upcoming Matches</h2>
        <RouterLink
          to="/matches"
          class="text-primary-600 hover:text-primary-700 text-sm font-medium"
        >
          View All
        </RouterLink>
      </div>

      <div v-if="upcomingMatches.length > 0" class="space-y-4">
        <div
          v-for="match in upcomingMatches"
          :key="match.id"
          class="flex items-center justify-between p-4 bg-gray-50 rounded-lg"
        >
          <div class="flex items-center space-x-4">
            <div class="text-center">
              <div class="text-sm font-medium text-gray-900">
                {{ formatMatchDate(match.match_date) }}
              </div>
              <div class="text-xs text-gray-500">{{ formatMatchTime(match.match_date) }}</div>
            </div>
            <div class="flex items-center space-x-3">
              <div class="text-right">
                <div class="font-medium text-gray-900">{{ match.home_team?.name }}</div>
                <div class="text-xs text-gray-500">Home</div>
              </div>
              <div class="text-gray-400 font-bold">VS</div>
              <div class="text-left">
                <div class="font-medium text-gray-900">{{ match.away_team?.name }}</div>
                <div class="text-xs text-gray-500">Away</div>
              </div>
            </div>
          </div>
          <div class="text-right">
            <div v-if="match.venue" class="text-sm text-gray-600">{{ match.venue }}</div>
            <div class="text-xs text-gray-500">{{ match.tournament?.name }}</div>
          </div>
        </div>
      </div>

      <div v-else class="text-center py-8 text-gray-500">
        <CalendarIcon class="w-12 h-12 mx-auto mb-2 text-gray-300" />
        <p>No upcoming matches</p>
        <p class="text-sm mt-2">
          Matches will appear here once your teams are registered in tournaments.
        </p>
      </div>
    </div>

    <!-- Player Management -->
    <div class="card p-6">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-900">Player Management</h2>
        <button
          @click="showPlayerSearch = true"
          class="btn-secondary"
          :disabled="managedTeams.length === 0"
        >
          <MagnifyingGlassIcon class="w-4 h-4 mr-2" />
          Find Players
        </button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Recent Player Additions -->
        <div>
          <h3 class="text-sm font-medium text-gray-700 mb-3">Recent Additions</h3>
          <div v-if="recentPlayers.length > 0" class="space-y-3">
            <div
              v-for="player in recentPlayers"
              :key="player.id"
              class="flex items-center justify-between"
            >
              <div class="flex items-center">
                <div
                  class="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center mr-3"
                >
                  <UserIcon class="w-4 h-4 text-primary-600" />
                </div>
                <div>
                  <div class="text-sm font-medium text-gray-900">{{ player.user?.name }}</div>
                  <div class="text-xs text-gray-500">{{ player.position || 'No position' }}</div>
                </div>
              </div>
              <div class="text-xs text-gray-500">{{ player.team?.name }}</div>
            </div>
          </div>
          <div v-else class="text-center py-6 text-gray-500">
            <UserIcon class="w-8 h-8 mx-auto mb-2 text-gray-300" />
            <p class="text-sm">No recent player additions</p>
          </div>
        </div>

        <!-- Player Statistics -->
        <div>
          <h3 class="text-sm font-medium text-gray-700 mb-3">Player Stats</h3>
          <div class="space-y-3">
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Total Players</span>
              <span class="text-sm font-medium text-gray-900">{{ playerStats.total }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Active Players</span>
              <span class="text-sm font-medium text-gray-900">{{ playerStats.active }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Top Scorer</span>
              <span class="text-sm font-medium text-gray-900">{{
                playerStats.topScorer || 'N/A'
              }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Most Active</span>
              <span class="text-sm font-medium text-gray-900">{{
                playerStats.mostActive || 'N/A'
              }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tournament Registrations -->
    <div class="card p-6">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-900">Tournament Registrations</h2>
        <RouterLink to="/tournaments" class="btn-primary">
          <CalendarIcon class="w-4 h-4 mr-2" />
          Browse Tournaments
        </RouterLink>
      </div>

      <div v-if="tournamentRegistrations.length > 0" class="space-y-4">
        <div
          v-for="registration in tournamentRegistrations"
          :key="registration.id"
          class="flex items-center justify-between p-4 border border-gray-200 rounded-lg"
        >
          <div>
            <div class="font-medium text-gray-900">{{ registration.tournament?.name }}</div>
            <div class="text-sm text-gray-600">Team: {{ registration.team?.name }}</div>
            <div class="text-xs text-gray-500">
              Registered: {{ formatDate(registration.registration_date) }}
            </div>
          </div>
          <div class="text-right">
            <span
              :class="getRegistrationStatusClass(registration.status)"
              class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
            >
              {{ formatRegistrationStatus(registration.status) }}
            </span>
            <div class="text-xs text-gray-500 mt-1">
              {{
                registration.tournament?.start_date
                  ? formatDate(registration.tournament.start_date)
                  : 'TBD'
              }}
            </div>
          </div>
        </div>
      </div>

      <div v-else class="text-center py-8 text-gray-500">
        <CalendarIcon class="w-12 h-12 mx-auto mb-2 text-gray-300" />
        <p>No tournament registrations</p>
        <p class="text-sm mt-2">Register your teams for tournaments to compete with other teams.</p>
        <RouterLink to="/tournaments" class="btn-secondary mt-4">
          Browse Available Tournaments
        </RouterLink>
      </div>
    </div>
  </div>
</template>

<script>
/**
 * Team Manager Dashboard Component
 * Provides team management tools and overview for team managers
 */

import { ref, computed, onMounted } from 'vue'
import {
  PlusIcon,
  UsersIcon,
  CalendarIcon,
  TrophyIcon,
  UserIcon,
  MagnifyingGlassIcon,
  UserGroupIcon,
} from '@heroicons/vue/24/outline'
import { teamAPI, matchAPI, apiHelpers } from '@/services/api'
import { useAuthStore } from '@/stores/auth'

export default {
  name: 'TeamManagerDashboard',
  components: {
    PlusIcon,
    UsersIcon,
    CalendarIcon,
    TrophyIcon,
    UserIcon,
    MagnifyingGlassIcon,
    UserGroupIcon,
  },
  setup() {
    const authStore = useAuthStore()
    const showPlayerSearch = ref(false)

    // Data - only real data from APIs
    const managedTeams = ref([])
    const upcomingMatches = ref([])
    const recentPlayers = ref([])
    const tournamentRegistrations = ref([])

    // Player statistics computed from real data
    const playerStats = computed(() => {
      const total = managedTeams.value.reduce((sum, team) => sum + (team.players_count || 0), 0)
      const active = managedTeams.value.reduce((sum, team) => {
        return sum + (team.is_active ? team.players_count || 0 : 0)
      }, 0)

      return {
        total,
        active,
        topScorer: null, // Will be populated when player stats API is available
        mostActive: null, // Will be populated when player stats API is available
      }
    })

    /**
     * Fetch dashboard data - only from real APIs
     */
    const fetchDashboardData = async () => {
      try {
        // Fetch teams managed by current user
        const teamsResponse = await teamAPI.getAll({
          manager_id: authStore.user?.id,
          per_page: 50, // Get all managed teams
        })

        if (apiHelpers.isSuccess(teamsResponse)) {
          const data = apiHelpers.getData(teamsResponse)
          managedTeams.value = data.data || []
        } else {
          managedTeams.value = []
        }

        // Only fetch matches if user has teams
        if (managedTeams.value.length > 0) {
          // Get team IDs for filtering matches
          const teamIds = managedTeams.value.map((team) => team.id)

          // Fetch upcoming matches for managed teams
          const matchResponse = await matchAPI.getAll({
            status: 'scheduled',
            per_page: 10,
          })

          if (apiHelpers.isSuccess(matchResponse)) {
            const allMatches = apiHelpers.getData(matchResponse).data || []
            // Filter matches involving managed teams
            upcomingMatches.value = allMatches
              .filter(
                (match) =>
                  teamIds.includes(match.home_team_id) || teamIds.includes(match.away_team_id),
              )
              .slice(0, 5) // Limit to 5 matches
          } else {
            upcomingMatches.value = []
          }

          // TODO: Fetch recent players when team roster API is enhanced
          // For now, we'll need to call team roster for each team to get recent players
          await fetchRecentPlayers(teamIds)

          // TODO: Fetch tournament registrations when API is available
          // const registrationsResponse = await tournamentAPI.getTeamRegistrations(teamIds)
          tournamentRegistrations.value = [] // Empty until API is implemented
        }
      } catch (error) {
        console.error('Failed to fetch dashboard data:', error)
        window.$notify?.error('Failed to load dashboard data')

        // Reset to empty state on error
        managedTeams.value = []
        upcomingMatches.value = []
        recentPlayers.value = []
        tournamentRegistrations.value = []
      }
    }

    /**
     * Fetch recent players from team rosters
     */
    const fetchRecentPlayers = async (teamIds) => {
      try {
        const allRecentPlayers = []

        // Get roster for each team to find recent additions
        for (const teamId of teamIds.slice(0, 3)) {
          // Limit to first 3 teams for performance
          try {
            const rosterResponse = await teamAPI.getRoster(teamId)
            if (apiHelpers.isSuccess(rosterResponse)) {
              const rosterData = apiHelpers.getData(rosterResponse)
              const teamPlayers = rosterData.players || []

              // Sort by joined date and get most recent
              const recentTeamPlayers = teamPlayers
                .filter((player) => player.pivot?.joined_date)
                .sort((a, b) => new Date(b.pivot.joined_date) - new Date(a.pivot.joined_date))
                .slice(0, 2) // Get 2 most recent per team
                .map((player) => ({
                  ...player,
                  team: { name: managedTeams.value.find((t) => t.id === teamId)?.name },
                }))

              allRecentPlayers.push(...recentTeamPlayers)
            }
          } catch (error) {
            console.error(`Failed to fetch roster for team ${teamId}:`, error)
          }
        }

        // Sort all players by joined date and take top 5
        recentPlayers.value = allRecentPlayers
          .sort((a, b) => new Date(b.pivot.joined_date) - new Date(a.pivot.joined_date))
          .slice(0, 5)
      } catch (error) {
        console.error('Failed to fetch recent players:', error)
        recentPlayers.value = []
      }
    }

    /**
     * Format match date
     */
    const formatMatchDate = (dateString) => {
      if (!dateString) return 'TBD'
      return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
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

    /**
     * Format general date
     */
    const formatDate = (dateString) => {
      if (!dateString) return 'N/A'
      return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
      })
    }

    /**
     * Format registration status
     */
    const formatRegistrationStatus = (status) => {
      const statusMap = {
        pending: 'Pending',
        approved: 'Approved',
        rejected: 'Rejected',
      }
      return statusMap[status] || status
    }

    /**
     * Get CSS classes for registration status
     */
    const getRegistrationStatusClass = (status) => {
      const classMap = {
        pending: 'bg-warning-100 text-warning-800',
        approved: 'bg-success-100 text-success-800',
        rejected: 'bg-danger-100 text-danger-800',
      }
      return classMap[status] || classMap.pending
    }

    // Initialize dashboard
    onMounted(() => {
      fetchDashboardData()
    })

    return {
      authStore,
      showPlayerSearch,
      managedTeams,
      upcomingMatches,
      recentPlayers,
      tournamentRegistrations,
      playerStats,
      formatMatchDate,
      formatMatchTime,
      formatDate,
      formatRegistrationStatus,
      getRegistrationStatusClass,
    }
  },
}
</script>
