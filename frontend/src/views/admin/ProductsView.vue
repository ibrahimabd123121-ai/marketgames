<script setup>
import { onMounted, ref } from 'vue'
import { getProducts, createProduct, updateProduct, deleteProduct } from '../../services/productService'
import { getCategories } from '@/services/categoryService'


const categories = ref([])
const showAddForm = ref(false)
const saving = ref(false)
const formError = ref('')
const editingProductId = ref(null)
const isEditMode = ref(false)
const deletingProductId = ref(null)
const existingImageUrl = ref('')

const newProduct = ref({
  category_id: '',
  name: '',
  slug: '',
  description: '',
  price: '',
  stock: 0,
  status: true,
  image: null,
  remove_image: false,
})

async function loadCategories() {
  try {
    categories.value = await getCategories()
  } catch (error) {
    console.error('Failed to load categories:', error)
    formError.value = 'تعذر تحميل الأقسام. تحقق من اتصال الخادم.'
  }
}

function handleImageChange(event) {
  newProduct.value.image = event.target.files?.[0] ?? null
  if (newProduct.value.image) {
    newProduct.value.remove_image = false
  }
}

const products = ref([])
const loading = ref(false)
const errorMessage = ref('')
const currentPage = ref(1)
const lastPage = ref(1)

async function loadProducts(page = currentPage.value) {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await getProducts(page)
    products.value = response.data
    currentPage.value = response.current_page
    lastPage.value = response.last_page

    if (products.value.length === 0 && currentPage.value > 1) {
      await loadProducts(currentPage.value - 1)
    }
  } catch (error) {
    console.error('Failed to load products:', error.response?.data || error)
    errorMessage.value =
      error.response?.data?.message ||
      'تعذر تحميل المنتجات. تحقق من اتصال الخادم.'
  } finally {
    loading.value = false
  }
}

async function saveProduct() {
  saving.value = true
  formError.value = ''
  const wasEditing = isEditMode.value

  try {
    const payload = {
      ...newProduct.value,
      name: newProduct.value.name.trim(),
      slug: newProduct.value.slug.trim(),
      description: newProduct.value.description.trim(),
      price: Number(newProduct.value.price),
      stock: Number(newProduct.value.stock),
      category_id: Number(newProduct.value.category_id),
      image: newProduct.value.image,
      remove_image: newProduct.value.remove_image,
    }

    if (wasEditing) {
      await updateProduct(editingProductId.value, payload)
    } else {
      await createProduct(payload)
    }

    showAddForm.value = false
    isEditMode.value = false
    editingProductId.value = null

    newProduct.value = {
      category_id: '',
      name: '',
      slug: '',
      description: '',
      price: '',
      stock: 0,
      status: true,
      image: null,
      remove_image: false,
    }
    existingImageUrl.value = ''

    await loadProducts()
    formError.value = ''
    window.alert(wasEditing ? 'تم تحديث المنتج بنجاح.' : 'تمت إضافة المنتج بنجاح.')
  } catch (error) {
    console.error('Product save failed:', error.response?.data || error)

    const errors = error.response?.data?.errors
    const firstValidationError = errors
      ? Object.values(errors).flat()[0]
      : null

    formError.value =
      firstValidationError ||
      error.response?.data?.message ||
      'تعذر حفظ المنتج.'
  } finally {
    saving.value = false
  }
}

function editProduct(product) {
  editingProductId.value = product.id
  isEditMode.value = true
  newProduct.value = {
    category_id: product.category_id ?? '',
    name: product.name ?? product.title ?? '',
    slug: product.slug ?? '',
    description: product.description ?? product.subtitle ?? '',
    price: product.price ?? '',
    stock: product.stock ?? 0,
    status: Boolean(product.status),
    image: null,
    remove_image: false,
  }
  existingImageUrl.value = product.image ?? ''
  formError.value = ''
  showAddForm.value = true
}

