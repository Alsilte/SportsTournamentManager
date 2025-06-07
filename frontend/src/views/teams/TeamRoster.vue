<template>
  <MainLayout>
    <!-- Loading State -->
    <div v-if="isLoading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
    </div>

    <!-- Main Content -->
    <div v-else-if="team" class="space-y-6">
      <!-- Team Header -->
      <div class="card p-6">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <RouterLink to="/teams" class="btn-secondary p-2">
              <ArrowLeftIcon class="w-5 h-5" />
            </RouterLink>
            <div>
              <h1 class="text-2xl font-bold text-gray-900">{{ team.name }}</h1>
              <p class="text-gray-600">Gestión de Plantilla</p>
            </div>
          </div>
          <button 
            v-if="canManageTeam" 
            @click="openAddPlayerModal" 
            class="btn-primary flex items-center"
          >
            <PlusIcon class="w-5 h-5 mr-2" />
            Agregar Jugador
          </button>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
          <div class="bg-primary-50 rounded-lg p-4 text-center">
            <div class="text-lg font-bold text-primary-600">{{ totalPlayers }}</div>
            <div class="text-xs text-gray-600">Total</div>
          </div>
          <div class="bg-success-50 rounded-lg p-4 text-center">
            <div class="text-lg font-bold text-success-600">{{ activePlayersCount }}</div>
            <div class="text-xs text-gray-600">Activos</div>
          </div>
          <div class="bg-warning-50 rounded-lg p-4 text-center">
            <div class="text-lg font-bold text-warning-600">{{ captainsCount }}</div>
            <div class="text-xs text-gray-600">Capitanes</div>
          </div>
          <div class="bg-secondary-50 rounded-lg p-4 text-center">
            <div class="text-lg font-bold text-secondary-600">{{ positionsCount }}</div>
            <div class="text-xs text-gray-600">Posiciones</div>
          </div>
        </div>
      </div>

      <!-- Roster Filters -->
      <div class="card p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Search -->
          <div>
            <label class="form-label">Buscar</label>
            <div class="relative">
              <MagnifyingGlassIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
              <input
                v-model="filters.search"
                type="text"
                placeholder="Buscar jugadores..."
                class="form-input pl-10"
              />
            </div>
          </div>

          <!-- Position Filter -->
          <div>
            <label class="form-label">Posición</label>
            <select v-model="filters.position" class="form-input">
              <option value="">Todas las posiciones</option>
              <option v-for="position in uniquePositions" :key="position" :value="position">
                {{ position }}
              </option>
            </select>
          </div>

          <!-- Status Filter -->
          <div>
            <label class="form-label">Estado</label>
            <select v-model="filters.status" class="form-input">
              <option value="">Todos los jugadores</option>
              <option value="active">Activos</option>
              <option value="inactive">Inactivos</option>
              <option value="captain">Capitanes</option>
            </select>
          </div>
        </div>

        <!-- Clear Filters Button -->
        <div v-if="hasActiveFilters" class="mt-4">
          <button @click="clearFilters" class="btn-secondary text-sm">
            Limpiar filtros
          </button>
        </div>
      </div>

      <!-- Players Roster -->
      <div class="card p-6">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-xl font-semibold text-gray-900">
            Plantilla de Jugadores
          </h2>
          <div class="text-sm text-gray-600">
            {{ filteredPlayers.length }} jugadores
          </div>
        </div>

        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Dorsal
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Jugador
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Posición
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Edad
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Fecha de Unión
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Estado
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="player in filteredPlayers" :key="player.id" class="hover:bg-gray-50">
                <!-- Jersey Number -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center">
                    <span class="font-bold text-primary-600">
                      {{ player.pivot?.jersey_number || '-' }}
                    </span>
                  </div>
                </td>

                <!-- Player Name -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <UserIcon class="h-10 w-10 rounded-full bg-gray-200 p-2 text-gray-400" />
                    </div>
                    <div class="ml-4">
                      <div class="flex items-center">
                        <div class="text-sm font-medium text-gray-900">
                          {{ player.user?.name }}
                        </div>
                        <StarIcon 
                          v-if="player.pivot?.is_captain" 
                          class="w-4 h-4 ml-2 text-warning-500" 
                        />
                      </div>
                      <div class="text-sm text-gray-500">{{ player.user?.email }}</div>
                    </div>
                  </div>
                </td>

                <!-- Position -->
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ player.pivot?.position || 'Sin posición' }}
                </td>

                <!-- Age -->
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ calculateAge(player.date_of_birth) }}
                </td>

                <!-- Joined Date -->
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(player.pivot?.joined_date) }}
                </td>

                <!-- Status -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="[
                    'px-2 py-1 text-xs font-semibold rounded-full',
                    player.pivot?.is_active 
                      ? 'bg-success-100 text-success-800' 
                      : 'bg-gray-100 text-gray-800'
                  ]">
                    {{ player.pivot?.is_active ? 'Activo' : 'Inactivo' }}
                  </span>
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
                  <p class="text-sm text-gray-600">{{ player.pivot?.position || 'Sin posición' }}</p>
                </div>
              </div>
              <span :class="[
                'px-2 py-1 text-xs font-semibold rounded-full',
                player.pivot?.is_active 
                  ? 'bg-success-100 text-success-800' 
                  : 'bg-gray-100 text-gray-800'
              ]">
                {{ player.pivot?.is_active ? 'Activo' : 'Inactivo' }}
              </span>
            </div>

            <div class="space-y-2 text-sm text-gray-600">
              <div class="flex justify-between">
                <span>Edad:</span>
                <span>{{ calculateAge(player.date_of_birth) }}</span>
              </div>
              <div class="flex justify-between">
                <span>Nacionalidad:</span>
                <span>{{ player.nationality || 'Desconocida' }}</span>
              </div>
              <div class="flex justify-between">
                <span>Fecha de unión:</span>
                <span>{{ formatDate(player.pivot?.joined_date) }}</span>
              </div>
              <div class="flex justify-between">
                <span>Correo:</span>
                <span>{{ player.user?.email }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="filteredPlayers.length === 0" class="text-center py-12">
          <UsersIcon class="w-12 h-12 text-gray-300 mx-auto mb-4" />
          <h3 class="text-lg font-medium text-gray-900 mb-2">
            {{ hasActiveFilters ? 'No hay jugadores que coincidan' : 'Aún no hay jugadores' }}
          </h3>
          <p class="text-gray-600 mb-6">
            {{ hasActiveFilters 
              ? 'Prueba con filtros diferentes' 
              : 'Agrega tu primer jugador para comenzar' 
            }}
          </p>
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <button v-if="hasActiveFilters" @click="clearFilters" class="btn-secondary">
              Limpiar filtros
            </button>
            <button v-if="canManageTeam" @click="showAddPlayerModal = true" class="btn-primary">
              Agregar Jugador
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="text-center py-12">
      <ExclamationTriangleIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
      <h3 class="text-lg font-medium text-gray-900 mb-2">Equipo no encontrado</h3>
      <p class="text-gray-600 mb-6">{{ error }}</p>
      <RouterLink to="/teams" class="btn-primary">
        Volver a equipos
      </RouterLink>
    </div>

<AddPlayerModal
  v-if="showAddPlayerModal"
  :show="showAddPlayerModal"
  :team-id="Number(route.params.id)"
  @close="handleCloseModal"
  @success="handlePlayerAdded"
/>
  </MainLayout>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import AddPlayerModal from '@/components/teams/AddPlayerModal.vue'
import {
  ArrowLeftIcon,
  PlusIcon,
  UserIcon,
  UsersIcon,
  StarIcon,
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
    MagnifyingGlassIcon,
    ExclamationTriangleIcon,
  },
  setup() {
    const route = useRoute()
    const authStore = useAuthStore()

    const team = ref(null)
    const players = ref([])
    const captain = ref(null)
    const isLoading = ref(false)
    const error = ref('')

    const filters = ref({
      position: '',
      search: '',
      status: ''
    })

    // Computed properties
    const canManageTeam = computed(() => {
      const user = authStore.user
    
      if (!user || !team.value) return false
    
      // Admin puede gestionar cualquier equipo
      if (user.role === 'admin') return true
    
      // Team manager puede gestionar solo sus equipos
      // Verificar tanto manager_id como user_id por compatibilidad
      if (user.role === 'team_manager' && 
          (team.value.manager_id === user.id || team.value.user_id === user.id)) {
        return true
      }
    
      return false
    })

    const filteredPlayers = computed(() => {
      let list = players.value
      if (filters.value.search) {
        list = list.filter(p =>
          p.user?.name.toLowerCase().includes(filters.value.search.toLowerCase())
        )
      }
      if (filters.value.position) {
        list = list.filter(p => p.pivot?.position === filters.value.position)
      }
      if (filters.value.status === 'active') {
        list = list.filter(p => p.pivot?.is_active)
      } else if (filters.value.status === 'inactive') {
        list = list.filter(p => !p.pivot?.is_active)
      } else if (filters.value.status === 'captain') {
        list = list.filter(p => p.pivot?.is_captain)
      }
      return list
    })

    const uniquePositions = computed(() =>
      [...new Set(players.value.map(p => p.pivot?.position).filter(Boolean))]
    )
    const totalPlayers = computed(() => players.value.length)
    const activePlayersCount = computed(() =>
      players.value.filter(p => p.pivot?.is_active).length
    )
    const captainsCount = computed(() =>
      players.value.filter(p => p.pivot?.is_captain).length
    )
    const positionsCount = computed(() => uniquePositions.value.length)
    const hasActiveFilters = computed(() =>
      Boolean(filters.value.search || filters.value.position || filters.value.status)
    )

    // API calls
    const fetchTeamRoster = async () => {
      isLoading.value = true
      error.value = ''
      
      try {
        const response = await teamAPI.getRoster(route.params.id)
        
        if (apiHelpers.isSuccess(response)) {
          const data = apiHelpers.getData(response)
          team.value = data.team
          players.value = data.players || []
          captain.value = data.captain
          
          // Debug: Log para verificar la estructura del equipo
          console.log('Team data:', team.value)
          console.log('Current user:', authStore.user)
          console.log('Can manage team:', canManageTeam.value)
        } else {
          error.value = 'Failed to load team roster'
          team.value = null
          players.value = []
          captain.value = null
        }
      } catch (err) {
        console.error('Error fetching roster:', err)
        error.value = apiHelpers.handleError(err)
        team.value = null
        players.value = []
        captain.value = null
      } finally {
        isLoading.value = false
      }
    }

    // Helper functions
    const formatDate = (dateString) => {
      if (!dateString) return 'N/A'
      return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      })
    }

    const calculateAge = (dateString) => {
      if (!dateString) return '-'
      const diff = Date.now() - new Date(dateString).getTime()
      return Math.floor(diff / (1000 * 3600 * 24 * 365.25))
    }

    const clearFilters = () => {
      filters.value.search = ''
      filters.value.position = ''
      filters.value.status = ''
    }

    // Modal management
    const showAddPlayerModal = ref(false)

    const openAddPlayerModal = () => {
      showAddPlayerModal.value = true
    }

    const handleCloseModal = () => {
      showAddPlayerModal.value = false
    }

    const handlePlayerAdded = async () => {
      await fetchTeamRoster()
      handleCloseModal()
      window.$notify?.success('Jugador agregado exitosamente')
    }

    // Initialize
    onMounted(() => {
      fetchTeamRoster()
    })

    return {
      route,
      authStore,
      team,
      players,
      captain,
      isLoading,
      error,
      showAddPlayerModal,
      filters,
      canManageTeam,
      fetchTeamRoster,
      formatDate,
      openAddPlayerModal,
      handleCloseModal,
      handlePlayerAdded,
      filteredPlayers,
      uniquePositions,
      totalPlayers,
      activePlayersCount,
      captainsCount,
      positionsCount,
      hasActiveFilters,
      clearFilters,
      calculateAge
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

/* Empty state styling */
.empty-state {
  @apply text-center py-12;
}

.empty-state-icon {
  @apply w-12 h-12 text-gray-300 mx-auto mb-4;
}
</style>