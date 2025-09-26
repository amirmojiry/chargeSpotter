<template>
  <div id="map" class="map"></div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue'
import L from 'leaflet'
import 'leaflet.heat'
import axios from 'axios'

const props = defineProps({
  bbox: {
    type: Array,
    required: true
  },
  weights: {
    type: Object,
    required: true
  },
  candidates: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['map-ready', 'candidate-click'])

let map = null
let heatLayer = null
let candidateMarkers = []

const initMap = () => {
  // Initialize map
  map = L.map('map').setView([63.1, 21.6], 12)

  // Add OpenStreetMap tiles
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
  }).addTo(map)

  // Set initial view to bbox
  const [minLng, minLat, maxLng, maxLat] = props.bbox
  map.fitBounds([[minLat, minLng], [maxLat, maxLng]])

  // Load initial heatmap data
  loadHeatmapData()

  // Listen for map move events to update heatmap
  map.on('moveend', debounce(loadHeatmapData, 500))

  emit('map-ready', map)
}

const loadHeatmapData = async () => {
  if (!map) return

  try {
    const bounds = map.getBounds()
    const bbox = [
      bounds.getWest(),
      bounds.getSouth(),
      bounds.getEast(),
      bounds.getNorth()
    ]

    const params = new URLSearchParams({
      bbox: bbox.join(','),
      w_pop: props.weights.population,
      w_poi: props.weights.poi,
      w_parking: props.weights.parking,
      w_traffic: props.weights.traffic
    })

    const response = await axios.get(`/api/grid-cells?${params}`)
    const cells = response.data.cells

    // Convert cells to heatmap format [lat, lng, intensity]
    const heatData = cells.map(cell => [
      cell.lat,
      cell.lng,
      cell.total_score
    ])

    // Remove existing heat layer
    if (heatLayer) {
      map.removeLayer(heatLayer)
    }

    // Add new heat layer
    if (heatData.length > 0) {
      heatLayer = L.heatLayer(heatData, {
        radius: 25,
        blur: 15,
        maxZoom: 17,
        max: 1.0,
        gradient: {
          0.0: 'blue',
          0.3: 'cyan',
          0.5: 'lime',
          0.7: 'yellow',
          1.0: 'red'
        }
      }).addTo(map)
    }
  } catch (error) {
    console.error('Error loading heatmap data:', error)
  }
}

// Debounce function to limit API calls
const debounce = (func, wait) => {
  let timeout
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout)
      func(...args)
    }
    clearTimeout(timeout)
    timeout = setTimeout(later, wait)
  }
}

// Add candidate markers to map
const addCandidateMarkers = () => {
  // Remove existing markers
  candidateMarkers.forEach(marker => map.removeLayer(marker))
  candidateMarkers = []

  // Add new markers
  props.candidates.forEach((candidate, index) => {
    // Add number label
    const numberLabel = L.divIcon({
      className: 'candidate-number-label',
      html: `<div style="
        background: #ff7800;
        color: white;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: bold;
        border: 2px solid white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.3);
      ">${index + 1}</div>`,
      iconSize: [24, 24],
      iconAnchor: [12, 12]
    })

    const numberMarker = L.marker([candidate.lat, candidate.lng], {
      icon: numberLabel
    }).addTo(map)

    // Add popup with candidate info
    numberMarker.bindPopup(`
      <div style="text-align: center;">
        <strong>#${index + 1} ${__('chargespotter.top_candidates')}</strong><br>
        ${__('chargespotter.score')}: ${candidate.total_score.toFixed(3)}<br>
        ${candidate.reason}
      </div>
    `)

    // Add click event
    numberMarker.on('click', () => {
      emit('candidate-click', candidate, index)
    })

    candidateMarkers.push(numberMarker)
  })
}

// Focus on a specific candidate
const focusOnCandidate = (candidate) => {
  if (map) {
    map.setView([candidate.lat, candidate.lng], 16)
  }
}

// Expose focusOnCandidate method
defineExpose({
  focusOnCandidate
})

// Watch for weight changes
watch(() => props.weights, () => {
  loadHeatmapData()
}, { deep: true })

// Watch for candidates changes
watch(() => props.candidates, () => {
  if (map) {
    addCandidateMarkers()
  }
}, { deep: true })

onMounted(() => {
  nextTick(() => {
    initMap()
  })
})
</script>

<style scoped>
.map {
  height: 100%;
  width: 100%;
}
</style>