function openAddForm() {
  editingProductId.value = null
  isEditMode.value = false
  newProduct.value = {
    category_id: '',
    name: '',
    slug: '',
    description: '',
    price: '',
    stock: 0,
    status: true,
    image: null,
    remove_image: false,
  }
  existingImageUrl.value = ''
  formError.value = ''
  showAddForm.value = true
}

async function handleDeleteProduct(product) {
  const confirmed = window.confirm(
    `هل أنت متأكد من حذف المنتج "${product.title}"؟`
  )

  if (!confirmed) return

  deletingProductId.value = product.id

  try {
    const response = await deleteProduct(product.id)
    await loadProducts()
    window.alert(response?.message || 'تم حذف المنتج بنجاح.')
  } catch (error) {
    console.error('Product deletion failed:', error.response?.data || error)
    window.alert(error.response?.data?.message || 'تعذر حذف المنتج.')
  } finally {
    deletingProductId.value = null
  }
}

onMounted(() => {
    loadProducts()
    loadCategories()
})

</script>

<template>
  <section class="products-view">
   <div class="products-header">
  <div>
    <h2>إدارة المنتجات</h2>
    <p>عرض المنتجات الموجودة في قاعدة البيانات.</p>
  </div>

  <div class="header-actions">
    <button
      type="button"
      class="refresh-button"
      :disabled="loading"
      @click="loadProducts"
    >
      {{ loading ? 'جارٍ التحميل...' : 'تحديث المنتجات' }}
    </button>

    <button
      type="button"
      class="add-button"
      @click="openAddForm"
    >
      + إضافة منتج
    </button>
  </div>
</div>

<div v-if="showAddForm" class="form-overlay">
  <form class="product-form" @submit.prevent="saveProduct">
    <h3>{{ isEditMode ? 'تعديل المنتج' : 'إضافة منتج جديد' }}</h3>

    <p v-if="formError" class="error-message">
      {{ formError }}
    </p>

    <label>
      اسم المنتج *
      <input v-model="newProduct.name" required maxlength="255" />
    </label>

    <label>
      القسم *
      <select v-model="newProduct.category_id" required>
        <option value="" disabled>اختر القسم</option>
        <option
          v-for="category in categories"
          :key="category.id"
          :value="category.id"
        >
          {{ category.name }}
        </option>
      </select>
    </label>

    <label>
      السعر *
      <input
        v-model="newProduct.price"
        type="number"
        min="0"
        step="0.01"
        required
      />
    </label>

    <label>
      المخزون *
      <input
        v-model.number="newProduct.stock"
        type="number"
        min="0"
        step="1"
        required
      />
    </label>

    <label>
      رابط الاسم (Slug) — اختياري
      <input v-model="newProduct.slug" maxlength="255" />
    </label>

    <label>
      وصف المنتج
      <textarea v-model="newProduct.description" rows="3"></textarea>
    </label>

    <label>
      صورة المنتج
      <input
        type="file"
        accept="image/*"
        @change="handleImageChange"
      />
    </label>

    <div v-if="existingImageUrl" class="image-preview">
      <img :src="existingImageUrl" alt="صورة المنتج الحالية" />
      <label class="status-label">
        <input v-model="newProduct.remove_image" type="checkbox" />
        إزالة الصورة الحالية
      </label>
    </div>

    <label class="status-label">
      <input v-model="newProduct.status" type="checkbox" />
      المنتج متاح
    </label>

    <div class="form-actions">
      <button
        type="button"
        class="cancel-button"
        @click="showAddForm = false; isEditMode = false; editingProductId = null"
      >
        إلغاء
      </button>

      <button type="submit" class="add-button" :disabled="saving">
        {{ saving ? 'جارٍ الحفظ...' : isEditMode ? 'تحديث المنتج' : 'حفظ المنتج' }}
      </button>
    </div>
  </form>
