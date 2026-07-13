<template>
  <div class="products-page">
    <div class="page-header">
      <div>
        <h2 class="page-title">🛍️ المنتجات</h2>
        <p class="page-desc">إدارة المنتجات وربطها بأنواع مشاكل البشرة للتوصيات الذكية</p>
      </div>
      <button class="btn btn-primary" @click="openCreateModal">
        <span>+</span> منتج جديد
      </button>
    </div>

    <div class="products-layout">
      <div class="products-main">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">قائمة المنتجات</h3>
            <input
              v-model="searchQuery"
              type="text"
              class="search-input"
              placeholder="بحث عن منتج..."
            />
          </div>

          <div class="table-wrapper">
            <table>
              <thead>
                <tr>
                  <th>المنتج</th>
                  <th>السعر</th>
                  <th>مشاكل البشرة المرتبطة</th>
                  <th>الحالة</th>
                  <th>إجراءات</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="loading && filteredProducts.length === 0">
                  <td colspan="5" style="text-align: center; padding: 2rem;">
                    <div class="spinner spinner-lg"></div>
                  </td>
                </tr>
                <tr v-else-if="filteredProducts.length === 0">
                  <td colspan="5">
                    <div class="empty-state">
                      <div class="empty-state-icon">🛍️</div>
                      <div class="empty-state-title">لا توجد منتجات</div>
                      <div class="empty-state-desc">أضف منتجاتك الأولى</div>
                    </div>
                  </td>
                </tr>
                <tr v-for="product in filteredProducts" :key="product.id">
                  <td>
                    <div class="product-cell">
                      <img
                        v-if="product.image_url"
                        :src="product.image_url"
                        :alt="product.name"
                        class="product-thumb"
                      />
                      <div v-else class="product-thumb-placeholder">🛍️</div>
                      <div>
                        <div class="product-name">{{ product.name }}</div>
                        <div v-if="product.description" class="product-desc">{{ product.description }}</div>
                      </div>
                    </div>
                  </td>
                  <td>
                    <span class="product-price">{{ product.price || 0 }} ريال</span>
                  </td>
                  <td>
                    <div class="linked-defects">
                      <span
                        v-for="defect in (product.linked_defects || [])"
                        :key="defect"
                        class="badge badge-info defect-badge"
                      >
                        {{ defectLabel(defect) }}
                        <span class="defect-remove" @click="unlinkDefect(product, defect)">✕</span>
                      </span>
                      <button
                        v-if="!product.linked_defects?.length"
                        class="btn btn-sm btn-secondary"
                        @click="openLinkDefectModal(product)"
                      >
                        + ربط
                      </button>
                      <button
                        v-else
                        class="btn btn-sm btn-secondary"
                        @click="openLinkDefectModal(product)"
                      >
                        + إضافة
                      </button>
                    </div>
                  </td>
                  <td>
                    <span class="badge" :class="product.is_active ? 'badge-success' : 'badge-muted'">
                      {{ product.is_active ? 'نشط' : 'غير نشط' }}
                    </span>
                  </td>
                  <td>
                    <div class="action-btns">
                      <button class="btn btn-sm btn-info" @click="openEditModal(product)">✏️</button>
                      <button class="btn btn-sm btn-danger" @click="deleteProduct(product.id)">🗑️</button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="card" v-if="rules">
          <div class="card-header">
            <h3 class="card-title">📐 قواعد التوصية</h3>
            <button class="btn btn-sm btn-primary" @click="saveRules" :disabled="savingRules">
              <span v-if="savingRules" class="spinner"></span>
              <span v-else>💾 حفظ القواعد</span>
            </button>
          </div>
          <div class="rules-info">
            <p>حدد أولوية أنواع المشاكل في التوصيات والحد الأدنى للمنتجات المقترحة</p>
          </div>
          <div class="rules-grid">
            <div v-for="defect in defectTypes" :key="defect.key" class="rule-item">
              <label class="rule-label">{{ defect.label }}</label>
              <div class="rule-inputs">
                <label>
                  الأولوية:
                  <input v-model.number="rules[defect.key + '_priority']" type="number" min="0" max="10" class="form-input rule-input" />
                </label>
                <label>
                  الحد الأدنى:
                  <input v-model.number="rules[defect.key + '_min_products']" type="number" min="0" max="5" class="form-input rule-input" />
                </label>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="productModal.show" class="modal-overlay" @click.self="productModal.show = false">
      <div class="modal-content animate-slideUp">
        <div class="modal-header">
          <h3 class="modal-title">{{ productModal.editMode ? 'تعديل منتج' : 'منتج جديد' }}</h3>
          <button class="btn btn-sm btn-secondary" @click="productModal.show = false">✕</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">اسم المنتج</label>
            <input v-model="productModal.name" type="text" class="form-input" placeholder="غسول نيفيا" />
          </div>
          <div class="form-group">
            <label class="form-label">الوصف</label>
            <textarea v-model="productModal.description" class="form-input" rows="3" placeholder="وصف المنتج..."></textarea>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">السعر (ريال)</label>
              <input v-model.number="productModal.price" type="number" class="form-input" placeholder="59" />
            </div>
            <div class="form-group">
              <label class="form-label">رابط الصورة</label>
              <input v-model="productModal.image_url" type="text" class="form-input" placeholder="https://..." />
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">نشط</label>
            <div class="toggle-wrapper">
              <span
                class="status-toggle"
                :class="{ active: productModal.is_active }"
                @click="productModal.is_active = !productModal.is_active"
              >
                <span class="toggle-knob"></span>
              </span>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="productModal.show = false">إلغاء</button>
          <button class="btn btn-primary" @click="saveProduct" :disabled="savingProduct">
            <span v-if="savingProduct" class="spinner"></span>
            <span v-else>حفظ</span>
          </button>
        </div>
      </div>
    </div>

    <div v-if="linkModal.show" class="modal-overlay" @click.self="linkModal.show = false">
      <div class="modal-content animate-slideUp">
        <div class="modal-header">
          <h3 class="modal-title">ربط مشكلة بشرة - {{ linkModal.productName }}</h3>
          <button class="btn btn-sm btn-secondary" @click="linkModal.show = false">✕</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">نوع المشكلة</label>
            <select v-model="linkModal.defectType" class="form-input">
              <option value="">اختر نوع المشكلة</option>
              <option v-for="defect in defectTypes" :key="defect.key" :value="defect.key">
                {{ defect.label }}
              </option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="linkModal.show = false">إلغاء</button>
          <button class="btn btn-primary" @click="linkDefect" :disabled="linkingDefect || !linkModal.defectType">
            <span v-if="linkingDefect" class="spinner"></span>
            <span v-else>ربط</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { productsApi } from '@/api/endpoints'
