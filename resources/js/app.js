import { createInertiaApp } from '@inertiajs/vue3'
import { createHead } from '@unhead/vue'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { CapoPlugin } from 'unhead'
import { createApp, h } from 'vue'
import { ZiggyVue } from 'ziggy-js'
import './bootstrap'
import '../css/app.css'
import { Toaster } from 'vue-sonner'

/**
 * This is used from unhead plugin to use seo meta tags
 * @see {@link https://unhead.unjs.io/setup/unhead/introduction} For createHead instance
 * @see {@link https://unhead.unjs.io/plugins/plugins/capo} For CapoPlugin
 */
const head = createHead({
  plugins: [
    CapoPlugin(),
  ],
})

const appName = import.meta.env.VITE_APP_NAME || 'Laravel'

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: name => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
  setup({ el, App, props, plugin }) {
    const app = createApp({ render: () => h(App, props) })
    
    // Register question components globally
    const questionComponents = import.meta.glob('./Components/Questions/*Question.vue', { eager: true })
    Object.entries(questionComponents).forEach(([path, module]) => {
      const componentName = path.split('/').pop().replace('.vue', '').toLowerCase()
      app.component(componentName, module.default)
    })
    
    app.use(plugin)
      .use(ZiggyVue)
      .use(head)
      .component('Toaster', Toaster)
      .mount(el)
  },
  progress: {
    color: '#4B5563',
  },
})

window.formatDate = (dateString) => {
    const options = { year: 'numeric', month: 'short', day: 'numeric' }
    return new Date(dateString).toLocaleDateString(undefined, options)
}
