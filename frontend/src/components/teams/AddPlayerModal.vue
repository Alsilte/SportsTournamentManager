<template>
  <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg max-w-md w-full p-6 max-h-[90vh] overflow-y-auto">
      <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-gray-900">
          {{ isAdmin ? 'Agregar Jugador (Administrador)' : 'Agregar Jugador' }}
        </h3>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
          <XMarkIcon class="w-5 h-5" />
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-4">
        <!-- Player Selection -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Jugador *
          </label>
          
          <!-- Estado de carga -->
          <div v-if="loadingPlayers" class="flex items-center justify-center py-3 bg-gray-50 rounded-lg">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500"></div>
            <span class="ml-2 text-sm text-gray-600">Cargando jugadores...</span>
          </div>
          
          <!-- Mensaje cuando no hay jugadores -->
          <div v-else-if="availablePlayers.length === 0" class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
            <p class="text-sm text-yellow-800">
              {{ playersErrorMessage || 'No hay jugadores disponibles' }}
            </p>
            <button 
              @click="fetchAvailablePlayers" 
              type="button"
              class="mt-2 text-xs text-blue-600 hover:text-blue-800"
            >
              🔄 Recargar jugadores
            </button>
          </div>
          
          <!-- Dropdown de jugadores -->
          <select 
            v-else
            v-model="form.player_id" 
            required 
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Seleccionar jugador</option>
            <optgroup v-if="isAdmin && playersInOtherTeams.length > 0" label="⚠️ En otros equipos">
              <option 
                v-for="player in playersInOtherTeams" 
                :key="'busy-' + player.id" 
                :value="player.id"
                class="text-orange-600"
              >
                {{ getPlayerName(player) }}
                <span v-if="player.current_team"> - {{ player.current_team.name }}</span>
              </option>
            </optgroup>
            <optgroup :label="isAdmin ? '✅ Disponibles' : 'Jugadores Disponibles'">
              <option 
                v-for="player in availablePlayersOnly" 
                :key="'free-' + player.id" 
                :value="player.id"
              >
                {{ getPlayerName(player) }}
                <span v-if="player.position"> - {{ getPositionLabel(player.position) }}</span>
              </option>
            </optgroup>
          </select>
          
          <!-- Info del jugador seleccionado -->
          <div v-if="selectedPlayer && isAdmin" class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded text-sm">
            <strong>{{ getPlayerName(selectedPlayer) }}</strong>
            <span v-if="selectedPlayer.current_team" class="text-orange-600">
              - Actualmente en: {{ selectedPlayer.current_team.name }}
            </span>
            <span v-else class="text-green-600">
              - Disponible
            </span>
          </div>
        </div>

        <!-- Jersey Number -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Número de Camiseta *
          </label>
          <input 
            v-model="form.jersey_number" 
            type="number" 
            min="1" 
            max="99" 
            required 
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            placeholder="Ej: 10"
          />
        </div>

        <!-- Position -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Posición
          </label>
          <select v-model="form.position" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            <option value="">Seleccionar posición</option>
            <option value="goalkeeper">Portero</option>
            <option value="defender">Defensor</option>
            <option value="midfielder">Centrocampista</option>
            <option value="delantero">Delantero</option>
          </select>
        </div>

        <!-- Join Date -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Fecha de Ingreso *
          </label>
          <input
            v-model="form.joined_date"
            type="date"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <!-- Captain -->
        <div class="flex items-center">
          <input
            v-model="form.is_captain"
            type="checkbox"
            id="is_captain"
            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
          />
          <label for="is_captain" class="ml-2 text-sm text-gray-700">
            Capitán del equipo
          </label>
        </div>

        <!-- Admin Override (solo para admins) -->
        <div v-if="isAdmin" class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
          <label class="flex items-center">
            <input
              v-model="form.admin_override"
              type="checkbox"
              class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
            />
            <span class="ml-2 text-sm text-gray-700">
              <strong>Forzar agregación</strong> (omitir restricciones)
            </span>
          </label>
          <p class="text-xs text-gray-600 mt-1">
            Permite agregar jugadores que ya pertenecen a otros equipos u omitir validaciones estándar
          </p>
        </div>

        <!-- Error Message -->
        <div v-if="error" class="p-3 bg-red-50 border border-red-200 rounded-lg">
          <p class="text-sm text-red-700">{{ error }}</p>
          <button 
            @click="error = ''" 
            type="button"
            class="mt-1 text-xs text-red-600 hover:text-red-800"
          >
            ✕ Cerrar
          </button>
        </div>

        <!-- Buttons -->
        <div class="flex space-x-3 pt-4">
          <button
            type="button"
            @click="$emit('close')"
            class="flex-1 px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
          >
            Cancelar
          </button>
          <button
            type="submit"
            :disabled="loading || loadingPlayers || availablePlayers.length === 0"
            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
          >
            {{ loading ? 'Agregando...' : 'Agregar Jugador' }}
          </button>
        </div>
      </form>
      
      <!-- Debug Panel -->
      <div v-if="debugMode" class="mt-4 p-3 bg-gray-100 rounded text-xs space-y-2">
        <div class="flex justify-between">
          <strong>Debug Info:</strong>
          <button @click="debugMode = false" class="text-red-600">✕</button>
        </div>
        <div><strong>Team ID:</strong> {{ teamId }}</div>
        <div><strong>Total jugadores:</strong> {{ availablePlayers.length }}</div>
        <div><strong>Disponibles:</strong> {{ availablePlayersOnly.length }}</div>
        <div><strong>En otros equipos:</strong> {{ playersInOtherTeams.length }}</div>
        <div><strong>Loading:</strong> {{ loadingPlayers }}</div>
        <div><strong>Is Admin:</strong> {{ isAdmin }}</div>
        <div><strong>Error:</strong> {{ error || 'Ninguno' }}</div>
        <div><strong>API Response:</strong> {{ lastApiResponse || 'Ninguna' }}</div>
        <button 
          @click="fetchAvailablePlayers" 
          class="px-2 py-1 bg-blue-500 text-white rounded text-xs"
        >
          🔄 Recargar
        </button>
      </div>
      
      <!-- Toggle Debug -->
      <button 
        @click="debugMode = !debugMode" 
        class="mt-2 text-xs text-gray-500 hover:text-gray-700"
        type="button"
      >
        {{ debugMode ? 'Ocultar' : 'Mostrar' }} Debug
      </button>
    </div>
  </div>
