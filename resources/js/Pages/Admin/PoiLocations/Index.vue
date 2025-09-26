<template>
  <AdminLayout>
    <div class="admin-page">
    <div class="page-header">
      <h1>{{ __('admin.poi_locations') }}</h1>
      <button @click="showCreateModal = true" class="btn btn-primary">
        {{ __('admin.add_poi_location') }}
      </button>
    </div>

    <!-- Filters -->
    <div class="filters">
      <div class="search-box">
        <input
          type="text"
          v-model="filters.search"
          @input="debouncedSearch"
          :placeholder="__('admin.search_poi_locations')"
          class="form-input"
        />
      </div>
      <div class="filter-controls">
        <select v-model="filters.category_id" @change="applyFilters" class="form-select">
          <option value="">{{ __('admin.all_categories') }}</option>
          <option v-for="category in categories" :key="category.id" :value="category.id">
            {{ category.name }}
          </option>
        </select>
        <select v-model="filters.sort_by" @change="applyFilters" class="form-select">
          <option value="name">{{ __('admin.name') }}</option>
          <option value="category">{{ __('admin.category') }}</option>
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

    <!-- POI Locations Table -->
    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>
              <input 
                type="checkbox" 
                @change="toggleAll" 
                :checked="selectedItems.length === poiLocations.data.length && poiLocations.data.length > 0"
                class="checkbox"
              />
            </th>
            <th class="sortable" @click="sortBy('name')">
              {{ __('admin.name') }}
              <span v-if="filters.sort_by === 'name'" class="sort-indicator">
                {{ filters.sort_order === 'asc' ? '↑' : '↓' }}
              </span>
            </th>
            <th class="sortable" @click="sortBy('category')">
              {{ __('admin.category') }}
              <span v-if="filters.sort_by === 'category'" class="sort-indicator">
                {{ filters.sort_order === 'asc' ? '↑' : '↓' }}
              </span>
            </th>
            <th class="sortable" @click="sortBy('lat')">
              {{ __('admin.latitude') }}
              <span v-if="filters.sort_by === 'lat'" class="sort-indicator">
                {{ filters.sort_order === 'asc' ? '↑' : '↓' }}
              </span>
            </th>
            <th class="sortable" @click="sortBy('lng')">
              {{ __('admin.longitude') }}
              <span v-if="filters.sort_by === 'lng'" class="sort-indicator">
                {{ filters.sort_order === 'asc' ? '↑' : '↓' }}
              </span>
            </th>
            <th>{{ __('admin.actions') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="poi in poiLocations.data" :key="poi.id">
            <td>
              <input 
                type="checkbox" 
                :value="poi.id" 
                v-model="selectedItems"
                class="checkbox"
              />
            </td>
            <td>{{ poi.name }}</td>
            <td>
              <span v-if="poi.category" class="category-badge" :style="{ backgroundColor: poi.category.color }">
                {{ poi.category.name }}
              </span>
              <span v-else>{{ poi.category || '-' }}</span>
            </td>
            <td>{{ poi.lat.toFixed(6) }}</td>
            <td>{{ poi.lng.toFixed(6) }}</td>
            <td>
              <button @click="editPoi(poi)" class="btn btn-sm btn-secondary">
                {{ __('admin.edit') }}
              </button>
              <button @click="deletePoi(poi)" class="btn btn-sm btn-danger">
                {{ __('admin.delete') }}
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="pagination" v-if="poiLocations.last_page > 1">
      <button 
        @click="changePage(poiLocations.current_page - 1)"
        :disabled="poiLocations.current_page <= 1"
        class="btn btn-sm"
      >
        {{ __('admin.previous') }}
      </button>
      <span class="page-info">
        {{ __('admin.page_of', { current: poiLocations.current_page, total: poiLocations.last_page }) }}
      </span>
      <button 
        @click="changePage(poiLocations.current_page + 1)"
        :disabled="poiLocations.current_page >= poiLocations.last_page"
        class="btn btn-sm"
      >
        {{ __('admin.next') }}
      </button>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showCreateModal || showEditModal" class="modal-overlay" @click="closeModal">
      <div class="modal" @click.stop>
        <div class="modal-header">
          <h3>{{ showCreateModal ? __('admin.add_poi_location') : __('admin.edit_poi_location') }}</h3>
          <button @click="closeModal" class="btn-close">&times;</button>
        </div>
        <form @submit.prevent="savePoi" class="modal-body">
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
            <label>{{ __('admin.category') }}</label>
            <select v-model="form.category_id" class="form-select">
              <option value="">{{ __('admin.no_category') }}</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
          </div>
          <div class="form-group">
            <label>{{ __('admin.latitude') }} *</label>
            <input 
              type="number" 
              v-model.number="form.lat" 
              step="0.000001"
              min="-90"
              max="90"
              required 
              class="form-input"
              :class="{ 'error': errors.lat }"
            />
            <div v-if="errors.lat" class="error-message">{{ errors.lat }}</div>
          </div>
          <div class="form-group">
            <label>{{ __('admin.longitude') }} *</label>
            <input 
              type="number" 
              v-model.number="form.lng" 
              step="0.000001"
              min="-180"
              max="180"
              required 
              class="form-input"
              :class="{ 'error': errors.lng }"
            />
            <div v-if="errors.lng" class="error-message">{{ errors.lng }}</div>
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
import { ref, reactive, onMounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { debounce } from 'lodash-es'
import AdminLayout from '../../../Components/Layout/AdminLayout.vue'

const props = defineProps({
  poiLocations: Object,
  categories: Array,
  filters: Object
})

const showCreateModal = ref(false)
const showEditModal = ref(false)
const loading = ref(false)
const errors = ref({})
const selectedItems = ref([])

const filters = reactive({
  search: props.filters.search || '',
  category_id: props.filters.category_id || '',
  sort_by: props.filters.sort_by || 'name',
  sort_order: props.filters.sort_order || 'asc'
})

const form = reactive({
  id: null,
  name: '',
  category_id: '',
  lat: null,
  lng: null
})

const debouncedSearch = debounce(() => {
  applyFilters()
}, 300)

const applyFilters = () => {
  router.get(route('admin.poi-locations.index'), filters, {
    preserveState: true,
    replace: true
  })
}

const changePage = (page) => {
  router.get(route('admin.poi-locations.index'), { ...filters, page }, {
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
  if (selectedItems.value.length === props.poiLocations.data.length) {
    selectedItems.value = []
  } else {
    selectedItems.value = props.poiLocations.data.map(poi => poi.id)
  }
}

const editPoi = (poi) => {
  form.id = poi.id
  form.name = poi.name
  form.category_id = poi.category_id || ''
  form.lat = poi.lat
  form.lng = poi.lng
  showEditModal.value = true
}

const deletePoi = (poi) => {
  if (confirm(__('admin.confirm_delete_poi', { name: poi.name }))) {
    router.delete(route('admin.poi-locations.destroy', poi.id), {
      onSuccess: () => {
        // Show success message
      }
    })
  }
}

const bulkDelete = () => {
  if (confirm(__('admin.confirm_bulk_delete', { count: selectedItems.value.length }))) {
    router.delete(route('admin.poi-locations.bulk-destroy'), {
      data: { ids: selectedItems.value },
      onSuccess: () => {
        selectedItems.value = []
      }
    })
  }
}

const savePoi = () => {
  loading.value = true
  errors.value = {}

  const url = form.id 
    ? route('admin.poi-locations.update', form.id)
    : route('admin.poi-locations.store')

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
  form.category_id = ''
  form.lat = null
  form.lng = null
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

.category-badge {
  color: white;
  padding: 0.25rem 0.5rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 500;
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
  max-width: 500px;
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
