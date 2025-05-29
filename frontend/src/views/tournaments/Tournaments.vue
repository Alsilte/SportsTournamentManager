<template>
  <MainLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Tournaments</h1>
          <p class="text-gray-600 mt-1">Discover and join sports tournaments</p>
        </div>
        <div class="flex items-center space-x-4">
          <RouterLink 
            v-if="authStore.isAdmin"
            to="/tournaments/create" 
            class="btn-primary"
          >
            <PlusIcon class="w-4 h-4 mr-2" />
            Create Tournament
          </RouterLink>
        </div>
      </div>
    </template>

    <!-- Filters and Search -->
    <div class="card p-6 mb-8">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Search -->
        <div class="md:col-span-2">
          <div class="relative">
            <MagnifyingGlassIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
            <input
              v-model="filters.search"
              type="text"
              placeholder="Search tournaments..."
              class="form-input pl-10"
              @input="debouncedSearch"
            />
          </div>
        </div>

        <!-- Status Filter -->
        <div>
          <select v-model="filters.status" @change="applyFilters" class="form-input">
            <option value="">All Status</option>
            <option value="draft">Draft</option>
            <option value="registration_open">Registration Open</option>
            <option value="in_progress">In Progress</option>
            <option value="completed">Completed</option>
          </select>
        </div>

        <!-- Sport Type Filter -->
        <div>
          <select v-model="filters.sport_type" @change="applyFilters" class="form-input">
            <option value="">All Sports</option>
            <option value="Football">Football</option>
            <option value="Basketball">Basketball</option>
            <option value="Tennis">Tennis</option>
            <option value="Volleyball">Volleyball</option>
            <option value="Other">Other</option>
          </select>
        </div>
      </div>

      <!-- Active Filters -->
      <div v-if="hasActiveFilters" class="flex flex-wrap gap-2 mt-4">
        <span class="text-sm text-gray-600">Active filters:</span>
        <span 
          v-if="filters.search"
          class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-primary-100 text-primary-800"
        >
          Search: "{{ filters.search }}"
          <button @click="clearFilter('search')" class="ml-2 hover:text-primary-900">
            <XMarkIcon class="w-3 h-3" />
          </button>
        </span>
        <span 
          v-if="filters.status"
          class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-primary-100 text-primary-800"
        >
          Status: {{ formatStatus(filters.status) }}
          <button @click="clearFilter('status')" class="ml-2 hover:text-primary-900">
            <XMarkIcon class="w-3 h-3" />
          </button>
        </span>
        <span 
          v-if="filters.sport_type"
          class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-primary-100 text-primary-800"
        >
          Sport: {{ filters.sport_type }}
          <button @click="clearFilter('sport_type')" class="ml-2 hover:text-primary-900">
            <XMarkIcon class="w-3 h-3" />
          </button>
        </span>
        <button 
          @click="clearAllFilters"
          class="text-xs text-gray-500 hover:text-gray-700 underline"
        >
          Clear all
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="i in 6" :key="i" class="card p-6">
        <div class="animate-pulse">
          <div class="h-48 bg-gray-200 rounded mb-4"></div>
          <div class="h-4 bg-gray-200 rounded mb-2"></div>
          <div class="h-3 bg-gray-200 rounded mb-2"></div>
          <div class="h-3 bg-gray-200 rounded w-3/4"></div>
        </div>
      </div>
    </div>

    <!-- Tournaments Grid -->
    <div v-else-if="tournaments.length" class="space-y-8">
      <!-- Summary Stats -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card p-4 text-center">
          <div class="text-2xl font-bold text-primary-600">{{ totalTournaments }}</div>
          <div class="text-sm text-gray-600">Total Tournaments</div>
        </div>
        <div class="card p-4 text-center">
          <div class="text-2xl font-bold text-success-600">{{ openRegistrations }}</div>
          <div class="text-sm text-gray-600">Open for Registration</div>
        </div>
        <div class="card p-4 text-center">
          <div class="text-2xl font-bold text-warning-600">{{ inProgress }}</div>
          <div class="text-sm text-gray-600">In Progress</div>
        </div>
        <div class="card p-4 text-center">
          <div class="text-2xl font-bold text-secondary-600">{{ completed }}</div>
          <div class="text-sm text-gray-600">Completed</div>
        </div>
      </div>

      <!-- Tournaments List -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <TournamentCard 
          v-for="tournament in tournaments" 
          :key="tournament.id"
          :tournament="tournament"
          @register="handleTeamRegistration"
        />
      </div>

      <!-- Pagination -->
      <div v-if="pagination.total > pagination.per_page" class="flex justify-center">
        <nav class="flex items-center space-x-2">
          <button 
            @click="goToPage(pagination.current_page - 1)"
            :disabled="pagination.current_page <= 1"
            class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Previous
          </button>
          
          <template v-for="page in visiblePages" :key="page">
            <button 
              v-if="page !== '...'"
              @click="goToPage(page)"
              :class="[
                'px-3 py-2 text-sm font-medium rounded-md',
                page === pagination.current_page 
                  ? 'bg-primary-600 text-white' 
                  : 'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50'
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
            Next
          </button>
        </nav>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-12">
      <CalendarIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
      <h3 class="text-lg font-medium text-gray-900 mb-2">
        {{ hasActiveFilters ? 'No tournaments match your filters' : 'No tournaments yet' }}
      </h3>
      <p class="text-gray-600 mb-6">
        {{ hasActiveFilters 
          ? 'Try adjusting your search criteria to find tournaments.' 
          : 'Be the first to create a tournament on our platform.' 
        }}
      </p>
      <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <button 
          v-if="hasActiveFilters"
          @click="clearAllFilters"
          class="btn-secondary"
        >
          Clear Filters
        </button>
        <RouterLink 
          v-if="authStore.isAdmin"
          to="/tournaments/create" 
          class="btn-primary"
        >
          Create Tournament
        </RouterLink>
      </div>
    </div>

    <!-- Team Registration Modal -->
    <TeamRegistrationModal 
      v-if="showRegistrationModal"
      :tournament="selectedTournament"
      @close="showRegistrationModal = false"
      @success="handleRegistrationSuccess"
    />
  </MainLayout>
