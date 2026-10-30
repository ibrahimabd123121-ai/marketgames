<script setup>
import { onMounted, ref } from 'vue'
import { getCategories, createCategory, updateCategory, deleteCategory } from '../../services/categoryService'

const categories = ref([])
const loading = ref(false)
const errorMessage = ref('')
const showAddForm = ref(false)
const editingCategoryId = ref(null)
const isEditMode = ref(false)
const existingImageUrl = ref('')
const newCategory = ref({
  name: '',
  slug: '',
  description: '',
  sort_order: 0,
  status: true,
  image: null,
  remove_image: false,
})
const saving = ref(false)
const formError = ref('')

async function loadCategories() {
  loading.value = true
  errorMessage.value = ''

  try {
    categories.value = await getCategories()
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message ||
      'تعذر تحميل الأقسام. تحقق من اتصال الخادم.'
  } finally {
    loading.value = false
  }
}

async function saveCategory() {
  saving.value = true
  formError.value = ''

  try {
    const payload = {
      ...newCategory.value,
      name: newCategory.value.name.trim(),
      slug: newCategory.value.slug.trim() || undefined,
      description: newCategory.value.description.trim() || null,
    }

    if (isEditMode.value) {
      await updateCategory(editingCategoryId.value, payload)
    } else {
      await createCategory(payload)
    }

    showAddForm.value = false
    isEditMode.value = false
    editingCategoryId.value = null

    newCategory.value = {
      name: '',
      slug: '',
      description: '',
      sort_order: 0,
      status: true,
      image: null,
      remove_image: false,
    }
    existingImageUrl.value = ''

    await loadCategories()
  } catch (error) {
    console.error('Category save failed:', error.response?.data || error)

    formError.value =
      error.response?.data?.message ||
      error.message ||
      'تعذر حفظ القسم.'
  } finally {
    saving.value = false
  }
}



  function editCategory(category) {
  editingCategoryId.value = category.id
  isEditMode.value = true

  newCategory.value = {
    name: category.name ?? '',
    slug: category.slug ?? '',
    description: category.description ?? '',
    sort_order: category.sort_order ?? 0,
    status: Boolean(category.status),
    image: null,
    remove_image: false,
  }

  existingImageUrl.value = category.image_url ?? category.image ?? ''
  formError.value = ''
  showAddForm.value = true
}

function openAddCategoryForm() {
  isEditMode.value = false
  editingCategoryId.value = null
  newCategory.value = {
    name: '',
    slug: '',
    description: '',
    sort_order: 0,
    status: true,
    image: null,
    remove_image: false,
  }
  existingImageUrl.value = ''
  formError.value = ''
  showAddForm.value = true
}

function handleImageChange(event) {
  newCategory.value.image = event.target.files?.[0] ?? null
  if (newCategory.value.image) {
    newCategory.value.remove_image = false
  }
}

async function handleDeleteCategory(category) {
  const confirmed = window.confirm(
    `هل أنت متأكد من حذف القسم "${category.name}"؟`
  )

  if (!confirmed) return

  try {
    const response = await deleteCategory(category.id)

    await loadCategories()

    window.alert(
      response?.message || 'تم حذف القسم بنجاح.'
    )
  } catch (error) {
    console.error('Category delete failed:', error.response?.data || error)

    const message =
      error.response?.data?.message ||
      'تعذر حذف القسم.'

    window.alert(message)
  }
}

onMounted(loadCategories)
</script>

<template>
  <section class="categories-page">
<header class="page-header">
  <div>
    <h1>إدارة الأقسام</h1>
    <p>عرض أقسام المتجر والتأكد من بياناتها.</p>
  </div>

  <div class="header-actions">
    <button class="refresh-button" @click="loadCategories">
      <i class="bi bi-arrow-clockwise"></i>
      تحديث القائمة
    </button>

    <button class="add-button" @click="openAddCategoryForm">
      <i class="bi bi-plus-lg"></i>
      إضافة قسم
    </button>
  </div>
</header>

    <p v-if="loading" class="status-message">
      جارٍ تحميل الأقسام...
    </p>

    <p v-else-if="errorMessage" class="error-message">
      {{ errorMessage }}
    </p>

    <div v-if="showAddForm" class="form-overlay">
  <form class="category-form" @submit.prevent="saveCategory">
    <h2>{{ isEditMode ? 'تعديل القسم' : 'إضافة قسم جديد' }}</h2>

    <p v-if="formError" class="error-message">
      {{ formError }}
    </p>

    <label>
      اسم القسم *
      <input
        v-model="newCategory.name"
        type="text"
        required
        maxlength="150"
        placeholder="مثال: الحقائب"
      />
    </label>

    <label>
      الرابط المختصر (Slug)
      <input
        v-model="newCategory.slug"
        type="text"
        maxlength="180"
        placeholder="يُنشأ تلقائيًا إذا تُرك فارغًا"
      />
    </label>

    <label>
      الوصف
      <textarea
        v-model="newCategory.description"
        rows="3"
        placeholder="وصف مختصر للقسم"
      />
    </label>

    <label>
      صورة القسم
      <input
        type="file"
        accept="image/*"
        @change="handleImageChange"
      />
    </label>

    <div v-if="existingImageUrl" class="image-preview">
      <img :src="existingImageUrl" alt="صورة القسم الحالية" />
      <label class="checkbox-label">
        <input v-model="newCategory.remove_image" type="checkbox" />
        إزالة الصورة الحالية
      </label>
    </div>

    <label>
      ترتيب العرض
      <input
        v-model.number="newCategory.sort_order"
        type="number"
        min="0"
      />
    </label>

    <label class="checkbox-label">
      <input v-model="newCategory.status" type="checkbox" />
      القسم نشط
    </label>

    <div class="form-actions">
      <button
        type="button"
        class="cancel-button"
        @click="showAddForm = false"
      >
        إلغاء
      </button>

      <button type="submit" class="add-button" :disabled="saving">
        {{ saving ? 'جارٍ الحفظ...' : isEditMode ? 'تعديل القسم' : 'حفظ القسم' }}
      </button>
    </div>
  </form>
