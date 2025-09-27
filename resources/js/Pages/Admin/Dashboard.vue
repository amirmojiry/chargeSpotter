<template>
  <AdminLayout>
    <div class="dashboard-content">
      <div class="controls">
        <div class="weight-controls">
          <h3>{{ __('chargespotter.weight_controls') }}</h3>
          <div class="sliders">
            <div class="slider-group">
              <label>{{ __('chargespotter.population') }}: {{ weights.population.toFixed(2) }}</label>
              <input 
                type="range" 
                min="0" 
                max="1" 
                step="0.01" 
                v-model.number="weights.population"
                @input="updateMap"
              />
            </div>
            <div class="slider-group">
              <label>{{ __('chargespotter.poi') }}: {{ weights.poi.toFixed(2) }}</label>
              <input 
                type="range" 
                min="0" 
                max="1" 
                step="0.01" 
                v-model.number="weights.poi"
                @input="updateMap"
              />
            </div>
            <div class="slider-group">
              <label>{{ __('chargespotter.parking') }}: {{ weights.parking.toFixed(2) }}</label>
              <input 
                type="range" 
                min="0" 
                max="1" 
                step="0.01" 
                v-model.number="weights.parking"
                @input="updateMap"
              />
            </div>
            <div class="slider-group">
              <label>{{ __('chargespotter.traffic') }}: {{ weights.traffic.toFixed(2) }}</label>
              <input 
                type="range" 
                min="0" 
                max="1" 
                step="0.01" 
                v-model.number="weights.traffic"
                @input="updateMap"
              />
            </div>
          </div>
          <button @click="normalizeWeights" class="normalize-btn">{{ __('chargespotter.normalize_weights') }}</button>
        </div>

        <div class="export-controls">
          <h3>{{ __('chargespotter.export') }}</h3>
          <button @click="exportCsv" class="export-btn">{{ __('chargespotter.export_csv') }}</button>
          <button @click="exportGeoJson" class="export-btn">{{ __('chargespotter.export_geojson') }}</button>
        </div>
      </div>

      <div class="main-content">
        <div class="map-container">
          <MapHeatmap 
            ref="mapRef"
            :bbox="bbox" 
            :weights="weights"
            :candidates="candidates"
            @map-ready="onMapReady"
            @candidate-click="onCandidateClick"
          />
        </div>

        <div class="candidates-panel">
          <h3>{{ __('chargespotter.top_candidates') }}</h3>
          <div v-if="loading" class="loading">{{ __('chargespotter.loading') }}</div>
          <div v-else class="candidates-list">
            <div 
              v-for="(candidate, index) in candidates" 
              :key="index"
              class="candidate-item"
              @click="focusOnCandidate(candidate, index)"
            >
              <div class="candidate-rank">#{{ index + 1 }}</div>
              <div class="candidate-info">
                <div class="candidate-score">{{ __('chargespotter.score') }}: {{ candidate.total_score.toFixed(3) }}</div>
                <div class="candidate-location">
                  {{ candidate.lat.toFixed(4) }}, {{ candidate.lng.toFixed(4) }}
                </div>
                <div class="candidate-reason">{{ candidate.reason }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import MapHeatmap from '../../Components/MapHeatmap.vue'
import AdminLayout from '../../Components/Layout/AdminLayout.vue'

const props = defineProps({
  defaultWeights: Object,
  defaultBbox: Array
})

const weights = reactive({ ...props.defaultWeights })
const bbox = ref([...props.defaultBbox])
const candidates = ref([])
const loading = ref(false)
const mapRef = ref(null)

const updateMap = () => {
  // Map will automatically update when weights change
  loadCandidates()
}

const normalizeWeights = () => {
  const total = weights.population + weights.poi + weights.parking + weights.traffic
  if (total > 0) {
    weights.population /= total
    weights.poi /= total
    weights.parking /= total
    weights.traffic /= total
  }
  updateMap()
}

const loadCandidates = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams({
      limit: '10',
      w_pop: weights.population,
      w_poi: weights.poi,
      w_parking: weights.parking,
      w_traffic: weights.traffic
    })
    
    const response = await axios.get(`/api/candidates?${params}`)
    candidates.value = response.data.candidates
  } catch (error) {
    console.error('Error loading candidates:', error)
  } finally {
    loading.value = false
  }
}

