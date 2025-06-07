<template>
  <MainLayout>
    <!-- Hero Section with Animated Background -->
    <section
      class="relative min-h-screen flex items-center justify-center overflow-hidden bg-gradient-to-br from-primary-900 via-primary-800 to-primary-700"
    >
      <!-- Animated Background Elements -->
      <div class="absolute inset-0">
        <div
          class="absolute top-1/4 left-1/4 w-96 h-96 bg-primary-500 rounded-full opacity-20 animate-pulse"
        ></div>
        <div
          class="absolute top-3/4 right-1/4 w-80 h-80 bg-primary-400 rounded-full opacity-15 animate-bounce"
          style="animation-duration: 3s"
        ></div>
        <div
          class="absolute bottom-1/4 left-1/3 w-64 h-64 bg-primary-300 rounded-full opacity-10 animate-pulse"
          style="animation-delay: 1s"
        ></div>

        <!-- Floating Icons -->
        <div class="absolute top-20 left-20 animate-float">
          <TrophyIcon class="w-16 h-16 text-primary-300 opacity-30" />
        </div>
        <div class="absolute top-40 right-32 animate-float" style="animation-delay: 0.5s">
          <CalendarIcon class="w-12 h-12 text-primary-200 opacity-40" />
        </div>
        <div class="absolute bottom-40 left-16 animate-float" style="animation-delay: 1s">
          <UserGroupIcon class="w-14 h-14 text-primary-400 opacity-25" />
        </div>
        <div class="absolute bottom-20 right-20 animate-float" style="animation-delay: 1.5s">
          <PlayIcon class="w-10 h-10 text-primary-300 opacity-35" />
        </div>
      </div>

      <!-- Hero Content -->
      <div class="relative z-10 container-custom text-center text-white">
        <div class="max-w-5xl mx-auto">
          <!-- Main Title with Gradient Text -->
          <h1 class="text-5xl md:text-7xl font-black mb-6 leading-tight">
            <span
              class="block text-transparent bg-clip-text bg-gradient-to-r from-white to-primary-200"
            >
              Gestión de Torneos
            </span>
            <span class="block text-4xl md:text-6xl mt-2 text-primary-200">
              Deportivos
            </span>
          </h1>

          <!-- Description -->
          <p class="text-xl md:text-2xl mb-8 text-primary-100 max-w-3xl mx-auto leading-relaxed">
            Organiza, gestiona y participa en torneos deportivos de manera profesional
          </p>

          <!-- CTA Buttons -->
          <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-12">
            <RouterLink
              v-if="!authStore.isAuthenticated"
              to="/register"
              class="group relative px-8 py-4 bg-white text-primary-600 rounded-2xl font-bold text-lg hover:bg-primary-50 transition-all duration-300 transform hover:scale-105 shadow-2xl"
            >
              <span class="relative z-10">Comenzar</span>
              <div
                class="absolute inset-0 bg-gradient-to-r from-primary-400 to-primary-600 rounded-2xl opacity-0 group-hover:opacity-10 transition-opacity duration-300"
              ></div>
            </RouterLink>
            <RouterLink
              v-else
              to="/dashboard"
              class="group relative px-8 py-4 bg-white text-primary-600 rounded-2xl font-bold text-lg hover:bg-primary-50 transition-all duration-300 transform hover:scale-105 shadow-2xl"
            >
              <span class="relative z-10">Ir al Dashboard</span>
            </RouterLink>

            <RouterLink
              to="/tournaments"
              class="group px-8 py-4 bg-transparent border-2 border-white text-white rounded-2xl font-bold text-lg hover:bg-white hover:text-primary-600 transition-all duration-300 transform hover:scale-105"
            >
              Ver Torneos
              <ArrowRightIcon
                class="w-5 h-5 ml-2 inline-block group-hover:translate-x-1 transition-transform duration-300"
              />
            </RouterLink>
          </div>

          <!-- Trust Indicators -->
          <div class="flex flex-wrap justify-center items-center gap-8 opacity-80">
            <div class="flex items-center space-x-2">
              <StarIcon class="w-5 h-5 text-yellow-400" />
              <span class="text-sm">4.9/5 estrellas</span>
            </div>
            <div class="flex items-center space-x-2">
              <UserGroupIcon class="w-5 h-5 text-primary-200" />
              <span class="text-sm">2,000+ usuarios activos</span>
            </div>
            <div class="flex items-center space-x-2">
              <ShieldCheckIcon class="w-5 h-5 text-green-400" />
              <span class="text-sm">100% Seguro</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Scroll Indicator -->
      <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
        <ChevronDownIcon class="w-8 h-8 text-white opacity-60" />
      </div>
    </section>

    <!-- Features Section -->
    <section class="py-24 bg-gradient-to-b from-gray-50 to-white relative overflow-hidden">
      <!-- Background Pattern -->
      <div class="absolute inset-0 opacity-5">
        <div
          class="absolute inset-0"
          style="
            background-image:
              radial-gradient(circle at 20% 50%, rgba(99, 102, 241, 0.3) 0%, transparent 50%),
              radial-gradient(circle at 80% 20%, rgba(168, 85, 247, 0.3) 0%, transparent 50%),
              radial-gradient(circle at 40% 80%, rgba(236, 72, 153, 0.3) 0%, transparent 50%);
          "
        ></div>
      </div>

      <div class="container-custom relative z-10">
        <!-- Section Header -->
        <div class="text-center mb-20">
          <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">
            Características Principales
          </h2>
          <p class="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
            Todas las herramientas que necesitas para gestionar torneos profesionales
          </p>
        </div>

        <!-- Features Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div
            v-for="(feature, index) in features"
            :key="feature.key"
            class="group relative bg-white rounded-3xl p-8 shadow-lg hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 border border-gray-100"
            :class="`animate-slide-up`"
            :style="{ animationDelay: `${index * 150}ms` }"
          >
            <!-- Feature Icon -->
            <div class="relative mb-6">
              <div
                :class="`w-16 h-16 ${feature.bgColor} rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300`"
              >
                <component :is="feature.icon" :class="`w-8 h-8 ${feature.iconColor}`" />
              </div>
              <!-- Animated background -->
              <div
                :class="`absolute inset-0 w-16 h-16 ${feature.bgColor} rounded-2xl opacity-0 group-hover:opacity-20 scale-150 transition-all duration-500`"
              ></div>
            </div>

            <!-- Feature Content -->
            <h3
              class="text-xl font-bold text-gray-900 mb-4 group-hover:text-primary-600 transition-colors duration-300"
            >
              {{ getFeatureTitle(feature.key) }}
            </h3>
            <p class="text-gray-600 leading-relaxed">
              {{ getFeatureDescription(feature.key) }}
            </p>

            <!-- Hover Effect -->
            <div
              class="absolute inset-0 bg-gradient-to-br from-primary-500 to-primary-600 rounded-3xl opacity-0 group-hover:opacity-5 transition-opacity duration-300"
            ></div>
          </div>
        </div>
      </div>
    </section>

    <!-- Stats Section -->
    <section class="py-24 bg-white relative overflow-hidden">
      <!-- Animated Background -->
      <div
        class="absolute inset-0 bg-gradient-to-r from-primary-50 via-transparent to-secondary-50"
      ></div>

      <div class="container-custom relative z-10">
        <div class="text-center mb-16">
          <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">
            Números que Hablan
          </h2>
          <p class="text-xl text-gray-600 max-w-2xl mx-auto">
            Únete a miles de organizadores y atletas que confían en nuestra plataforma
          </p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-8">
          <div
            v-for="(stat, index) in stats"
            :key="stat.key"
            class="text-center group"
            :class="`animate-fade-in`"
            :style="{ animationDelay: `${index * 200}ms` }"
          >
            <div class="relative inline-block mb-4">
              <div
                class="text-5xl md:text-6xl font-black text-transparent bg-clip-text bg-gradient-to-br from-primary-600 to-primary-800 group-hover:from-primary-500 group-hover:to-primary-700 transition-all duration-300"
              >
                {{ stat.value }}
              </div>
              <!-- Animated underline -->
              <div
                class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-0 h-1 bg-gradient-to-r from-primary-500 to-primary-600 group-hover:w-full transition-all duration-500 rounded-full"
              ></div>
            </div>
            <div class="text-gray-600 font-medium text-lg">
              {{ getStatLabel(stat.key) }}
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Recent Tournaments Section -->
    <section class="py-24 bg-gradient-to-b from-gray-50 to-white">
      <div class="container-custom">
        <div
          class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-12 gap-6"
        >
          <div>
            <h2 class="text-4xl font-bold text-gray-900 mb-4">
              Torneos Recientes
            </h2>
            <p class="text-gray-600 text-lg">
              Descubre los últimos torneos y únete a la competencia
            </p>
          </div>
          <RouterLink
            to="/tournaments"
            class="group flex items-center px-6 py-3 bg-primary-600 text-white rounded-2xl font-semibold hover:bg-primary-700 transition-all duration-300 transform hover:scale-105 shadow-lg"
          >
            Ver Todos
            <ArrowRightIcon
              class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform duration-300"
            />
          </RouterLink>
        </div>

        <!-- Loading State -->
        <div
          v-if="isLoadingTournaments"
          class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8"
        >
          <div
            v-for="i in 6"
            :key="i"
            class="bg-white rounded-2xl shadow-lg overflow-hidden animate-pulse"
          >
            <div class="h-48 bg-gray-200"></div>
            <div class="p-6 space-y-3">
              <div class="h-4 bg-gray-200 rounded w-3/4"></div>
              <div class="h-3 bg-gray-200 rounded w-1/2"></div>
              <div class="h-3 bg-gray-200 rounded w-2/3"></div>
            </div>
          </div>
        </div>

        <!-- Tournaments Grid -->
        <div
          v-else-if="recentTournaments.length"
          class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8"
        >
          <div
            v-for="(tournament, index) in recentTournaments"
            :key="tournament.id"
            class="group bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 overflow-hidden border border-gray-100"
            :class="`animate-slide-up`"
            :style="{ animationDelay: `${index * 100}ms` }"
          >
            <!-- Tournament Header -->
            <div
              class="relative h-48 bg-gradient-to-br from-primary-500 to-primary-700 overflow-hidden"
            >
              <div class="absolute inset-0 bg-black opacity-20"></div>
              <div
                class="absolute inset-0 bg-gradient-to-br from-transparent to-black opacity-50"
              ></div>

              <!-- Floating elements -->
              <div class="absolute top-4 right-4 opacity-20">
                <TrophyIcon class="w-16 h-16 text-white" />
              </div>

              <!-- Status Badge -->
              <div class="absolute top-4 left-4">
                <span
                  :class="getStatusBadgeClass(tournament.status)"
                  class="px-3 py-1 rounded-full text-xs font-medium backdrop-blur-sm"
                >
                  {{ formatStatus(tournament.status) }}
                </span>
              </div>

              <!-- Tournament Info -->
              <div class="absolute bottom-4 left-4 right-4">
                <h3
                  class="text-white text-xl font-bold mb-1 truncate group-hover:text-primary-200 transition-colors duration-300"
                >
                  {{ tournament.name }}
                </h3>
                <p class="text-primary-100 text-sm">
                  {{ tournament.sport_type }} •
                  {{ formatTournamentType(tournament.tournament_type) }}
                </p>
              </div>
            </div>

            <!-- Tournament Details -->
            <div class="p-6">
              <p v-if="tournament.description" class="text-gray-600 text-sm mb-4 line-clamp-2">
                {{ tournament.description }}
              </p>

              <!-- Tournament Info Grid -->
              <div class="space-y-3 mb-6">
                <div class="flex items-center text-sm text-gray-600">
                  <CalendarIcon class="w-4 h-4 mr-2 text-gray-400" />
                  <span>{{ formatDate(tournament.start_date) }}</span>
                </div>

                <div v-if="tournament.location" class="flex items-center text-sm text-gray-600">
                  <MapPinIcon class="w-4 h-4 mr-2 text-gray-400" />
                  <span>{{ tournament.location }}</span>
                </div>

                <div class="flex items-center text-sm text-gray-600">
                  <UserGroupIcon class="w-4 h-4 mr-2 text-gray-400" />
                  <span
                    >{{ tournament.registered_teams_count || 0 }}/{{ tournament.max_teams }}
                    equipos</span
                  >
                </div>

                <div v-if="tournament.prize_pool" class="flex items-center text-sm text-gray-600">
                  <CurrencyDollarIcon class="w-4 h-4 mr-2 text-gray-400" />
                  <span>${{ formatMoney(tournament.prize_pool) }}</span>
                </div>
              </div>

              <!-- Progress Bar -->
              <div v-if="tournament.status === 'registration_open'" class="mb-4">
                <div class="flex justify-between text-xs text-gray-600 mb-1">
                  <span>Progreso de inscripciones</span>
                  <span>{{ Math.round(getRegistrationProgress(tournament)) }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div
                    class="bg-gradient-to-r from-primary-500 to-primary-600 h-2 rounded-full transition-all duration-500"
                    :style="{ width: `${getRegistrationProgress(tournament)}%` }"
                  ></div>
                </div>
              </div>

              <!-- Action Button -->
              <RouterLink
                :to="`/tournaments/${tournament.id}`"
                class="block w-full text-center py-3 bg-gray-100 hover:bg-primary-600 text-gray-700 hover:text-white rounded-xl font-semibold transition-all duration-300 group-hover:bg-primary-500 group-hover:text-white"
              >
                Ver Detalles
              </RouterLink>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-16">
          <div class="relative inline-block mb-8">
            <CalendarIcon class="w-24 h-24 text-gray-300 mx-auto" />
            <div
              class="absolute inset-0 bg-gradient-to-br from-primary-500 to-primary-600 rounded-full opacity-10 animate-pulse"
            ></div>
          </div>
          <h3 class="text-2xl font-bold text-gray-900 mb-4">
            No hay torneos disponibles
          </h3>
          <p class="text-gray-600 mb-8 max-w-md mx-auto">
            Sé el primero en crear un torneo emocionante
          </p>
          <RouterLink
            v-if="authStore.isAdmin"
            to="/tournaments/create"
            class="inline-flex items-center px-6 py-3 bg-primary-600 text-white rounded-2xl font-semibold hover:bg-primary-700 transition-all duration-300 transform hover:scale-105 shadow-lg"
          >
            <PlusIcon class="w-5 h-5 mr-2" />
            Crear Torneo
          </RouterLink>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section
      class="py-24 bg-gradient-to-br from-primary-600 via-primary-700 to-primary-800 relative overflow-hidden"
    >
      <!-- Background Elements -->
      <div class="absolute inset-0">
        <div class="absolute top-0 left-0 w-full h-full bg-black opacity-10"></div>
        <div
          class="absolute top-1/4 right-1/4 w-96 h-96 bg-primary-500 rounded-full opacity-20 animate-pulse"
        ></div>
        <div
          class="absolute bottom-1/4 left-1/4 w-80 h-80 bg-primary-400 rounded-full opacity-15 animate-bounce"
          style="animation-duration: 4s"
        ></div>
      </div>

      <div class="container-custom text-center relative z-10">
        <div class="max-w-4xl mx-auto">
          <h2 class="text-4xl md:text-5xl font-bold text-white mb-6">
            ¿Listo para Comenzar?
          </h2>
          <p class="text-xl text-primary-100 mb-10 max-w-2xl mx-auto leading-relaxed">
            Únete a miles de organizadores y atletas. Crea tu cuenta gratis hoy.
          </p>

          <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
            <RouterLink
              v-if="!authStore.isAuthenticated"
              to="/register"
              class="group px-8 py-4 bg-white text-primary-600 rounded-2xl font-bold text-lg hover:bg-primary-50 transition-all duration-300 transform hover:scale-105 shadow-2xl"
            >
              Registrarse Ahora
              <ArrowRightIcon
                class="w-5 h-5 ml-2 inline-block group-hover:translate-x-1 transition-transform duration-300"
              />
            </RouterLink>

            <RouterLink
              to="/tournaments"
              class="group px-8 py-4 bg-transparent border-2 border-white text-white rounded-2xl font-bold text-lg hover:bg-white hover:text-primary-600 transition-all duration-300 transform hover:scale-105"
            >
              Explorar Torneos
              <EyeIcon
                class="w-5 h-5 ml-2 inline-block group-hover:scale-110 transition-transform duration-300"
              />
            </RouterLink>
          </div>
        </div>
      </div>
    </section>
  </MainLayout>
</template>

<script>
/**
 * Enhanced Home Page Component with I18n
 * Beautiful landing page with animations, modern design, and full internationalization
 */

import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import {
  CalendarIcon,
  UserGroupIcon,
  ChartBarIcon,
  CogIcon,
  TrophyIcon,
  PlayIcon,
  ArrowRightIcon,
  ChevronDownIcon,
  StarIcon,
  ShieldCheckIcon,
  MapPinIcon,
  CurrencyDollarIcon,
  PlusIcon,
  EyeIcon,
} from '@heroicons/vue/24/outline'
import { useAuthStore } from '@/stores/auth'
import { tournamentAPI, apiHelpers } from '@/services/api'
import MainLayout from '@/components/layout/MainLayout.vue'

export default {
  name: 'Home',
  components: {
    MainLayout,
    CalendarIcon,
    UserGroupIcon,
    ChartBarIcon,
    CogIcon,
    TrophyIcon,
    PlayIcon,
    ArrowRightIcon,
    ChevronDownIcon,
    StarIcon,
    ShieldCheckIcon,
    MapPinIcon,
    CurrencyDollarIcon,
    PlusIcon,
    EyeIcon,
  },
  setup() {
    const authStore = useAuthStore()
    const recentTournaments = ref([])
    const isLoadingTournaments = ref(false)

    // Platform features
    const features = [
      {
        key: 'tournament_management',
        icon: CalendarIcon,
        bgColor: 'bg-primary-100',
        iconColor: 'text-primary-600',
      },
      {
        key: 'team_organization',
        icon: UserGroupIcon,
        bgColor: 'bg-success-100',
        iconColor: 'text-success-600',
      },
      {
        key: 'match_scheduling',
        icon: PlayIcon,
        bgColor: 'bg-warning-100',
        iconColor: 'text-warning-600',
      },
      {
        key: 'live_statistics',
        icon: ChartBarIcon,
        bgColor: 'bg-secondary-100',
        iconColor: 'text-secondary-600',
      },
      {
        key: 'user_management',
        icon: CogIcon,
        bgColor: 'bg-danger-100',
        iconColor: 'text-danger-600',
      },
      {
        key: 'awards_recognition',
        icon: TrophyIcon,
        bgColor: 'bg-indigo-100',
        iconColor: 'text-indigo-600',
      },
    ]

    // Platform statistics
    const stats = [
      { key: 'tournaments', value: '150+' },
      { key: 'teams', value: '500+' },
      { key: 'players', value: '2,000+' },
      { key: 'matches', value: '1,200+' },
    ]

    /**
     * Fetch recent tournaments - SOLO DATOS REALES
     */
    const fetchRecentTournaments = async () => {
      isLoadingTournaments.value = true

      try {
        console.log('🔍 Fetching tournaments...')

        const response = await tournamentAPI.getAll({
          per_page: 6,
        })

        console.log('📡 API Response:', response)

        if (apiHelpers.isSuccess(response)) {
          const data = apiHelpers.getData(response)
          console.log('📊 Data received:', data)

          const tournaments = data.data || data || []
          console.log('🏆 Tournaments found:', tournaments.length)

          recentTournaments.value = tournaments.slice(0, 6)
        } else {
          console.warn('Tournament API returned unsuccessful response')
          recentTournaments.value = []
        }
      } catch (error) {
        console.error('💥 Failed to fetch tournaments:', error)
        recentTournaments.value = []
        window.$notify?.error('Failed to load tournaments')
      } finally {
        isLoadingTournaments.value = false
      }
    }

    /**
     * Get registration progress percentage
     */
    const getRegistrationProgress = (tournament) => {
      const registered = tournament.registered_teams_count || 0
      const max = tournament.max_teams || 1
      return Math.min((registered / max) * 100, 100)
    }

    /**
     * Get status badge CSS classes
     */
    const getStatusBadgeClass = (status) => {
      const classMap = {
        draft: 'bg-gray-100 bg-opacity-90 text-gray-800',
        registration_open: 'bg-success-100 bg-opacity-90 text-success-800',
        in_progress: 'bg-primary-100 bg-opacity-90 text-primary-800',
        completed: 'bg-secondary-100 bg-opacity-90 text-secondary-800',
        cancelled: 'bg-danger-100 bg-opacity-90 text-danger-800',
      }
      return classMap[status] || classMap.draft
    }

    /**
     * Format tournament type
     */
    const formatTournamentType = (type) => {
      return t(`tournaments.formats.${type}`)
    }

    /**
     * Format status for display
     */
    const formatStatus = (status) => {
      const statusMap = {
        draft: 'Borrador',
        registration_open: 'Inscripciones Abiertas',
        in_progress: 'En Progreso',
        completed: 'Completado',
        cancelled: 'Cancelado',
      }
      return statusMap[status] || status
    }

    /**
     * Format date for display
     */
    const formatDate = (dateString) => {
      if (!dateString) return 'Por determinar'
      return new Date(dateString).toLocaleDateString('es-ES', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
      })
    }

    /**
     * Format money amount
     */
    const formatMoney = (amount) => {
      return Number(amount).toLocaleString()
    }

    /**
     * Get feature title in Spanish
     */
    const getFeatureTitle = (key) => {
      const titles = {
        tournament_management: 'Gestión de Torneos',
        team_organization: 'Organización de Equipos',
        match_scheduling: 'Programación de Partidos',
        live_statistics: 'Estadísticas en Vivo',
        user_management: 'Gestión de Usuarios',
        awards_recognition: 'Premios y Reconocimientos'
      }
      return titles[key] || key
    }

    /**
     * Get feature description in Spanish
     */
    const getFeatureDescription = (key) => {
      const descriptions = {
        tournament_management: 'Crea y administra torneos con facilidad. Gestiona inscripciones, calendario y resultados.',
        team_organization: 'Organiza equipos, gestiona jugadores y mantén toda la información actualizada.',
        match_scheduling: 'Programa partidos automáticamente y mantén a todos informados sobre horarios.',
        live_statistics: 'Seguimiento en tiempo real de estadísticas de jugadores y equipos.',
        user_management: 'Sistema completo de gestión de usuarios con roles y permisos.',
        awards_recognition: 'Sistema de premios y reconocimientos para motivar a los participantes.'
      }
      return descriptions[key] || key
    }

    /**
     * Get stat label in Spanish
     */
    const getStatLabel = (key) => {
      const labels = {
        tournaments: 'Torneos',
        teams: 'Equipos',
        players: 'Jugadores',
        matches: 'Partidos'
      }
      return labels[key] || key
    }

    onMounted(() => {
      fetchRecentTournaments()
    })

    return {
      authStore,
      features,
      stats,
      recentTournaments,
      isLoadingTournaments,
      getFeatureTitle,
      getFeatureDescription,
      getStatLabel,
      getRegistrationProgress,
      getStatusBadgeClass,
      formatStatus,
      formatTournamentType,
      formatDate,
      formatMoney,
    }
  },
}
</script>

<style scoped>
/* Custom animations */
@keyframes float {
  0%,
  100% {
    transform: translateY(0px);
  }
  50% {
    transform: translateY(-20px);
  }
}

@keyframes slide-up {
  0% {
    opacity: 0;
    transform: translateY(60px);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fade-in {
  0% {
    opacity: 0;
  }
  100% {
    opacity: 1;
  }
}

.animate-float {
  animation: float 6s ease-in-out infinite;
}

.animate-slide-up {
  animation: slide-up 0.8s ease-out forwards;
  opacity: 0;
}

.animate-fade-in {
  animation: fade-in 1s ease-out forwards;
  opacity: 0;
}

/* Text clamp utility */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Glass morphism effect */
.glass {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
}
</style>