import Swal from 'sweetalert2'

const loading = ref(false)
const savingProduct = ref(false)
const savingRules = ref(false)
const linkingDefect = ref(false)
const searchQuery = ref('')
const products = ref([])

const defectTypes = [
  { key: 'acne', label: 'حب الشباب' },
  { key: 'wrinkles', label: 'التجاعيد' },
  { key: 'pigmentation', label: 'التصبغات' },
  { key: 'dryness', label: 'الجفاف' },
  { key: 'oiliness', label: 'الدهون الزائدة' },
  { key: 'pores', label: 'المسام الواسعة' },
  { key: 'redness', label: 'احمرار' },
  { key: 'dark_circles', label: 'الهالات السوداء' }
]

const rules = reactive({
  acne_priority: 8,
  acne_min_products: 2,
  wrinkles_priority: 7,
  wrinkles_min_products: 2,
  pigmentation_priority: 6,
  pigmentation_min_products: 2,
  dryness_priority: 5,
  dryness_min_products: 2,
  oiliness_priority: 7,
  oiliness_min_products: 2,
  pores_priority: 6,
  pores_min_products: 1,
  redness_priority: 5,
  redness_min_products: 1,
  dark_circles_priority: 4,
  dark_circles_min_products: 1
})

const productModal = reactive({
  show: false,
  editMode: false,
  id: null,
  name: '',
  description: '',
  price: 0,
  image_url: '',
  is_active: true
})

