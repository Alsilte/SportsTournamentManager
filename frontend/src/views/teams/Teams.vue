<template>
  <MainLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">{{ $t('teams.title') }}</h1>
          <p class="text-gray-600 mt-1">{{ $t('teams.subtitle') }}</p>
        </div>
        <div class="flex items-center space-x-4">
          <RouterLink v-if="authStore.canManageTeams" to="/teams/create" class="btn-primary">
            <PlusIcon class="w-4 h-4 mr-2" />
            Crear Torneo
          </RouterLink>
        </div>
      </div>
    </template>

    <!-- Filters and Search -->
    <div class="card p-6 mb-8">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Search -->
        <div class="md:col-span-2">
          <div class="relative">
            <MagnifyingGlassIcon
              class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400"
            />
            <input
              v-model="filters.search"
              type="text"
              :placeholder="$t('teams.searchTeams')"
              class="form-input pl-10"
              @input="debouncedSearch"
            />
          </div>
        </div>

        <!-- Status Filter -->
        <div>
          <select v-model="filters.active" @change="applyFilters" class="form-input">
            <option value="">{{ $t('teams.allTeams') }}</option>
            <option value="true">{{ $t('teams.activeTeams') }}</option>
            <option value="false">{{ $t('teams.inactiveTeams') }}</option>
          </select>
        </div>
      </div>

      <!-- Active Filters -->
      <div v-if="hasActiveFilters" class="flex flex-wrap gap-2 mt-4">
        <span class="text-sm text-gray-600">{{ $t('teams.activeFilters') }}</span>
        <span
          v-if="filters.search"
          class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-primary-100 text-primary-800"
        >
          {{ $t('teams.search') }}: "{{ filters.search }}"
          <button @click="clearFilter('search')" class="ml-2 hover:text-primary-900">
            <XMarkIcon class="w-3 h-3" />
          </button>
        </span>
        <span
          v-if="filters.active"
          class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-primary-100 text-primary-800"
        >
          {{ $t('common.status') }}: {{ filters.active === 'true' ? $t('common.active') : $t('common.inactive') }}
          <button @click="clearFilter('active')" class="ml-2 hover:text-primary-900">
            <XMarkIcon class="w-3 h-3" />
          </button>
        </span>
        <button
          @click="clearAllFilters"
          class="text-xs text-gray-500 hover:text-gray-700 underline"
        >
          {{ $t('teams.clearAll') }}
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="i in 6" :key="i" class="card p-6">
        <div class="animate-pulse">
          <div class="h-4 bg-gray-200 rounded mb-4"></div>
          <div class="h-3 bg-gray-200 rounded mb-2"></div>
          <div class="h-3 bg-gray-200 rounded w-3/4"></div>
        </div>
      </div>
    </div>

    <!-- Teams Grid -->
    <div v-else-if="teams.length" class="space-y-8">
      <!-- Summary Stats -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card p-4 text-center">
          <div class="text-2xl font-bold text-primary-600">{{ totalTeams }}</div>
          <div class="text-sm text-gray-600">{{ $t('teams.stats.totalTeams') }}</div>
        </div>
        <div class="card p-4 text-center">
          <div class="text-2xl font-bold text-success-600">{{ activeTeams }}</div>
          <div class="text-sm text-gray-600">{{ $t('teams.stats.activeTeams') }}</div>
        </div>
        <div class="card p-4 text-center">
          <div class="text-2xl font-bold text-warning-600">{{ totalPlayers }}</div>
          <div class="text-sm text-gray-600">{{ $t('teams.stats.totalPlayers') }}</div>
        </div>
        <div class="card p-4 text-center">
          <div class="text-2xl font-bold text-secondary-600">{{ activeTournaments }}</div>
          <div class="text-sm text-gray-600">{{ $t('teams.stats.inTournaments') }}</div>
        </div>
      </div>

      <!-- Teams List -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="team in teams" :key="team.id" class="card card-hover overflow-hidden">
          <!-- Team Header -->
          <div class="relative h-32 bg-gradient-to-br from-primary-500 to-primary-700">
            <div class="absolute inset-0 bg-black opacity-20"></div>
            <div class="absolute top-4 right-4">
              <span
                :class="[
                  'px-2 py-1 rounded-full text-xs font-medium',
                  team.is_active ? 'bg-success-100 text-success-800' : 'bg-gray-100 text-gray-800',
                ]"
              >
                {{ team.is_active ? $t('common.active') : $t('common.inactive') }}
              </span>
            </div>
            <div class="absolute bottom-4 left-4 right-4">
              <h3 class="text-white text-lg font-bold truncate">{{ team.name }}</h3>
              <p v-if="team.short_name" class="text-primary-100 text-sm">{{ team.short_name }}</p>
            </div>
          </div>

          <!-- Team Details -->
          <div class="p-6">
            <!-- Description -->
            <p v-if="team.description" class="text-gray-600 text-sm mb-4 line-clamp-2">
              {{ team.description }}
            </p>

            <!-- Team Info -->
            <div class="space-y-3 mb-6">
              <!-- Manager -->
              <div class="flex items-center text-sm text-gray-600">
                <UserIcon class="w-4 h-4 mr-2 text-gray-400" />
                <span>{{ team.manager?.name || $t('teams.noManager') }}</span>
              </div>

              <!-- Players Count -->
              <div class="flex items-center text-sm text-gray-600">
                <UsersIcon class="w-4 h-4 mr-2 text-gray-400" />
                <span>{{ team.players_count || 0 }} {{ $t('teams.players') }}</span>
              </div>

              <!-- Founded Year -->
              <div v-if="team.founded_year" class="flex items-center text-sm text-gray-600">
                <CalendarIcon class="w-4 h-4 mr-2 text-gray-400" />
                <span>{{ $t('teams.founded') }} {{ team.founded_year }}</span>
              </div>

              <!-- Home Venue -->
              <div v-if="team.home_venue" class="flex items-center text-sm text-gray-600">
                <MapPinIcon class="w-4 h-4 mr-2 text-gray-400" />
                <span>{{ team.home_venue }}</span>
              </div>

              <!-- Contact -->
              <div v-if="team.contact_email" class="flex items-center text-sm text-gray-600">
                <EnvelopeIcon class="w-4 h-4 mr-2 text-gray-400" />
                <span>{{ team.contact_email }}</span>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-3">
              <RouterLink :to="`/teams/${team.id}`" class="btn-primary flex-1 text-center">
                {{ $t('teams.viewDetails') }}
              </RouterLink>

              <RouterLink
                v-if="canManageTeam(team)"
                :to="`/teams/${team.id}/roster`"
                class="btn-secondary px-4"
              >
                <UsersIcon class="w-4 h-4" />
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
            {{ $t('common.previous') }}
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
            {{ $t('common.next') }}
          </button>
        </nav>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-12">
      <UserGroupIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
      <h3 class="text-lg font-medium text-gray-900 mb-2">
        {{ hasActiveFilters ? $t('teams.noTeamsFiltered') : $t('teams.noTeamsYet') }}
      </h3>
      <p class="text-gray-600 mb-6">
        {{
          hasActiveFilters
            ? $t('teams.tryAdjustingFilters')
            : $t('teams.createFirstTeam')
        }}
      </p>
      <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <button v-if="hasActiveFilters" @click="clearAllFilters" class="btn-secondary">
          {{ $t('teams.clearFilters') }}
        </button>
        <RouterLink v-if="authStore.canManageTeams" to="/teams/create" class="btn-primary">
          Crear Equipo
        </RouterLink>
      </div>
    </div>
  </MainLayout>
