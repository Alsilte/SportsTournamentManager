<template>
  <MainLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <button @click="$router.go(-1)" class="btn-secondary p-2">
            <ArrowLeftIcon class="w-5 h-5" />
          </button>
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Team Roster</h1>
            <p class="text-gray-600 mt-1">Manage team players</p>
          </div>
        </div>
        <button class="btn-primary">
          <PlusIcon class="w-4 h-4 mr-2" />
          Add Player
        </button>
      </div>
    </template>

    <div class="card p-6">
      <div class="text-center py-12">
        <UsersIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
        <h3 class="text-lg font-medium text-gray-900 mb-2">Team Roster Management</h3>
        <p class="text-gray-600">This feature is under development</p>
      </div>
    </div>
  </MainLayout>
</template>

<script>
import { ArrowLeftIcon, PlusIcon, UsersIcon } from '@heroicons/vue/24/outline'
import MainLayout from '@/components/layout/MainLayout.vue'

export default {
  name: 'TeamRoster',
  components: { MainLayout, ArrowLeftIcon, PlusIcon, UsersIcon },
}
</script>