</div>

    <p v-if="loading && products.length === 0">
      جارٍ تحميل المنتجات...
    </p>

    <p v-else-if="errorMessage" class="error-message">
      {{ errorMessage }}
    </p>

    <p v-else-if="products.length === 0">
      لا توجد منتجات لعرضها.
    </p>

    <div v-else class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th>الصورة</th>
            <th>اسم المنتج</th>
            <th>القسم</th>
            <th>السعر</th>
            <th>المخزون</th>
            <th>الحالة</th>
            <th>الإجراءات</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="product in products" :key="product.id">
            <td>
              <img
                v-if="product.image"
                :src="product.image"
                :alt="product.title"
                class="product-image"
              />
              <span v-else>لا توجد صورة</span>
            </td>
            <td>{{ product.title }}</td>
            <td>{{ product.category }}</td>
            <td>{{ product.price.toFixed(2) }}</td>
            <td>{{ product.stock }}</td>
            <td>
              {{ product.badge || 'متاح' }}
            </td>
            <td>
              <button
                type="button"
                class="edit-button"
                @click="editProduct(product)"
              >
                تعديل
              </button>
              <button
                type="button"
                class="delete-button"
                :disabled="deletingProductId === product.id"
                @click="handleDeleteProduct(product)"
              >
                {{ deletingProductId === product.id ? 'جارٍ الحذف...' : 'حذف' }}
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <nav
      v-if="lastPage > 1"
      class="pagination-controls"
      aria-label="صفحات المنتجات"
    >
      <button
        type="button"
        :disabled="loading || currentPage <= 1"
        @click="loadProducts(currentPage - 1)"
      >
        السابق
      </button>
      <span>صفحة {{ currentPage }} من {{ lastPage }}</span>
      <button
        type="button"
        :disabled="loading || currentPage >= lastPage"
        @click="loadProducts(currentPage + 1)"
      >
        التالي
      </button>
    </nav>
  </section>
</template>


<style scoped>
.products-view {
  padding: 24px;
  color: #333;
  direction: rtl;
}

.products-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  margin-bottom: 24px;
}

.products-header h2 {
  margin: 0 0 8px;
  color: #29251f;
  font-size: 24px;
  font-weight: 700;
}

