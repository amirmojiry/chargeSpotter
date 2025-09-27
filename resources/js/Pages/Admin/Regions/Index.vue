<template>
  <AdminLayout>
    <div class="admin-page">
    <div class="page-header">
      <h1>{{ __('admin.regions') }}</h1>
      <button @click="showCreateModal = true" class="btn btn-primary">
        Add Region
      </button>
    </div>

    <!-- Filters -->
    <div class="filters">
      <div class="search-box">
        <input
          type="text"
          v-model="filters.search"
          @input="debouncedSearch"
          placeholder="Search regions..."
          class="form-input"
        />
      </div>
      <div class="filter-controls">
        <select v-model="filters.sort_by" @change="applyFilters" class="form-select">
          <option value="name">{{ __('admin.name') }}</option>
          <option value="created_at">{{ __('admin.created_at') }}</option>
        </select>
        <select v-model="filters.sort_order" @change="applyFilters" class="form-select">
          <option value="asc">{{ __('admin.ascending') }}</option>
          <option value="desc">{{ __('admin.descending') }}</option>
        </select>
      </div>
    </div>

    <!-- Bulk Actions -->
    <div v-if="selectedItems.length > 0" class="bulk-actions">
      <span>{{ __('admin.selected_items', { count: selectedItems.length }) }}</span>
      <button @click="bulkDelete" class="btn btn-danger btn-sm">
        {{ __('admin.delete_selected') }}
      </button>
    </div>

    <!-- Regions Table -->
    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>
              <input 
                type="checkbox" 
                @change="toggleAll" 
                :checked="selectedItems.length === regions.data.length && regions.data.length > 0"
                class="checkbox"
              />
            </th>
            <th class="sortable" @click="sortBy('name')">
              {{ __('admin.name') }}
              <span v-if="filters.sort_by === 'name'" class="sort-indicator">
                {{ filters.sort_order === 'asc' ? '↑' : '↓' }}
              </span>
            </th>
            <th>{{ __('admin.description') }}</th>
            <th>Grid Cells</th>
            <th class="sortable" @click="sortBy('center_lat')">
              Center Lat
              <span v-if="filters.sort_by === 'center_lat'" class="sort-indicator">
                {{ filters.sort_order === 'asc' ? '↑' : '↓' }}
              </span>
            </th>
            <th class="sortable" @click="sortBy('center_lng')">
              Center Lng
              <span v-if="filters.sort_by === 'center_lng'" class="sort-indicator">
                {{ filters.sort_order === 'asc' ? '↑' : '↓' }}
              </span>
            </th>
            <th>{{ __('admin.actions') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="region in regions.data" :key="region.id">
            <td>
              <input 
                type="checkbox" 
                :value="region.id" 
                v-model="selectedItems"
                class="checkbox"
              />
            </td>
            <td>{{ region.name }}</td>
            <td>
              <span v-if="region.description" class="description-text">
                {{ region.description.length > 50 ? region.description.substring(0, 50) + '...' : region.description }}
              </span>
              <span v-else class="text-muted">-</span>
            </td>
            <td>
              <span class="grid-cells-badge">
                {{ region.grid_cells_count }}
              </span>
            </td>
            <td>{{ region.center_lat.toFixed(6) }}</td>
            <td>{{ region.center_lng.toFixed(6) }}</td>
            <td>
              <button @click="viewRegion(region)" class="btn btn-sm btn-info">
                View
              </button>
              <button @click="editRegion(region)" class="btn btn-sm btn-secondary">
                {{ __('admin.edit') }}
              </button>
              <button @click="deleteRegion(region)" class="btn btn-sm btn-danger">
                {{ __('admin.delete') }}
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="pagination" v-if="regions.last_page > 1">
      <button 
        @click="changePage(regions.current_page - 1)"
        :disabled="regions.current_page <= 1"
        class="btn btn-sm"
      >
        {{ __('admin.previous') }}
      </button>
      <span class="page-info">
        {{ __('admin.page_of', { current: regions.current_page, total: regions.last_page }) }}
      </span>
      <button 
        @click="changePage(regions.current_page + 1)"
        :disabled="regions.current_page >= regions.last_page"
        class="btn btn-sm"
      >
        {{ __('admin.next') }}
      </button>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showCreateModal || showEditModal" class="modal-overlay" @click="closeModal">
      <div class="modal" @click.stop>
        <div class="modal-header">
          <h3>{{ showCreateModal ? 'Add Region' : 'Edit Region' }}</h3>
          <button @click="closeModal" class="btn-close">&times;</button>
        </div>
        <form @submit.prevent="saveRegion" class="modal-body">
          <div class="form-group">
            <label>{{ __('admin.name') }} *</label>
            <input 
              type="text" 
              v-model="form.name" 
              required 
              class="form-input"
              :class="{ 'error': errors.name }"
            />
            <div v-if="errors.name" class="error-message">{{ errors.name }}</div>
          </div>
          <div class="form-group">
            <label>{{ __('admin.description') }}</label>
            <textarea 
              v-model="form.description" 
              class="form-input"
              rows="3"
              :class="{ 'error': errors.description }"
            ></textarea>
            <div v-if="errors.description" class="error-message">{{ errors.description }}</div>
          </div>
          <div class="form-group">
            <label>Center Latitude *</label>
            <input 
              type="number" 
              v-model.number="form.center_lat" 
              step="0.000001"
              min="-90"
              max="90"
              required 
              class="form-input"
              :class="{ 'error': errors.center_lat }"
            />
            <div v-if="errors.center_lat" class="error-message">{{ errors.center_lat }}</div>
          </div>
          <div class="form-group">
            <label>Center Longitude *</label>
            <input 
              type="number" 
              v-model.number="form.center_lng" 
              step="0.000001"
              min="-180"
              max="180"
              required 
              class="form-input"
              :class="{ 'error': errors.center_lng }"
            />
            <div v-if="errors.center_lng" class="error-message">{{ errors.center_lng }}</div>
          </div>
          <div class="form-group">
            <label>Area JSON *</label>
            <textarea 
              v-model="areaJsonText" 
              required 
              class="form-input"
              rows="5"
              :class="{ 'error': errors.area_json }"
              placeholder='[{"lat": 63.1, "lng": 21.6}, {"lat": 63.1, "lng": 21.7}, ...]'
            ></textarea>
            <div v-if="errors.area_json" class="error-message">{{ errors.area_json }}</div>
          </div>
          <div class="modal-footer">
            <button type="button" @click="closeModal" class="btn btn-secondary">
              {{ __('admin.cancel') }}
            </button>
            <button type="submit" class="btn btn-primary" :disabled="loading">
              {{ loading ? __('admin.saving') : __('admin.save') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { debounce } from 'lodash-es'
import AdminLayout from '../../../Components/Layout/AdminLayout.vue'

const props = defineProps({
  regions: Object,
  filters: Object
})

const showCreateModal = ref(false)
const showEditModal = ref(false)
const loading = ref(false)
const errors = ref({})
const selectedItems = ref([])

const filters = reactive({
  search: props.filters.search || '',
  sort_by: props.filters.sort_by || 'name',
  sort_order: props.filters.sort_order || 'asc'
})

const form = reactive({
  id: null,
  name: '',
  description: '',
  center_lat: null,
  center_lng: null,
  area_json: []
})

const areaJsonText = computed({
  get: () => JSON.stringify(form.area_json, null, 2),
  set: (value) => {
    try {
      form.area_json = JSON.parse(value)
    } catch (e) {
      // Keep the text value if JSON is invalid
    }
  }
})

const debouncedSearch = debounce(() => {
  applyFilters()
}, 300)

const applyFilters = () => {
  router.get(route('admin.regions.index'), filters, {
    preserveState: true,
    replace: true
  })
}

const changePage = (page) => {
  router.get(route('admin.regions.index'), { ...filters, page }, {
    preserveState: true
  })
}

const sortBy = (column) => {
  if (filters.sort_by === column) {
    filters.sort_order = filters.sort_order === 'asc' ? 'desc' : 'asc'
  } else {
    filters.sort_by = column
    filters.sort_order = 'asc'
  }
  applyFilters()
}

const toggleAll = () => {
  if (selectedItems.value.length === props.regions.data.length) {
    selectedItems.value = []
  } else {
    selectedItems.value = props.regions.data.map(region => region.id)
  }
}

const viewRegion = (region) => {
  router.visit(route('admin.regions.show', region.id))
}

const editRegion = (region) => {
  form.id = region.id
  form.name = region.name
  form.description = region.description || ''
  form.center_lat = region.center_lat
  form.center_lng = region.center_lng
  form.area_json = region.area_json || []
  showEditModal.value = true
}

const deleteRegion = (region) => {
  if (confirm(`Are you sure you want to delete the region "${region.name}"?`)) {
    router.delete(route('admin.regions.destroy', region.id), {
      onSuccess: () => {
        // Show success message
      }
    })
  }
}

const bulkDelete = () => {
  if (confirm(`Are you sure you want to delete ${selectedItems.value.length} regions?`)) {
    router.delete(route('admin.regions.bulk-destroy'), {
      data: { ids: selectedItems.value },
      onSuccess: () => {
        selectedItems.value = []
      }
    })
  }
}

const saveRegion = () => {
  loading.value = true
  errors.value = {}

  // Validate area_json
  try {
    JSON.parse(areaJsonText.value)
  } catch (e) {
    errors.value.area_json = 'Invalid JSON format'
    loading.value = false
    return
  }

  const url = form.id 
    ? route('admin.regions.update', form.id)
    : route('admin.regions.store')

  const method = form.id ? 'put' : 'post'

  router[method](url, form, {
    onSuccess: () => {
      closeModal()
      loading.value = false
    },
    onError: (errs) => {
      errors.value = errs
      loading.value = false
    }
  })
}

const closeModal = () => {
  showCreateModal.value = false
  showEditModal.value = false
  form.id = null
  form.name = ''
  form.description = ''
  form.center_lat = null
  form.center_lng = null
  form.area_json = []
  errors.value = {}
}

// Watch for filter changes
watch(filters, () => {
  applyFilters()
}, { deep: true })
</script>

<style scoped>
.admin-page {
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.page-header h1 {
  margin: 0;
  color: #2c3e50;
}

.filters {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
  align-items: center;
  flex-wrap: wrap;
}

.search-box {
  flex: 1;
  min-width: 200px;
}

.filter-controls {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
  align-items: center;
}

.bulk-actions {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: #e3f2fd;
  border-radius: 4px;
  margin-bottom: 1rem;
}

.table-container {
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th,
.data-table td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid #e9ecef;
}

.data-table th {
  background: #f8f9fa;
  font-weight: 600;
  color: #495057;
}

.sortable {
  cursor: pointer;
  user-select: none;
  position: relative;
}

.sortable:hover {
  background: #e9ecef;
}

.sort-indicator {
  margin-left: 0.5rem;
  font-weight: bold;
  color: #007bff;
}

.checkbox {
  width: 16px;
  height: 16px;
}

.description-text {
  color: #495057;
}

.grid-cells-badge {
  background: #17a2b8;
  color: white;
  padding: 0.25rem 0.5rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 500;
}

.text-muted {
  color: #6c757d;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-top: 2rem;
}

.page-info {
  color: #6c757d;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal {
  background: white;
  border-radius: 8px;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid #e9ecef;
}

.modal-header h3 {
  margin: 0;
}

.btn-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #6c757d;
}

.modal-body {
  padding: 1.5rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #495057;
}

.form-input,
.form-select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ced4da;
  border-radius: 4px;
  font-size: 1rem;
}

.form-input.error {
  border-color: #dc3545;
}

.error-message {
  color: #dc3545;
  font-size: 0.875rem;
  margin-top: 0.25rem;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 2rem;
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

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary {
  background: #007bff;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #0056b3;
}

.btn-secondary {
  background: #6c757d;
  color: white;
}

.btn-secondary:hover:not(:disabled) {
  background: #545b62;
}

.btn-danger {
  background: #dc3545;
  color: white;
}

.btn-danger:hover:not(:disabled) {
  background: #c82333;
}

.btn-info {
  background: #17a2b8;
  color: white;
}

.btn-info:hover:not(:disabled) {
  background: #138496;
}

.btn-sm {
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
}

@media (max-width: 768px) {
  .admin-page {
    padding: 1rem;
  }
  
  .page-header {
    flex-direction: column;
    gap: 1rem;
    align-items: stretch;
  }
  
  .filters {
    flex-direction: column;
    align-items: stretch;
  }
  
  .filter-controls {
    justify-content: space-between;
  }
  
  .data-table {
    font-size: 0.875rem;
  }
  
  .data-table th,
  .data-table td {
    padding: 0.75rem 0.5rem;
  }
}
</style>