</template>

<script>
import { XMarkIcon } from '@heroicons/vue/24/outline'
import { teamAPI, apiHelpers } from '@/services/api'

export default {
  name: 'AddPlayerModal',
  components: {
    XMarkIcon
  },
  props: {
    show: {
      type: Boolean,
      default: false
    },
    teamId: {
      type: Number,
      required: true
    },
    isAdmin: {
      type: Boolean,
      default: false
    }
  },
  emits: ['close', 'success'],
  data() {
    return {
      loading: false,
      loadingPlayers: false,
      error: '',
      availablePlayers: [],
      debugMode: false,
      playersErrorMessage: '',
      lastApiResponse: null,
      form: {
        player_id: '',
        jersey_number: '',
        position: '',
        is_captain: false,
        joined_date: new Date().toISOString().split('T')[0],
        admin_override: false
      }
    }
  },
  computed: {
    selectedPlayer() {
      return this.availablePlayers.find(p => p.id == this.form.player_id)
    },
    
    availablePlayersOnly() {
      return this.availablePlayers.filter(player => player.is_available !== false)
    },
    
    playersInOtherTeams() {
      return this.availablePlayers.filter(player => player.is_available === false)
    }
  },
  watch: {
    show(newVal) {
      if (newVal) {
        this.resetForm()
        this.fetchAvailablePlayers()
      }
    }
  },
  methods: {
    resetForm() {
      this.error = ''
      this.debugMode = false
      this.playersErrorMessage = ''
      this.lastApiResponse = null
      this.form = {
        player_id: '',
        jersey_number: '',
        position: '',
        is_captain: false,
        joined_date: new Date().toISOString().split('T')[0],
        admin_override: false
      }
    },

    getPositionLabel(position) {
      const positions = {
        goalkeeper: 'Portero',
        defender: 'Defensor',
        midfielder: 'Centrocampista',
        delantero: 'Delantero'
      }
      return positions[position] || position
    },

    getPlayerName(player) {
      return player.user?.name || player.name || `Jugador #${player.id}`
    },

    async fetchAvailablePlayers() {
      this.loadingPlayers = true
      this.error = ''
      this.playersErrorMessage = ''
      
      try {
        console.log('🔍 Fetching available players for team:', this.teamId)
        
        const response = await teamAPI.getAvailablePlayers(this.teamId)
        this.lastApiResponse = JSON.stringify(response.data?.meta || response, null, 2)
        
        console.log('📡 API response:', response)
        
        if (apiHelpers.isSuccess(response)) {
          this.availablePlayers = apiHelpers.getData(response) || []
          console.log('✅ Available players loaded:', this.availablePlayers)
          
          if (this.availablePlayers.length === 0) {
            this.playersErrorMessage = response.data?.message || 'No hay jugadores disponibles para agregar al equipo'
          }
        } else {
          this.error = apiHelpers.getMessage(response) || 'Error al cargar jugadores'
          this.playersErrorMessage = this.error
        }
        
      } catch (err) {
        console.error('❌ Error loading players:', err)
        this.error = apiHelpers.handleError(err) || 'Error al cargar jugadores'
        this.playersErrorMessage = this.error
        this.availablePlayers = []
      } finally {
        this.loadingPlayers = false
      }
    },

    async handleSubmit() {
      this.loading = true
      this.error = ''

      try {
        console.log('📤 Submitting player data:', this.form)
        
        const response = await teamAPI.addPlayer(this.teamId, this.form)
        console.log('📥 Add player response:', response)
        
        if (apiHelpers.isSuccess(response)) {
          this.$emit('success')
          this.$emit('close')
          
          if (this.$notify) {
            this.$notify.success('Jugador agregado exitosamente')
          }
        } else {
          this.error = apiHelpers.getMessage(response) || 'Error al agregar jugador'
        }
      } catch (err) {
        console.error('❌ Error adding player:', err)
        this.error = apiHelpers.handleError(err) || 'Error al agregar jugador'
      } finally {
        this.loading = false
      }
    }
  }
}
</script>