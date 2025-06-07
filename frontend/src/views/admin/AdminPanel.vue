<template>
  <MainLayout>
    <template #header>
      <div>
        <h1 class="text-3xl font-bold text-gray-900">{{ $t('admin.panel.title') }}</h1>
        <p class="text-gray-600 mt-1">{{ $t('admin.panel.subtitle') }}</p>
      </div>
    </template>

    <div class="space-y-8">
      <!-- Use the existing AdminDashboard component -->
      <AdminDashboard />
    </div>
  </MainLayout>
</template>

<script>
import MainLayout from '@/components/layout/MainLayout.vue'
import AdminDashboard from '@/components/dashboard/AdminDashboard.vue'

export default {
  name: 'AdminPanel',
  components: { MainLayout, AdminDashboard },
}
</script>
