<template>
  <AdminLayout>
    <div class="admin-page">
    <div class="page-header">
      <h1>{{ __('admin.population_cells') }}</h1>
      <button @click="showCreateModal = true" class="btn btn-primary">
        {{ __('admin.add_population_cell') }}
      </button>
    </div>

    <!-- Filters -->
    <div class="filters">
      <div class="search-box">
        <input
          type="text"
          v-model="filters.search"
          @input="debouncedSearch"
          :placeholder="__('admin.search_population_cells')"
          class="form-input"
        />
      </div>
      <div class="filter-controls">
        <div class="density-range">
          <label>{{ __('admin.density_range') }}:</label>
          <input
            type="number"
            v-model.number="filters.min_density"
            @input="debouncedSearch"
            :placeholder="__('admin.min_density')"
            class="form-input"
            min="0"
            step="0.1"
          />
          <span>-</span>
          <input
            type="number"
            v-model.number="filters.max_density"
            @input="debouncedSearch"
            :placeholder="__('admin.max_density')"
            class="form-input"
            min="0"
            step="0.1"
          />
        </div>
        <select v-model="filters.sort_by" @change="applyFilters" class="form-select">
          <option value="density">{{ __('admin.density') }}</option>
          <option value="lat">{{ __('admin.latitude') }}</option>
          <option value="lng">{{ __('admin.longitude') }}</option>
          <option value="created_at">{{ __('admin.created_at') }}</option>
        </select>
        <select v-model="filters.sort_order" @change="applyFilters" class="form-select">
          <option value="desc">{{ __('admin.descending') }}</option>
          <option value="asc">{{ __('admin.ascending') }}</option>
        </select>
      </div>
    </div>

    <!-- Bulk Actions -->
    <div v-if="selectedItems.length > 0" class="bulk-actions">
      <span>{{ __('admin.selected_items', { count: selectedItems.length }) }}</span>
      <button @click="bulkDelete" class="btn btn-danger btn-sm">
        <!-- DISABLED: Bulk delete button temporarily disabled -->
        <button @click="bulkDelete" class="btn btn-danger btn-sm" disabled title="Functionality is implemented but disabled for demo to avoid data issues">
          {{ __('admin.delete_selected') }}
        </button>
      </button>
    </div>

    <!-- Population Cells Table -->
    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>
              <input 
                type="checkbox" 
                @change="toggleAll" 
                :checked="selectedItems.length === populationCells.data.length && populationCells.data.length > 0"
                class="checkbox"
              />
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
            <th class="sortable" @click="sortBy('density')">
              {{ __('admin.density') }}
              <span v-if="filters.sort_by === 'density'" class="sort-indicator">
                {{ filters.sort_order === 'asc' ? '↑' : '↓' }}
              </span>
            </th>
            <th>{{ __('admin.actions') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="cell in populationCells.data" :key="cell.id">
            <td>
              <input 
                type="checkbox" 
                :value="cell.id" 
                v-model="selectedItems"
                class="checkbox"
              />
            </td>
            <td>{{ cell.lat.toFixed(6) }}</td>
            <td>{{ cell.lng.toFixed(6) }}</td>
            <td>
              <span class="density-badge" :class="getDensityClass(cell.density)">
                {{ cell.density.toFixed(2) }}
              </span>
            </td>
            <td>
              <div class="action-buttons">

                 <!-- DISABLED: Edit button temporarily disabled -->
                 <button @click="editCell(cell)" class="btn btn-sm btn-secondary" disabled title="Functionality is implemented but disabled for demo to avoid data issues">
                  {{ __('admin.edit') }}
                </button>
                 <!-- DISABLED: Delete button temporarily disabled -->
                 <button @click="deleteCell(cell)" class="btn btn-sm btn-danger" disabled title="Functionality is implemented but disabled for demo to avoid data issues">
                  {{ __('admin.delete') }}
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="pagination" v-if="populationCells.last_page > 1">
      <button 
        @click="changePage(populationCells.current_page - 1)"
        :disabled="populationCells.current_page <= 1"
        class="btn btn-sm"
      >
        {{ __('admin.previous') }}
      </button>
      <span class="page-info">
        {{ __('admin.page_of', { current: populationCells.current_page, total: populationCells.last_page }) }}
      </span>
      <button 
        @click="changePage(populationCells.current_page + 1)"
        :disabled="populationCells.current_page >= populationCells.last_page"
        class="btn btn-sm"
      >
        {{ __('admin.next') }}
      </button>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showCreateModal || showEditModal" class="modal-overlay" @click="closeModal">
      <div class="modal" @click.stop>
        <div class="modal-header">
          <h3>{{ showCreateModal ? __('admin.add_population_cell') : __('admin.edit_population_cell') }}</h3>
          <button @click="closeModal" class="btn-close">&times;</button>
        </div>
        <form @submit.prevent="saveCell" class="modal-body">
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
          <div class="form-group">
            <label>{{ __('admin.density') }} *</label>
            <input 
              type="number" 
              v-model.number="form.density" 
              step="0.1"
              min="0"
              required 
              class="form-input"
              :class="{ 'error': errors.density }"
            />
            <div v-if="errors.density" class="error-message">{{ errors.density }}</div>
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
  populationCells: Object,
  filters: Object
})

