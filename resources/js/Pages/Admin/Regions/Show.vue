<template>
  <AdminLayout>
    <div class="region-show">
      <div class="page-header">
        <div class="header-left">
          <button @click="goBack" class="btn btn-secondary">
            ← Back to Regions
          </button>
        </div>
        <div class="header-center">
          <h1>{{ region.name }}</h1>
          <p v-if="region.description" class="region-description">{{ region.description }}</p>
        </div>
        <div class="header-right">
          <!-- DISABLED: Edit button temporarily disabled -->
          <button @click="editRegion" class="btn btn-primary" disabled title="Functionality is implemented but disabled for demo to avoid data issues">
            Edit Region
          </button>
        </div>
      </div>

      <div class="region-info">
        <div class="info-cards">
          <div class="info-card">
            <h3>Region Details</h3>
            <div class="info-item">
              <label>Center Coordinates:</label>
              <span>{{ region.center_lat.toFixed(6) }}, {{ region.center_lng.toFixed(6) }}</span>
            </div>
            <div class="info-item">
              <label>Grid Cells:</label>
              <span>{{ region.grid_cells_count || region.grid_cells?.length || 0 }}</span>
            </div>
            <div class="info-item">
              <label>Created:</label>
              <span>{{ formatDate(region.created_at) }}</span>
            </div>
            <div class="info-item">
              <label>Updated:</label>
              <span>{{ formatDate(region.updated_at) }}</span>
            </div>
          </div>

          <div class="info-card">
            <h3>Area Geometry</h3>
            <div class="area-preview">
              <div v-if="region.area_json && region.area_json.length > 0" class="coordinates-list">
                <div v-for="(point, index) in region.area_json" :key="index" class="coordinate">
                  Point {{ index + 1 }}: {{ point.lat.toFixed(6) }}, {{ point.lng.toFixed(6) }}
                </div>
              </div>
              <div v-else class="no-data">
                No area geometry defined
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="map-section">
        <div class="section-header">
          <h2>Region Map</h2>
          <div class="map-controls">
            <button @click="toggleGridCellsOnMap" class="btn btn-sm" :class="{ 'btn-primary': showGridCellsOnMap, 'btn-secondary': !showGridCellsOnMap }">
              {{ showGridCellsOnMap ? 'Hide' : 'Show' }} Grid Cells
            </button>
            <button @click="fitToBounds" class="btn btn-sm btn-info">
              Fit to Region
            </button>
          </div>
        </div>
        <div class="map-container">
          <div id="region-map" class="map"></div>
        </div>
      </div>

      <div class="grid-cells-section">
        <div class="section-header">
          <h2>Grid Cells ({{ region.grid_cells?.length || 0 }})</h2>
          <div class="section-actions">
            <button @click="toggleGridCellsView" class="btn btn-sm">
              {{ showGridCells ? 'Hide' : 'Show' }} Grid Cells
            </button>
          </div>
        </div>

        <div v-if="showGridCells && region.grid_cells" class="grid-cells-container">
          <div class="grid-cells-stats">
            <div class="stat-item">
              <label>Average Population Score:</label>
              <span>{{ averageScore('population_z') }}</span>
            </div>
            <div class="stat-item">
              <label>Average POI Score:</label>
              <span>{{ averageScore('poi_z') }}</span>
            </div>
            <div class="stat-item">
              <label>Average Parking Score:</label>
              <span>{{ averageScore('parking_z') }}</span>
            </div>
            <div class="stat-item">
              <label>Average Traffic Score:</label>
              <span>{{ averageScore('traffic_z') }}</span>
            </div>
          </div>

          <div class="grid-cells-table">
            <table class="data-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Coordinates</th>
                  <th>Population</th>
                  <th>POI</th>
                  <th>Parking</th>
                  <th>Traffic</th>
                  <th>Total Score</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="cell in region.grid_cells" :key="cell.id">
                  <td>{{ cell.id }}</td>
                  <td>{{ cell.lat.toFixed(4) }}, {{ cell.lng.toFixed(4) }}</td>
                  <td>{{ cell.population_z?.toFixed(3) || '0.000' }}</td>
                  <td>{{ cell.poi_z?.toFixed(3) || '0.000' }}</td>
                  <td>{{ cell.parking_z?.toFixed(3) || '0.000' }}</td>
                  <td>{{ cell.traffic_z?.toFixed(3) || '0.000' }}</td>
                  <td>{{ cell.total_score_cached?.toFixed(3) || '0.000' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '../../../Components/Layout/AdminLayout.vue'

const props = defineProps({
  region: Object
})

const showGridCells = ref(false)
const showGridCellsOnMap = ref(true)
let map = null
let regionPolygon = null
let gridCellLayers = []

const goBack = () => {
  router.visit(route('admin.regions.index'))
}

const editRegion = () => {
  router.visit(route('admin.regions.edit', props.region.id))
}

const toggleGridCellsView = () => {
  showGridCells.value = !showGridCells.value
}

const toggleGridCellsOnMap = () => {
  showGridCellsOnMap.value = !showGridCellsOnMap.value
  updateGridCellsOnMap()
}

const fitToBounds = () => {
  if (map && regionPolygon) {
    map.fitBounds(regionPolygon.getBounds(), { padding: [20, 20] })
  }
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const averageScore = (field) => {
  if (!props.region.grid_cells || props.region.grid_cells.length === 0) {
    return '0.000'
  }
  
  const sum = props.region.grid_cells.reduce((acc, cell) => {
    return acc + (cell[field] || 0)
  }, 0)
  
  return (sum / props.region.grid_cells.length).toFixed(3)
}

const initializeMap = async () => {
  // Import Leaflet dynamically
  const L = await import('leaflet')
  
  // Initialize map
  map = L.map('region-map').setView([props.region.center_lat, props.region.center_lng], 13)
  
  // Add tile layer
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
  }).addTo(map)
  
  // Add region polygon
  if (props.region.area_json && props.region.area_json.length > 0) {
    const coordinates = props.region.area_json.map(point => [point.lat, point.lng])
    regionPolygon = L.polygon(coordinates, {
      color: '#007bff',
      weight: 2,
      fillColor: '#007bff',
      fillOpacity: 0.1
    }).addTo(map)
    
    // Add center marker
    L.marker([props.region.center_lat, props.region.center_lng])
      .addTo(map)
      .bindPopup(`<b>${props.region.name}</b><br>Center Point`)
    
    // Fit map to region bounds
    map.fitBounds(regionPolygon.getBounds(), { padding: [20, 20] })
  }
  
  // Add grid cells
  updateGridCellsOnMap()
}

const updateGridCellsOnMap = () => {
  if (!map || !props.region.grid_cells) return
  
  // Clear existing grid cell layers
  gridCellLayers.forEach(layer => map.removeLayer(layer))
  gridCellLayers = []
  
  if (!showGridCellsOnMap.value) return
  
  // Import Leaflet
  import('leaflet').then(L => {
    props.region.grid_cells.forEach(cell => {
      if (cell.bbox_json && cell.bbox_json.length >= 4) {
        const bbox = cell.bbox_json
        const bounds = [[bbox[1], bbox[0]], [bbox[3], bbox[2]]] // [minLat, minLng], [maxLat, maxLng]
        
        // Color based on total score
        const score = cell.total_score_cached || 0
        const color = score > 0.7 ? '#28a745' : score > 0.4 ? '#ffc107' : '#dc3545'
        
        const rectangle = L.rectangle(bounds, {
          color: color,
          weight: 1,
          fillColor: color,
          fillOpacity: 0.3
        }).addTo(map)
        
        // Add popup with cell info
        rectangle.bindPopup(`
          <b>Grid Cell ${cell.id}</b><br>
          Coordinates: ${cell.lat.toFixed(4)}, ${cell.lng.toFixed(4)}<br>
          Population: ${(cell.population_z || 0).toFixed(3)}<br>
          POI: ${(cell.poi_z || 0).toFixed(3)}<br>
          Parking: ${(cell.parking_z || 0).toFixed(3)}<br>
          Traffic: ${(cell.traffic_z || 0).toFixed(3)}<br>
          <b>Total Score: ${(cell.total_score_cached || 0).toFixed(3)}</b>
        `)
        
        gridCellLayers.push(rectangle)
      }
    })
  })
}

onMounted(async () => {
  await nextTick()
  await initializeMap()
})
</script>

<style scoped>
.region-show {
  padding: 2rem;
  max-width: 1400px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #e9ecef;
}

.header-left,
.header-right {
  flex: 0 0 auto;
}

.header-center {
  flex: 1;
  text-align: center;
}

.header-center h1 {
  margin: 0;
  color: #2c3e50;
  font-size: 2rem;
}

.region-description {
  margin: 0.5rem 0 0 0;
  color: #6c757d;
  font-style: italic;
}

.region-info {
  margin-bottom: 2rem;
}

.info-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1.5rem;
}

