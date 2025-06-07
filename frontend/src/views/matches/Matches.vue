<template>
  <MainLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">{{ $t('matches.title') }}</h1>
          <p class="text-gray-600 mt-1">{{ $t('matches.viewSchedulesResults') }}</p>
        </div>
        <button v-if="authStore.isAdmin" class="btn-primary" @click="$router.push('/matches/create')">
          <PlusIcon class="w-4 h-4 mr-2" />
          {{ $t('matches.createMatch') }}
        </button>
      </div>
    </template>

    <!-- Filters and Search -->
    <div class="card p-6 mb-8">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Search -->
        <div class="md:col-span-2">
          <div class="relative">
            <MagnifyingGlassIcon
              class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400"
            />
            <input
              v-model="filters.search"
              type="text"
              :placeholder="$t('matches.searchMatches')"
              class="form-input pl-10"
              @input="debouncedSearch"
            />
          </div>
        </div>

        <!-- Status Filter -->
        <div>
          <select v-model="filters.status" @change="applyFilters" class="form-input">
            <option value="">{{ $t('matches.allMatches') }}</option>
            <option value="scheduled">{{ $t('matches.status.scheduled') }}</option>
            <option value="in_progress">{{ $t('matches.status.inProgress') }}</option>
            <option value="completed">{{ $t('matches.status.completed') }}</option>
            <option value="postponed">{{ $t('matches.status.postponed') }}</option>
            <option value="cancelled">{{ $t('matches.status.cancelled') }}</option>
          </select>
        </div>

        <!-- Tournament Filter -->
        <div>
          <select v-model="filters.tournament_id" @change="applyFilters" class="form-input">
            <option value="">{{ $t('matches.allTournaments') }}</option>
            <option v-for="tournament in tournaments" :key="tournament.id" :value="tournament.id">
              {{ tournament.name }}
            </option>
          </select>
        </div>
      </div>

      <!-- Active Filters -->
      <div v-if="hasActiveFilters" class="flex flex-wrap gap-2 mt-4">
        <span class="text-sm text-gray-600">{{ $t('matches.activeFilters') }}</span>
        <span
          v-if="filters.search"
          class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-primary-100 text-primary-800"
        >
          {{ $t('matches.search') }}: "{{ filters.search }}"
          <button @click="clearFilter('search')" class="ml-2 hover:text-primary-900">
            <XMarkIcon class="w-3 h-3" />
          </button>
        </span>
        <span
          v-if="filters.status"
          class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-primary-100 text-primary-800"
        >
          {{ $t('matches.status') }}: {{ formatStatus(filters.status) }}
          <button @click="clearFilter('status')" class="ml-2 hover:text-primary-900">
            <XMarkIcon class="w-3 h-3" />
          </button>
        </span>
        <button
          @click="clearAllFilters"
          class="text-xs text-gray-500 hover:text-gray-700 underline"
        >
          {{ $t('matches.clearAll') }}
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="space-y-4">
      <div v-for="i in 5" :key="i" class="card p-6">
        <div class="animate-pulse">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <div class="h-4 bg-gray-200 rounded w-16"></div>
              <div class="h-4 bg-gray-200 rounded w-32"></div>
              <div class="h-4 bg-gray-200 rounded w-32"></div>
            </div>
            <div class="h-4 bg-gray-200 rounded w-24"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Matches List -->
    <div v-else-if="matches.length" class="space-y-8">
      <!-- Summary Stats -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card p-4 text-center">
          <div class="text-2xl font-bold text-primary-600">{{ totalMatches }}</div>
          <div class="text-sm text-gray-600">{{ $t('matches.totalMatches') }}</div>
        </div>
        <div class="card p-4 text-center">
          <div class="text-2xl font-bold text-success-600">{{ upcomingMatches }}</div>
          <div class="text-sm text-gray-600">{{ $t('matches.upcoming') }}</div>
        </div>
        <div class="card p-4 text-center">
          <div class="text-2xl font-bold text-warning-600">{{ inProgressMatches }}</div>
          <div class="text-sm text-gray-600">{{ $t('matches.inProgress') }}</div>
        </div>
        <div class="card p-4 text-center">
          <div class="text-2xl font-bold text-secondary-600">{{ completedMatches }}</div>
          <div class="text-sm text-gray-600">{{ $t('matches.completed') }}</div>
        </div>
      </div>

      <!-- Matches List -->
      <div class="space-y-4">
        <div
          v-for="match in matches"
          :key="match.id"
          class="card p-6 hover:shadow-card-hover transition-shadow cursor-pointer"
          @click="goToMatch(match.id)"
        >
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-6">
              <!-- Date/Time -->
              <div class="text-center min-w-[80px]">
                <div class="text-sm font-medium text-gray-900">
                  {{ formatMatchDate(match.match_date) }}
                </div>
                <div class="text-xs text-gray-500">{{ formatMatchTime(match.match_date) }}</div>
              </div>

              <!-- Teams -->
              <div class="flex items-center space-x-4 min-w-[300px]">
                <div class="text-right flex-1">
                  <div class="font-medium text-gray-900">{{ match.home_team?.name }}</div>
                  <div class="text-xs text-gray-500">{{ $t('matches.home') }}</div>
                </div>
                
                <div class="text-center px-4">
                  <div v-if="match.status === 'completed'" class="text-xl font-bold text-gray-900">
                    {{ match.home_score }} - {{ match.away_score }}
                  </div>
                  <div v-else-if="match.status === 'in_progress'" class="text-lg font-bold text-primary-600">
                    {{ match.home_score || 0 }} - {{ match.away_score || 0 }}
                  </div>
                  <div v-else class="text-gray-400 font-bold">{{ $t('matches.vs') }}</div>
                  <div class="text-xs mt-1">
                    <span
                      :class="getStatusBadgeClass(match.status)"
                      class="px-2 py-1 rounded-full text-xs font-medium"
                    >
                      {{ formatStatus(match.status) }}
                    </span>
                  </div>
                </div>
                
                <div class="text-left flex-1">
                  <div class="font-medium text-gray-900">{{ match.away_team?.name }}</div>
                  <div class="text-xs text-gray-500">{{ $t('matches.away') }}</div>
                </div>
              </div>
            </div>

            <!-- Match Info -->
            <div class="text-right">
              <div v-if="match.venue" class="text-sm text-gray-600 mb-1">
                <MapPinIcon class="w-4 h-4 inline mr-1" />
                {{ match.venue }}
              </div>
              <div class="text-xs text-gray-500 mb-1">{{ match.tournament?.name }}</div>
              <div v-if="match.round" class="text-xs text-gray-500 mb-2">{{ match.round }}</div>
              <div v-if="match.referee" class="text-xs text-gray-500">
                {{ $t('matches.referee') }}: {{ match.referee.name }}
              </div>
            </div>
          </div>

          <!-- Extra Time / Penalties (if applicable) -->
          <div v-if="match.status === 'completed' && (match.extra_time_home || match.penalty_home)" class="mt-4 pt-4 border-t border-gray-200">
            <div class="flex justify-center space-x-8 text-sm text-gray-600">
              <div v-if="match.extra_time_home !== null">
                {{ $t('matches.extraTime') }}: {{ match.extra_time_home }} - {{ match.extra_time_away }}
              </div>
              <div v-if="match.penalty_home !== null">
                {{ $t('matches.penalties') }}: {{ match.penalty_home }} - {{ match.penalty_away }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.total > pagination.per_page" class="flex justify-center">
        <nav class="flex items-center space-x-2">
          <button
            @click="goToPage(pagination.current_page - 1)"
            :disabled="pagination.current_page <= 1"
            class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ $t('matches.previous') }}
          </button>

          <template v-for="page in visiblePages" :key="page">
            <button
              v-if="page !== '...'"
              @click="goToPage(page)"
              :class="[
                'px-3 py-2 text-sm font-medium rounded-md',
                page === pagination.current_page
                  ? 'bg-primary-600 text-white'
                  : 'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50',
              ]"
            >
              {{ page }}
            </button>
            <span v-else class="px-3 py-2 text-sm font-medium text-gray-500">...</span>
          </template>

          <button
            @click="goToPage(pagination.current_page + 1)"
            :disabled="pagination.current_page >= pagination.last_page"
            class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ $t('matches.next') }}
          </button>
        </nav>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-12">
      <PlayIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
      <h3 class="text-lg font-medium text-gray-900 mb-2">
        {{ hasActiveFilters ? $t('matches.noMatchesFound') : $t('matches.noMatchesScheduled') }}
      </h3>
      <p class="text-gray-600 mb-6">
        {{
          hasActiveFilters
            ? $t('matches.tryAdjustingFilters')
            : $t('matches.matchesWillAppear')
        }}
      </p>
      <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <button v-if="hasActiveFilters" @click="clearAllFilters" class="btn-secondary">
          {{ $t('matches.clearFilters') }}
        </button>
        <RouterLink v-if="authStore.isAdmin" to="/matches/create" class="btn-primary">
          {{ $t('matches.scheduleMatch') }}
        </RouterLink>
      </div>
    </div>
  </MainLayout>
