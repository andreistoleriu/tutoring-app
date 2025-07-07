import { createRouter, createWebHistory } from 'vue-router'
import TutorsView from '../views/TutorsView.vue'
import { requireAuth, requireStudent } from './guards'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: () => import('../views/HomeView.vue')
    },
    {
      path: '/tutors',
      name: 'tutors',
      component: TutorsView
    },
    // Protected Student Routes
    {
      path: '/dashboard/student',
      name: 'student-dashboard',
      component: () => import('../views/dashboard/StudentDashboardView.vue'),
      beforeEnter: [requireAuth, requireStudent]
    }
  ]
})

export default router
