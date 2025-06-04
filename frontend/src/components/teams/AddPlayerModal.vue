<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg max-w-md w-full p-6">
      <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-gray-900">{{ t('teams.addPlayerToTeam') }}</h3>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
          <XMarkIcon class="w-5 h-5" />
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-4">
        <!-- Player Selection -->
        <div>
          <label class="form-label">{{ t('teams.selectPlayer') }}</label>
          <select v-model="form.player_id" required class="form-input">
            <option value="">{{ t('teams.choosePlayer') }}</option>
            <option v-for="player in availablePlayers" :key="player.id" :value="player.id">
              {{ player.user?.name }} - {{ player.nationality || t('common.unknown') }}
            </option>
          </select>
          <p v-if="error" class="form-error">{{ error }}</p>
        </div>

        <!-- Jersey Number -->
        <div>
          <label class="form-label">{{ t('teams.jerseyNumber') }}</label>
          <input 
            v-model="form.jersey_number" 
            type="number" 
            min="1" 
            max="99" 
            required 
            class="form-input"
            :placeholder="t('teams.jerseyPlaceholder')"
          />
        </div>

        <!-- Position -->
        <div>
          <label class="form-label">{{ t('teams.position') }}</label>
          <select v-model="form.position" class="form-input">
            <option value="">{{ t('teams.selectPosition') }}</option>
            <option value="Goalkeeper">{{ t('teams.positions.goalkeeper') }}</option>
            <option value="Defender">{{ t('teams.positions.defender') }}</option>
            <option value="Midfielder">{{ t('teams.positions.midfielder') }}</option>
            <option value="Forward">{{ t('teams.positions.forward') }}</option>
          </select>
        </div>

        <!-- Captain -->
        <div class="flex items-center">
          <input 
            v-model="form.is_captain" 
            type="checkbox" 
            id="is_captain"
            class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
          />
          <label for="is_captain" class="ml-2 text-sm text-gray-700">{{ t('teams.teamCaptain') }}</label>
        </div>

        <!-- Actions -->
        <div class="flex space-x-3 pt-4">
          <button type="button" @click="$emit('close')" class="flex-1 btn-secondary">
            {{ t('common.cancel') }}
          </button>
          <button type="submit" :disabled="isLoading" class="flex-1 btn-primary">
            <div v-if="isLoading" class="flex items-center justify-center">
              <div class="spinner w-4 h-4 mr-2"></div>
              {{ t('teams.adding') }}...
            </div>
            <span v-else>{{ t('teams.addPlayer') }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { XMarkIcon } from '@heroicons/vue/24/outline'
import { playerAPI, teamAPI, apiHelpers } from '@/services/api'

export default {
  name: 'AddPlayerModal',
  components: { XMarkIcon },
  props: {
    teamId: {
      type: Number,
      required: true
    }
  },
  emits: ['close', 'success'],
  setup(props, { emit }) {
    const { t } = useI18n()
    const availablePlayers = ref([])
    const isLoading = ref(false)
    const error = ref('')
    
    const form = ref({
      player_id: '',
      jersey_number: '',
      position: '',
      is_captain: false
    })

    const fetchAvailablePlayers = async () => {
      try {
        const response = await playerAPI.available()
        if (apiHelpers.isSuccess(response)) {
          availablePlayers.value = apiHelpers.getData(response).available_players || []
        }
      } catch (err) {
        console.error('Failed to fetch available players:', err)
        availablePlayers.value = []
      }
    }

    const handleSubmit = async () => {
      error.value = ''
      isLoading.value = true

      try {
        const response = await teamAPI.addPlayer(props.teamId, {
          ...form.value,
          joined_date: new Date().toISOString().split('T')[0]
        })

        if (apiHelpers.isSuccess(response)) {
          emit('success')
          emit('close')
          window.$notify?.success(t('teams.playerAddedSuccess'))
        } else {
          error.value = response.data?.message || t('teams.failedToAddPlayer')
        }
      } catch (err) {
        error.value = apiHelpers.handleError(err)
      } finally {
        isLoading.value = false
      }
    }

    onMounted(() => {
      fetchAvailablePlayers()
    })

    return {
      availablePlayers,
      form,
      isLoading,
      error,
      handleSubmit,
      t
    }
  }
}
</script>