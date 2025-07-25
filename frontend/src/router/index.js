import { createRouter, createWebHistory } from 'vue-router'
import TutorsView from '../views/TutorsView.vue'
import { requireAuth, requireStudent, requireTutor } from './guards'
import MessagesView from '../views/MessagesView.vue'
import ConversationView from '../views/ConversationView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: () => import('../views/HomeView.vue'),
    },
    {
      path: '/tutors',
      name: 'tutors',
      component: TutorsView,
    },

    {
      path: '/tutors/:id',
      name: 'tutor-profile-public',
      component: () => import('../views/TutorProfilePublicView.vue'),
      props: true,
    },

    // Protected Student Routes
    {
      path: '/dashboard/student',
      name: 'student-dashboard',
      component: () => import('../views/dashboard/StudentDashboardView.vue'),
      beforeEnter: [requireAuth, requireStudent],
    },

    // Protected Tutor Routes
    {
      path: '/dashboard/tutor',
      name: 'tutor-dashboard',
      component: () => import('../views/dashboard/TutorDashboardView.vue'),
      beforeEnter: [requireAuth, requireTutor],
    },

    // Tutor Management Routes
    {
      path: '/tutor/profile',
      name: 'tutor-profile',
      component: () => import('../views/tutor/TutorProfileView.vue'),
      beforeEnter: [requireAuth, requireTutor],
    },
    {
      path: '/tutor/availability',
      name: 'tutor-availability',
      component: () => import('../views/tutor/TutorAvailabilityView.vue'),
      beforeEnter: [requireAuth, requireTutor],
    },
    {
      path: '/tutor/bookings',
      name: 'tutor-bookings',
      component: () => import('../views/tutor/TutorBookingsView.vue'),
      beforeEnter: [requireAuth, requireTutor],
    },
    {
      path: '/tutor/schedule',
      name: 'tutor-schedule',
      component: () => import('../views/tutor/TutorScheduleView.vue'),
      beforeEnter: [requireAuth, requireTutor],
    },
    {
      path: '/tutor/reviews',
      name: 'tutor-reviews',
      component: () => import('../views/tutor/TutorReviewsView.vue'),
      beforeEnter: [requireAuth, requireTutor],
    },

    // Placeholder routes for components not yet created
    {
      path: '/tutor/earnings',
      name: 'tutor-earnings',
      redirect: '/dashboard/tutor',
      beforeEnter: [requireAuth, requireTutor],
    },
    {
      path: '/tutor/subscription',
      name: 'tutor-subscription',
      redirect: '/dashboard/tutor',
      beforeEnter: [requireAuth, requireTutor],
    },
    {
      path: '/tutor/lesson/:id',
      name: 'tutor-lesson',
      redirect: '/dashboard/tutor',
      beforeEnter: [requireAuth, requireTutor],
    },
    {
      path: '/student/profile',
      name: 'student-profile',
      component: () => import('../views/student/StudentProfileView.vue'),
      beforeEnter: [requireAuth, requireStudent],
    },
    {
      path: '/student/bookings',
      name: 'student-bookings',
      component: () => import('../views/student/StudentBookingsView.vue'),
      beforeEnter: [requireAuth, requireStudent],
    },
    {
      path: '/messages',
      name: 'messages',
      component: MessagesView,
      beforeEnter: [requireAuth],
    },
    {
      path: '/messages/:id',
      name: 'conversation',
      component: ConversationView,
      props: true,
      beforeEnter: [requireAuth],
    },
  ],
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    }
    return { top: 0 }
  },
})

export default router
