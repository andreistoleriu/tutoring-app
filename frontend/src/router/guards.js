// router/guards.js
import { useAuthStore } from '@/stores/auth'

export const requireAuth = async (to, from, next) => {
  const authStore = useAuthStore()

  try {
    // Check if user is authenticated
    if (!authStore.isAuthenticated) {
      // Try to refresh user data if token exists
      if (authStore.token) {
        try {
          await authStore.fetchUser()
        } catch (error) {
          console.error('Failed to fetch user:', error)
          authStore.logout()
          return next('/login')
        }
      } else {
        return next('/login')
      }
    }

    next()
  } catch (error) {
    console.error('Auth guard error:', error)
    next('/login')
  }
}

export const requireStudent = (to, from, next) => {
  const authStore = useAuthStore()

  try {
    if (!authStore.user || authStore.user.user_type !== 'student') {
      // Redirect to appropriate dashboard based on user type
      if (authStore.user?.user_type === 'tutor') {
        return next('/dashboard/tutor')
      }
      return next('/login')
    }

    next()
  } catch (error) {
    console.error('Student guard error:', error)
    next('/login')
  }
}

export const requireTutor = (to, from, next) => {
  const authStore = useAuthStore()

  try {
    if (!authStore.user || authStore.user.user_type !== 'tutor') {
      // Redirect to appropriate dashboard based on user type
      if (authStore.user?.user_type === 'student') {
        return next('/dashboard/student')
      }
      return next('/login')
    }

    next()
  } catch (error) {
    console.error('Tutor guard error:', error)
    next('/login')
  }
}

export const requireGuest = (to, from, next) => {
  const authStore = useAuthStore()

  try {
    if (authStore.isAuthenticated) {
      // Redirect to appropriate dashboard based on user type
      if (authStore.user?.user_type === 'tutor') {
        return next('/dashboard/tutor')
      } else if (authStore.user?.user_type === 'student') {
        return next('/dashboard/student')
      }
      return next('/dashboard')
    }

    next()
  } catch (error) {
    console.error('Guest guard error:', error)
    next()
  }
}

// Enhanced dashboard redirect logic
export const redirectToDashboard = (userType) => {
  switch (userType) {
    case 'tutor':
      return '/dashboard/tutor'
    case 'student':
      return '/dashboard/student'
    default:
      return '/login'
  }
}