const exportCsv = () => {
  const params = new URLSearchParams({
    w_pop: weights.population,
    w_poi: weights.poi,
    w_parking: weights.parking,
    w_traffic: weights.traffic
  })
  
  window.open(`/api/export/csv?${params}`, '_blank')
}

const exportGeoJson = () => {
  const params = new URLSearchParams({
    w_pop: weights.population,
    w_poi: weights.poi,
    w_parking: weights.parking,
    w_traffic: weights.traffic
  })
  
  window.open(`/api/export/geojson?${params}`, '_blank')
}

const onMapReady = (map) => {
  mapRef.value = map
  loadCandidates()
}

const onCandidateClick = (candidate, index) => {
  // Optional: highlight the clicked candidate in the list
  console.log('Candidate clicked:', candidate, index)
}

const focusOnCandidate = (candidate, index) => {
  if (mapRef.value && mapRef.value.focusOnCandidate) {
    mapRef.value.focusOnCandidate(candidate)
  }
}

onMounted(() => {
  loadCandidates()
})
</script>

<style scoped>
.dashboard-content {
  padding: 2rem;
  max-width: 1400px;
  margin: 0 auto;
}

.controls {
  background: #f8f9fa;
  padding: 1rem;
  display: flex;
  gap: 2rem;
  border-bottom: 1px solid #dee2e6;
  flex-wrap: wrap;
  border-radius: 8px;
  margin-bottom: 2rem;
}

@media (max-width: 768px) {
  .controls {
    flex-direction: column;
    gap: 1rem;
    padding: 0.75rem;
  }
}

.weight-controls, .export-controls {
  flex: 1;
}

.weight-controls h3, .export-controls h3 {
  margin: 0 0 1rem 0;
  font-size: 1.1rem;
}

.sliders {
  display: flex;
  gap: 1rem;
  margin-bottom: 1rem;
  flex-wrap: wrap;
}

@media (max-width: 768px) {
  .sliders {
    flex-direction: column;
    gap: 0.75rem;
  }
}

.slider-group {
  flex: 1;
}

.slider-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-size: 0.9rem;
  font-weight: 500;
}

.slider-group input[type="range"] {
  width: 100%;
  height: 6px;
  border-radius: 3px;
  background: #ddd;
  outline: none;
  -webkit-appearance: none;
}

.slider-group input[type="range"]::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  background: #007bff;
  cursor: pointer;
}

.normalize-btn, .export-btn {
  background: #007bff;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  margin-right: 0.5rem;
  font-size: 0.9rem;
}

.normalize-btn:hover, .export-btn:hover {
  background: #0056b3;
}

.main-content {
  display: flex;
  min-height: 0;
  gap: 1rem;
}

@media (max-width: 768px) {
  .main-content {
    flex-direction: column;
  }
}

.map-container {
  flex: 1;
  height: 50vh;
  overflow-y: auto;
  min-height: 300px;
  max-height: 500px;
  border-radius: 8px;
  overflow: hidden;
}

@media (max-width: 768px) {
  .map-container {
    min-height: 300px;
    max-height: 40vh;
  }
}

.candidates-panel {
  flex: 1;
  background: white;
  border: 1px solid #dee2e6;
  padding: 1rem;
  overflow-y: auto;
  height: 50vh;
  min-height: 300px;
  max-height: 500px;
  border-radius: 8px;
}

@media (max-width: 768px) {
  .candidates-panel {
    min-height: 300px;
    max-height: 40vh;
  }
}

.candidates-panel h3 {
  margin: 0 0 1rem 0;
  font-size: 1.1rem;
}

.loading {
  text-align: center;
  color: #6c757d;
  padding: 2rem;
}

.candidates-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.candidate-item {
  display: flex;
  gap: 1rem;
  padding: 0.75rem;
  background: #f8f9fa;
  border-radius: 4px;
  border-left: 4px solid #007bff;
  cursor: pointer;
  transition: all 0.2s ease;
}

.candidate-item:hover {
  background: #e9ecef;
  transform: translateX(2px);
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.candidate-rank {
  font-weight: bold;
  color: #007bff;
  min-width: 2rem;
}

.candidate-info {
  flex: 1;
}

.candidate-score {
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.candidate-location {
  font-size: 0.85rem;
  color: #6c757d;
  margin-bottom: 0.25rem;
}

.candidate-reason {
  font-size: 0.8rem;
  color: #495057;
  font-style: italic;
}
</style>