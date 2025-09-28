<template>
  <AdminLayout>
    <div class="admin-page">
    <div class="page-header">
      <h1>{{ __('admin.poi_categories') }}</h1>
      <button @click="showCreateModal = true" class="btn btn-primary">
        {{ __('admin.add_category') }}
      </button>
    </div>

    <!-- Filters -->
    <div class="filters">
      <div class="search-box">
        <input
          type="text"
          v-model="filters.search"
          @input="debouncedSearch"
          :placeholder="__('admin.search_categories')"
          class="form-input"
        />
      </div>
      <div class="sort-controls">
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

    <!-- Categories Table -->
    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th class="sortable" @click="sortBy('name')">
              {{ __('admin.name') }}
              <span v-if="filters.sort_by === 'name'" class="sort-indicator">
                {{ filters.sort_order === 'asc' ? '↑' : '↓' }}
              </span>
            </th>
            <th>{{ __('admin.description') }}</th>
            <th>{{ __('admin.color') }}</th>
            <th class="sortable" @click="sortBy('poi_locations_count')">
              {{ __('admin.poi_count') }}
              <span v-if="filters.sort_by === 'poi_locations_count'" class="sort-indicator">
                {{ filters.sort_order === 'asc' ? '↑' : '↓' }}
              </span>
            </th>
            <th>{{ __('admin.actions') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="category in categories.data" :key="category.id">
            <td>{{ category.name }}</td>
            <td>{{ category.description || '-' }}</td>
            <td>
              <div class="color-preview" :style="{ backgroundColor: category.color }"></div>
              {{ category.color }}
            </td>
            <td>{{ category.poi_locations_count }}</td>
            <td>
              <div class="action-buttons">
                 <!-- DISABLED: Edit button temporarily disabled -->
                 <button @click="editCategory(category)" class="btn btn-sm btn-secondary" disabled title="Functionality is implemented but disabled for demo to avoid data issues">
                  {{ __('admin.edit') }}
                </button>
                 <!-- DISABLED: Delete button temporarily disabled -->
                 <button 
                   @click="deleteCategory(category)" 
                   class="btn btn-sm btn-danger"
                   disabled
                   title="Functionality is implemented but disabled for demo to avoid data issues"
                 >
                  {{ __('admin.delete') }}
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="pagination" v-if="categories.last_page > 1">
      <button 
        @click="changePage(categories.current_page - 1)"
        :disabled="categories.current_page <= 1"
        class="btn btn-sm"
      >
        {{ __('admin.previous') }}
      </button>
      <span class="page-info">
        {{ __('admin.page_of', { current: categories.current_page, total: categories.last_page }) }}
      </span>
      <button 
        @click="changePage(categories.current_page + 1)"
        :disabled="categories.current_page >= categories.last_page"
        class="btn btn-sm"
      >
        {{ __('admin.next') }}
      </button>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showCreateModal || showEditModal" class="modal-overlay" @click="closeModal">
      <div class="modal" @click.stop>
        <div class="modal-header">
          <h3>{{ showCreateModal ? __('admin.add_category') : __('admin.edit_category') }}</h3>
          <button @click="closeModal" class="btn-close">&times;</button>
        </div>
        <form @submit.prevent="saveCategory" class="modal-body">
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
            ></textarea>
          </div>
          <div class="form-group">
            <label>{{ __('admin.color') }} *</label>
            <div class="color-input-group">
              <input 
                type="color" 
                v-model="form.color" 
                class="color-picker"
              />
              <input 
                type="text" 
                v-model="form.color" 
                class="form-input color-text"
                placeholder="#007bff"
              />
            </div>
            <div v-if="errors.color" class="error-message">{{ errors.color }}</div>
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
  categories: Object,
  filters: Object
})

const showCreateModal = ref(false)
const showEditModal = ref(false)
const loading = ref(false)
const errors = ref({})

const filters = reactive({
  search: props.filters.search || '',
  sort_by: props.filters.sort_by || 'name',
  sort_order: props.filters.sort_order || 'asc'
})

const form = reactive({
  id: null,
  name: '',
  description: '',
  color: '#007bff'
})

const debouncedSearch = debounce(() => {
  applyFilters()
}, 300)

const applyFilters = () => {
  router.get(route('admin.poi-categories.index'), filters, {
    preserveState: true,
    replace: true
  })
}

const changePage = (page) => {
  router.get(route('admin.poi-categories.index'), { ...filters, page }, {
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

const editCategory = (category) => {
  form.id = category.id
  form.name = category.name
  form.description = category.description || ''
  form.color = category.color
  showEditModal.value = true
}

const deleteCategory = (category) => {
  if (confirm(__('admin.confirm_delete_category', { name: category.name }))) {
    router.delete(route('admin.poi-categories.destroy', category.id), {
      onSuccess: () => {
        // Show success message
      }
    })
  }
}

const saveCategory = () => {
  loading.value = true
  errors.value = {}

  const url = form.id 
    ? route('admin.poi-categories.update', form.id)
    : route('admin.poi-categories.store')

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
  form.color = '#007bff'
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
}

.search-box {
  flex: 1;
}

.sort-controls {
  display: flex;
  gap: 0.5rem;
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

.color-preview {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  display: inline-block;
  margin-right: 0.5rem;
  border: 1px solid #dee2e6;
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

.color-input-group {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.color-picker {
  width: 50px;
  height: 40px;
  border: 1px solid #ced4da;
  border-radius: 4px;
  cursor: pointer;
}

.color-text {
  flex: 1;
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
  
  .sort-controls {
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
