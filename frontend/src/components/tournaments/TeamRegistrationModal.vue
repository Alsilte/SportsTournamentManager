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
          </div>

          <!-- Admin Notice -->
          <div v-if="isAdmin" class="bg-blue-50 border border-blue-200 rounded p-3">
            <p class="text-sm text-blue-700">
              ✓ Como administrador puedes registrar equipos sin restricciones
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
              :disabled="!selectedTeamId || isLoading"
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
import { tournamentAPI, teamAPI } from '@/services/api'
import { apiHelpers } from '@/utils/api'

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
    const error = ref('')

    // Computed
    const isAdmin = computed(() => authStore.user?.isAdmin || false)
    
    const selectedTeam = computed(() => {
      return availableTeams.value.find(team => team.id === selectedTeamId.value)
    })

    // Methods
    const fetchAvailableTeams = async () => {
      try {
        const response = isAdmin.value 
          ? await teamAPI.getAll() 
          : await teamAPI.getMyTeams()

        if (apiHelpers.isSuccess(response)) {
          availableTeams.value = apiHelpers.getData(response) || []
        }
      } catch (err) {
        error.value = 'Error al cargar equipos'
      }
    }

    const handleSubmit = async () => {
      error.value = ''
      
      if (!selectedTeamId.value) {
        error.value = 'Selecciona un equipo'
        return
      }

      // Solo validar para no-admins
      if (!isAdmin.value) {
        const team = selectedTeam.value
        if (!team || (team.players_count || 0) < 1) {
          error.value = 'El equipo debe tener al menos 1 jugador'
          return
        }
      }

      isLoading.value = true

      try {
        const response = await tournamentAPI.registerTeam(props.tournament.id, {
          team_id: selectedTeamId.value
        })

        if (apiHelpers.isSuccess(response)) {
          emit('success')
        } else {
          error.value = response.data?.message || 'Error en el registro'
        }
      } catch (err) {
        error.value = 'Error al registrar equipo'
      } finally {
        isLoading.value = false
      }
    }

    onMounted(() => {
      fetchAvailableTeams()
    })

    return {
      availableTeams,
      selectedTeamId,
      selectedTeam,
      isLoading,
      isAdmin,
      error,
      handleSubmit
    }
  }
}
</script>