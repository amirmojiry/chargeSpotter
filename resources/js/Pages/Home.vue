<template>
  <div class="home-page">
    <header class="hero-header">
      <div class="hero-content">
        <h1>{{ __('chargespotter.title') }}</h1>
        <p class="hero-subtitle">{{ __('chargespotter.subtitle') }}</p>
        <p class="hero-description">
          Find the best locations for EV charging stations using data-driven analysis. 
          Our platform combines population density, points of interest, parking availability, 
          and traffic patterns to identify optimal charging station locations.
        </p>
        <div class="hero-actions">
          <a href="/admin/dashboard" class="btn btn-primary btn-large">
            <i class="icon-dashboard"></i>
            Go to Dashboard
          </a>
        </div>
      </div>
    </header>

    <main class="main-content">
      <section class="features-section">
        <div class="container">
          <h2>Key Features</h2>
          <div class="features-grid">
            <div class="feature-card">
              <div class="feature-icon">
                <i class="icon-map"></i>
              </div>
              <h3>Interactive Maps</h3>
              <p>Visualize charging station recommendations with interactive heatmaps and detailed location analysis.</p>
            </div>
            <div class="feature-card">
              <div class="feature-icon">
                <i class="icon-data"></i>
              </div>
              <h3>Data-Driven Analysis</h3>
              <p>Leverage multiple data sources including population density, POI locations, and parking availability.</p>
            </div>
            <div class="feature-card">
              <div class="feature-icon">
                <i class="icon-export"></i>
              </div>
              <h3>Export & Integration</h3>
              <p>Export results as CSV or GeoJSON for further analysis and integration with other systems.</p>
            </div>
            <div class="feature-card">
              <div class="feature-icon">
                <i class="icon-customize"></i>
              </div>
              <h3>Customizable Weights</h3>
              <p>Adjust scoring weights in real-time to prioritize different factors based on your specific needs.</p>
            </div>
          </div>
        </div>
      </section>

      <section class="how-it-works-section">
        <div class="container">
          <h2>How It Works</h2>
          <div class="steps">
            <div class="step">
              <div class="step-number">1</div>
              <div class="step-content">
                <h3>Define Regions</h3>
                <p>Create geographic regions and automatically generate grid cells for analysis.</p>
              </div>
            </div>
            <div class="step">
              <div class="step-number">2</div>
              <div class="step-content">
                <h3>Import Data</h3>
                <p>Add population data, points of interest, parking locations, and traffic information.</p>
              </div>
            </div>
            <div class="step">
              <div class="step-number">3</div>
              <div class="step-content">
                <h3>Analyze & Score</h3>
                <p>Our algorithm scores each grid cell based on weighted factors to identify optimal locations.</p>
              </div>
            </div>
            <div class="step">
              <div class="step-number">4</div>
              <div class="step-content">
                <h3>Visualize Results</h3>
                <p>View heatmaps and ranked recommendations with detailed explanations for each suggestion.</p>
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>

    <footer class="footer">
      <div class="footer-content">
        <div class="container">
          <div class="footer-info">
            <h3>{{ __('chargespotter.title') }}</h3>
            <p>Find the best locations for EV charging — fast, data-driven, demo-ready.</p>
          </div>
          <div class="footer-links">
            <a href="/admin/dashboard">Dashboard</a>
            <a href="/admin/regions">Regions</a>
            <a href="/admin/poi-locations">POI Locations</a>
            <a href="/admin/parking-locations">Parking Locations</a>
          </div>
        </div>
        <div class="footer-bottom">
          <p>&copy; 2024 ChargeSpotter - Built with Laravel, Vue.js, and Leaflet</p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
// No JavaScript needed for this static landing page
</script>

<style scoped>
.home-page {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.hero-header {
  background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
  color: white;
  padding: 4rem 2rem;
  text-align: center;
  position: relative;
  overflow: hidden;
}

.hero-header::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
  opacity: 0.3;
}

.hero-content {
  position: relative;
  z-index: 1;
  max-width: 800px;
  margin: 0 auto;
}

.hero-header h1 {
  font-size: 3.5rem;
  margin: 0 0 1rem 0;
  font-weight: 700;
  text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.hero-subtitle {
  font-size: 1.5rem;
  margin: 0 0 2rem 0;
  opacity: 0.9;
  font-weight: 300;
}

.hero-description {
  font-size: 1.1rem;
  line-height: 1.6;
  margin: 0 0 3rem 0;
  opacity: 0.8;
  max-width: 600px;
  margin-left: auto;
  margin-right: auto;
}

.hero-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
}

.btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 1rem 2rem;
  border: none;
  border-radius: 8px;
  text-decoration: none;
  font-size: 1.1rem;
  font-weight: 600;
  transition: all 0.3s ease;
  cursor: pointer;
}

.btn-large {
  padding: 1.25rem 2.5rem;
  font-size: 1.2rem;
}

.btn-primary {
  background: #007bff;
  color: white;
  box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
}

.btn-primary:hover {
  background: #0056b3;
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0, 123, 255, 0.4);
}

.btn-secondary {
  background: rgba(255, 255, 255, 0.1);
  color: white;
  border: 2px solid rgba(255, 255, 255, 0.3);
}

.btn-secondary:hover {
  background: rgba(255, 255, 255, 0.2);
  border-color: rgba(255, 255, 255, 0.5);
  transform: translateY(-2px);
}

.icon-dashboard::before { content: '📊'; }
.icon-regions::before { content: '🗺️'; }
.icon-map::before { content: '🗺️'; }
.icon-data::before { content: '📈'; }
.icon-export::before { content: '📤'; }
.icon-customize::before { content: '⚙️'; }

.main-content {
  flex: 1;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
}

.features-section {
  padding: 5rem 0;
  background: #f8f9fa;
}

.features-section h2 {
  text-align: center;
  font-size: 2.5rem;
  margin: 0 0 3rem 0;
  color: #2c3e50;
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 2rem;
}

.feature-card {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  text-align: center;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.feature-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.feature-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.feature-card h3 {
  font-size: 1.5rem;
  margin: 0 0 1rem 0;
  color: #2c3e50;
}

.feature-card p {
  color: #6c757d;
  line-height: 1.6;
  margin: 0;
}

.how-it-works-section {
  padding: 5rem 0;
  background: white;
}

.how-it-works-section h2 {
  text-align: center;
  font-size: 2.5rem;
  margin: 0 0 3rem 0;
  color: #2c3e50;
}

.steps {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
}

.step {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
}

.step-number {
  background: #007bff;
  color: white;
  width: 3rem;
  height: 3rem;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  font-weight: bold;
  flex-shrink: 0;
}

.step-content h3 {
  font-size: 1.3rem;
  margin: 0 0 0.5rem 0;
  color: #2c3e50;
}

.step-content p {
  color: #6c757d;
  line-height: 1.6;
  margin: 0;
}

.footer {
  background: #2c3e50;
  color: white;
  margin-top: auto;
}

.footer-content {
  padding: 3rem 0 1rem 0;
}

.footer-content .container {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 2rem;
  margin-bottom: 2rem;
}

.footer-info h3 {
  margin: 0 0 1rem 0;
  font-size: 1.5rem;
}

.footer-info p {
  margin: 0;
  opacity: 0.8;
  line-height: 1.6;
}

.footer-links {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.footer-links a {
  color: white;
  text-decoration: none;
  opacity: 0.8;
  transition: opacity 0.3s ease;
}

.footer-links a:hover {
  opacity: 1;
}

.footer-bottom {
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  padding: 1rem 0;
  text-align: center;
}

.footer-bottom p {
  margin: 0;
  opacity: 0.6;
  font-size: 0.9rem;
}

@media (max-width: 768px) {
  .hero-header {
    padding: 3rem 1rem;
  }
  
  .hero-header h1 {
    font-size: 2.5rem;
  }
  
  .hero-subtitle {
    font-size: 1.2rem;
  }
  
  .hero-description {
    font-size: 1rem;
  }
  
  .hero-actions {
    flex-direction: column;
    align-items: center;
  }
  
  .btn-large {
    width: 100%;
    max-width: 300px;
    justify-content: center;
  }
  
  .features-section,
  .how-it-works-section {
    padding: 3rem 0;
  }
  
  .features-section h2,
  .how-it-works-section h2 {
    font-size: 2rem;
  }
  
  .features-grid {
    grid-template-columns: 1fr;
  }
  
  .steps {
    grid-template-columns: 1fr;
  }
  
  .footer-content .container {
    flex-direction: column;
    text-align: center;
  }
  
  .footer-links {
    flex-direction: row;
    justify-content: center;
    flex-wrap: wrap;
  }
}
</style>
