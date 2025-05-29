<template>
  <MainLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Matches</h1>
          <p class="text-gray-600 mt-1">View match schedules and results</p>
        </div>
        <button v-if="authStore.isAdmin" class="btn-primary">
          <PlusIcon class="w-4 h-4 mr-2" />
          Create Match
        </button>
      </div>
    </template>

    <div class="card p-6">
      <div class="text-center py-12">
        <PlayIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
        <h3 class="text-lg font-medium text-gray-900 mb-2">Match Center</h3>
        <p class="text-gray-600">This feature is under development</p>
      </div>
    </div>
  </MainLayout>
</template>

<script>
import { PlusIcon, PlayIcon } from '@heroicons/vue/24/outline'
import { useAuthStore } from '@/stores/auth'
import MainLayout from '@/components/layout/MainLayout.vue'

export default {
  name: 'Matches',
  components: { MainLayout, PlusIcon, PlayIcon },
  setup() {
    const authStore = useAuthStore()
    return { authStore }
  },
}
</script>
