import api from './api'

export async function getCategories() {
  const response = await api.get('/categories')

  console.log('Categories API response:', response.data)

  const categories = response.data?.data

  if (!Array.isArray(categories)) {
    throw new Error('Categories API did not return an array')
  }

  return categories
}

  
function categoryFormData(category, updating = false) {
  const formData = new FormData()

  Object.entries(category).forEach(([key, value]) => {
    if (key === 'image' || key === 'remove_image' || value === undefined) {
      return
    }

    formData.append(key, value === null ? '' : String(value))
  })

  if (category.image instanceof File) {
    formData.append('image', category.image)
  }

  if (category.remove_image) {
    formData.append('remove_image', '1')
  }

  if (updating) {
    formData.append('_method', 'PUT')
  }

  return formData
}

export async function createCategory(category) {
  const response = await api.post('/categories', categoryFormData(category))
  return response.data
}

export async function updateCategory(id, category) {
  const response = await api.post(
    `/categories/${id}`,
    categoryFormData(category, true),
  )
  return response.data
}

export async function deleteCategory(id) {
  const response = await api.delete(`/categories/${id}`)
  return response.data
}