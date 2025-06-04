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
              {{ team?.name }} - {{ $t('teams.roster') || 'Team Roster' }}
            </h1>
            <p class="text-gray-600 mt-1">
              {{ $t('teams.manageRoster') || 'Manage team players' }}
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
          <div class="bg-primary-50 rounded-lg p-4 text-center">
            <div class="text-lg font-bold text-primary-600">{{ totalPlayers }}</div>
            <div class="text-xs text-gray-600">{{ $t('common.total') || 'Total' }}</div>
          </div>
          <div class="bg-success-50 rounded-lg p-4 text-center">
            <div class="text-lg font-bold text-success-600">{{ activePlayersCount }}</div>
            <div class="text-xs text-gray-600">{{ $t('common.active') || 'Active' }}</div>
          </div>
          <div class="bg-warning-50 rounded-lg p-4 text-center">
            <div class="text-lg font-bold text-warning-600">{{ captainsCount }}</div>
            <div class="text-xs text-gray-600">{{ $t('teams.captains') || 'Captains' }}</div>
          </div>
          <div class="bg-secondary-50 rounded-lg p-4 text-center">
            <div class="text-lg font-bold text-secondary-600">{{ positionsCount }}</div>
            <div class="text-xs text-gray-600">{{ $t('teams.positions') || 'Positions' }}</div>
          </div>
        </div>
      </div>

      <!-- Roster Filters -->
      <div class="card p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Search -->
          <div>
            <label class="form-label">{{ $t('common.search') || 'Search' }}</label>
            <div class="relative">
              <MagnifyingGlassIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
              <input
                v-model="filters.search"
                type="text"
                :placeholder="$t('teams.searchPlayers') || 'Search players...'"
                class="form-input pl-10"
                @input="applyFilters"
              />
            </div>
          </div>

          <!-- Position Filter -->
          <div>
            <label class="form-label">{{ $t('teams.position') || 'Position' }}</label>
            <select v-model="filters.position" @change="applyFilters" class="form-input">
              <option value="">{{ $t('teams.allPositions') || 'All Positions' }}</option>
              <option v-for="position in uniquePositions" :key="position" :value="position">
                {{ position }}
              </option>
            </select>
          </div>

          <!-- Status Filter -->
          <div>
            <label class="form-label">{{ $t('common.status') || 'Status' }}</label>
            <select v-model="filters.status" @change="applyFilters" class="form-input">
              <option value="">{{ $t('teams.allPlayers') || 'All Players' }}</option>
              <option value="active">{{ $t('common.active') || 'Active' }}</option>
              <option value="inactive">{{ $t('common.inactive') || 'Inactive' }}</option>
              <option value="captain">{{ $t('teams.captains') || 'Captains' }}</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Players Roster -->
      <div class="card p-6">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-xl font-semibold text-gray-900">
            {{ $t('teams.playerRoster') || 'Player Roster' }}
          </h2>
          <div class="text-sm text-gray-600">
            {{ filteredPlayers.length }} {{ $t('teams.players') || 'players' }}
          </div>
        </div>

        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('teams.jerseyNumber') || 'Jersey #' }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('players.name') || 'Player' }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('teams.position') || 'Position' }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('players.age') || 'Age' }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('teams.joinedDate') || 'Joined' }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('common.status') || 'Status' }}
                </th>
                <th v-if="canManageTeam" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('common.actions') || 'Actions' }}
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="player in filteredPlayers" :key="player.id" class="hover:bg-gray-50">
                <!-- Jersey Number -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center">
                      <span class="font-bold text-primary-600">
                        {{ player.pivot?.jersey_number || '-' }}
                      </span>
                    </div>
                  </div>
                </td>

                <!-- Player Info -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center mr-4">
                      <UserIcon class="w-5 h-5 text-gray-600" />
                    </div>
                    <div>
                      <div class="text-sm font-medium text-gray-900 flex items-center">
                        {{ player.user?.name }}
                        <StarIcon 
                          v-if="player.pivot?.is_captain" 
                          class="w-4 h-4 ml-2 text-warning-500" 
                          :title="$t('teams.captain') || 'Captain'"
                        />
                      </div>
                      <div class="text-sm text-gray-500">{{ player.nationality || 'Unknown' }}</div>
                      <div class="text-xs text-gray-400">{{ player.height }}cm • {{ player.weight }}kg</div>
                    </div>
                  </div>
                </td>

                <!-- Position -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-secondary-100 text-secondary-800">
                    {{ player.pivot?.position || $t('teams.noPosition') || 'No position' }}
                  </span>
                </td>

                <!-- Age -->
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ calculateAge(player.date_of_birth) }}
                </td>

                <!-- Joined Date -->
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(player.pivot?.joined_date) }}
                </td>

                <!-- Status -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="[
                    'px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full',
                    player.pivot?.is_active 
                      ? 'bg-success-100 text-success-800' 
                      : 'bg-gray-100 text-gray-800'
                  ]">
                    {{ player.pivot?.is_active ? ($t('common.active') || 'Active') : ($t('common.inactive') || 'Inactive') }}
                  </span>
                </td>

                <!-- Actions -->
                <td v-if="canManageTeam" class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end space-x-2">
                    <button
                      @click="editPlayer(player)"
                      class="text-primary-600 hover:text-primary-900"
                      :title="$t('common.edit') || 'Edit'"
                    >
                      <PencilIcon class="w-4 h-4" />
                    </button>
                    <button
                      @click="confirmRemovePlayer(player)"
                      class="text-danger-600 hover:text-danger-900"
                      :title="$t('teams.removePlayer') || 'Remove'"
                    >
                      <TrashIcon class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Mobile Card View -->
        <div class="md:hidden space-y-4">
          <div 
            v-for="player in filteredPlayers" 
            :key="player.id"
            class="border border-gray-200 rounded-lg p-4"
          >
            <div class="flex items-center justify-between mb-3">
              <div class="flex items-center">
                <div class="w-12 h-12 bg-primary-100 rounded-full flex items-center justify-center mr-3">
                  <span class="font-bold text-primary-600">
                    {{ player.pivot?.jersey_number || '-' }}
                  </span>
                </div>
                <div>
                  <h3 class="font-medium text-gray-900 flex items-center">
                    {{ player.user?.name }}
                    <StarIcon 
                      v-if="player.pivot?.is_captain" 
                      class="w-4 h-4 ml-2 text-warning-500" 
                    />
                  </h3>
                  <p class="text-sm text-gray-600">{{ player.pivot?.position || $t('teams.noPosition') || 'No position' }}</p>
                </div>
              </div>
              <span :class="[
                'px-2 py-1 text-xs font-semibold rounded-full',
                player.pivot?.is_active 
                  ? 'bg-success-100 text-success-800' 
                  : 'bg-gray-100 text-gray-800'
              ]">
                {{ player.pivot?.is_active ? ($t('common.active') || 'Active') : ($t('common.inactive') || 'Inactive') }}
              </span>
            </div>

            <div class="space-y-2 text-sm text-gray-600 mb-4">
              <div class="flex justify-between">
                <span>{{ $t('players.age') || 'Age' }}:</span>
                <span>{{ calculateAge(player.date_of_birth) }}</span>
              </div>
              <div class="flex justify-between">
                <span>{{ $t('players.nationality') || 'Nationality' }}:</span>
                <span>{{ player.nationality || 'Unknown' }}</span>
              </div>
              <div class="flex justify-between">
                <span>{{ $t('teams.joinedDate') || 'Joined' }}:</span>
                <span>{{ formatDate(player.pivot?.joined_date) }}</span>
              </div>
              <div class="flex justify-between">
                <span>{{ $t('common.email') || 'Email' }}:</span>
                <span>{{ player.user?.email }}</span>
              </div>
            </div>

            <div v-if="canManageTeam" class="flex space-x-2">
              <button @click="editPlayer(player)" class="btn-secondary text-xs px-3 py-1 flex-1">
                {{ $t('common.edit') || 'Edit' }}
              </button>
              <button @click="confirmRemovePlayer(player)" class="btn-danger text-xs px-3 py-1 flex-1">
                {{ $t('teams.removePlayer') || 'Remove' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="filteredPlayers.length === 0" class="text-center py-12">
          <UsersIcon class="w-12 h-12 text-gray-300 mx-auto mb-4" />
          <h3 class="text-lg font-medium text-gray-900 mb-2">
            {{ hasActiveFilters ? ($t('teams.noPlayersMatch') || 'No players match your filters') : ($t('teams.noPlayersYet') || 'No players in this team yet') }}
          </h3>
          <p class="text-gray-600 mb-6">
            {{ hasActiveFilters 
              ? ($t('teams.tryDifferentFilters') || 'Try adjusting your filters') 
              : ($t('teams.addFirstPlayer') || 'Add the first player to get started') 
            }}
          </p>
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <button v-if="hasActiveFilters" @click="clearFilters" class="btn-secondary">
              {{ $t('common.clearFilters') || 'Clear Filters' }}
            </button>
            <button v-if="canManageTeam" @click="showAddPlayerModal = true" class="btn-primary">
              {{ $t('teams.addPlayer') || 'Add Player' }}
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
        {{ $t('teams.backToTeams') || 'Back to Teams' }}
      </RouterLink>
    </div>

    <!-- Add Player Modal -->
    <div v-if="showAddPlayerModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg max-w-md w-full p-6">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-lg font-semibold text-gray-900">{{ t('teams.addPlayer') }}</h3>
          <button @click="closeAddPlayerModal" class="text-gray-400 hover:text-gray-600">
            <XMarkIcon class="w-5 h-5" />
          </button>
        </div>

        <!-- Estado de carga -->
        <div v-if="isLoadingPlayers" class="text-center py-4">
          <div class="spinner w-6 h-6 mx-auto mb-2"></div>
          <p class="text-gray-600">{{ t('teams.loadingPlayers') }}</p>
        </div>

        <!-- Formulario -->
        <form v-else @submit.prevent="addPlayerToTeam" class="space-y-4">
          <!-- Jugador -->
          <div>
            <label class="form-label" for="player_id">
              {{ $t('teams.player') || 'Player' }}
            </label>
            <div class="relative">
              <select
                v-model="newPlayerForm.player_id"
                id="player_id"
                class="form-input"
                required
              >
                <option value="">{{ $t('common.select') || 'Select' }}</option>
                <option v-for="player in availablePlayers" :key="player.id" :value="player.id">
                  {{ player.name }} ({{ player.email }})
                </option>
              </select>
              <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <UserIcon class="w-5 h-5 text-gray-400" />
              </div>
            </div>
          </div>

          <!-- Número de camiseta -->
          <div>
            <label class="form-label" for="jersey_number">
              {{ $t('teams.jerseyNumber') || 'Jersey Number' }}
            </label>
            <div class="relative">
              <input
                v-model="newPlayerForm.jersey_number"
                id="jersey_number"
                type="number"
                min="1"
                class="form-input"
                placeholder="10"
                required
              />
              <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <span class="text-gray-400">#</span>
              </div>
            </div>
          </div>

          <!-- Posición -->
          <div>
            <label class="form-label" for="position">
              {{ $t('teams.position') || 'Position' }}
            </label>
            <div class="relative">
              <input
                v-model="newPlayerForm.position"
                id="position"
                type="text"
                class="form-input"
                placeholder="{{ $t('teams.positionPlaceholder') || 'e.g. Forward' }}"
                required
              />
              <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <PencilIcon class="w-5 h-5 text-gray-400" />
              </div>
            </div>
          </div>

          <!-- Capitán -->
          <div class="flex items-center">
            <input
              v-model="newPlayerForm.is_captain"
              id="is_captain"
              type="checkbox"
              class="form-checkbox h-5 w-5 text-primary-600 border-gray-300 rounded"
            />
            <label for="is_captain" class="ml-2 text-sm text-gray-700">
              {{ $t('teams.assignCaptain') || 'Assign as captain' }}
            </label>
          </div>

          <!-- Fecha de ingreso -->
          <div>
            <label class="form-label" for="joined_date">
              {{ $t('teams.joinedDate') || 'Joined Date' }}
            </label>
            <div class="relative">
              <input
                v-model="newPlayerForm.joined_date"
                id="joined_date"
                type="date"
                class="form-input"
                :max="today"
                required
              />
              <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <CalendarIcon class="w-5 h-5 text-gray-400" />
              </div>
            </div>
          </div>

          <!-- Error message -->
          <div v-if="addPlayerError" class="text-red-500 text-sm mt-2">
            {{ addPlayerError }}
          </div>

          <!-- Botones de acción -->
          <div class="flex justify-end gap-4">
            <button 
              @click="closeAddPlayerModal" 
              class="btn-secondary"
            >
              {{ $t('common.cancel') || 'Cancel' }}
            </button>
            <button 
              type="submit" 
              class="btn-primary"
              :disabled="isSubmitting"
            >
              <span v-if="isSubmitting" class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>
              {{ $t('common.add') || 'Add' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </MainLayout>
</template>

<script>
/**
 * Team Roster Component
 * Complete team roster management with API integration
 */

import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n' // Asegúrate de que esta importación esté presente
import { XMarkIcon } from '@heroicons/vue/24/outline'


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
  XMarkIcon,
} from '@heroicons/vue/24/outline'
import { useAuthStore } from '@/stores/auth'
import { teamAPI, apiHelpers, playerAPI } from '@/services/api'
import MainLayout from '@/components/layout/MainLayout.vue'

export default {
  name: 'TeamRoster',
  components: {
    MainLayout,
    ArrowLeftIcon,
    PlusIcon,
    UserIcon,
    UsersIcon,
    StarIcon,
    PencilIcon,
    TrashIcon,
    MagnifyingGlassIcon,
    ExclamationTriangleIcon,
    XMarkIcon,
  },
  setup() {
    const { t } = useI18n() // Debe estar al inicio del setup
    const route = useRoute()
    const authStore = useAuthStore()

    // Data
    const team = ref(null)
    const players = ref([])
    const captain = ref(null)
    const isLoading = ref(false)
    const error = ref('')
    const showAddPlayerModal = ref(false)

    // Filters
    const filters = ref({
      search: '',
      position: '',
      status: ''
    })

    // Nuevas variables reactivas
    const availablePlayers = ref([])
    const isLoadingPlayers = ref(false)
    const isSubmitting = ref(false)
    const addPlayerError = ref('')
    
    const newPlayerForm = ref({
      player_id: '',
      jersey_number: '',
      position: '',
      is_captain: false,
      joined_date: new Date().toISOString().split('T')[0]
    })

    const today = computed(() => new Date().toISOString().split('T')[0])

    // Computed properties
    const canManageTeam = computed(() => {
      return authStore.isAdmin || team.value?.manager_id === authStore.user?.id
    })

    const totalPlayers = computed(() => players.value.length)

    const activePlayersCount = computed(() => 
      players.value.filter(player => player.pivot?.is_active).length
    )

    const captainsCount = computed(() => 
      players.value.filter(player => player.pivot?.is_captain).length
    )

    const uniquePositions = computed(() => {
      const positions = players.value
        .map(player => player.pivot?.position)
        .filter(position => position && position.trim())
      return [...new Set(positions)].sort()
    })

    const positionsCount = computed(() => uniquePositions.value.length)

    const uniqueNationalities = computed(() => {
      const nationalities = players.value
        .map(player => player.nationality)
        .filter(nationality => nationality && nationality.trim())
      return [...new Set(nationalities)].sort()
    })

    const nationalitiesCount = computed(() => uniqueNationalities.value.length)

    const hasActiveFilters = computed(() => 
      filters.value.search || filters.value.position || filters.value.status
    )

    const filteredPlayers = computed(() => {
      let filtered = [...players.value]

      // Search filter
      if (filters.value.search) {
        const searchTerm = filters.value.search.toLowerCase()
        filtered = filtered.filter(player => 
          player.user?.name?.toLowerCase().includes(searchTerm) ||
          player.user?.email?.toLowerCase().includes(searchTerm) ||
          player.pivot?.position?.toLowerCase().includes(searchTerm)
        )
      }

      // Position filter
      if (filters.value.position) {
        filtered = filtered.filter(player => 
          player.pivot?.position === filters.value.position
        )
      }

      // Status filter
      if (filters.value.status) {
        switch (filters.value.status) {
          case 'active':
            filtered = filtered.filter(player => player.pivot?.is_active)
            break
          case 'inactive':
            filtered = filtered.filter(player => !player.pivot?.is_active)
            break
          case 'captain':
            filtered = filtered.filter(player => player.pivot?.is_captain)
            break
        }
      }

      // Sort by jersey number
      return filtered.sort((a, b) => {
        const jerseyA = a.pivot?.jersey_number || 999
        const jerseyB = b.pivot?.jersey_number || 999
        return jerseyA - jerseyB
      })
    })

    /**
     * Fetch team and roster data
     */
    const fetchTeamRoster = async () => {
      isLoading.value = true
      error.value = ''

      try {
        // Fetch team roster
        const response = await teamAPI.getRoster(route.params.id)

        if (apiHelpers.isSuccess(response)) {
          const data = apiHelpers.getData(response)
          team.value = data.team
          players.value = data.players || []
          captain.value = data.captain || null
        } else {
          error.value = 'Team not found or no access'
        }
      } catch (err) {
        console.error('Failed to fetch team roster:', err)
        error.value = apiHelpers.handleError(err)
      } finally {
        isLoading.value = false
      }
    }

    /**
     * Fetch available players for adding to team
     */
    const fetchAvailablePlayers = async () => {
      isLoadingPlayers.value = true
      addPlayerError.value = ''

      try {
        const response = await playerAPI.available()
        
        if (apiHelpers.isSuccess(response)) {
          const data = apiHelpers.getData(response)
          availablePlayers.value = data.available_players || []
        } else {
          addPlayerError.value = t('errors.failedToLoadPlayers')
          availablePlayers.value = []
        }
      } catch (error) {
        console.error('Failed to fetch available players:', error)
        addPlayerError.value = t('errors.failedToLoadPlayers')
        availablePlayers.value = []
      } finally {
        isLoadingPlayers.value = false
      }
    }

    /**
     * Apply filters (for future debounced search)
     */
    const applyFilters = () => {
      // Filters are reactive, no need to do anything
      console.log('Filters applied:', filters.value)
    }

    /**
     * Clear all filters
     */
    const clearFilters = () => {
      filters.value = {
        search: '',
        position: '',
        status: ''
      }
    }

    /**
     * Open Add Player modal and fetch available players
     */
    const openAddPlayerModal = () => {
      showAddPlayerModal.value = true
      fetchAvailablePlayers()
    }

    /**
     * Close Add Player modal
     */
    const closeAddPlayerModal = () => {
      showAddPlayerModal.value = false
      availablePlayers.value = []
      addPlayerError.value = ''
      
      newPlayerForm.value = {
        player_id: '',
        jersey_number: '',
        position: '',
        is_captain: false,
        joined_date: new Date().toISOString().split('T')[0]
      }
    }

    /**
     * Edit player (placeholder)
     */
    const editPlayer = (player) => {
      console.log('Edit player:', player)
      window.$notify?.info('Edit player functionality coming soon')
    }

    /**
     * Confirm remove player
     */
    const confirmRemovePlayer = (player) => {
      if (confirm(`Remove ${player.user?.name} from the team?`)) {
        removePlayer(player)
      }
    }

    /**
     * Remove player from team
     */
    const removePlayer = async (player) => {
      try {
        const response = await teamAPI.removePlayer(team.value.id, player.id)

        if (apiHelpers.isSuccess(response)) {
          // Remove player from local array
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

    /**
     * Add player to team
     */
    const addPlayerToTeam = async () => {
      addPlayerError.value = ''
      isSubmitting.value = true

      try {
        const response = await teamAPI.addPlayer(route.params.id, newPlayerForm.value)

        if (apiHelpers.isSuccess(response)) {
          closeAddPlayerModal()
          await fetchTeamRoster()
          window.$notify?.success(t('teams.playerAddedSuccess'))
        } else {
          addPlayerError.value = response.data?.message || t('errors.failedToAddPlayer')
        }
      } catch (error) {
        console.error('Failed to add player:', error)
        addPlayerError.value = apiHelpers.handleError(error)
      } finally {
        isSubmitting.value = false
      }
    }

    /**
     * Calculate age from date of birth
     */
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

    /**
     * Format date for display
     */
    const formatDate = (dateString) => {
      if (!dateString) return 'N/A'
      return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      })
    }

    // Initialize
    onMounted(() => {
      fetchTeamRoster()
    })

    return {
      authStore,
      team,
      players,
      captain,
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
      availablePlayers,
      isLoadingPlayers,
      isSubmitting,
      addPlayerError,
      newPlayerForm,
      today,
      openAddPlayerModal,
      closeAddPlayerModal,
      applyFilters,
      clearFilters,
      editPlayer,
      confirmRemovePlayer,
      calculateAge,
      formatDate,
      addPlayerToTeam,
    }
  }
}
</script>

<style scoped>
/* Custom styles for better visual hierarchy */
.jersey-number {
  @apply w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center font-bold text-primary-600;
}

.player-card {
  @apply border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow;
}

.status-badge {
  @apply px-2 py-1 text-xs font-semibold rounded-full;
}

.captain-indicator {
  @apply w-4 h-4 ml-2 text-warning-500;
}

/* Responsive table improvements */
@media (max-width: 768px) {
  .mobile-card {
    @apply space-y-4;
  }
}

/* Filter section styling */
.filter-section {
  @apply grid grid-cols-1 md:grid-cols-3 gap-4;
}

/* Action buttons styling */
.action-buttons {
  @apply flex items-center justify-end space-x-2;
}

/* Empty state styling */
.empty-state {
  @apply text-center py-12;
}

.empty-state-icon {
  @apply w-12 h-12 text-gray-300 mx-auto mb-4;
}

.spinner {
  border: 2px solid #f3f3f3;
  border-top: 2px solid #3498db;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>