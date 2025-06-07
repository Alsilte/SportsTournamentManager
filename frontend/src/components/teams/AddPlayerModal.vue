<template>
  <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg max-w-md w-full p-6">
      <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-gray-900">
          {{ isAdmin ? 'Añadir Jugador (Admin)' : 'Añadir Jugador' }}
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
          <select v-model="form.player_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            <option value="">Seleccionar jugador</option>
            <option v-for="player in availablePlayers" :key="player.id" :value="player.id">
              {{ player.user?.name }} 
              <span v-if="player.position"> - {{ player.position }}</span>
              <span v-if="player.current_team" class="text-orange-600"> (En: {{ player.current_team.name }})</span>
            </option>
          </select>
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
            placeholder="ej: 10"
          />
        </div>

        <!-- Position -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Posición
          </label>
          <select v-model="form.position" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            <option value="">Seleccionar posición</option>
            <option value="Goalkeeper">Portero</option>
            <option value="Defender">Defensor</option>
            <option value="Midfielder">Mediocampista</option>
            <option value="Forward">Delantero</option>
          </select>
        </div>

        <!-- Join Date -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Fecha de Incorporación *
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
            Es capitán del equipo
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
              <strong>Forzar adición</strong> (omitir restricciones)
            </span>
          </label>
          <p class="text-xs text-gray-600 mt-1">
            Permite añadir jugadores de otros equipos, forzar números, etc.
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
            :disabled="loading"
            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
          >
            {{ loading ? 'Añadiendo...' : 'Añadir Jugador' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { XMarkIcon } from '@heroicons/vue/24/outline'

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
      error: '',
      availablePlayers: [],
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
      this.form = {
        player_id: '',
        jersey_number: '',
        position: '',
        is_captain: false,
        joined_date: new Date().toISOString().split('T')[0],
        admin_override: false
      }
    },

    async fetchAvailablePlayers() {
      try {
        // AQUÍ VA TU LLAMADA A LA API REAL
        // const response = await this.$api.get(`/teams/${this.teamId}/available-players`)
        // this.availablePlayers = response.data.players

        // MOCK DATA - reemplazar con API real
        this.availablePlayers = [
          {
            id: 1,
            user: { name: 'Carlos Rodriguez' },
            position: 'Forward',
            current_team: null
          },
          {
            id: 2,
            user: { name: 'Marco Silva' },
            position: 'Midfielder',
            current_team: { name: 'FC Barcelona' }
          },
          {
            id: 3,
            user: { name: 'John Smith' },
            position: 'Defender',
            current_team: null
          }
        ]
      } catch (err) {
        console.error('Error loading players:', err)
        this.error = 'Error al cargar jugadores disponibles'
      }
    },

    async handleSubmit() {
      this.loading = true
      this.error = ''

      try {
        // AQUÍ VA TU LLAMADA A LA API REAL
        // const response = await this.$api.post(`/teams/${this.teamId}/players`, this.form)
        
        // SIMULACIÓN - reemplazar con API real
        console.log('Enviando:', this.form)
        await new Promise(resolve => setTimeout(resolve, 1000)) // Simular delay

        this.$emit('success')
        this.$emit('close')
        
        // Mostrar notificación
        if (window.$notify) {
          window.$notify.success('Jugador añadido correctamente')
        }
      } catch (err) {
        this.error = err.response?.data?.message || 'Error al añadir jugador'
      } finally {
        this.loading = false
      }
    }
  }
}
</script>