.info-card {
  background: white;
  border-radius: 8px;
  padding: 1.5rem;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.info-card h3 {
  margin: 0 0 1rem 0;
  color: #2c3e50;
  font-size: 1.2rem;
}

.info-item {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.75rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid #f8f9fa;
}

.info-item:last-child {
  margin-bottom: 0;
  border-bottom: none;
}

.info-item label {
  font-weight: 500;
  color: #495057;
}

.info-item span {
  color: #6c757d;
}

.area-preview {
  max-height: 200px;
  overflow-y: auto;
}

.coordinates-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.coordinate {
  font-family: monospace;
  font-size: 0.9rem;
  color: #495057;
  padding: 0.25rem 0.5rem;
  background: #f8f9fa;
  border-radius: 4px;
}

.no-data {
  color: #6c757d;
  font-style: italic;
  text-align: center;
  padding: 1rem;
}

.grid-cells-section {
  background: white;
  border-radius: 8px;
  padding: 1.5rem;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  margin-top: 2rem;
}

.map-section {
  background: white;
  border-radius: 8px;
  padding: 1.5rem;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  margin-top: 2rem;
}

.map-controls {
  display: flex;
  gap: 0.5rem;
}

.map-container {
  height: 500px;
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid #e9ecef;
}

.map {
  height: 100%;
  width: 100%;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #e9ecef;
}

.section-header h2 {
  margin: 0;
  color: #2c3e50;
}

.grid-cells-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 6px;
}

