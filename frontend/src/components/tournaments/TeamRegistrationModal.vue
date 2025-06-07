<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg shadow-lg max-w-md w-full">
      <!-- Header -->
      <div class="p-4 border-b">
        <h3 class="text-lg font-semibold">Registrar Equipo</h3>
        <p class="text-sm text-gray-600">{{ tournament?.name }}</p>
      </div>

      <!-- Content -->
      <div class="p-4">
        <form @submit.prevent="handleSubmit" class="space-y-4">
          <!-- Team Selection -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Seleccionar Equipo
            </label>
            <select
              v-model="selectedTeamId"
              required
              :disabled="isLoading"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">Elegir equipo...</option>
              <option v-for="team in availableTeams" :key="team.id" :value="team.id">
                {{ team.name }} ({{ team.players_count || 0 }} jugadores)
              </option>
            </select>
            
            <!-- Loading teams -->
            <p v-if="isLoadingTeams" class="text-sm text-gray-500 mt-1">
              Cargando equipos...
            </p>
            
            <!-- No teams available -->
            <p v-else-if="availableTeams.length === 0" class="text-sm text-gray-500 mt-1">
              {{ isAdmin ? 'No hay equipos en el sistema' : 'No tienes equipos asignados' }}
            </p>
          </div>

          <!-- Admin Notice -->
          <div v-if="isAdmin" class="bg-blue-50 border border-blue-200 rounded p-3">
            <p class="text-sm text-blue-700">
              ✓ Como administrador puedes registrar cualquier equipo sin restricciones
            </p>
          </div>

          <!-- Manager Notice -->
          <div v-else class="bg-yellow-50 border border-yellow-200 rounded p-3">
            <p class="text-sm text-yellow-700">
              • El equipo debe tener al menos 1 jugador
            </p>
          </div>

          <!-- Error Message -->
          <div v-if="error" class="bg-red-50 border border-red-200 rounded p-3">
            <p class="text-sm text-red-700">{{ error }}</p>
          </div>

          <!-- Actions -->
          <div class="flex space-x-3">
            <button
              type="button"
              @click="$emit('close')"
              :disabled="isLoading"
              class="flex-1 px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="!selectedTeamId || isLoading || availableTeams.length === 0"
              class="flex-1 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50"
            >
              {{ isLoading ? 'Registrando...' : 'Registrar' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { tournamentAPI, teamAPI, apiHelpers } from '@/services/api'

export default {
  name: 'TeamRegistrationModal',
  props: {
    tournament: {
      type: Object,
      required: true
    }
  },
  emits: ['close', 'success'],
  setup(props, { emit }) {
    const authStore = useAuthStore()
    
    // State
    const availableTeams = ref([])
    const selectedTeamId = ref('')
    const isLoading = ref(false)
    const isLoadingTeams = ref(false)
    const error = ref('')

    // Computed
    const isAdmin = computed(() => authStore.user?.isAdmin || false)
    
    const selectedTeam = computed(() => {
      return availableTeams.value.find(team => team.id === selectedTeamId.value)
    })

    // Methods
    const fetchAvailableTeams = async () => {
      isLoadingTeams.value = true
      error.value = ''
      
      try {
        let response;
        
        if (isAdmin.value) {
          // Admins ven TODOS los equipos
          console.log('Fetching all teams for admin')
          response = await teamAPI.getAll()
        } else {
          // Managers solo ven SUS equipos
          console.log('Fetching teams for manager:', authStore.user?.id)
response = await teamAPI.getAll({ 
  manager_id: authStore.user?.id,
  active: true  // Cambiar 'is_active' por 'active'
})
        }

        console.log('Teams response:', response)

        if (apiHelpers.isSuccess(response)) {
          const data = apiHelpers.getData(response)
          // Handle both paginated and non-paginated responses
          availableTeams.value = data.data || data || []
          console.log('Available teams loaded:', availableTeams.value)
        } else {
          console.error('Failed to fetch teams:', response)
          availableTeams.value = []
          error.value = 'Error al cargar equipos'
        }
      } catch (err) {
        console.error('Error loading teams:', err)
        error.value = 'Error al cargar equipos'
        availableTeams.value = []
      } finally {
        isLoadingTeams.value = false
      }
    }

   const handleSubmit = async () => {
  error.value = ''
  
  if (!selectedTeamId.value) {
    error.value = 'Selecciona un equipo'
    return
  }

  isLoading.value = true

  try {
    console.log('🚀 Intentando registrar equipo...')
    console.log('📋 Datos:', {
      tournamentId: props.tournament.id,
      teamId: selectedTeamId.value,
      tournament: props.tournament,
      selectedTeam: selectedTeam.value
    })
    
    const response = await tournamentAPI.registerTeam(props.tournament.id, {
      team_id: selectedTeamId.value
    })

    console.log('✅ Respuesta exitosa:', response)

    if (apiHelpers.isSuccess(response)) {
      emit('success')
    } else {
      console.error('❌ Respuesta no exitosa:', response)
      error.value = response.data?.message || 'Error en el registro'
    }
  } catch (err) {
    console.error('💥 Error completo:', err)
    console.error('📄 Respuesta del servidor:', err.response?.data)
    
    // Mostrar mensaje específico del servidor
    if (err.response?.data?.message) {
      error.value = err.response.data.message
    } else if (err.response?.data?.errors) {
      // Si hay errores de validación específicos
      const validationErrors = Object.values(err.response.data.errors).flat()
      error.value = validationErrors.join(', ')
    } else {
      error.value = 'Error al registrar equipo'
    }
  } finally {
    isLoading.value = false
  }
}

    onMounted(() => {
      console.log('Modal mounted, user:', authStore.user)
      console.log('Is admin:', isAdmin.value)
      fetchAvailableTeams()
    })

    return {
      availableTeams,
      selectedTeamId,
      selectedTeam,
      isLoading,
      isLoadingTeams,
      isAdmin,
      error,
      handleSubmit
    }
  }
}
</script>