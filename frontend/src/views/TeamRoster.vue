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
              {{ team?.name || 'Cargando...' }} - Plantilla
            </h1>
            <p class="text-gray-600 mt-1">
              Gestionar jugadores del equipo
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
            Agregar Jugador
          </button>
        </div>
      </div>
    </template>

    <!-- Debug Info (solo en desarrollo) -->
    <div v-if="showDebug" class="bg-yellow-100 border border-yellow-300 p-4 rounded mb-4">
      <h3 class="font-bold">Debug Info:</h3>
      <p>Team ID: {{ teamId }}</p>
      <p>Loading: {{ isLoading }}</p>
      <p>Error: {{ error }}</p>
      <p>Team: {{ team ? 'Loaded' : 'Not loaded' }}</p>
      <p>Players: {{ players?.length || 0 }}</p>
      <p>Auth: {{ authStore.isAuthenticated ? 'Yes' : 'No' }}</p>
      <p>Role: {{ authStore.user?.role || 'None' }}</p>
      <p>User ID: {{ authStore.user?.id || 'None' }}</p>
      <p>Team Manager: {{ team?.manager_id || 'None' }}</p>
    </div>

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
            <p class="text-gray-600">{{ team.description || 'No hay descripción disponible' }}</p>
          </div>
          <div class="text-right">
            <div class="text-2xl font-bold text-primary-600">{{ activePlayersCount }}</div>
            <div class="text-sm text-gray-600">Jugadores Activos</div>
          </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="text-center p-4 bg-gray-50 rounded-lg">
            <div class="text-lg font-semibold text-gray-900">{{ totalPlayers }}</div>
            <div class="text-sm text-gray-600">Jugadores</div>
          </div>
          <div class="text-center p-4 bg-gray-50 rounded-lg">
            <div class="text-lg font-semibold text-gray-900">{{ captainsCount }}</div>
            <div class="text-sm text-gray-600">Capitanes</div>
          </div>
          <div class="text-center p-4 bg-gray-50 rounded-lg">
            <div class="text-lg font-semibold text-gray-900">{{ positionsCount }}</div>
            <div class="text-sm text-gray-600">Posiciones</div>
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
          <h3 class="text-lg font-medium text-gray-900 mb-4">Lista de Jugadores</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
          <!-- Search -->
          <div class="relative">
            <MagnifyingGlassIcon class="w-5 h-5 text-gray-400 absolute left-3 top-3" />
            <input
              v-model="filters.search"
              type="text"
              placeholder="Buscar jugadores..."
              class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            />
          </div>

          <!-- Position Filter -->
          <select
            v-model="filters.position"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
          >
            <option value="">Todas las Posiciones</option>
            <option value="goalkeeper">Portero</option>
            <option value="defender">Defensa</option>
            <option value="midfielder">Centrocampista</option>
            <option value="forward">Delantero</option>
          </select>

          <!-- Status Filter -->
          <select
            v-model="filters.status"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
          >
            <option value="">Todos los Jugadores</option>
            <option value="captain">Solo Capitanes</option>
            <option value="active">Solo Activos</option>
          </select>

          <!-- Clear Filters -->
          <button
            v-if="hasActiveFilters"
            @click="clearFilters"
            class="btn-secondary w-full"
          >
            Limpiar Filtros
          </button>
        </div>

        <!-- Players List -->
        <div v-if="filteredPlayers.length > 0" class="space-y-4">
          <div
            v-for="player in filteredPlayers"
            :key="player.id"
            class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50"
          >
            <div class="flex items-center space-x-4">
              <div class="w-12 h-12 bg-primary-100 rounded-full flex items-center justify-center">
                <span class="text-primary-600 font-semibold">
                  {{ player.pivot?.jersey_number || '?' }}
                </span>
              </div>
              <div>
                <div class="flex items-center space-x-2">
                  <h4 class="font-medium text-gray-900">{{ player.user?.name || 'Nombre no disponible' }}</h4>
                  <StarIcon
                    v-if="player.pivot?.is_captain"
                    class="w-4 h-4 text-yellow-500"
                    title="Capitán"
                  />
                </div>
                <div class="flex items-center space-x-4 text-sm text-gray-600">
                  <span v-if="player.pivot?.position">{{ formatPosition(player.pivot.position) }}</span>
                  <span v-if="player.pivot?.joined_date">
                    Se unió: {{ formatDate(player.pivot.joined_date) }}
                  </span>
                </div>
              </div>
            </div>

            <div v-if="canManageTeam" class="flex items-center space-x-2">
              <button
                @click="editPlayer(player)"
                class="p-2 text-gray-400 hover:text-primary-600"
                title="Editar jugador"
              >
                <PencilIcon class="w-4 h-4" />
              </button>
              <button
                @click="removePlayer(player)"
                class="p-2 text-gray-400 hover:text-red-600"
                title="Eliminar jugador"
              >
                <TrashIcon class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-12">
          <UsersIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
          <h3 class="text-lg font-medium text-gray-900 mb-2">
            {{ hasActiveFilters 
              ? 'No hay jugadores que coincidan con los filtros' 
              : 'Aún no hay jugadores en este equipo' 
            }}
          </h3>
          <p class="text-gray-600 mb-6">
            {{ hasActiveFilters 
              ? 'Intenta ajustar tus filtros' 
              : 'Agrega el primer jugador para comenzar' 
            }}
          </p>
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <button v-if="hasActiveFilters" @click="clearFilters" class="btn-secondary">
              Limpiar Filtros
            </button>
            <button v-if="canManageTeam" @click="openAddPlayerModal" class="btn-primary">
              Agregar Jugador
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="text-center py-12">
      <ExclamationTriangleIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
      <h3 class="text-lg font-medium text-gray-900 mb-2">Error al cargar el equipo</h3>
      <p class="text-gray-600 mb-6">{{ error }}</p>
      <div class="space-x-4">
        <button @click="fetchTeamRoster" class="btn-primary">
          Reintentar
        </button>
        <RouterLink to="/teams" class="btn-secondary">
          Volver a Equipos
        </RouterLink>
      </div>
    </div>

    <!-- Add Player Modal -->
    <AddPlayerModal
      v-if="showAddPlayerModal"
      :show="showAddPlayerModal"
      :team-id="teamId"
      :is-admin="authStore.isAdmin"
      @close="handleCloseModal"
      @success="handlePlayerAdded"
    />
  </MainLayout>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
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
    const route = useRoute()
    const authStore = useAuthStore()

    // Estado reactivo
    const team = ref(null)
    const players = ref([])
    const captain = ref(null)
    const isLoading = ref(true)
    const error = ref('')
    const showAddPlayerModal = ref(false)
    const showDebug = ref(import.meta.env.DEV || process.env.NODE_ENV === 'development') // Solo en desarrollo

    // Obtener el ID del equipo de forma segura
    const teamId = computed(() => {
      const id = route.params?.id
      return id ? parseInt(id) : null
    })

    // Filtros
    const filters = ref({
      search: '',
      position: '',
      status: ''
    })

    // Computed properties
    const canManageTeam = computed(() => {
      return authStore.isAdmin || 
             (authStore.user?.role === 'team_manager' && team.value?.manager_id === authStore.user?.id)
    })

    const activePlayersCount = computed(() => {
      return players.value.filter(p => p.pivot?.is_active !== false).length
    })

    const totalPlayers = computed(() => players.value.length)

    const captainsCount = computed(() => {
      return players.value.filter(p => p.pivot?.is_captain).length
    })

    const positionsCount = computed(() => {
      const positions = new Set(players.value.map(p => p.pivot?.position).filter(Boolean))
      return positions.size
    })

    const nationalitiesCount = computed(() => {
      const nationalities = new Set(players.value.map(p => p.nationality).filter(Boolean))
      return nationalities.size
    })

    const hasActiveFilters = computed(() => {
      return filters.value.search || filters.value.position || filters.value.status
    })

    const filteredPlayers = computed(() => {
      let result = [...players.value]

      // Filtro por búsqueda
      if (filters.value.search) {
        const search = filters.value.search.toLowerCase()
        result = result.filter(player => 
          player.user?.name?.toLowerCase().includes(search) ||
          player.pivot?.jersey_number?.toString().includes(search)
        )
      }

      // Filtro por posición
      if (filters.value.position) {
        result = result.filter(player => player.pivot?.position === filters.value.position)
      }

      // Filtro por status
      if (filters.value.status === 'captain') {
        result = result.filter(player => player.pivot?.is_captain)
      } else if (filters.value.status === 'active') {
        result = result.filter(player => player.pivot?.is_active !== false)
      }

      return result
    })

    // Métodos
    const fetchTeamRoster = async () => {
      try {
        isLoading.value = true
        error.value = ''
        
        if (!teamId.value || isNaN(teamId.value)) {
          throw new Error('ID de equipo inválido')
        }

        console.log('Fetching team roster for ID:', teamId.value)
        
        // USAR EL ENDPOINT CORRECTO: getRoster en lugar de getPlayers
        const response = await teamAPI.getRoster(teamId.value)
        console.log('API Response:', response)
        
        if (apiHelpers.isSuccess(response)) {
          const data = apiHelpers.getData(response)
          team.value = data.team
          players.value = data.players || []
          captain.value = data.captain
          console.log('Team roster loaded:', data)
        } else {
          throw new Error(response.data?.message || 'Error al cargar el roster del equipo')
        }
      } catch (err) {
        console.error('Error fetching team roster:', err)
        error.value = err.message || 'Error al cargar los datos del equipo'
      } finally {
        isLoading.value = false
      }
    }

    const openAddPlayerModal = () => {
      showAddPlayerModal.value = true
    }

    const handleCloseModal = () => {
      showAddPlayerModal.value = false
    }

    const handlePlayerAdded = () => {
      showAddPlayerModal.value = false
      fetchTeamRoster() // Recargar datos
    }

    const clearFilters = () => {
      filters.value = {
        search: '',
        position: '',
        status: ''
      }
    }

    const formatPosition = (position) => {
      const positions = {
        goalkeeper: 'Portero',
        defender: 'Defensa',
        midfielder: 'Centrocampista',
        forward: 'Delantero'
      }
      return positions[position] || position
    }

    const formatDate = (dateString) => {
      if (!dateString) return ''
      try {
        return new Date(dateString).toLocaleDateString('es-ES')
      } catch {
        return dateString
      }
    }

    const editPlayer = (player) => {
      console.log('Edit player:', player)
      // TODO: Implementar edición de jugador
    }

    const removePlayer = async (player) => {
      if (confirm('¿Estás seguro de que quieres eliminar este jugador del equipo?')) {
        try {
          // TODO: Implementar eliminación de jugador
          console.log('Remove player:', player)
        } catch (err) {
          console.error('Error removing player:', err)
        }
      }
    }

    // Watchers
    watch(() => teamId.value, (newId) => {
      if (newId && !isNaN(newId)) {
        fetchTeamRoster()
      }
    }, { immediate: true })

    // Lifecycle
    onMounted(() => {
      console.log('TeamRoster component mounted')
      console.log('Route params:', route.params)
      console.log('Team ID:', teamId.value)
      console.log('Auth store:', {
        isAuthenticated: authStore.isAuthenticated,
        user: authStore.user,
        role: authStore.user?.role
      })
    })

    return {
      // Estado
      team,
      players,
      captain,
      isLoading,
      error,
      showAddPlayerModal,
      showDebug,
      filters,
      teamId,
      authStore,
      
      // Computed
      canManageTeam,
      activePlayersCount,
      totalPlayers,
      captainsCount,
      positionsCount,
      nationalitiesCount,
      hasActiveFilters,
      filteredPlayers,
      
      // Métodos
      fetchTeamRoster,
      openAddPlayerModal,
      handleCloseModal,
      handlePlayerAdded,
      clearFilters,
      formatPosition,
      formatDate,
      editPlayer,
      removePlayer
    }
  }
}
</script>

<style scoped>
.btn-primary {
  @apply bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200 flex items-center;
}

.btn-secondary {
  @apply bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center;
}

.card {
  @apply bg-white rounded-lg shadow-sm border border-gray-200;
}

.spinner {
  @apply border-4 border-gray-200 border-t-blue-600 rounded-full animate-spin;
}

.text-primary-600 {
  @apply text-blue-600;
}

.bg-primary-100 {
  @apply bg-blue-100;
}

.focus\:ring-primary-500:focus {
  @apply ring-blue-500;
}
</style>