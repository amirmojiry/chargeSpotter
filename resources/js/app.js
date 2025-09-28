import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3' 

// Global translation helper function
const translate = (key, parameters = {}) => {
  const keys = key.split('.')
  let value = window.$page?.props?.translations
  
  for (const k of keys) {
    if (value && typeof value === 'object' && k in value) {
      value = value[k]
    } else {
      return key // Return the key if translation not found
    }
  }
  
  let translation = value || key
  
  // Replace parameters in the translation
  if (typeof translation === 'string' && Object.keys(parameters).length > 0) {
    Object.keys(parameters).forEach(param => {
      translation = translation.replace(new RegExp(`:${param}`, 'g'), parameters[param])
    })
  }
  
  return translation
}

// Make it available globally
window.__ = translate

createInertiaApp({
  resolve: name => {
    const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
    return pages[`./Pages/${name}.vue`]
  },
  setup({ el, App, props, plugin }) {
    // Make $page available globally for translations
    window.$page = props.initialPage
    
    const app = createApp({ render: () => h(App, props) })
      .use(plugin)
    
    // Make route helper available globally
    if (window.Ziggy) {
      app.config.globalProperties.route = window.Ziggy.route
      window.route = window.Ziggy.route
    }
    
    // Make __ function available in Vue templates
    app.config.globalProperties.__ = translate
    
    app.mount(el)
    
    return app
  },
})