const showCreateModal = ref(false)
const showEditModal = ref(false)
const loading = ref(false)
const errors = ref({})
const selectedItems = ref([])

const filters = reactive({
  search: props.filters.search || '',
  min_density: props.filters.min_density || null,
  max_density: props.filters.max_density || null,
  sort_by: props.filters.sort_by || 'density',
  sort_order: props.filters.sort_order || 'desc'
})

const form = reactive({
  id: null,
  lat: null,
  lng: null,
  density: null
})

const debouncedSearch = debounce(() => {
  applyFilters()
}, 300)

const applyFilters = () => {
  router.get(route('admin.population-cells.index'), filters, {
    preserveState: true,
    replace: true
  })
}

const changePage = (page) => {
  router.get(route('admin.population-cells.index'), { ...filters, page }, {
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
  if (selectedItems.value.length === props.populationCells.data.length) {
    selectedItems.value = []
  } else {
    selectedItems.value = props.populationCells.data.map(cell => cell.id)
  }
}

const getDensityClass = (density) => {
  if (density >= 100) return 'density-very-high'
  if (density >= 50) return 'density-high'
  if (density >= 20) return 'density-medium'
  if (density >= 5) return 'density-low'
  return 'density-very-low'
}

const editCell = (cell) => {
  form.id = cell.id
  form.lat = cell.lat
  form.lng = cell.lng
  form.density = cell.density
  showEditModal.value = true
}

const deleteCell = (cell) => {
  if (confirm(__('admin.confirm_delete_population_cell'))) {
    router.delete(route('admin.population-cells.destroy', cell.id), {
      onSuccess: () => {
        // Show success message
      }
    })
  }
}

const bulkDelete = () => {
  if (confirm(__('admin.confirm_bulk_delete_population_cells', { count: selectedItems.value.length }))) {
    router.delete(route('admin.population-cells.bulk-destroy'), {
      data: { ids: selectedItems.value },
      onSuccess: () => {
        selectedItems.value = []
      }
    })
  }
}

const saveCell = () => {
  loading.value = true
  errors.value = {}

  const url = form.id 
    ? route('admin.population-cells.update', form.id)
    : route('admin.population-cells.store')

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
  form.lat = null
  form.lng = null
  form.density = null
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

.density-range {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.density-range label {
  white-space: nowrap;
  font-size: 0.9rem;
  color: #495057;
}

.density-range input {
  width: 80px;
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

.density-badge {
  padding: 0.25rem 0.5rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 500;
  color: white;
}

.density-very-high {
  background: #dc3545;
}

.density-high {
  background: #fd7e14;
}

.density-medium {
  background: #ffc107;
  color: #212529;
}

.density-low {
  background: #28a745;
}

.density-very-low {
  background: #6c757d;
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

.action-buttons {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
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
  
  .density-range {
    flex-direction: column;
    align-items: stretch;
  }
  
  .density-range input {
    width: 100%;
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