</template>

<script>
/**
 * Teams List Page Component
 * Displays filterable list of teams with search and pagination
 */

import { ref, computed, onMounted } from 'vue'
import {
  PlusIcon,
  MagnifyingGlassIcon,
  UserGroupIcon,
  XMarkIcon,
  UserIcon,
  UsersIcon,
  CalendarIcon,
  MapPinIcon,
  EnvelopeIcon,
} from '@heroicons/vue/24/outline'
import { useAuthStore } from '@/stores/auth'
import { teamAPI, apiHelpers } from '@/services/api'
import MainLayout from '@/components/layout/MainLayout.vue'

export default {
  name: 'Teams',
  components: {
    MainLayout,
    PlusIcon,
    MagnifyingGlassIcon,
    UserGroupIcon,
    XMarkIcon,
    UserIcon,
    UsersIcon,
    CalendarIcon,
    MapPinIcon,
    EnvelopeIcon,
  },
  setup() {
    const authStore = useAuthStore()

    // Data
    const teams = ref([])
    const isLoading = ref(false)

    // Filters
    const filters = ref({
      search: '',
      active: '',
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
      return filters.value.search || filters.value.active
    })

    const totalTeams = computed(() => teams.value.length)
    const activeTeams = computed(() => teams.value.filter((t) => t.is_active).length)
    const totalPlayers = computed(() =>
      teams.value.reduce((sum, team) => sum + (team.players_count || 0), 0),
    )
    const activeTournaments = computed(
      () => teams.value.filter((t) => t.tournaments_count > 0).length,
    )

    const visiblePages = computed(() => {
      const current = pagination.value.current_page
      const last = pagination.value.last_page
      const pages = []

      if (last <= 7) {
        // Show all pages if 7 or fewer
        for (let i = 1; i <= last; i++) {
          pages.push(i)
        }
      } else {
        // Always show first page
        pages.push(1)

        if (current > 4) {
          pages.push('...')
        }

        // Show pages around current
        const start = Math.max(2, current - 1)
        const end = Math.min(last - 1, current + 1)

        for (let i = start; i <= end; i++) {
          pages.push(i)
        }

        if (current < last - 3) {
          pages.push('...')
        }

        // Always show last page
        if (last > 1) {
          pages.push(last)
        }
      }

      return pages
    })

    /**
     * Fetch teams with current filters
     */
    const fetchTeams = async (page = 1) => {
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

        const response = await teamAPI.getAll(params)

        if (apiHelpers.isSuccess(response)) {
          const data = apiHelpers.getData(response)
          teams.value = data.data || []

          // Update pagination
          pagination.value = {
            current_page: data.current_page || 1,
            last_page: data.last_page || 1,
            per_page: data.per_page || 12,
            total: data.total || 0,
          }
        }
      } catch (error) {
        console.error('Failed to fetch teams:', error)
        window.$notify?.error('Failed to load teams')
        teams.value = []
      } finally {
        isLoading.value = false
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
      fetchTeams(1)
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
        active: '',
      }
      applyFilters()
    }

    /**
     * Go to specific page
     */
    const goToPage = (page) => {
      if (page >= 1 && page <= pagination.value.last_page) {
        pagination.value.current_page = page
        fetchTeams(page)
      }
    }

    /**
     * Check if user can manage a team
     */
    const canManageTeam = (team) => {
      return authStore.isAdmin || team.manager_id === authStore.user?.id
    }

    // Initialize
    onMounted(() => {
      fetchTeams()
    })

    return {
      authStore,
      teams,
      isLoading,
      filters,
      pagination,
      hasActiveFilters,
      totalTeams,
      activeTeams,
      totalPlayers,
      activeTournaments,
      visiblePages,
      fetchTeams,
      debouncedSearch,
      applyFilters,
      clearFilter,
      clearAllFilters,
      goToPage,
      canManageTeam,
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