</template>

<script>
/**
 * Tournaments List Page Component
 * Displays filterable list of tournaments with search and pagination
 */

import { ref, computed, onMounted, watch } from 'vue'
import { 
  PlusIcon,
  MagnifyingGlassIcon,
  CalendarIcon,
  XMarkIcon
} from '@heroicons/vue/24/outline'
import { useAuthStore } from '@/stores/auth'
import { tournamentAPI, apiHelpers } from '@/services/api'
import MainLayout from '@/components/layout/MainLayout.vue'
import TournamentCard from '@/components/tournaments/TournamentCard.vue'
import TeamRegistrationModal from '@/views/tournaments/TeamRegistrationModal.vue'

export default {
  name: 'Tournaments',
  components: {
    MainLayout,
    TournamentCard,
    TeamRegistrationModal,
    PlusIcon,
    MagnifyingGlassIcon,
    CalendarIcon,
    XMarkIcon
  },
  setup() {
    const authStore = useAuthStore()
    
    // Data
    const tournaments = ref([])
    const isLoading = ref(false)
    const showRegistrationModal = ref(false)
    const selectedTournament = ref(null)
    
    // Filters
    const filters = ref({
      search: '',
      status: '',
      sport_type: ''
    })
    
    // Pagination
    const pagination = ref({
      current_page: 1,
      last_page: 1,
      per_page: 12,
      total: 0
    })

    // Computed properties
    const hasActiveFilters = computed(() => {
      return filters.value.search || filters.value.status || filters.value.sport_type
    })

    const totalTournaments = computed(() => tournaments.value.length)
    const openRegistrations = computed(() => 
      tournaments.value.filter(t => t.status === 'registration_open').length
    )
    const inProgress = computed(() => 
      tournaments.value.filter(t => t.status === 'in_progress').length
    )
    const completed = computed(() => 
      tournaments.value.filter(t => t.status === 'completed').length
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
     * Fetch tournaments with current filters
     */
    const fetchTournaments = async (page = 1) => {
      isLoading.value = true
      try {
        const params = {
          page,
          per_page: pagination.value.per_page,
          ...filters.value
        }

        // Clean empty filters
        Object.keys(params).forEach(key => {
          if (params[key] === '' || params[key] == null) {
            delete params[key]
          }
        })

        const response = await tournamentAPI.getAll(params)
        
        if (apiHelpers.isSuccess(response)) {
          const data = apiHelpers.getData(response)
          tournaments.value = data.data || []
          
          // Update pagination
          pagination.value = {
            current_page: data.current_page || 1,
            last_page: data.last_page || 1,
            per_page: data.per_page || 12,
            total: data.total || 0
          }
        }
      } catch (error) {
        console.error('Failed to fetch tournaments:', error)
        window.$notify?.error('Failed to load tournaments')
        tournaments.value = []
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
      fetchTournaments(1)
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
        sport_type: ''
      }
      applyFilters()
    }

    /**
     * Go to specific page
     */
    const goToPage = (page) => {
      if (page >= 1 && page <= pagination.value.last_page) {
        pagination.value.current_page = page
        fetchTournaments(page)
      }
    }

    /**
     * Handle team registration
     */
    const handleTeamRegistration = (tournament) => {
      if (!authStore.isAuthenticated) {
        window.$notify?.warning('Please login to register for tournaments')
        return
      }

      if (!authStore.canManageTeams) {
        window.$notify?.warning('Only team managers can register for tournaments')
        return
      }

      selectedTournament.value = tournament
      showRegistrationModal.value = true
    }

    /**
     * Handle successful registration
     */
    const handleRegistrationSuccess = () => {
      showRegistrationModal.value = false
      selectedTournament.value = null
      window.$notify?.success('Team registered successfully!')
      // Refresh tournaments to update registration counts
      fetchTournaments(pagination.value.current_page)
    }

    /**
     * Format status for display
     */
    const formatStatus = (status) => {
      const statusMap = {
        'draft': 'Draft',
        'registration_open': 'Open',
        'in_progress': 'In Progress',
        'completed': 'Completed',
        'cancelled': 'Cancelled'
      }
      return statusMap[status] || status
    }

    // Initialize
    onMounted(() => {
      fetchTournaments()
    })

    return {
      authStore,
      tournaments,
      isLoading,
      filters,
      pagination,
      showRegistrationModal,
      selectedTournament,
      hasActiveFilters,
      totalTournaments,
      openRegistrations,
      inProgress,
      completed,
      visiblePages,
      fetchTournaments,
      debouncedSearch,
      applyFilters,
      clearFilter,
      clearAllFilters,
      goToPage,
      handleTeamRegistration,
      handleRegistrationSuccess,
      formatStatus
    }
  }
}
</script>