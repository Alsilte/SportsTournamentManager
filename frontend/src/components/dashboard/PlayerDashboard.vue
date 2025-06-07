<template>
  <div class="space-y-8">
    <!-- Player Profile Summary -->
    <div class="card p-6">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-900">{{ $t('dashboard.myProfile') }}</h2>
        <RouterLink to="/profile" class="btn-secondary">
          <PencilIcon class="w-4 h-4 mr-2" />
          {{ $t('common.edit') }}
        </RouterLink>
      </div>

      <div class="flex items-center space-x-6">
        <div class="w-20 h-20 bg-primary-100 rounded-full flex items-center justify-center">
          <UserIcon class="w-10 h-10 text-primary-600" />
        </div>
        <div class="flex-1">
          <h3 class="text-xl font-semibold text-gray-900">{{ playerProfile.name }}</h3>
          <p class="text-gray-600">{{ playerProfile.position || $t('common.unknown') }}</p>
          <div class="flex items-center space-x-4 mt-2 text-sm text-gray-500">
            <span v-if="playerProfile.age">{{ playerProfile.age }} {{ $t('dashboard.yearsOld') }}</span>
            <span v-if="playerProfile.nationality">{{ playerProfile.nationality }}</span>
            <span v-if="playerProfile.height">{{ playerProfile.height }}cm</span>
          </div>
        </div>
        <div class="text-right">
          <div class="text-2xl font-bold text-primary-600">{{ playerStats.totalGoals }}</div>
          <div class="text-sm text-gray-500">{{ $t('dashboard.totalGoals') }}</div>
        </div>
      </div>
    </div>

    <!-- My Teams -->
    <div class="card p-6">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-900">{{ $t('dashboard.myTeams') }}</h2>
        <span class="text-sm text-gray-500">{{ playerTeams.length }} {{ $t('dashboard.activeTeams') }}</span>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div
          v-for="team in playerTeams"
          :key="team.id"
          class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
        >
          <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold text-gray-900">{{ team.name }}</h3>
            <span
              v-if="team.pivot?.is_captain"
              class="bg-warning-100 text-warning-800 px-2 py-1 text-xs font-medium rounded-full"
            >
              {{ $t('teams.roster.captain') }}
            </span>
          </div>

          <div class="space-y-2 text-sm text-gray-600 mb-4">
            <div class="flex justify-between">
              <span>{{ $t('teams.roster.position') }}:</span>
              <span class="font-medium">{{ team.pivot?.position || $t('common.unknown') }}</span>
            </div>
            <div class="flex justify-between">
              <span>{{ $t('teams.roster.jerseyNumber') }}:</span>
              <span class="font-medium">{{ team.pivot?.jersey_number || $t('common.na') }}</span>
            </div>
            <div class="flex justify-between">
              <span>{{ $t('teams.roster.joinedDate') }}:</span>
              <span class="font-medium">{{ formatDate(team.pivot?.joined_date) }}</span>
            </div>
          </div>

          <RouterLink
            :to="`/teams/${team.id}`"
            class="btn-primary text-xs px-4 py-2 w-full text-center block"
          >
            {{ $t('common.view') }}
          </RouterLink>
        </div>

        <div v-if="playerTeams.length === 0" class="col-span-2 text-center py-8 text-gray-500">
          <UserGroupIcon class="w-12 h-12 mx-auto mb-2 text-gray-300" />
          <p>{{ $t('dashboard.noTeamsYet') }}</p>
        </div>
      </div>
    </div>

    <!-- My Statistics -->
    <div class="card p-6">
      <h2 class="text-xl font-semibold text-gray-900 mb-6">{{ $t('dashboard.myStatistics') }}</h2>

      <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-6">
        <div class="text-center">
          <div class="text-3xl font-bold text-primary-600">{{ playerStats.totalMatches }}</div>
          <div class="text-sm text-gray-500">
            {{ $t('dashboard.matchesPlayed') }}
          </div>
        </div>
        <div class="text-center">
          <div class="text-3xl font-bold text-success-600">{{ playerStats.totalGoals }}</div>
          <div class="text-sm text-gray-500">
            {{ $t('dashboard.goalsScored') }}
          </div>
        </div>
        <div class="text-center">
          <div class="text-3xl font-bold text-warning-600">{{ playerStats.yellowCards }}</div>
          <div class="text-sm text-gray-500">
            {{ $t('dashboard.yellowCards') }}
          </div>
        </div>
        <div class="text-center">
          <div class="text-3xl font-bold text-danger-600">{{ playerStats.redCards }}</div>
          <div class="text-sm text-gray-500">
            {{ $t('dashboard.redCards') }}
          </div>
        </div>
      </div>

      <div class="bg-gray-50 rounded-lg p-6 text-center">
        <ChartBarIcon class="w-12 h-12 text-gray-300 mx-auto mb-2" />
        <p class="text-gray-500 text-sm">{{ $t('dashboard.performancePlaceholder') }}</p>
      </div>
    </div>

    <!-- Upcoming Matches -->
    <div class="card p-6">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-900">{{ $t('dashboard.upcomingMatches') }}</h2>
        <RouterLink
          to="/matches/mine"
          class="text-primary-600 hover:text-primary-700 text-sm font-medium"
        >
          {{ $t('dashboard.viewAll') }}
        </RouterLink>
      </div>

      <div class="space-y-4">
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
                <div class="text-xs text-gray-500">
                  {{ $t('common.home') }}
                </div>
              </div>
              <div class="text-gray-400 font-bold">VS</div>
              <div class="text-left">
                <div class="font-medium text-gray-900">{{ match.away_team?.name }}</div>
                <div class="text-xs text-gray-500">
                  {{ $t('dashboard.away') }}
                </div>
              </div>
            </div>
          </div>
          <div class="text-right">
            <div v-if="match.venue" class="text-sm text-gray-600">{{ match.venue }}</div>
            <div class="text-xs text-gray-500">{{ match.tournament?.name }}</div>
            <span
              v-if="isMyTeamMatch(match)"
              class="inline-block mt-1 bg-primary-100 text-primary-800 px-2 py-1 text-xs font-medium rounded"
            >
              {{ $t('dashboard.myTeam') }}
            </span>
          </div>
        </div>

        <div v-if="upcomingMatches.length === 0" class="text-center py-8 text-gray-500">
          <CalendarIcon class="w-12 h-12 mx-auto mb-2 text-gray-300" />
          <p>{{ $t('dashboard.noUpcomingMatches') }}</p>
        </div>
      </div>
    </div>

    <!-- Recent Match Results -->
    <div class="card p-6">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-900">{{ $t('dashboard.recentMatches') }}</h2>
        <RouterLink
          to="/matches/history"
          class="text-primary-600 hover:text-primary-700 text-sm font-medium"
        >
          {{ $t('common.viewHistory') }}
        </RouterLink>
      </div>

      <div class="space-y-4">
        <div
          v-for="match in recentMatches"
          :key="match.id"
          class="flex items-center justify-between p-4 border border-gray-200 rounded-lg"
        >
          <div class="flex items-center space-x-4">
            <div class="flex items-center space-x-3">
              <div class="text-center">
                <div class="font-medium text-gray-900">{{ match.home_team?.name }}</div>
                <div class="text-xs text-gray-500">
                  {{ $t('common.home') }}
                </div>
              </div>
              <div class="text-center">
                <div class="text-xl font-bold text-gray-900">
                  {{ match.home_score }} - {{ match.away_score }}
                </div>
                <div class="text-xs text-gray-500">
                  {{ $t('dashboard.final') }}
                </div>
              </div>
              <div class="text-center">
                <div class="font-medium text-gray-900">{{ match.away_team?.name }}</div>
                <div class="text-xs text-gray-500">
                  {{ $t('dashboard.away') }}
                </div>
              </div>
            </div>
          </div>
          <div class="text-right">
            <div class="text-sm text-gray-600">{{ formatDate(match.match_date) }}</div>
            <div class="text-xs text-gray-500">{{ match.tournament?.name }}</div>
            <div v-if="getMatchResult(match)" class="mt-1">
              <span
                :class="getMatchResultClass(match)"
                class="inline-block px-2 py-1 text-xs font-medium rounded"
              >
                {{ getMatchResult(match) }}
              </span>
            </div>
          </div>
        </div>

        <div v-if="recentMatches.length === 0" class="text-center py-8 text-gray-500">
          <PlayIcon class="w-12 h-12 mx-auto mb-2 text-gray-300" />
          <p>{{ $t('dashboard.noRecentMatches') }}</p>
        </div>
      </div>
    </div>

    <!-- Achievements -->
    <div class="card p-6" v-if="hasRealAchievements">
      <h2 class="text-xl font-semibold text-gray-900 mb-6">{{ $t('dashboard.achievements') }}</h2>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- First Goal Achievement -->
        <div
          v-if="playerStats.totalGoals > 0"
          class="flex items-center p-4 bg-gradient-to-r from-primary-50 to-primary-100 rounded-lg"
        >
          <div class="w-12 h-12 bg-primary-600 rounded-full flex items-center justify-center mr-4">
            <StarIcon class="w-6 h-6 text-white" />
          </div>
          <div>
            <div class="font-medium text-gray-900">{{ $t('dashboard.achievementGoalScorer') }}</div>
            <div class="text-sm text-gray-600">{{ playerStats.totalGoals }} {{ $t('dashboard.goalsScored') }}</div>
          </div>
        </div>

        <!-- Team Captain Achievement -->
        <div
          v-if="isCaptain"
          class="flex items-center p-4 bg-gradient-to-r from-warning-50 to-warning-100 rounded-lg"
        >
          <div class="w-12 h-12 bg-warning-600 rounded-full flex items-center justify-center mr-4">
           <img src="../../../public/logo.png" alt="Team Captain Icon" class="w-6 h-6" />
          </div>
          <div>
            <div class="font-medium text-gray-900">{{ $t('teams.teamCaptain') }}</div>
            <div class="text-sm text-gray-600">{{ $t('dashboard.achievementLeadershipRole') }}</div>
          </div>
        </div>

        <!-- Experience Achievement -->
        <div
          v-if="playerStats.totalMatches >= 10"
          class="flex items-center p-4 bg-gradient-to-r from-success-50 to-success-100 rounded-lg"
        >
          <div class="w-12 h-12 bg-success-600 rounded-full flex items-center justify-center mr-4">
            <FireIcon class="w-6 h-6 text-white" />
          </div>
          <div>
            <div class="font-medium text-gray-900">{{ $t('dashboard.achievementExperiencedPlayer') }}</div>
            <div class="text-sm text-gray-600">{{ playerStats.totalMatches }} {{ $t('dashboard.matchesPlayed') }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- No achievements state -->
    <div class="card p-6" v-if="!hasRealAchievements">
      <h2 class="text-xl font-semibold text-gray-900 mb-6">{{ $t('dashboard.achievements') }}</h2>
      <div class="text-center py-8 text-gray-500">
        <TrophyIcon class="w-12 h-12 mx-auto mb-2 text-gray-300" />
        <p>{{ $t('dashboard.noAchievementsYet') }}</p>
      </div>
    </div>
  </div>
</template>

<script>
/**
 * Player Dashboard Component - API ONLY VERSION
 * Provides personalized dashboard for players with REAL stats, matches, and achievements
 */

import { ref, computed, onMounted } from 'vue'
import {
  UserIcon,
  PencilIcon,
  UserGroupIcon,
  CalendarIcon,
  PlayIcon,
  TrophyIcon,
  ChartBarIcon,
  StarIcon,
  FireIcon,
} from '@heroicons/vue/24/outline'
import { useAuthStore } from '@/stores/auth'
import { playerAPI, matchAPI, apiHelpers } from '@/services/api'

export default {
  name: 'PlayerDashboard',
  components: {
    UserIcon,
    PencilIcon,
    UserGroupIcon,
    CalendarIcon,
    PlayIcon,
    TrophyIcon,
    ChartBarIcon,
    StarIcon,
    FireIcon,
  },
  setup() {
    const authStore = useAuthStore()

    // Player data
    const playerProfile = ref({
      name: '',
      position: '',
      age: null,
      nationality: '',
      height: null,
    })

    const playerTeams = ref([])
    const upcomingMatches = ref([])
    const recentMatches = ref([])

    const playerStats = ref({
      totalMatches: 0,
      totalGoals: 0,
      yellowCards: 0,
      redCards: 0,
    })

    // Computed properties
    const myTeamIds = computed(() => playerTeams.value.map((team) => team.id))

    const isCaptain = computed(() => playerTeams.value.some((team) => team.pivot?.is_captain))

    const hasRealAchievements = computed(
      () =>
        playerStats.value.totalGoals > 0 || isCaptain.value || playerStats.value.totalMatches >= 10,
    )

    /**
     * Fetch player dashboard data
     */
    const fetchDashboardData = async () => {
      try {
        // Get user profile with player data
        if (authStore.user?.player?.id) {
          const playerResponse = await playerAPI.getById(authStore.user.player.id)
          if (apiHelpers.isSuccess(playerResponse)) {
            const playerData = apiHelpers.getData(playerResponse)
            playerProfile.value = {
              name: playerData.user?.name || authStore.userName,
              position: playerData.position,
              age: playerData.age,
              nationality: playerData.nationality,
              height: playerData.height,
            }
            playerTeams.value = playerData.active_teams || []
          }

          // Get player statistics
          const statsResponse = await playerAPI.getStatistics(authStore.user.player.id)
          if (apiHelpers.isSuccess(statsResponse)) {
            const stats = apiHelpers.getData(statsResponse).statistics
            playerStats.value = {
              totalMatches: stats.total_matches || 0,
              totalGoals: stats.goals || 0,
              yellowCards: stats.yellow_cards || 0,
              redCards: stats.red_cards || 0,
            }
          }
        }

        // Get upcoming matches for player's teams (only if has teams)
        if (myTeamIds.value.length > 0) {
          try {
            const matchResponse = await matchAPI.getAll({
              status: 'scheduled',
              per_page: 5,
            })
            if (apiHelpers.isSuccess(matchResponse)) {
              const allMatches = apiHelpers.getData(matchResponse).data || []
              upcomingMatches.value = allMatches.filter(
                (match) =>
                  myTeamIds.value.includes(match.home_team_id) ||
                  myTeamIds.value.includes(match.away_team_id),
              )
            }
          } catch (error) {
            console.error('Failed to fetch upcoming matches:', error)
            upcomingMatches.value = []
          }

          // Get recent completed matches
          try {
            const recentResponse = await matchAPI.getAll({
              status: 'completed',
              per_page: 5,
            })
            if (apiHelpers.isSuccess(recentResponse)) {
              const allCompleted = apiHelpers.getData(recentResponse).data || []
              recentMatches.value = allCompleted.filter(
                (match) =>
                  myTeamIds.value.includes(match.home_team_id) ||
                  myTeamIds.value.includes(match.away_team_id),
              )
            }
          } catch (error) {
            console.error('Failed to fetch recent matches:', error)
            recentMatches.value = []
          }
        }
      } catch (error) {
        console.error('Failed to fetch player dashboard data:', error)
        window.$notify?.error('Failed to load dashboard data')
      }
    }

    /**
     * Check if match involves player's team
     */
    const isMyTeamMatch = (match) => {
      return (
        myTeamIds.value.includes(match.home_team_id) || myTeamIds.value.includes(match.away_team_id)
      )
    }

    /**
     * Get match result for player's team
     */
    const getMatchResult = (match) => {
      const myTeamId = myTeamIds.value.find(
        (teamId) => teamId === match.home_team_id || teamId === match.away_team_id,
      )

      if (!myTeamId) return null

      const isHome = myTeamId === match.home_team_id
      const myScore = isHome ? match.home_score : match.away_score
      const opponentScore = isHome ? match.away_score : match.home_score

      if (myScore > opponentScore) return 'Win'
      if (myScore < opponentScore) return 'Loss'
      return 'Draw'
    }

    /**
     * Get CSS class for match result
     */
    const getMatchResultClass = (match) => {
      const result = getMatchResult(match)
      const classMap = {
        Win: 'bg-success-100 text-success-800',
        Loss: 'bg-danger-100 text-danger-800',
        Draw: 'bg-secondary-100 text-secondary-800',
      }
      return classMap[result] || ''
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

    // Initialize dashboard
    onMounted(() => {
      fetchDashboardData()
    })

    return {
      authStore,
      playerProfile,
      playerTeams,
      upcomingMatches,
      recentMatches,
      playerStats,
      isCaptain,
      hasRealAchievements,
      isMyTeamMatch,
      getMatchResult,
      getMatchResultClass,
      formatMatchDate,
      formatMatchTime,
      formatDate,
    }
  },
}
</script>
