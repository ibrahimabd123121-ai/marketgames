import api from './api'

function mapProduct(product) {
  const isActive =
    product.status === true || product.status === 1 || product.status === '1'

  return {
    id: product.id,
    name: product.name,
    slug: product.slug ?? '',
    description: product.description ?? '',
    category_id: product.category_id,
    status: isActive,
    title: product.name,
    subtitle: product.description ?? '',
    category: product.category?.name ?? 'Uncategorized',
    image: product.image_url ?? product.image ?? '',
    price: Number(product.price ?? 0),
    stock: Number(product.stock ?? 0),
    badge: isActive ? '' : 'Unavailable',
    region: '',
  }
}

export async function getProducts(page = 1) {
  const response = await api.get('/products', {
    params: { page },
  })

  const products = response.data?.data

  if (!Array.isArray(products)) {
    throw new Error('products api did not return an array')
  }

  return {
    ...response.data,
    data: products.map(mapProduct),
  }
}

function productFormData(product, updating = false) {
  const formData = new FormData()
  const fields = {
    category_id: Number(product.category_id),
    name: product.name,
    price: Number(product.price),
    stock: Number(product.stock),
    status: product.status ? '1' : '0',
    description: product.description?.trim() ?? '',
  }

  if (product.slug?.trim()) {
    fields.slug = product.slug.trim()
  }

  Object.entries(fields).forEach(([key, value]) => {
    formData.append(key, String(value))
  })

  if (product.image instanceof File) {
    formData.append('image', product.image)
  }

  if (product.remove_image) {
    formData.append('remove_image', '1')
  }

  if (updating) {
    formData.append('_method', 'PUT')
  }

  return formData
}

export async function createProduct(product) {
  const response = await api.post('/products', productFormData(product))

  return response.data
}

export async function updateProduct(id, product) {
  const response = await api.post(
    `/products/${id}`,
    productFormData(product, true),
  )

  return response.data
}

export async function deleteProduct(id) {
  const response = await api.delete(`/products/${id}`)
  return response.data
}