.products-header p {
  margin: 0;
  color: #77716a;
  font-size: 14px;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.refresh-button,
.add-button,
.cancel-button {
  padding: 11px 18px;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s ease, transform 0.2s ease;
}

.refresh-button {
  background: #f2eee6;
  color: #514735;
}

.refresh-button:hover {
  background: #e7dfd1;
}

.add-button {
  background: #b38b45;
  color: #fff;
}

.edit-button {
  padding: 8px 12px;
  border: 1px solid #b38b45;
  border-radius: 8px;
  background: #fff;
  color: #765a2c;
  font: inherit;
  cursor: pointer;
}

.edit-button:hover {
  background: #f8f5ef;
}

.products-view .delete-button {
  margin-inline-start: 8px;
  padding: 8px 14px;
  border: 1px solid #b42318;
  border-radius: 8px;
  background-color: #b42318;
  color: #fff;
  font: inherit;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s ease, border-color 0.2s ease;
}

.products-view .delete-button:hover:not(:disabled) {
  border-color: #8f1d15;
  background-color: #8f1d15;
}

.products-view .delete-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.add-button:hover {
  background: #987238;
}

.cancel-button {
  background: #eeeae3;
  color: #514b43;
}

.cancel-button:hover {
  background: #ded7cb;
}

.refresh-button:disabled,
.add-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.table-wrapper {
  width: 100%;
  overflow-x: auto;
  border: 1px solid #eee7dc;
  border-radius: 12px;
  background: #fff;
}

.pagination-controls {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 16px;
  margin-top: 20px;
  color: #625641;
}

.pagination-controls button {
  padding: 8px 14px;
  border: 1px solid #b38b45;
  border-radius: 8px;
  background: #fff;
  color: #765a2c;
  font: inherit;
  cursor: pointer;
}

.pagination-controls button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

table {
  width: 100%;
  border-collapse: collapse;
  min-width: 650px;
}

thead {
  background: #f8f5ef;
}

th,
td {
  padding: 15px 16px;
  border-bottom: 1px solid #eeeae3;
  text-align: right;
  vertical-align: middle;
  white-space: nowrap;
}

th {
  color: #625641;
  font-size: 13px;
  font-weight: 700;
}

td {
  color: #49443d;
  font-size: 14px;
}

tbody tr:last-child td {
  border-bottom: none;
}

tbody tr:hover {
  background: #fcfaf6;
}

.product-image {
  display: block;
  width: 58px;
  height: 58px;
  object-fit: cover;
  border: 1px solid #eee7dc;
  border-radius: 9px;
  background: #f8f5ef;
}

.image-preview img {
  display: block;
  width: 96px;
  height: 96px;
  margin-bottom: 10px;
  border-radius: 8px;
  object-fit: cover;
}

.error-message {
  margin: 12px 0;
  padding: 12px 14px;
  border: 1px solid #f1caca;
  border-radius: 8px;
  background: #fff3f3;
  color: #b42318;
  font-size: 14px;
}

.products-view > p {
  color: #77716a;
  line-height: 1.7;
}

/* خلفية نموذج الإضافة */
.form-overlay {
  position: fixed;
  inset: 0;
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  background: rgb(35 30 23 / 48%);
  overflow-y: auto;
}

/* نموذج المنتج */
.product-form {
  width: 100%;
  max-width: 560px;
  max-height: calc(100vh - 40px);
  overflow-y: auto;
  padding: 28px;
  border: 1px solid #eee7dc;
  border-radius: 16px;
  background: #fffdf9;
  box-shadow: 0 20px 60px rgb(0 0 0 / 18%);
}

.product-form h3 {
  margin: 0 0 22px;
  color: #30291f;
  font-size: 22px;
  font-weight: 700;
}

.product-form label {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-bottom: 17px;
  color: #514735;
  font-size: 14px;
  font-weight: 600;
}

.product-form input:not([type="checkbox"]):not([type="file"]),
.product-form select,
.product-form textarea {
  box-sizing: border-box;
  width: 100%;
  padding: 11px 12px;
  border: 1px solid #ddd5c8;
  border-radius: 8px;
  outline: none;
  background: #fff;
  color: #333;
  font: inherit;
  font-weight: 400;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.product-form input:focus,
.product-form select:focus,
.product-form textarea:focus {
  border-color: #b38b45;
  box-shadow: 0 0 0 3px rgb(179 139 69 / 14%);
}

.product-form input[type="file"] {
  box-sizing: border-box;
  width: 100%;
  padding: 10px;
  border: 1px dashed #cfc3b0;
  border-radius: 8px;
  background: #faf7f1;
  color: #625641;
  font-size: 13px;
}

.product-form textarea {
  min-height: 90px;
  resize: vertical;
}

.product-form .status-label {
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 10px;
  cursor: pointer;
}

.status-label input[type="checkbox"] {
  width: 17px;
  height: 17px;
  accent-color: #b38b45;
}

.form-actions {
  display: flex;
  justify-content: flex-start;
  gap: 10px;
  margin-top: 24px;
  padding-top: 18px;
  border-top: 1px solid #eee7dc;
}

.form-actions button {
  min-width: 110px;
}

/* الشاشات الصغيرة */
@media (max-width: 700px) {
  .products-view {
    padding: 16px;
  }

  .products-header {
    align-items: stretch;
    flex-direction: column;
  }

  .header-actions {
    width: 100%;
  }

  .header-actions button {
    flex: 1;
  }

  .product-form {
    padding: 20px;
  }

  .form-actions button {
    flex: 1;
  }
}
</style>