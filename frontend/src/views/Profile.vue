<!-- src/views/Profile.vue -->
<template>
  <MainLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Profile</h1>
          <p class="text-gray-600 mt-1">Manage your account settings</p>
        </div>
      </div>
    </template>

    <div class="max-w-2xl mx-auto">
      <div class="card p-6">
        <div class="flex items-center space-x-6 mb-6">
          <div class="w-20 h-20 bg-primary-100 rounded-full flex items-center justify-center">
            <UserIcon class="w-10 h-10 text-primary-600" />
          </div>
          <div>
            <h2 class="text-xl font-semibold text-gray-900">{{ authStore.userName }}</h2>
            <p class="text-gray-600 capitalize">{{ authStore.userRole }}</p>
            <p class="text-sm text-gray-500">{{ authStore.userEmail }}</p>
          </div>
        </div>

        <div class="space-y-4">
          <div>
            <label class="form-label">Full Name</label>
            <input type="text" :value="authStore.userName" disabled class="form-input bg-gray-50" />
          </div>
          <div>
            <label class="form-label">Email</label>
            <input
              type="email"
              :value="authStore.userEmail"
              disabled
              class="form-input bg-gray-50"
            />
          </div>
          <div>
            <label class="form-label">Role</label>
            <input
              type="text"
              :value="authStore.userRole"
              disabled
              class="form-input bg-gray-50 capitalize"
            />
          </div>
        </div>

        <div class="mt-6 pt-6 border-t border-gray-200">
          <button class="btn-secondary mr-3">Edit Profile</button>
          <button class="btn-danger">Change Password</button>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script>
import { UserIcon } from '@heroicons/vue/24/outline'
import { useAuthStore } from '@/stores/auth'
import MainLayout from '@/components/layout/MainLayout.vue'

export default {
  name: 'Profile',
  components: {
    MainLayout,
    UserIcon,
  },
  setup() {
    const authStore = useAuthStore()
    return { authStore }
  },
}
</script>

<!-- src/views/Dashboard.vue -->
<template>
  <MainLayout>
    <template #header>
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Welcome back, {{ authStore.userName }}!</h1>
        <p class="text-gray-600 mt-1">{{ getWelcomeMessage() }}</p>
      </div>
    </template>

    <!-- Role-based dashboard content -->
    <AdminDashboard v-if="authStore.isAdmin" />
    <TeamManagerDashboard v-else-if="authStore.isTeamManager" />
    <RefereeDashboard v-else-if="authStore.isReferee" />
    <PlayerDashboard v-else />
  </MainLayout>
</template>

<script>
import { useAuthStore } from '@/stores/auth'
import MainLayout from '@/components/layout/MainLayout.vue'
import AdminDashboard from '@/components/dashboard/AdminDashboard.vue'
import TeamManagerDashboard from '@/components/dashboard/TeamManagerDashboard.vue'
import RefereeDashboard from '@/components/dashboard/RefereeDashboard.vue'
import PlayerDashboard from '@/components/dashboard/PlayerDashboard.vue'

export default {
  name: 'Dashboard',
  components: {
    MainLayout,
    AdminDashboard,
    TeamManagerDashboard,
    RefereeDashboard,
    PlayerDashboard,
  },
  setup() {
    const authStore = useAuthStore()

    const getWelcomeMessage = () => {
      const messages = {
        admin: 'Manage tournaments and oversee the entire system',
        team_manager: 'Manage your teams and register for tournaments',
        referee: 'Review your assigned matches and manage game events',
        player: 'Check your stats, teams, and upcoming matches',
      }
      return messages[authStore.userRole] || 'Welcome to Tournament Manager'
    }

    return {
      authStore,
      getWelcomeMessage,
    }
  },
}
</script>
