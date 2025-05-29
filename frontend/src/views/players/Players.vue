<template>
  <MainLayout>
    <template #header>
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Players</h1>
        <p class="text-gray-600 mt-1">Browse player profiles and statistics</p>
      </div>
    </template>

    <div class="card p-6">
      <div class="text-center py-12">
        <UsersIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
        <h3 class="text-lg font-medium text-gray-900 mb-2">Player Directory</h3>
        <p class="text-gray-600">This feature is under development</p>
      </div>
    </div>
  </MainLayout>
</template>

<script>
import { UsersIcon } from '@heroicons/vue/24/outline'
import MainLayout from '@/components/layout/MainLayout.vue'

export default {
  name: 'Players',
  components: { MainLayout, UsersIcon },
}
</script>