const linkModal = reactive({
  show: false,
  productId: null,
  productName: '',
  defectType: ''
})

const filteredProducts = computed(() => {
  if (!searchQuery.value.trim()) return products.value
  const query = searchQuery.value.trim().toLowerCase()
  return products.value.filter(p =>
    (p.name || '').toLowerCase().includes(query) ||
    (p.description || '').toLowerCase().includes(query)
  )
})

function defectLabel(key) {
  const found = defectTypes.find(d => d.key === key)
  return found ? found.label : key
}

async function fetchProducts() {
  loading.value = true
  try {
    const { data } = await productsApi.list({ per_page: 100 })
    products.value = data.products || data.data || []
  } catch (err) {
    console.error('Failed to fetch products:', err)
  } finally {
    loading.value = false
  }
}

async function fetchRules() {
  try {
    const { data } = await productsApi.recommendationRules()
    if (data.rules) {
      Object.assign(rules, data.rules)
    }
  } catch {
  }
}

function openCreateModal() {
  productModal.editMode = false
  productModal.id = null
  productModal.name = ''
  productModal.description = ''
  productModal.price = 0
  productModal.image_url = ''
  productModal.is_active = true
  productModal.show = true
}

function openEditModal(product) {
  productModal.editMode = true
  productModal.id = product.id
  productModal.name = product.name
  productModal.description = product.description || ''
  productModal.price = product.price || 0
  productModal.image_url = product.image_url || ''
  productModal.is_active = product.is_active
  productModal.show = true
}

async function saveProduct() {
  savingProduct.value = true
  try {
    const payload = {
      name: productModal.name,
      description: productModal.description,
      price: productModal.price,
      image_url: productModal.image_url,
      is_active: productModal.is_active
    }

    if (productModal.editMode && productModal.id) {
      await productsApi.update(productModal.id, payload)
      const idx = products.value.findIndex(p => p.id === productModal.id)
      if (idx >= 0) products.value[idx] = { ...products.value[idx], ...payload }
    } else {
      const { data } = await productsApi.create(payload)
      products.value.unshift(data.product || data)
    }

    productModal.show = false
    Swal.fire({ title: 'تم الحفظ', icon: 'success', timer: 1500, showConfirmButton: false })
  } catch (err) {
    Swal.fire({ title: 'خطأ', text: err.response?.data?.message || 'فشل الحفظ', icon: 'error' })
  } finally {
    savingProduct.value = false
  }
}

async function deleteProduct(id) {
  const result = await Swal.fire({
    title: 'حذف المنتج؟',
    text: 'لا يمكن التراجع عن هذا الإجراء',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'حذف',
    cancelButtonText: 'إلغاء',
    confirmButtonColor: '#ef4444'
  })

  if (result.isConfirmed) {
    try {
      await productsApi.delete(id)
      products.value = products.value.filter(p => p.id !== id)
      Swal.fire({ title: 'تم الحذف', icon: 'success', timer: 1500, showConfirmButton: false })
    } catch (err) {
      Swal.fire({ title: 'خطأ', text: err.response?.data?.message || 'فشل الحذف', icon: 'error' })
    }
  }
}

function openLinkDefectModal(product) {
  linkModal.show = true
  linkModal.productId = product.id
  linkModal.productName = product.name
  linkModal.defectType = ''
}

async function linkDefect() {
  if (!linkModal.defectType) return
  linkingDefect.value = true
  try {
    await productsApi.linkDefect(linkModal.productId, linkModal.defectType)
    const product = products.value.find(p => p.id === linkModal.productId)
    if (product) {
      if (!product.linked_defects) product.linked_defects = []
      if (!product.linked_defects.includes(linkModal.defectType)) {
        product.linked_defects.push(linkModal.defectType)
      }
    }
    linkModal.show = false
    Swal.fire({ title: 'تم الربط', icon: 'success', timer: 1500, showConfirmButton: false })
  } catch (err) {
    Swal.fire({ title: 'خطأ', text: err.response?.data?.message || 'فشل الربط', icon: 'error' })
  } finally {
    linkingDefect.value = false
  }
}

