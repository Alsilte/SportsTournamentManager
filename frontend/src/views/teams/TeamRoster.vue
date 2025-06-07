<template>
  <MainLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <button @click="$router.go(-1)" class="btn-secondary p-2">
            <ArrowLeftIcon class="w-5 h-5" />
          </button>
          <div>
            <h1 class="text-3xl font-bold text-gray-900">
              {{ team?.name }} - {{ t('teams.roster.title') }}
            </h1>
            <p class="text-gray-600 mt-1">
              {{ t('teams.roster.manageRoster') }}
            </p>
          </div>
        </div>
        <div class="flex items-center space-x-3">
          <button 
            v-if="canManageTeam"
            @click="openAddPlayerModal"
            class="btn-primary"
          >
            <PlusIcon class="w-4 h-4 mr-2" />
            {{ t('teams.addPlayer') }}
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

    <!-- Team Roster Content -->
    <div v-else-if="team" class="space-y-8">
      <!-- Team Overview -->
      <div class="card p-6">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h2 class="text-xl font-semibold text-gray-900">{{ team.name }}</h2>
            <p class="text-gray-600">{{ team.description || $t('teams.noDescription') || 'No description available' }}</p>
          </div>
          <div class="text-right">
            <div class="text-2xl font-bold text-primary-600">{{ activePlayersCount }}</div>
            <div class="text-sm text-gray-600">{{ $t('teams.activePlayers') || 'Active Players' }}</div>
          </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="text-center p-4 bg-gray-50 rounded-lg">
            <div class="text-lg font-semibold text-gray-900">{{ totalPlayers }}</div>
            <div class="text-sm text-gray-600">{{ $t('teams.roster.players') || 'Players' }}</div>
          </div>
          <div class="text-center p-4 bg-gray-50 rounded-lg">
            <div class="text-lg font-semibold text-gray-900">{{ captainsCount }}</div>
            <div class="text-sm text-gray-600">{{ $t('teams.roster.captains') || 'Captains' }}</div>
          </div>
          <div class="text-center p-4 bg-gray-50 rounded-lg">
            <div class="text-lg font-semibold text-gray-900">{{ positionsCount }}</div>
            <div class="text-sm text-gray-600">{{ $t('teams.roster.positions') || 'Positions' }}</div>
          </div>
          <div class="text-center p-4 bg-gray-50 rounded-lg">
            <div class="text-lg font-semibold text-gray-900">{{ nationalitiesCount }}</div>
            <div class="text-sm text-gray-600">Nacionalidades</div>
          </div>
        </div>
      </div>

      <!-- Filters and Search -->
      <div class="card p-6">
        <div class="mb-4">
          <h3 class="text-lg font-medium text-gray-900 mb-4">{{ $t('teams.roster.playerRoster') || 'Player Roster' }}</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
          <!-- Search -->
          <div class="relative">
            <MagnifyingGlassIcon class="w-5 h-5 text-gray-400 absolute left-3 top-3" />
            <input
              v-model="filters.search"
              type="text"
              :placeholder="$t('teams.roster.searchPlayers') || 'Search players...'"
              class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            />
          </div>

          <!-- Position Filter -->
          <select v-model="filters.position" class="form-select">
            <option value="">{{ $t('teams.roster.allPositions') || 'All Positions' }}</option>
            <option v-for="position in uniquePositions" :key="position" :value="position">
              {{ position }}
            </option>
          </select>

          <!-- Status Filter -->
          <select v-model="filters.status" class="form-select">
            <option value="">{{ $t('teams.roster.allPlayers') || 'All Players' }}</option>
            <option value="captain">{{ $t('teams.roster.captain') || 'Captain' }}</option>
            <option value="active">Activos</option>
          </select>

          <!-- Clear Filters -->
          <button
            v-if="hasActiveFilters"
            @click="clearFilters"
            class="btn-secondary"
          >
            {{ $t('common.clear') || 'Clear' }}
          </button>
        </div>

        <!-- Players Table/List -->
        <div v-if="filteredPlayers.length > 0" class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('teams.roster.jerseyNumber') || 'Number' }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('teams.players.name') || 'Player' }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('teams.roster.position') || 'Position' }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('teams.players.age') || 'Age' }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('teams.players.nationality') || 'Nationality' }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('teams.roster.joinedDate') || 'Joined' }}
                </th>
                <th v-if="canManageTeam" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('common.actions') || 'Actions' }}
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="player in filteredPlayers" :key="player.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="jersey-number">
                    {{ player.jersey_number }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                        <UserIcon class="h-5 w-5 text-gray-500" />
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900 flex items-center">
                        {{ player.player?.user?.name || 'Unknown Player' }}
                        <StarIcon v-if="player.is_captain" class="captain-indicator" />
                      </div>
                      <div class="text-sm text-gray-500">
                        {{ player.player?.user?.email || 'No email' }}
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                    {{ player.position || $t('teams.roster.noPosition') || 'No position' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ calculateAge(player.player?.date_of_birth) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ player.player?.nationality || 'N/A' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatDate(player.joined_date) }}
                </td>
                <td v-if="canManageTeam" class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end space-x-2">
                    <button @click="editPlayer(player)" class="text-primary-600 hover:text-primary-900">
                      <PencilIcon class="h-4 w-4" />
                    </button>
                    <button @click="confirmRemovePlayer(player)" class="text-red-600 hover:text-red-900">
                      <TrashIcon class="h-4 w-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-12">
          <UsersIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
          <h3 class="text-lg font-medium text-gray-900 mb-2">
            {{ hasActiveFilters 
              ? ($t('teams.roster.noPlayersMatch') || 'No players match your filters') 
              : ($t('teams.roster.noPlayersYet') || 'No players in this team yet') 
            }}
          </h3>
          <p class="text-gray-600 mb-6">
            {{ hasActiveFilters 
              ? ($t('teams.roster.tryDifferentFilters') || 'Try adjusting your filters') 
              : ($t('teams.roster.addFirstPlayer') || 'Add the first player to get started') 
            }}
          </p>
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <button v-if="hasActiveFilters" @click="clearFilters" class="btn-secondary">
              {{ $t('common.clear') || 'Clear Filters' }}
            </button>
            <button v-if="canManageTeam" @click="openAddPlayerModal" class="btn-primary">
              {{ t('teams.addPlayer') || 'Add Players' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="text-center py-12">
      <ExclamationTriangleIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
      <h3 class="text-lg font-medium text-gray-900 mb-2">{{ $t('errors.teamNotFound') || 'Team not found' }}</h3>
      <p class="text-gray-600 mb-6">{{ error }}</p>
      <RouterLink to="/teams" class="btn-primary">
        {{ $t('teams.roster.backToTeams') || 'Back to Teams' }}
      </RouterLink>
    </div>

    <!-- Add Player Modal -->
    <AddPlayerModal
      :show="showAddPlayerModal"
      :team-id="Number(route.params.id)"
      :is-admin="authStore.isAdmin"
      @close="handleCloseModal"
      @success="handlePlayerAdded"
    />
  </MainLayout>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import AddPlayerModal from '@/components/teams/AddPlayerModal.vue'
import {
  ArrowLeftIcon,
  PlusIcon,
  UserIcon,
  UsersIcon,
  StarIcon,
  PencilIcon,
  TrashIcon,
  MagnifyingGlassIcon,
  ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline'
import { useAuthStore } from '@/stores/auth'
import { teamAPI, apiHelpers } from '@/services/api'
import MainLayout from '@/components/layout/MainLayout.vue'

export default {
  name: 'TeamRoster',
  components: {
    MainLayout,
    AddPlayerModal,
    ArrowLeftIcon,
    PlusIcon,
    UserIcon,
    UsersIcon,
    StarIcon,
    PencilIcon,
    TrashIcon,
    MagnifyingGlassIcon,
    ExclamationTriangleIcon,
  },
  setup() {
    const { t } = useI18n()
    const route = useRoute()
    const authStore = useAuthStore()

    // State
    const team = ref(null)
    const players = ref([])
    const isLoading = ref(true)
    const error = ref('')
    const showAddPlayerModal = ref(false)

    // Filters
    const filters = ref({
      search: '',
      position: '',
      status: ''
    })

    // Computed properties
    const canManageTeam = computed(() => {
      if (!team.value || !authStore.user) return false
      return authStore.isAdmin || team.value.manager_id === authStore.user.id
    })

    const totalPlayers = computed(() => players.value.length)

    const activePlayersCount = computed(() => 
      players.value.filter(p => p.is_active).length
    )

    const captainsCount = computed(() => 
      players.value.filter(p => p.is_captain && p.is_active).length
    )

    const positionsCount = computed(() => {
      const positions = new Set(players.value.filter(p => p.position).map(p => p.position))
      return positions.size
    })

    const nationalitiesCount = computed(() => {
      const nationalities = new Set(
        players.value
          .filter(p => p.player?.nationality)
          .map(p => p.player.nationality)
      )
      return nationalities.size
    })

    const uniquePositions = computed(() => {
      const positions = new Set(players.value.filter(p => p.position).map(p => p.position))
      return Array.from(positions)
    })

    const hasActiveFilters = computed(() => {
      return filters.value.search || filters.value.position || filters.value.status
    })

    const filteredPlayers = computed(() => {
      return players.value.filter(player => {
        const matchesSearch = !filters.value.search || 
          player.player?.user?.name?.toLowerCase().includes(filters.value.search.toLowerCase())

        const matchesPosition = !filters.value.position || 
          player.position === filters.value.position

        const matchesStatus = !filters.value.status || 
          (filters.value.status === 'captain' && player.is_captain) ||
          (filters.value.status === 'active' && player.is_active)

        return matchesSearch && matchesPosition && matchesStatus
      })
    })

    // Methods
    const fetchTeamRoster = async () => {
      isLoading.value = true
      error.value = ''

      try {
        const teamId = route.params.id
        const response = await teamAPI.show(teamId)

        if (apiHelpers.isSuccess(response)) {
          const data = apiHelpers.getData(response)
          team.value = data.team
          
          // Fetch team players
          const playersResponse = await teamAPI.getPlayers(teamId)
          if (apiHelpers.isSuccess(playersResponse)) {
            players.value = apiHelpers.getData(playersResponse).players || []
          }
        } else {
          error.value = 'Failed to load team information'
        }
      } catch (err) {
        console.error('Failed to fetch team roster:', err)
        error.value = apiHelpers.handleError(err)
      } finally {
        isLoading.value = false
      }
    }

    const clearFilters = () => {
      filters.value = {
        search: '',
        position: '',
        status: ''
      }
    }

    const editPlayer = (player) => {
      console.log('Edit player:', player)
      window.$notify?.info('Edit player functionality coming soon')
    }

    const confirmRemovePlayer = (player) => {
      if (confirm(`Remove ${player.player?.user?.name} from the team?`)) {
        removePlayer(player)
      }
    }

    const removePlayer = async (player) => {
      try {
        const response = await teamAPI.removePlayer(team.value.id, player.player_id)

        if (apiHelpers.isSuccess(response)) {
          players.value = players.value.filter(p => p.id !== player.id)
          window.$notify?.success('Player removed successfully')
        } else {
          window.$notify?.error('Failed to remove player')
        }
      } catch (err) {
        console.error('Failed to remove player:', err)
        window.$notify?.error(apiHelpers.handleError(err))
      }
    }

    const calculateAge = (dateOfBirth) => {
      if (!dateOfBirth) return 'N/A'
      const today = new Date()
      const birthDate = new Date(dateOfBirth)
      let age = today.getFullYear() - birthDate.getFullYear()
      const monthDiff = today.getMonth() - birthDate.getMonth()
      
      if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--
      }
      
      return age
    }

    const formatDate = (dateString) => {
      if (!dateString) return 'N/A'
      return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      })
    }

    // Modal methods
    const openAddPlayerModal = () => {
      showAddPlayerModal.value = true
    }

    const handleCloseModal = () => {
      showAddPlayerModal.value = false
    }

    const handlePlayerAdded = async () => {
      await fetchTeamRoster()
      handleCloseModal()
      window.$notify?.success('Jugador añadido correctamente')
    }

    // Initialize
    onMounted(() => {
      fetchTeamRoster()
    })

    return {
      t,
      authStore,
      team,
      players,
      isLoading,
      error,
      showAddPlayerModal,
      filters,
      canManageTeam,
      totalPlayers,
      activePlayersCount,
      captainsCount,
      positionsCount,
      nationalitiesCount,
      uniquePositions,
      hasActiveFilters,
      filteredPlayers,
      fetchTeamRoster,
      clearFilters,
      editPlayer,
      confirmRemovePlayer,
      removePlayer,
      calculateAge,
      formatDate,
      openAddPlayerModal,
      handleCloseModal,
      handlePlayerAdded
    }
  }
}
</script>

<style scoped>
.jersey-number {
  @apply w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center font-bold text-primary-600;
}

.captain-indicator {
  @apply w-4 h-4 ml-2 text-yellow-500;
}

.form-select {
  @apply w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500;
}
</style>