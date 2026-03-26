export default defineNuxtRouteMiddleware((to) => {
  const auth = useAuth()

  // Define if the destination route is an auth route (login/register)
  const isAuthRoute = to.path === '/login' || to.path === '/register'
  
  // You can set `auth: false` in definePageMeta inside the page to allow unauthenticated access
  const requiresAuth = to.meta.auth !== false

  // Not authenticated
  if (!auth.isAuthenticated.value) {
    // If not authenticated and trying to access a protected route, redirect to login
    if (requiresAuth && !isAuthRoute) {
      return navigateTo('/login')
    }
  } else {
    // If authenticated and trying to access login/register, redirect to home
    if (isAuthRoute) {
      return navigateTo('/')
    }
  }
})
