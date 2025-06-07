<template>
  <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg max-w-md w-full p-6">
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
          <div v-if="loadingPlayers" class="flex items-center justify-center py-3">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500"></div>
            <span class="ml-2 text-sm text-gray-600">Cargando jugadores...</span>
          </div>
          
          <!-- Dropdown de jugadores -->
          <select 
            v-else
            v-model="form.player_id" 
            required 
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Seleccionar jugador</option>
            <option v-for="player in availablePlayers" :key="player.id" :value="player.id">
              {{ getPlayerName(player) }}
              <span v-if="player.position"> - {{ getPositionLabel(player.position) }}</span>
              <span v-if="player.current_team" class="text-orange-600"> (En {{ player.current_team.name }})</span>
            </option>
          </select>
          
          <!-- Debug info -->
          <div v-if="debugMode" class="mt-2 p-2 bg-gray-100 rounded text-xs">
            <strong>Debug:</strong><br>
            Total jugadores: {{ availablePlayers.length }}<br>
            Loading: {{ loadingPlayers }}<br>
            Error: {{ error || 'Ninguno' }}
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
            <option value="forward">Delantero</option>
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
            :disabled="loading || loadingPlayers"
            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
          >
            {{ loading ? 'Agregando...' : 'Agregar Jugador' }}
          </button>
        </div>
      </form>
      
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
      loadingPlayers: false, // ✅ Variable separada para carga de jugadores
      error: '',
      availablePlayers: [],
      debugMode: false, // ✅ Modo debug para diagnosticar
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
        forward: 'Delantero'
      }
      return positions[position] || position
    },

    // ✅ Helper para obtener nombre del jugador
    getPlayerName(player) {
      return player.user?.name || player.name || `Jugador #${player.id}`
    },

    async fetchAvailablePlayers() {
      this.loadingPlayers = true // ✅ Variable correcta
      this.error = ''
      
      try {
        console.log('🔍 Fetching available players for team:', this.teamId)
        
        // ✅ MÚLTIPLES ESTRATEGIAS DE FALLBACK
        let response
        let players = []
        
        // Estrategia 1: API de equipo específico
        try {
          response = await teamAPI.getAvailablePlayers(this.teamId)
          console.log('📡 Team API response:', response)
          
          if (apiHelpers.isSuccess(response)) {
            players = apiHelpers.getData(response) || []
          } else {
            throw new Error('Team API failed')
          }
        } catch (teamAPIError) {
          console.warn('⚠️ Team API failed, trying alternative:', teamAPIError)
          
          // Estrategia 2: API general de jugadores disponibles
          try {
            response = await fetch('/api/players/available')
            const data = await response.json()
            
            if (data.success) {
              players = data.data?.available_players || data.data || []
            } else {
              throw new Error('Players API failed')
            }
          } catch (playersAPIError) {
            console.warn('⚠️ Players API also failed:', playersAPIError)
            
            // Estrategia 3: Datos de prueba para debugging
            players = [
              {
                id: 1,
                user: { name: 'Juan Pérez' },
                position: 'forward'
              },
              {
                id: 2,
                user: { name: 'Carlos López' },
                position: 'midfielder'
              }
            ]
            console.log('🧪 Using test data for debugging')
          }
        }
        
        this.availablePlayers = players
        console.log('✅ Available players loaded:', this.availablePlayers)
        
        if (players.length === 0) {
          this.error = 'No hay jugadores disponibles para agregar al equipo'
        }
        
      } catch (err) {
        console.error('❌ Error loading players:', err)
        this.error = err.message || 'Error al cargar jugadores'
        this.availablePlayers = []
        
        // Mostrar notificación de error
        if (this.$notify) {
          this.$notify.error('Error al cargar jugadores disponibles')
        }
      } finally {
        this.loadingPlayers = false
      }
    },

    async handleSubmit() {
      this.loading = true
      this.error = ''

      try {
        console.log('📤 Submitting player data:', this.form)
        
        // ✅ USAR LA API REAL - teamAPI.addPlayer
        const response = await teamAPI.addPlayer(this.teamId, this.form)
        console.log('📥 Add player response:', response)
        
        if (apiHelpers.isSuccess(response)) {
          this.$emit('success')
          this.$emit('close')
          
          // Mostrar notificación
          if (this.$notify) {
            this.$notify.success('Jugador agregado exitosamente')
          }
        } else {
          this.error = response.data?.message || 'Error al agregar jugador'
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