<template>
  <MainLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Gestión de Usuarios</h1>
          <p class="text-gray-600 mt-1">Administra usuarios del sistema</p>
        </div>
        <button class="btn-primary">
          <PlusIcon class="w-4 h-4 mr-2" />
          Agregar Usuario
        </button>
      </div>
    </template>

    <div class="card p-6">
      <div class="text-center py-12">
        <UsersIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
        <h3 class="text-lg font-medium text-gray-900 mb-2">Gestión de Usuarios</h3>
        <p class="text-gray-600">En desarrollo</p>
      </div>
    </div>
  </MainLayout>
</template>

<script>
import { PlusIcon, UsersIcon } from '@heroicons/vue/24/outline'
import MainLayout from '@/components/layout/MainLayout.vue'

export default {
  name: 'UserManagement',
  components: { MainLayout, PlusIcon, UsersIcon },
}
</script>