</div>

    <div v-else-if="categories.length" class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>اسم القسم</th>
            <th>الرابط المختصر</th>
            <th>الحالة</th>
            <th>الإجراءات</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="category in categories" :key="category.id">
            <td>{{ category.id }}</td>
            <td>{{ category.name }}</td>
            <td>{{ category.slug }}</td>
            <td>
              <span
                :class="category.status ? 'status-active' : 'status-inactive'"
              >
                {{ category.status ? 'نشط' : 'غير نشط' }}
              </span>
            </td>
            <td>
              <button 
              type="button"
              class="edit-button" 
              @click="editCategory(category)">
                <i class="bi bi-pencil">تعديل</i>
              </button>
              <button 
              type="button" 
              class="delete-button" 
              @click="handleDeleteCategory(category)">
                <i class="bi bi-trash">حذف</i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <p v-else class="status-message">
      لا توجد أقسام لعرضها حاليًا.
    </p>
  </section>
</template>

<style scoped>
.categories-page {
  color: #383126;
}

.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 24px;
}

h1 {
  margin: 0 0 8px;
  font-size: 26px;
}

.page-header p {
  margin: 0;
  color: #81796d;
}

.refresh-button {
  padding: 11px 18px;
  border: 1px solid #c5a46d;
  border-radius: 9px;
  background: #c5a46d;
  color: white;
  cursor: pointer;
}

.table-wrapper {
  overflow-x: auto;
  border: 1px solid #e9e1d5;
  border-radius: 12px;
  background: #fffdf9;
}

table {
  width: 100%;
  border-collapse: collapse;
  text-align: right;
}

th,
td {
  padding: 16px;
  border-bottom: 1px solid #eee7dc;
}

th {
  background: #f6f0e7;
  font-weight: 600;
}

tbody tr:last-child td {
  border-bottom: none;
}

.status-active,
.status-inactive {
  display: inline-block;
  padding: 5px 10px;
  border-radius: 20px;
  font-size: 13px;
}

.status-active {
  background: #e5f2e7;
  color: #28663a;
}

.status-inactive {
  background: #f5e6e2;
  color: #963f31;
}

.status-message {
  padding: 24px;
  color: #81796d;
  text-align: center;
}

.error-message {
  padding: 16px;
  border-radius: 9px;
  background: #f8e8e5;
  color: #963f31;
}

@media (max-width: 600px) {
  .page-header {
    align-items: flex-start;
    flex-direction: column;
  }
}
.header-actions {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.add-button,
.cancel-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 11px 18px;
  border: 1px solid transparent;
  border-radius: 9px;
  font: inherit;
  cursor: pointer;
  transition: background 0.2s ease;
}

.add-button {
  background: #b9975b;
  color: #fff;
}

.add-button:hover {
  background: #a48349;
}

.add-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.cancel-button {
  background: #f3eee5;
  border-color: #e4d9c8;
  color: #554a3b;
}

.form-overlay {
  position: fixed;
  inset: 0;
  z-index: 1000;
  display: grid;
  place-items: center;
  padding: 20px;
  background: rgb(35 29 22 / 45%);
}

.category-form {
  width: 100%;
  max-width: 520px;
  max-height: 90vh;
  overflow-y: auto;
  padding: 28px;
  border: 1px solid #e9dfcf;
  border-radius: 16px;
  background: #fffdf9;
  box-shadow: 0 20px 60px rgb(0 0 0 / 15%);
}

.category-form h2 {
  margin: 0 0 22px;
  color: #383126;
  font-size: 22px;
}

.category-form > label:not(.checkbox-label) {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-bottom: 18px;
  color: #514738;
  font-size: 14px;
  font-weight: 600;
}

.category-form input:not([type='checkbox']),
.category-form textarea {
  width: 100%;
  box-sizing: border-box;
  padding: 12px;
  border: 1px solid #ded4c5;
  border-radius: 8px;
  outline: none;
  background: #fff;
  color: #383126;
  font: inherit;
}

.category-form input:focus,
.category-form textarea:focus {
  border-color: #b9975b;
  box-shadow: 0 0 0 3px rgb(185 151 91 / 15%);
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 9px;
  margin: 8px 0 22px;
  color: #514738;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding-top: 12px;
  border-top: 1px solid #eee7dc;
}

@media (max-width: 600px) {
  .category-form {
    padding: 20px;
  }

  .header-actions {
    width: 100%;
  }
}
</style>