.stat-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.stat-item label {
  font-weight: 500;
  color: #495057;
}

.stat-item span {
  font-family: monospace;
  color: #007bff;
  font-weight: 600;
}

.grid-cells-table {
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.9rem;
}

.data-table th,
.data-table td {
  padding: 0.75rem;
  text-align: left;
  border-bottom: 1px solid #e9ecef;
}

.data-table th {
  background: #f8f9fa;
  font-weight: 600;
  color: #495057;
}

.data-table td {
  color: #6c757d;
}

.btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 1rem;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.btn-primary {
  background: #007bff;
  color: white;
}

.btn-primary:hover {
  background: #0056b3;
}

.btn-secondary {
  background: #6c757d;
  color: white;
}

.btn-secondary:hover {
  background: #545b62;
}

.btn-info {
  background: #17a2b8;
  color: white;
}

.btn-info:hover {
  background: #138496;
}

@media (max-width: 768px) {
  .region-show {
    padding: 1rem;
  }
  
  .page-header {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }
  
  .header-center {
    order: -1;
  }
  
  .info-cards {
    grid-template-columns: 1fr;
  }
  
  .grid-cells-stats {
    grid-template-columns: 1fr;
  }
  
  .data-table {
    font-size: 0.8rem;
  }
  
  .data-table th,
  .data-table td {
    padding: 0.5rem;
  }
}
</style>
