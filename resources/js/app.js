import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'

// Global translation helper function
window.__ = (key) => {
  const keys = key.split('.')
  let value = window.$page?.props?.translations
  
  for (const k of keys) {
    if (value && typeof value === 'object' && k in value) {
      value = value[k]
    } else {
      return key // Return the key if translation not found
    }
  }
  
  return value || key
}

createInertiaApp({
  resolve: name => import(`./Pages/${name}.vue`),
  setup({ el, App, props, plugin }) {
    // Make $page available globally for translations
    window.$page = props.initialPage
    
    const app = createApp({ render: () => h(App, props) })
      .use(plugin)
      .mount(el)
    
    // Update global $page when props change
    app.config.globalProperties.$page = props.initialPage
    
    return app
  },
})