</template>

<script>
/**
 * Matches List Page Component
 * Displays filterable list of matches with real-time data
 */

import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import {
  PlusIcon,
  PlayIcon,
  MagnifyingGlassIcon,
  XMarkIcon,
  MapPinIcon,
} from '@heroicons/vue/24/outline'
import { useAuthStore } from '@/stores/auth'
import { matchAPI, tournamentAPI, apiHelpers } from '@/services/api'
import MainLayout from '@/components/layout/MainLayout.vue'
import { useI18n } from 'vue-i18n'

export default {
  name: 'Matches',
  components: {
    MainLayout,
    PlusIcon,
    PlayIcon,
    MagnifyingGlassIcon,
    XMarkIcon,
    MapPinIcon,
  },
  setup() {
    const router = useRouter()
    const authStore = useAuthStore()
    const { t } = useI18n()

    // Data
    const matches = ref([])
    const tournaments = ref([])
    const isLoading = ref(false)

    // Filters
    const filters = ref({
      search: '',
      status: '',
      tournament_id: '',
    })

    // Pagination
    const pagination = ref({
      current_page: 1,
      last_page: 1,
      per_page: 15,
      total: 0,
    })

    // Computed properties
    const hasActiveFilters = computed(() => {
      return filters.value.search || filters.value.status || filters.value.tournament_id
    })

    const totalMatches = computed(() => pagination.value.total || matches.value.length)
    const upcomingMatches = computed(() => 
      matches.value.filter(m => m.status === 'scheduled').length
    )
    const inProgressMatches = computed(() => 
      matches.value.filter(m => m.status === 'in_progress').length
    )
    const completedMatches = computed(() => 
      matches.value.filter(m => m.status === 'completed').length
    )

    const visiblePages = computed(() => {
      const current = pagination.value.current_page
      const last = pagination.value.last_page
      const pages = []

      if (last <= 7) {
        for (let i = 1; i <= last; i++) {
          pages.push(i)
        }
      } else {
        pages.push(1)
        if (current > 4) pages.push('...')
        const start = Math.max(2, current - 1)
        const end = Math.min(last - 1, current + 1)
        for (let i = start; i <= end; i++) {
          pages.push(i)
        }
        if (current < last - 3) pages.push('...')
        if (last > 1) pages.push(last)
      }

      return pages
    })

    /**
     * Fetch matches with current filters
     */
    const fetchMatches = async (page = 1) => {
      isLoading.value = true
      try {
        const params = {
          page,
          per_page: pagination.value.per_page,
          ...filters.value,
        }

        // Clean empty filters
        Object.keys(params).forEach((key) => {
          if (params[key] === '' || params[key] == null) {
            delete params[key]
          }
        })

        const response = await matchAPI.getAll(params)

        if (apiHelpers.isSuccess(response)) {
          const data = apiHelpers.getData(response)
          matches.value = data.data || []

          // Update pagination
          pagination.value = {
            current_page: data.current_page || 1,
            last_page: data.last_page || 1,
            per_page: data.per_page || 15,
            total: data.total || 0,
          }
        } else {
          matches.value = []
        }
      } catch (error) {
        console.error('Failed to fetch matches:', error)
        window.$notify?.error(t('notifications.networkError'))
        matches.value = []
      } finally {
        isLoading.value = false
      }
    }

    /**
     * Fetch tournaments for filter dropdown
     */
    const fetchTournaments = async () => {
      try {
        const response = await tournamentAPI.getAll({ per_page: 100 })
        if (apiHelpers.isSuccess(response)) {
          const data = apiHelpers.getData(response)
          tournaments.value = data.data || []
        }
      } catch (error) {
        console.error('Failed to fetch tournaments:', error)
        tournaments.value = []
      }
    }

    /**
     * Debounced search function
     */
    let searchTimeout
    const debouncedSearch = () => {
      clearTimeout(searchTimeout)
      searchTimeout = setTimeout(() => {
        applyFilters()
      }, 500)
    }

    /**
     * Apply current filters
     */
    const applyFilters = () => {
      pagination.value.current_page = 1
      fetchMatches(1)
    }

    /**
     * Clear specific filter
     */
    const clearFilter = (filterKey) => {
      filters.value[filterKey] = ''
      applyFilters()
    }

    /**
     * Clear all filters
     */
    const clearAllFilters = () => {
      filters.value = {
        search: '',
        status: '',
        tournament_id: '',
      }
      applyFilters()
    }

    /**
     * Go to specific page
     */
    const goToPage = (page) => {
      if (page >= 1 && page <= pagination.value.last_page) {
        pagination.value.current_page = page
        fetchMatches(page)
      }
    }

    /**
     * Navigate to match details
     */
    const goToMatch = (matchId) => {
      router.push(`/matches/${matchId}`)
    }

    /**
     * Format match status
     */
    const formatStatus = (status) => {
      const statusMap = {
        scheduled: t('matches.status.scheduled'),
        in_progress: t('matches.status.inProgress'),
        completed: t('matches.status.completed'),
        postponed: t('matches.status.postponed'),
        cancelled: t('matches.status.cancelled'),
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
     * Format match date
     */
    const formatMatchDate = (dateString) => {
      if (!dateString) return t('common.tbd')
      return new Date(dateString).toLocaleDateString(undefined, {
        month: 'short',
        day: 'numeric',
      })
    }

    /**
     * Format match time
     */
    const formatMatchTime = (dateString) => {
      if (!dateString) return t('common.tbd')
      return new Date(dateString).toLocaleTimeString(undefined, {
        hour: '2-digit',
        minute: '2-digit',
      })
    }

    // Initialize
    onMounted(() => {
      fetchMatches()
      fetchTournaments()
    })

    return {
      authStore,
      matches,
      tournaments,
      isLoading,
      filters,
      pagination,
      hasActiveFilters,
      totalMatches,
      upcomingMatches,
      inProgressMatches,
      completedMatches,
      visiblePages,
      fetchMatches,
      debouncedSearch,
      applyFilters,
      clearFilter,
      clearAllFilters,
      goToPage,
      goToMatch,
      formatStatus,
      getStatusBadgeClass,
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

.transition-shadow {
  transition: all 0.3s ease;
}
</style>