export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },
  css: ['~/assets/css/main.css'],
  ssr: false,
  modules: [
    '@nuxtjs/tailwindcss'
  ],
  components: [
    {
      path: '~/components/ui',
      prefix: 'Ui',
      pathPrefix: false,
      ignore: ['**/dropdown-menu/index.ts'],
    },
    {
      path: '~/components',
      pathPrefix: false,
    },
  ],
  vite: {
    server: {
      allowedHosts: [
        '9f9f-102-98-209-183.ngrok-free.app'
      ]
    }
  },
  
})