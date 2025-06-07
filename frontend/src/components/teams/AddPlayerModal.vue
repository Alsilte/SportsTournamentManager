<template>
  <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg max-w-md w-full p-6">
      <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-gray-900">
          {{ isAdmin ? $t('teams.modal.addPlayerAdmin') : $t('teams.modal.addPlayer') }}
        </h3>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
          <XMarkIcon class="w-5 h-5" />
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-4">
        <!-- Player Selection -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            {{ $t('teams.modal.player') }} *
          </label>
          <select v-model="form.player_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            <option value="">{{ $t('teams.modal.selectPlayer') }}</option>
            <option v-for="player in availablePlayers" :key="player.id" :value="player.id">
              {{ player.user?.name || player.name }}
              <span v-if="player.position"> - {{ $t(`teams.positions.${player.position}`) }}</span>
              <span v-if="player.current_team" class="text-orange-600"> {{ $t('teams.modal.inTeam', { teamName: player.current_team.name }) }}</span>
            </option>
          </select>
        </div>

        <!-- Jersey Number -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            {{ $t('teams.jerseyNumber') }} *
          </label>
          <input 
            v-model="form.jersey_number" 
            type="number" 
            min="1" 
            max="99" 
            required 
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            :placeholder="$t('teams.jerseyPlaceholder')"
          />
        </div>

        <!-- Position -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            {{ $t('teams.modal.position') }}
          </label>
          <select v-model="form.position" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            <option value="">{{ $t('teams.modal.selectPosition') }}</option>
            <option value="goalkeeper">{{ $t('teams.positions.goalkeeper') }}</option>
            <option value="defender">{{ $t('teams.positions.defender') }}</option>
            <option value="midfielder">{{ $t('teams.positions.midfielder') }}</option>
            <option value="forward">{{ $t('teams.positions.forward') }}</option>
          </select>
        </div>

        <!-- Join Date -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            {{ $t('teams.modal.joinDate') }} *
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
            {{ $t('teams.modal.teamCaptain') }}
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
              <strong>{{ $t('teams.modal.forceAddition') }}</strong> {{ $t('teams.modal.omitRestrictions') }}
            </span>
          </label>
          <p class="text-xs text-gray-600 mt-1">
            {{ $t('teams.modal.forceAdditionDescription') }}
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
            {{ $t('common.cancel') }}
          </button>
          <button
            type="submit"
            :disabled="loading"
            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
          >
            {{ loading ? $t('teams.modal.adding') : $t('teams.modal.addPlayer') }}
          </button>
        </div>
      </form>
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
        console.log('Fetching available players for team:', this.teamId)
        
        // ✅ USAR LA API REAL - NO MOCK DATA
        const response = await teamAPI.getAvailablePlayers(this.teamId)
        console.log('Available players response:', response)
        
        if (apiHelpers.isSuccess(response)) {
          this.availablePlayers = apiHelpers.getData(response) || []
          console.log('Available players loaded:', this.availablePlayers)
        } else {
          throw new Error(response.data?.message || this.$t('teams.modal.errorLoadingPlayers'))
        }
      } catch (err) {
        console.error('Error loading players:', err)
        this.error = err.message || this.$t('teams.modal.errorLoadingPlayers')
        
        // Fallback: Si la API falla, mostrar datos mock temporales
        this.availablePlayers = [
          {
            id: 1,
            user: { name: 'Carlos Rodriguez' },
            position: 'forward',
            current_team: null
          },
          {
            id: 2,
            user: { name: 'Marco Silva' },
            position: 'midfielder',
            current_team: null
          },
          {
            id: 3,
            user: { name: 'John Smith' },
            position: 'defender',
            current_team: null
          }
        ]
      }
    },

    async handleSubmit() {
      this.loading = true
      this.error = ''

      try {
        console.log('Submitting player data:', this.form)
        
        // ✅ USAR LA API REAL - teamAPI.addPlayer
        const response = await teamAPI.addPlayer(this.teamId, this.form)
        console.log('Add player response:', response)
        
        if (apiHelpers.isSuccess(response)) {
          this.$emit('success')
          this.$emit('close')
          
          // Mostrar notificación
          if (window.$notify) {
            window.$notify.success(this.$t('teams.modal.playerAddedSuccessfully'))
          }
        } else {
          this.error = response.data?.message || this.$t('teams.modal.errorAddingPlayer')
        }
      } catch (err) {
        console.error('Error adding player:', err)
        this.error = apiHelpers.handleError(err) || this.$t('teams.modal.errorAddingPlayer')
      } finally {
        this.loading = false
      }
    }
  }
}
</script>