<template>
  <MainLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">{{ $t('players.title') }}</h1>
          <p class="text-gray-600 mt-1">{{ $t('players.subtitle') }}</p>
        </div>
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
              :placeholder="$t('players.searchPlayers')"
              class="form-input pl-10"
              @input="debouncedSearch"
            />
          </div>
        </div>

        <!-- Position Filter -->
        <div>
          <select v-model="filters.position" @change="applyFilters" class="form-input">
            <option value="">{{ $t('players.allPositions') }}</option>
            <option value="Goalkeeper">{{ $t('players.positions.goalkeeper') }}</option>
            <option value="Defender">{{ $t('players.positions.defender') }}</option>
            <option value="Midfielder">{{ $t('players.positions.midfielder') }}</option>
            <option value="Forward">{{ $t('players.positions.forward') }}</option>
          </select>
        </div>

        <!-- Nationality Filter -->
        <div>
          <select v-model="filters.nationality" @change="applyFilters" class="form-input">
            <option value="">{{ $t('players.allNationalities') }}</option>
            <option value="Spain">Spain</option>
            <option value="France">France</option>
            <option value="Germany">Germany</option>
            <option value="Italy">Italy</option>
            <option value="Brazil">Brazil</option>
            <option value="Argentina">Argentina</option>
          </select>
        </div>
      </div>

      <!-- Active Filters -->
      <div v-if="hasActiveFilters" class="flex flex-wrap gap-2 mt-4">
        <span class="text-sm text-gray-600">{{ $t('players.activeFilters') }}</span>
        <span
          v-if="filters.search"
          class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-primary-100 text-primary-800"
        >
          {{ $t('players.search') }}: "{{ filters.search }}"
          <button @click="clearFilter('search')" class="ml-2 hover:text-primary-900">
            <XMarkIcon class="w-3 h-3" />
          </button>
        </span>
        <span
          v-if="filters.position"
          class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-primary-100 text-primary-800"
        >
          {{ $t('players.position') }}: {{ getPositionTranslation(filters.position) }}
          <button @click="clearFilter('position')" class="ml-2 hover:text-primary-900">
            <XMarkIcon class="w-3 h-3" />
          </button>
        </span>
        <span
          v-if="filters.nationality"
          class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-primary-100 text-primary-800"
        >
          {{ $t('players.nationality') }}: {{ filters.nationality }}
          <button @click="clearFilter('nationality')" class="ml-2 hover:text-primary-900">
            <XMarkIcon class="w-3 h-3" />
          </button>
        </span>
        <button
          @click="clearAllFilters"
          class="text-xs text-gray-500 hover:text-gray-700 underline"
        >
          {{ $t('players.clearAll') }}
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="i in 9" :key="i" class="card p-6">
        <div class="animate-pulse">
          <div class="w-16 h-16 bg-gray-200 rounded-full mx-auto mb-4"></div>
          <div class="h-4 bg-gray-200 rounded mb-2"></div>
          <div class="h-3 bg-gray-200 rounded mb-2"></div>
          <div class="h-3 bg-gray-200 rounded w-3/4 mx-auto"></div>
        </div>
      </div>
    </div>

    <!-- Players Grid -->
    <div v-else-if="players.length" class="space-y-8">
      <!-- Summary Stats -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card p-4 text-center">
          <div class="text-2xl font-bold text-primary-600">{{ totalPlayers }}</div>
          <div class="text-sm text-gray-600">{{ $t('players.totalPlayers') }}</div>
        </div>
        <div class="card p-4 text-center">
          <div class="text-2xl font-bold text-success-600">{{ activePlayers }}</div>
          <div class="text-sm text-gray-600">{{ $t('players.activePlayers') }}</div>
        </div>
        <div class="card p-4 text-center">
          <div class="text-2xl font-bold text-warning-600">{{ averageAge }}</div>
          <div class="text-sm text-gray-600">{{ $t('players.averageAge') }}</div>
        </div>
        <div class="card p-4 text-center">
          <div class="text-2xl font-bold text-secondary-600">{{ totalGoals }}</div>
          <div class="text-sm text-gray-600">{{ $t('players.totalGoals') }}</div>
        </div>
      </div>

      <!-- Players List -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="player in players" :key="player.id" class="card card-hover overflow-hidden">
          <!-- Player Header -->
          <div class="relative h-32 bg-gradient-to-br from-primary-500 to-primary-700">
            <div class="absolute inset-0 bg-black opacity-20"></div>
            <div class="absolute top-4 right-4">
              <span
                v-if="player.position"
                class="px-2 py-1 rounded-full text-xs font-medium bg-white bg-opacity-90 text-primary-800"
              >
                {{ getPositionTranslation(player.position) }}
              </span>
            </div>
            <div class="absolute bottom-4 left-4 right-4">
              <h3 class="text-white text-lg font-bold truncate">
                {{ player.user?.name || $t('players.unknownPlayer') }}
              </h3>
              <p v-if="player.nationality" class="text-primary-100 text-sm">
                {{ player.nationality }}
              </p>
            </div>
          </div>

          <!-- Player Details -->
          <div class="p-6">
            <!-- Player Info -->
            <div class="space-y-3 mb-6">
              <!-- Age -->
              <div v-if="player.age" class="flex items-center text-sm text-gray-600">
                <CalendarIcon class="w-4 h-4 mr-2 text-gray-400" />
                <span>{{ player.age }} {{ $t('players.yearsOld') }}</span>
              </div>

              <!-- Height & Weight -->
              <div
                v-if="player.height || player.weight"
                class="flex items-center text-sm text-gray-600"
              >
                <UserIcon class="w-4 h-4 mr-2 text-gray-400" />
                <span>
                  {{ player.height ? player.height + 'cm' : '' }}
                  {{ player.height && player.weight ? ' / ' : '' }}
                  {{ player.weight ? player.weight + 'kg' : '' }}
                </span>
              </div>

              <!-- Preferred Foot -->
              <div v-if="player.preferred_foot" class="flex items-center text-sm text-gray-600">
                <HandRaisedIcon class="w-4 h-4 mr-2 text-gray-400" />
                <span class="capitalize">{{ player.preferred_foot }} {{ $t('players.footed') }}</span>
              </div>

              <!-- Current Teams -->
              <div
                v-if="player.active_teams?.length"
                class="flex items-center text-sm text-gray-600"
              >
                <UserGroupIcon class="w-4 h-4 mr-2 text-gray-400" />
                <span>{{ player.active_teams.length }} {{ $t('players.teams') }}</span>
              </div>
            </div>

            <!-- Player Stats -->
            <div v-if="playerStats[player.id]" class="grid grid-cols-3 gap-4 mb-6">
              <div class="text-center">
                <div class="text-lg font-bold text-gray-900">
                  {{ playerStats[player.id]?.goals || 0 }}
                </div>
                <div class="text-xs text-gray-500">{{ $t('players.goals') }}</div>
              </div>
              <div class="text-center">
                <div class="text-lg font-bold text-gray-900">
                  {{ playerStats[player.id]?.matches || 0 }}
                </div>
                <div class="text-xs text-gray-500">{{ $t('players.matches') }}</div>
              </div>
              <div class="text-center">
                <div class="text-lg font-bold text-gray-900">
                  {{ playerStats[player.id]?.cards || 0 }}
                </div>
                <div class="text-xs text-gray-500">{{ $t('players.cards') }}</div>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-3">
              <RouterLink
                :to="{ name: 'player-detail', params: { id: player.id } }"
                class="btn-primary flex-1 text-center"
              >
                {{ $t('players.viewProfile') }}
              </RouterLink>
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
            {{ $t('players.previous') }}
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
            {{ $t('players.next') }}
          </button>
        </nav>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-12">
      <UsersIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
      <h3 class="text-lg font-medium text-gray-900 mb-2">
        {{ hasActiveFilters ? $t('players.noPlayersFound') : $t('players.noPlayersYet') }}
      </h3>
      <p class="text-gray-600 mb-6">
        {{
          hasActiveFilters
            ? $t('players.tryAdjustingFilters')
            : $t('players.playersWillAppear')
        }}
      </p>
      <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <button v-if="hasActiveFilters" @click="clearAllFilters" class="btn-secondary">
          {{ $t('players.clearFilters') }}
        </button>
        <RouterLink v-if="authStore.isAuthenticated" to="/register" class="btn-primary">
          {{ $t('players.registerAsPlayer') }}
        </RouterLink>
      </div>
    </div>
  </MainLayout>
