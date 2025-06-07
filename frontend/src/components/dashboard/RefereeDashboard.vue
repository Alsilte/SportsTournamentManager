<template>
  <div class="space-y-8">
    <!-- Referee Overview -->
    <div class="card p-6">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-900">Resumen del Árbitro</h2>
        <div class="flex items-center space-x-2">
          <div class="w-3 h-3 bg-success-500 rounded-full"></div>
          <span class="text-sm text-gray-600">Disponible para asignaciones</span>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="text-center p-4 bg-primary-50 rounded-lg">
          <PlayIcon class="w-8 h-8 text-primary-600 mx-auto mb-2" />
          <div class="text-2xl font-bold text-primary-600">{{ refereeStats.totalMatches }}</div>
          <div class="text-sm text-gray-600">Partidos dirigidos</div>
        </div>
        <div class="text-center p-4 bg-warning-50 rounded-lg">
          <ClockIcon class="w-8 h-8 text-warning-600 mx-auto mb-2" />
          <div class="text-2xl font-bold text-warning-600">{{ refereeStats.upcomingMatches }}</div>
          <div class="text-sm text-gray-600">Próximos partidos</div>
        </div>
        <div class="text-center p-4 bg-success-50 rounded-lg">
          <CheckCircleIcon class="w-8 h-8 text-success-600 mx-auto mb-2" />
          <div class="text-2xl font-bold text-success-600">{{ refereeStats.completedMatches }}</div>
          <div class="text-sm text-gray-600">Completados este mes</div>
        </div>
        <div class="text-center p-4 bg-secondary-50 rounded-lg">
          <StarIcon class="w-8 h-8 text-secondary-600 mx-auto mb-2" />
          <div class="text-2xl font-bold text-secondary-600">{{ refereeStats.averageRating }}</div>
          <div class="text-sm text-gray-600">Calificación promedio</div>
        </div>
      </div>
    </div>

    <!-- My Assigned Matches -->
    <div class="card p-6">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-900">Mis Partidos Asignados</h2>
        <div class="flex space-x-2">
          <button
            @click="filterMatches('upcoming')"
            :class="[
              'px-3 py-1 text-sm rounded-lg transition-colors',
              matchFilter === 'upcoming'
                ? 'bg-primary-100 text-primary-700'
                : 'text-gray-600 hover:bg-gray-100',
            ]"
          >
            Próximos
          </button>
          <button
            @click="filterMatches('completed')"
            :class="[
              'px-3 py-1 text-sm rounded-lg transition-colors',
              matchFilter === 'completed'
                ? 'bg-primary-100 text-primary-700'
                : 'text-gray-600 hover:bg-gray-100',
            ]"
          >
            Completados
          </button>
        </div>
      </div>

      <div v-if="filteredMatches.length > 0" class="space-y-4">
        <div
          v-for="match in filteredMatches"
          :key="match.id"
          class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
        >
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-6">
              <div class="text-center">
                <div class="text-sm font-medium text-gray-900">
                  {{ formatMatchDate(match.match_date) }}
                </div>
                <div class="text-xs text-gray-500">{{ formatMatchTime(match.match_date) }}</div>
              </div>
              <div class="flex items-center space-x-4">
                <div class="text-right">
                  <div class="font-medium text-gray-900">{{ match.home_team?.name }}</div>
                  <div class="text-xs text-gray-500">Local</div>
                </div>
                <div class="text-center">
                  <div v-if="match.status === 'completed'" class="text-xl font-bold text-gray-900">
                    {{ match.home_score }} - {{ match.away_score }}
                  </div>
                  <div v-else class="text-gray-400 font-bold">VS</div>
                </div>
                <div class="text-left">
                  <div class="font-medium text-gray-900">{{ match.away_team?.name }}</div>
                  <div class="text-xs text-gray-500">Visitante</div>
                </div>
              </div>
            </div>
            <div class="text-right">
              <div v-if="match.venue" class="text-sm text-gray-600">{{ match.venue }}</div>
              <div class="text-xs text-gray-500 mb-2">{{ match.tournament?.name }}</div>
              <div class="flex space-x-2">
                <RouterLink :to="`/matches/${match.id}`" class="btn-secondary text-xs px-3 py-1">
                  Ver detalles
                </RouterLink>
                <button
                  v-if="match.status === 'in_progress' || match.status === 'scheduled'"
                  @click="manageMatch(match)"
                  class="btn-primary text-xs px-3 py-1"
                >
                  {{ match.status === 'in_progress' ? 'Gestionar' : 'Iniciar partido' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="text-center py-8 text-gray-500">
        <CalendarIcon class="w-12 h-12 mx-auto mb-2 text-gray-300" />
        <p>{{ matchFilter === 'upcoming' ? 'No hay próximos partidos asignados' : 'No hay partidos completados asignados' }}</p>
        <p class="text-sm mt-2">
          {{
            matchFilter === 'upcoming'
              ? 'Las nuevas asignaciones aparecerán aquí'
              : 'Los partidos completados aparecerán aquí'
          }}
        </p>
      </div>
    </div>

    <!-- Match Management Tools -->
    <div class="card p-6">
      <h2 class="text-xl font-semibold text-gray-900 mb-6">Herramientas de Gestión de Partidos</h2>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <button
          @click="openEventRecorder"
          class="flex items-center p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow"
        >
          <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center mr-4">
            <DocumentPlusIcon class="w-6 h-6 text-primary-600" />
          </div>
          <div class="text-left">
            <div class="font-medium text-gray-900">Registrar eventos</div>
            <div class="text-sm text-gray-600">Registrar goles y tarjetas</div>
          </div>
        </button>

        <button
          @click="openMatchTimer"
          class="flex items-center p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow"
        >
          <div class="w-12 h-12 bg-success-100 rounded-lg flex items-center justify-center mr-4">
            <ClockIcon class="w-6 h-6 text-success-600" />
          </div>
          <div class="text-left">
            <div class="font-medium text-gray-900">Cronómetro</div>
            <div class="text-sm text-gray-600">Controlar tiempo del partido</div>
          </div>
        </button>

        <button
          @click="viewRefereeReports"
          class="flex items-center p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow"
        >
          <div class="w-12 h-12 bg-warning-100 rounded-lg flex items-center justify-center mr-4">
            <DocumentTextIcon class="w-6 h-6 text-warning-600" />
          </div>
          <div class="text-left">
            <div class="font-medium text-gray-900">Reportes de partidos</div>
            <div class="text-sm text-gray-600">Ver y enviar reportes</div>
          </div>
        </button>

        <button
          @click="accessRules"
          class="flex items-center p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow"
        >
          <div class="w-12 h-12 bg-secondary-100 rounded-lg flex items-center justify-center mr-4">
            <BookOpenIcon class="w-6 h-6 text-secondary-600" />
          </div>
          <div class="text-left">
            <div class="font-medium text-gray-900">Reglamento</div>
            <div class="text-sm text-gray-600">Acceso rápido a reglas</div>
          </div>
        </button>

        <button
          @click="contactSupport"
          class="flex items-center p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow"
        >
          <div class="w-12 h-12 bg-danger-100 rounded-lg flex items-center justify-center mr-4">
            <PhoneIcon class="w-6 h-6 text-danger-600" />
          </div>
          <div class="text-left">
            <div class="font-medium text-gray-900">Contacto de emergencia</div>
            <div class="text-sm text-gray-600">Línea directa del torneo</div>
          </div>
        </button>

        <button
          @click="viewSchedule"
          class="flex items-center p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow"
        >
          <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center mr-4">
            <CalendarDaysIcon class="w-6 h-6 text-primary-600" />
          </div>
          <div class="text-left">
            <div class="font-medium text-gray-900">Mi horario</div>
            <div class="text-sm text-gray-600">Ver horario completo</div>
          </div>
        </button>
      </div>
    </div>

    <!-- Recent Activity -->
    <div class="card p-6">
      <h2 class="text-xl font-semibold text-gray-900 mb-6">Actividad reciente</h2>

      <div v-if="recentActivity.length > 0" class="space-y-4">
        <div
          v-for="activity in recentActivity"
          :key="activity.id"
          class="flex items-start space-x-4"
        >
          <div
            :class="[
              'w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0',
              activity.bgColor,
            ]"
          >
            <component :is="activity.icon" :class="['w-5 h-5', activity.iconColor]" />
          </div>
          <div class="flex-1 min-w-0">
            <div class="text-sm font-medium text-gray-900">{{ activity.title }}</div>
            <div class="text-sm text-gray-600">{{ activity.description }}</div>
            <div class="text-xs text-gray-500 mt-1">{{ activity.time }}</div>
          </div>
          <div v-if="activity.action" class="flex-shrink-0">
            <button
              @click="activity.action.callback"
              class="text-primary-600 hover:text-primary-700 text-sm font-medium"
            >
              {{ activity.action.text }}
            </button>
          </div>
        </div>
      </div>

      <div v-else class="text-center py-8 text-gray-500">
        <ClockIcon class="w-12 h-12 mx-auto mb-2 text-gray-300" />
        <p>No hay actividad reciente</p>
        <p class="text-sm mt-2">
          La actividad aparecerá aquí
        </p>
      </div>
    </div>

    <!-- Performance Metrics -->
    <div class="card p-6">
      <h2 class="text-xl font-semibold text-gray-900 mb-6">Métricas de rendimiento</h2>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <h3 class="text-sm font-medium text-gray-700 mb-3">Estadísticas de partidos</h3>
          <div class="space-y-3">
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Tarjetas promedio por partido</span>
              <span class="text-sm font-medium text-gray-900">{{
                performanceMetrics.avgCards
              }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Goles promedio por partido</span>
              <span class="text-sm font-medium text-gray-900">{{
                performanceMetrics.avgGoals
              }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Partidos iniciados a tiempo</span>
              <span class="text-sm font-medium text-gray-900"
                >{{ performanceMetrics.onTimeRate }}%</span
              >
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Partidos esta temporada</span>
              <span class="text-sm font-medium text-gray-900">{{
                performanceMetrics.seasonMatches
              }}</span>
            </div>
          </div>
        </div>

        <div>
          <h3 class="text-sm font-medium text-gray-700 mb-3">Resumen de comentarios</h3>
          <div class="space-y-3">
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Calificación general</span>
              <div class="flex items-center">
                <span class="text-sm font-medium text-gray-900 mr-1">{{
                  performanceMetrics.rating
                }}</span>
                <div class="flex">
                  <StarIcon
                    v-for="i in 5"
                    :key="i"
                    :class="[
                      'w-4 h-4',
                      i <= Math.floor(performanceMetrics.rating)
                        ? 'text-warning-400'
                        : 'text-gray-300',
                    ]"
                  />
                </div>
              </div>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Comentarios positivos</span>
              <span class="text-sm font-medium text-gray-900"
                >{{ performanceMetrics.positiveFeedback }}%</span
              >
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Puntuación de consistencia</span>
              <span class="text-sm font-medium text-gray-900"
                >{{ performanceMetrics.consistency }}%</span
              >
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Puntuación de puntualidad</span>
              <span class="text-sm font-medium text-gray-900"
                >{{ performanceMetrics.punctuality }}%</span
              >
            </div>
          </div>
        </div>
      </div>

      <!-- Note about metrics -->
      <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <div class="flex items-start">
          <div class="flex-shrink-0">
            <StarIcon class="w-5 h-5 text-blue-600" />
          </div>
          <div class="ml-3">
            <h4 class="text-sm font-medium text-blue-800">Seguimiento de rendimiento</h4>
            <p class="mt-1 text-sm text-blue-700">
              Las métricas se calculan en base a tus partidos completados y comentarios de equipos
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
/**
 * Referee Dashboard Component
 * Provides match management tools and performance metrics for referees
 */

import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import {
  PlayIcon,
  ClockIcon,
  CheckCircleIcon,
  StarIcon,
  CalendarIcon,
  DocumentPlusIcon,
  DocumentTextIcon,
  BookOpenIcon,
  PhoneIcon,
  CalendarDaysIcon,
} from '@heroicons/vue/24/outline'
import { matchAPI, apiHelpers } from '@/services/api'
import { useAuthStore } from '@/stores/auth'

export default {
  name: 'RefereeDashboard',
  components: {
    PlayIcon,
    ClockIcon,
    CheckCircleIcon,
    StarIcon,
    CalendarIcon,
    DocumentPlusIcon,
    DocumentTextIcon,
    BookOpenIcon,
    PhoneIcon,
    CalendarDaysIcon,
  },
  setup() {
    const router = useRouter()
    const authStore = useAuthStore()
    const matchFilter = ref('upcoming')

    // Referee statistics - calculated from real data
    const refereeStats = ref({
      totalMatches: 0,
      upcomingMatches: 0,
      completedMatches: 0,
      averageRating: 0,
    })

    // Assigned matches - only real data from API
    const assignedMatches = ref([])

    // Recent activity - will be populated from real APIs when available
    const recentActivity = ref([])

    // Performance metrics - calculated from real match data
    const performanceMetrics = computed(() => {
      const completedMatches = assignedMatches.value.filter((m) => m.status === 'completed')
      const totalCompleted = completedMatches.length

      if (totalCompleted === 0) {
        return {
          avgCards: 0,
          avgGoals: 0,
          onTimeRate: 0,
          seasonMatches: 0,
          rating: 0,
          positiveFeedback: 0,
          consistency: 0,
          punctuality: 0,
        }
      }

      // Calculate average goals per match
      const totalGoals = completedMatches.reduce((sum, match) => {
        return sum + (match.home_score || 0) + (match.away_score || 0)
      }, 0)
      const avgGoals = totalCompleted > 0 ? (totalGoals / totalCompleted).toFixed(1) : 0

      // TODO: Calculate cards when match events API is available
      // TODO: Calculate other metrics when feedback system is implemented

      return {
        avgCards: 0, // Will be calculated from match events
        avgGoals: avgGoals,
        onTimeRate: 0, // Will be calculated from match start times
        seasonMatches: totalCompleted,
        rating: 0, // Will come from feedback system
        positiveFeedback: 0, // Will come from feedback system
        consistency: 0, // Will be calculated from performance data
        punctuality: 0, // Will be calculated from match timing data
      }
    })

    // Computed properties
    const filteredMatches = computed(() => {
      if (matchFilter.value === 'upcoming') {
        return assignedMatches.value.filter((match) =>
          ['scheduled', 'in_progress'].includes(match.status),
        )
      } else {
        return assignedMatches.value.filter((match) => match.status === 'completed')
      }
    })

    /**
     * Fetch referee dashboard data - only from real APIs
     */
    const fetchDashboardData = async () => {
      try {
        // Fetch matches assigned to this referee
        const matchResponse = await matchAPI.getAll({
          referee_id: authStore.user?.id,
          per_page: 50, // Get all referee matches
        })

        if (apiHelpers.isSuccess(matchResponse)) {
          const data = apiHelpers.getData(matchResponse)
          assignedMatches.value = data.data || []

          // Calculate statistics from real data
          const upcoming = assignedMatches.value.filter((m) =>
            ['scheduled', 'in_progress'].includes(m.status),
          ).length

          const completedThisMonth = assignedMatches.value.filter((m) => {
            if (m.status !== 'completed' || !m.match_date) return false
            const matchDate = new Date(m.match_date)
            const now = new Date()
            return (
              matchDate.getMonth() === now.getMonth() &&
              matchDate.getFullYear() === now.getFullYear()
            )
          }).length

          refereeStats.value = {
            totalMatches: assignedMatches.value.filter((m) => m.status === 'completed').length,
            upcomingMatches: upcoming,
            completedMatches: completedThisMonth,
            averageRating: 0,
          }
        } else {
          assignedMatches.value = []
          refereeStats.value = {
            totalMatches: 0,
            upcomingMatches: 0,
            completedMatches: 0,
            averageRating: 0,
          }
        }

        // TODO: Fetch recent activity when API is available
        // const activityResponse = await refereeAPI.getRecentActivity()
        recentActivity.value = [] // Empty until API is implemented
      } catch (error) {
        console.error('Failed to fetch referee dashboard data:', error)
        window.$notify?.error('Failed to load dashboard data')

        // Reset to empty state on error
        assignedMatches.value = []
        refereeStats.value = {
          totalMatches: 0,
          upcomingMatches: 0,
          completedMatches: 0,
          averageRating: 0,
        }
        recentActivity.value = []
      }
    }

    /**
     * Filter matches by type
     */
    const filterMatches = (filter) => {
      matchFilter.value = filter
    }

    /**
     * Manage match (start/continue)
     */
    const manageMatch = (match) => {
      router.push(`/matches/${match.id}/manage`)
    }

    /**
     * Accept assignment (placeholder for future implementation)
     */
    const acceptAssignment = (activityId) => {
      // TODO: Implement real API call
      console.log('Accept assignment:', activityId)

      // Remove from recent activity (temporary)
      recentActivity.value = recentActivity.value.filter((a) => a.id !== activityId)
      window.$notify?.success('Assignment accepted')
    }

    /**
     * Open event recorder tool
     */
    const openEventRecorder = () => {
      // TODO: Implement event recorder tool
      window.$notify?.info('Event recorder tool coming soon')
    }

    /**
     * Open match timer tool
     */
    const openMatchTimer = () => {
      // TODO: Implement match timer tool
      window.$notify?.info('Match timer tool coming soon')
    }

    /**
     * View referee reports
     */
    const viewRefereeReports = () => {
      // TODO: Implement reports system
      window.$notify?.info('Match reports system coming soon')
    }

    /**
     * Access rules reference
     */
    const accessRules = () => {
      // TODO: Implement rules reference
      window.$notify?.info('Rules reference coming soon')
    }

    /**
     * Contact support
     */
    const contactSupport = () => {
      window.$notify?.info('Emergency hotline: +1-800-REFEREE')
    }

    /**
     * View full schedule
     */
    const viewSchedule = () => {
      router.push('/referee/schedule')
    }

    /**
     * Format match date
     */
    const formatMatchDate = (dateString) => {
      if (!dateString) return 'TBD'
      return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
      })
    }

    /**
     * Format match time
     */
    const formatMatchTime = (dateString) => {
      if (!dateString) return 'TBD'
      return new Date(dateString).toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
      })
    }

    // Initialize dashboard
    onMounted(() => {
      fetchDashboardData()
    })

    return {
      authStore,
      matchFilter,
      refereeStats,
      assignedMatches,
      filteredMatches,
      performanceMetrics,
      recentActivity,
      filterMatches,
      manageMatch,
      acceptAssignment,
      openEventRecorder,
      openMatchTimer,
      viewRefereeReports,
      accessRules,
      contactSupport,
      viewSchedule,
      formatMatchDate,
      formatMatchTime,
    }
  },
}
</script>
