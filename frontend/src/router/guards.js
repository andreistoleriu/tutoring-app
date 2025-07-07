import { useAuthStore } from '@/stores/auth'

export const requireAuth = (to, from, next) => {
  const authStore = useAuthStore()

  if (!authStore.isAuthenticated) {
    next('/')
  } else {
    next()
  }
}

export const requireStudent = (to, from, next) => {
  const authStore = useAuthStore()

  if (!authStore.isAuthenticated) {
    next('/')
  } else if (!authStore.isStudent) {
    next('/dashboard/tutor')
  } else {
    next()
  }
}

export const requireTutor = (to, from, next) => {
  const authStore = useAuthStore()

  if (!authStore.isAuthenticated) {
    next('/')
  } else if (!authStore.isTutor) {
    next('/dashboard/student')
  } else {
    next()
  }
}

export const redirectIfAuthenticated = (to, from, next) => {
  const authStore = useAuthStore()

  if (authStore.isAuthenticated) {
    if (authStore.isTutor) {
      next('/dashboard/tutor')
    } else {
      next('/dashboard/student')
    }
  } else {
    next()
  }
}