async function unlinkDefect(product, defectType) {
  try {
    await productsApi.unlinkDefect(product.id, defectType)
    product.linked_defects = product.linked_defects.filter(d => d !== defectType)
    Swal.fire({ title: 'تم إلغاء الربط', icon: 'success', timer: 1500, showConfirmButton: false })
  } catch (err) {
    Swal.fire({ title: 'خطأ', text: err.response?.data?.message || 'فشل إلغاء الربط', icon: 'error' })
  }
}

async function saveRules() {
  savingRules.value = true
  try {
    await productsApi.updateRecommendationRules({ ...rules })
    Swal.fire({ title: 'تم حفظ القواعد', icon: 'success', timer: 1500, showConfirmButton: false })
  } catch (err) {
    Swal.fire({ title: 'خطأ', text: err.response?.data?.message || 'فشل الحفظ', icon: 'error' })
  } finally {
    savingRules.value = false
  }
}

function handleRefresh() {
  fetchProducts()
}

onMounted(() => {
  fetchProducts()
  fetchRules()
  window.addEventListener('admin-refresh', handleRefresh)
})
</script>

<style lang="scss" scoped>
.products-page {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
}

.page-title {
  font-size: 1.375rem;
  font-weight: 800;
  color: var(--text-primary);
}

.page-desc {
  font-size: 0.8125rem;
  color: var(--text-secondary);
  margin-top: 0.25rem;
}

.products-layout {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.products-main {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.search-input {
  width: 240px;
  padding: 0.5rem 0.75rem;
}

.product-cell {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.product-thumb {
  width: 44px;
  height: 44px;
  border-radius: var(--radius-sm);
  object-fit: cover;
  border: 1px solid var(--border-light);
}

.product-thumb-placeholder {
  width: 44px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg-body);
  border-radius: var(--radius-sm);
  font-size: 1.25rem;
}

.product-name {
  font-weight: 600;
  font-size: 0.875rem;
  color: var(--text-primary);
}

.product-desc {
  font-size: 0.75rem;
  color: var(--text-muted);
  max-width: 200px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.product-price {
  font-weight: 700;
  color: var(--primary);
}

.linked-defects {
  display: flex;
  flex-wrap: wrap;
  gap: 0.375rem;
  align-items: center;
}

.defect-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
}

.defect-remove {
  cursor: pointer;
  opacity: 0.6;
  font-size: 0.625rem;
}

.defect-remove:hover {
  opacity: 1;
}

.action-btns {
  display: flex;
  gap: 0.375rem;
}

.rules-info {
  margin-bottom: 1rem;
  font-size: 0.8125rem;
  color: var(--text-secondary);
}

.rules-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 0.75rem;
}

.rule-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.625rem 0.75rem;
  background: var(--bg-body);
  border-radius: var(--radius-sm);
}

.rule-label {
  font-weight: 600;
  font-size: 0.8125rem;
  color: var(--text-primary);
}

.rule-inputs {
  display: flex;
  gap: 0.5rem;
  align-items: center;
  font-size: 0.75rem;
  color: var(--text-secondary);
}

.rule-input {
  width: 60px;
  padding: 0.25rem 0.5rem;
  text-align: center;
}

.toggle-wrapper {
  display: flex;
  align-items: center;
}

.status-toggle {
  width: 44px;
  height: 24px;
  background: var(--border-color);
  border-radius: var(--radius-full);
  position: relative;
  cursor: pointer;
  display: block;
  transition: background var(--transition-fast);
}

.status-toggle.active {
  background: var(--success);
}

.toggle-knob {
  position: absolute;
  top: 3px;
  right: 3px;
  width: 18px;
  height: 18px;
  background: #fff;
  border-radius: 50%;
  transition: transform var(--transition-fast);
  box-shadow: var(--shadow-sm);
}

.status-toggle.active .toggle-knob {
  transform: translateX(-20px);
}
</style>