</template>

<script>
/**
 * Players List Page Component
 * Displays filterable list of players with search and pagination
 */

import { ref, computed, onMounted } from 'vue'
import {
  MagnifyingGlassIcon,
  UsersIcon,
  XMarkIcon,
  UserIcon,
  CalendarIcon,
  UserGroupIcon,
  HandRaisedIcon,
} from '@heroicons/vue/24/outline'
import { useAuthStore } from '@/stores/auth'
import { playerAPI, apiHelpers } from '@/services/api'
import MainLayout from '@/components/layout/MainLayout.vue'
import { useI18n } from 'vue-i18n'

export default {
  name: 'Players',
  components: {
    MainLayout,
    MagnifyingGlassIcon,
    UsersIcon,
    XMarkIcon,
    UserIcon,
    CalendarIcon,
    UserGroupIcon,
    HandRaisedIcon,
  },
  setup() {
    const authStore = useAuthStore()
    const { t } = useI18n()

    const handlePlayerClick = (player) => {
      console.log('Navigating to player:', player)
      console.log('Player ID:', player.id)
      console.log('Route will be:', `/players/${player.id}`)
    }
    // Data
    const players = ref([])
    const playerStats = ref({})
    const isLoading = ref(false)

    // Filters
    const filters = ref({
      search: '',
      position: '',
      nationality: '',
    })

    // Pagination
    const pagination = ref({
      current_page: 1,
      last_page: 1,
      per_page: 12,
      total: 0,
    })

    // Computed properties
    const hasActiveFilters = computed(() => {
      return filters.value.search || filters.value.position || filters.value.nationality
    })

    const totalPlayers = computed(() => pagination.value.total || players.value.length)
    const activePlayers = computed(
      () => players.value.filter((p) => p.user?.is_active !== false).length,
    )
    const averageAge = computed(() => {
      const ages = players.value.map((p) => p.age).filter(Boolean)
      if (ages.length === 0) return 0
      return Math.round(ages.reduce((sum, age) => sum + age, 0) / ages.length)
    })
    const totalGoals = computed(() => {
      return Object.values(playerStats.value).reduce((sum, stats) => sum + (stats?.goals || 0), 0)
    })

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
     * Get position translation
     */
    const getPositionTranslation = (position) => {
      const positionMap = {
        'Goalkeeper': t('players.positions.goalkeeper'),
        'Defender': t('players.positions.defender'),
        'Midfielder': t('players.positions.midfielder'),
        'Forward': t('players.positions.forward'),
      }
      return positionMap[position] || position
    }

    /**
     * Fetch players with current filters
     */
    const fetchPlayers = async (page = 1) => {
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

        const response = await playerAPI.getAll(params)

        if (apiHelpers.isSuccess(response)) {
          const data = apiHelpers.getData(response)
          players.value = data.data || []

          // Update pagination
          pagination.value = {
            current_page: data.current_page || 1,
            last_page: data.last_page || 1,
            per_page: data.per_page || 12,
            total: data.total || 0,
          }

          // Fetch stats for each player
          await fetchPlayerStats()
        } else {
          // No fallback data - just empty state
          players.value = []
          pagination.value = {
            current_page: 1,
            last_page: 1,
            per_page: 12,
            total: 0,
          }
        }
      } catch (error) {
        console.error('Failed to fetch players:', error)
        players.value = []
        window.$notify?.error(t('notifications.networkError'))
      } finally {
        isLoading.value = false
      }
    }

    /**
     * Fetch statistics for current players
     */
    const fetchPlayerStats = async () => {
      for (const player of players.value) {
        try {
          const response = await playerAPI.getStatistics(player.id)
          if (apiHelpers.isSuccess(response)) {
            const stats = apiHelpers.getData(response).statistics
            playerStats.value[player.id] = {
              goals: stats.goals || 0,
              matches: stats.total_matches || 0,
              cards: (stats.yellow_cards || 0) + (stats.red_cards || 0),
            }
          }
        } catch (error) {
          // Don't use mock stats - just set to 0
          console.error(`Failed to fetch stats for player ${player.id}:`, error)
          playerStats.value[player.id] = {
            goals: 0,
            matches: 0,
            cards: 0,
          }
        }
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
      fetchPlayers(1)
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
        position: '',
        nationality: '',
      }
      applyFilters()
    }

    /**
     * Go to specific page
     */
    const goToPage = (page) => {
      if (page >= 1 && page <= pagination.value.last_page) {
        pagination.value.current_page = page
        fetchPlayers(page)
      }
    }

    // Initialize
    onMounted(() => {
      fetchPlayers()
    })

    return {
      authStore,
      players,
      playerStats,
      isLoading,
      filters,
      pagination,
      hasActiveFilters,
      totalPlayers,
      activePlayers,
      averageAge,
      totalGoals,
      visiblePages,
      fetchPlayers,
      debouncedSearch,
      applyFilters,
      clearFilter,
      clearAllFilters,
      goToPage,
      handlePlayerClick,
      getPositionTranslation,
    }
  },
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
