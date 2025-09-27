<template>
  <div class="admin-layout">
    <header class="header">
      <div class="header-content">
        <div class="header-text">
          <h1>{{ __('chargespotter.title') }}</h1>
          <p>{{ __('chargespotter.subtitle') }}</p>
        </div>
        <nav class="header-nav">
          <a href="/" class="nav-link">{{ __('admin.dashboard') }}</a>
          <a href="/admin/regions" class="nav-link" :class="{ active: $page.url.startsWith('/admin/regions') }">
            {{ __('admin.regions') }}
          </a>
          <a href="/admin/poi-categories" class="nav-link" :class="{ active: $page.url.startsWith('/admin/poi-categories') }">
            {{ __('admin.poi_categories') }}
          </a>
          <a href="/admin/poi-locations" class="nav-link" :class="{ active: $page.url.startsWith('/admin/poi-locations') }">
            {{ __('admin.poi_locations') }}
          </a>
          <a href="/admin/parking-locations" class="nav-link" :class="{ active: $page.url.startsWith('/admin/parking-locations') }">
            {{ __('admin.parking_locations') }}
          </a>
          <a href="/admin/population-cells" class="nav-link" :class="{ active: $page.url.startsWith('/admin/population-cells') }">
            {{ __('admin.population_cells') }}
          </a>
        </nav>
      </div>
    </header>

    <main class="main-content">
      <slot />
    </main>

    <footer class="footer">
      <div class="footer-content">
        <p>&copy; 2024 ChargeSpotter - Find the best locations for EV charging</p>
        <p>Built with Laravel, Vue.js, and Leaflet</p>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3'

const $page = usePage()
</script>

<style scoped>
.admin-layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.header {
  background: #2c3e50;
  color: white;
  padding: 1rem;
}

.header-content {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.header-text {
  text-align: center;
  flex: 1;
}

.header h1 {
  margin: 0;
  font-size: 2rem;
}

.header p {
  margin: 0.5rem 0 0 0;
  opacity: 0.8;
}

.header-nav {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.nav-link {
  color: white;
  text-decoration: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  background: rgba(255,255,255,0.1);
  transition: background 0.2s;
  font-size: 0.9rem;
}

.nav-link:hover,
.nav-link.active {
  background: rgba(255,255,255,0.2);
}

.main-content {
  flex: 1;
  min-height: 0;
}

.footer {
  background: #2c3e50;
  color: white;
  padding: 1rem;
  text-align: center;
  margin-top: auto;
}

.footer-content p {
  margin: 0.25rem 0;
  font-size: 0.9rem;
  opacity: 0.8;
}

@media (max-width: 768px) {
  .header-content {
    flex-direction: column;
    text-align: center;
  }
  
  .header h1 {
    font-size: 1.5rem;
  }

  .header p {
    font-size: 0.9rem;
  }
  
  .header-nav {
    justify-content: center;
  }
  
  .nav-link {
    font-size: 0.8rem;
    padding: 0.4rem 0.8rem;
  }
  
  .footer {
    padding: 0.75rem;
  }

  .footer-content p {
    font-size: 0.8rem;
  }
}
</style>
