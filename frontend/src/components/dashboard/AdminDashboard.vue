<template>
  <div class="space-y-8">
    <!-- System Overview -->
    <div class="card p-6">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-900">{{ $t('dashboard.systemOverview') }}</h2>
        <button @click="refreshData" :disabled="isLoading" class="btn-secondary flex items-center">
          <ArrowPathIcon :class="['w-4 h-4 mr-2', isLoading ? 'animate-spin' : '']" />
          {{ $t('common.refresh') }}
        </button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="text-center p-4 bg-primary-50 rounded-lg">
          <CalendarIcon class="w-8 h-8 text-primary-600 mx-auto mb-2" />
          <div class="text-2xl font-bold text-primary-600">{{ systemStats.totalTournaments }}</div>
          <div class="text-sm text-gray-600">{{ $t('dashboard.totalTournaments') }}</div>
        </div>
        <div class="text-center p-4 bg-success-50 rounded-lg">
          <UserGroupIcon class="w-8 h-8 text-success-600 mx-auto mb-2" />
          <div class="text-2xl font-bold text-success-600">{{ systemStats.totalTeams }}</div>
          <div class="text-sm text-gray-600">{{ $t('dashboard.registeredTeams') }}</div>
        </div>
        <div class="text-center p-4 bg-warning-50 rounded-lg">
          <UsersIcon class="w-8 h-8 text-warning-600 mx-auto mb-2" />
          <div class="text-2xl font-bold text-warning-600">{{ systemStats.totalUsers }}</div>
          <div class="text-sm text-gray-600">{{ $t('dashboard.activeUsers') }}</div>
        </div>
      </div>
    </div>

    <!-- Recent Tournaments -->
    <div class="card p-6">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-900">{{ $t('dashboard.recentTournaments') }}</h2>
        <RouterLink to="/tournaments/create" class="btn-primary">
          <PlusIcon class="w-4 h-4 mr-2" />
          {{ $t('tournaments.create') || 'Crear Torneo' }}
        </RouterLink>
      </div>

      <div v-if="recentTournaments.length > 0" class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('tournaments.name') }}</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('common.status') }}</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teams</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('tournaments.startDate') }}</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('common.actions') }}</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="tournament in recentTournaments" :key="tournament.id">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ tournament.name }}</div>
                <div class="text-sm text-gray-500">{{ tournament.sport_type }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="getStatusBadgeClass(tournament.status)"
                  class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                >
                  {{ formatStatus(tournament.status) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ tournament.registered_teams_count || 0 }}/{{ tournament.max_teams }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatDate(tournament.start_date) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <RouterLink :to="`/tournaments/${tournament.id}`" class="text-primary-600 hover:text-primary-900 mr-3">{{ $t('common.view') }}</RouterLink>
                <button @click="editTournament(tournament.id)" class="text-secondary-600 hover:text-secondary-900">{{ $t('common.edit') }}</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Empty state for tournaments -->
      <div v-else class="text-center py-8 text-gray-500">
        <CalendarIcon class="w-12 h-12 mx-auto mb-2 text-gray-300" />
        <p>{{ $t('tournaments.noTournaments') }}</p>
        <RouterLink to="/tournaments/create" class="btn-primary mt-4">{{ $t('tournaments.createFirst') }}</RouterLink>
      </div>
    </div>

    <!-- Pending Actions -->
    <div class="card p-6">
      <h2 class="text-xl font-semibold text-gray-900 mb-6">{{ $t('dashboard.pendingActions') }}</h2>

      <div class="space-y-4">
        <div v-for="action in pendingActions" :key="action.id" class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
          <div class="flex items-center">
            <div
              :class="[
                'w-10 h-10 rounded-full flex items-center justify-center mr-4',
                action.bgColor,
              ]"
            >
              <component :is="action.icon" :class="['w-5 h-5', action.iconColor]" />
            </div>
            <div>
              <div class="text-sm font-medium text-gray-900">{{ action.title }}</div>
              <div class="text-sm text-gray-500">{{ action.description }}</div>
            </div>
          </div>
          <div class="flex space-x-2">
            <button
              v-if="action.type === 'team_registration'"
              @click="approveTeamRegistration(action.id)"
              class="btn-success text-xs px-3 py-1"
            >
              {{ $t('common.approve') }}
            </button>
            <button
              v-if="action.type === 'team_registration'"
              @click="rejectTeamRegistration(action.id)"
              class="btn-danger text-xs px-3 py-1"
            >
              {{ $t('common.reject') }}
            </button>
            <button
              v-if="action.type === 'match_result'"
              @click="reviewMatchResult(action.id)"
              class="btn-primary text-xs px-3 py-1"
            >
              {{ $t('common.review') }}
            </button>
          </div>
        </div>

        <div v-if="pendingActions.length === 0" class="text-center py-8 text-gray-500">
          <CheckCircleIcon class="w-12 h-12 mx-auto mb-2 text-gray-300" />
          <p>{{ $t('dashboard.noPendingActions') }}</p>
        </div>
      </div>
    </div>

  
  </div>
</template>

<script>
/**
 * Admin Dashboard Component
 * Provides comprehensive system overview and management tools for administrators
 */

import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import {
  CalendarIcon,
  UserGroupIcon,
  UsersIcon,
  PlusIcon,
  ArrowPathIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon,
  ClockIcon,
} from '@heroicons/vue/24/outline'
import { tournamentAPI, apiHelpers } from '@/services/api'

export default {
  name: 'AdminDashboard',
  components: {
    CalendarIcon,
    UserGroupIcon,
    UsersIcon,
    PlusIcon,
    ArrowPathIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
    ClockIcon,
  },
  setup() {
    const router = useRouter()
    const isLoading = ref(false)

    // System statistics - only real data
    const systemStats = ref({
      totalTournaments: 0,
      totalTeams: 0,
      totalUsers: 0,
      activeSessions: 0,
    })

    // Recent tournaments - only real data
    const recentTournaments = ref([])

    // Pending actions - will be populated from real APIs when available
    const pendingActions = ref([])

    // Computed properties
    const lastBackupTime = computed(() => {
      // This would come from a real system monitoring API
      return 'Not available'
    })

    /**
     * Fetch dashboard data - only from real APIs
     */
    const fetchDashboardData = async () => {
      isLoading.value = true
      try {
        // Fetch recent tournaments
        const tournamentResponse = await tournamentAPI.getAll({ per_page: 5 })
        if (apiHelpers.isSuccess(tournamentResponse)) {
          const data = apiHelpers.getData(tournamentResponse)
          recentTournaments.value = data.data || []

          // Update tournament count from real data
          systemStats.value.totalTournaments = data.total || recentTournaments.value.length
        }

        // TODO: Implement these APIs when available
        // const teamsResponse = await teamAPI.getAll({ per_page: 1 })
        // const usersResponse = await userAPI.getAll({ per_page: 1 })
        // const sessionsResponse = await systemAPI.getActiveSessions()

        // For now, we can only count what we have
        systemStats.value.totalTeams = 0 // Will be updated when team API is called
        systemStats.value.totalUsers = 0 // Will be updated when user API is called
        systemStats.value.activeSessions = 0 // Will be updated when session API is available

        // TODO: Fetch pending actions from real API when available
        // const pendingResponse = await adminAPI.getPendingActions()
        pendingActions.value = [] // Empty until API is implemented
      } catch (error) {
        console.error('Failed to fetch dashboard data:', error)
        window.$notify?.error('Failed to load dashboard data')

        // Reset to empty state on error
        recentTournaments.value = []
        systemStats.value = {
          totalTournaments: 0,
          totalTeams: 0,
          totalUsers: 0,
          activeSessions: 0,
        }
        pendingActions.value = []
      } finally {
        isLoading.value = false
      }
    }

    /**
     * Refresh dashboard data
     */
    const refreshData = () => {
      fetchDashboardData()
    }

    /**
     * Navigate to edit tournament
     */
    const editTournament = (tournamentId) => {
      router.push(`/tournaments/${tournamentId}/edit`)
    }

    /**
     * Handle team registration approval
     */
    const approveTeamRegistration = (actionId) => {
      // TODO: Implement real API call
      console.log('Approve team registration:', actionId)

      // Remove from pending actions (temporary until real API)
      pendingActions.value = pendingActions.value.filter((action) => action.id !== actionId)
      window.$notify?.success('Team registration approved')
    }

    /**
     * Handle team registration rejection
     */
    const rejectTeamRegistration = (actionId) => {
      // TODO: Implement real API call
      console.log('Reject team registration:', actionId)

      // Remove from pending actions (temporary until real API)
      pendingActions.value = pendingActions.value.filter((action) => action.id !== actionId)
      window.$notify?.warning('Team registration rejected')
    }

    /**
     * Handle match result review
     */
    const reviewMatchResult = (actionId) => {
      // Navigate to match review page
      router.push(`/admin/matches/review/${actionId}`)
    }

    /**
     * Format tournament status for display
     */
    const formatStatus = (status) => {
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
     * Get CSS classes for status badge
     */
    const getStatusBadgeClass = (status) => {
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
     * Format date for display
     */
    const formatDate = (dateString) => {
      if (!dateString) return 'TBD'
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
      isLoading,
      systemStats,
      recentTournaments,
      pendingActions,
      lastBackupTime,
      refreshData,
      editTournament,
      approveTeamRegistration,
      rejectTeamRegistration,
      reviewMatchResult,
      formatStatus,
      getStatusBadgeClass,
      formatDate,
    }
  },
}
</script>
