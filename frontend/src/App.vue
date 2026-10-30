<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { Modal } from 'bootstrap'
import { useRoute } from 'vue-router'

import AppHeader from './components/AppHeader.vue'
import HeroSection from './components/HeroSection.vue'
import CategoryList from './components/CategoryList.vue'
import ProductCard from './components/ProductCard.vue'

import { getProducts } from './services/productService'
import { translate, translateProductText } from './i18n'

const products = ref([])
const loading = ref(true)
const error = ref(false)
const route = useRoute()
const isAdminRoute = computed(() => route.path.startsWith('/admin'))
const locale = ref('en')
const theme = ref('dark')

const search = ref('')
const activeCategory = ref('All')
const cart = ref([])
const t = (key, values) => translate(locale.value, key, values)

watch(
  locale,
  (value) => {
    document.documentElement.lang = value
    document.documentElement.dir = value === 'ar' ? 'rtl' : 'ltr'
  },
  { immediate: true },
)

watch(
  theme,
  (value) => {
    document.documentElement.dataset.theme = value

    try {
      window.localStorage.setItem('marketgames-theme', value)
    } catch (storageError) {
      console.error('Unable to save theme preference.', storageError)
    }
  },
)

watch(
  isAdminRoute,
  (value) => {
    document.title = value
      ? 'Admin Dashboard | Carlacard'
      : 'Market Games - Digital Marketplace'
  },
  { immediate: true },
)

const cartCount = computed(() =>
  cart.value.reduce((total, item) => total + item.quantity, 0),
)

const cartTotal = computed(() =>
  cart.value.reduce(
    (total, item) => total + item.price * item.quantity,
    0,
  ),
)

const filteredProducts = computed(() => {
  const query = search.value.trim().toLowerCase()

  return products.value.filter((product) => {
    const matchesCategory =
      activeCategory.value === 'All' ||
      product.category === activeCategory.value

    const matchesSearch =
      !query ||
      [
        product.title,
        product.subtitle,
        product.category,
        product.region,
      ].some((value) => value.toLowerCase().includes(query)) ||
      (locale.value === 'ar' &&
        [
          translateProductText(locale.value, product.title),
          translateProductText(locale.value, product.subtitle),
          translateProductText(locale.value, product.category),
          translateProductText(locale.value, product.region),
        ].some((value) => value.toLowerCase().includes(query)))

    return matchesCategory && matchesSearch
  })
})

const featuredProducts = computed(() =>
  filteredProducts.value.slice(0, 8),
)

function addToCart(product) {
  const existing = cart.value.find(
    (item) => item.id === product.id,
  )

  if (existing) {
    existing.quantity++
  } else {
    cart.value.push({
      id: product.id,
      title: product.title,
      price: product.price,
      quantity: 1,
    })
  }
}

function removeFromCart(productId) {
  cart.value = cart.value.filter(
    (item) => item.id !== productId,
  )
}

function selectCategory(category) {
  activeCategory.value = category

  document.getElementById('products')?.scrollIntoView({
    behavior: 'smooth',
    block: 'start',
  })
}

function toggleLanguage() {
  locale.value = locale.value === 'en' ? 'ar' : 'en'
}

function toggleTheme() {
  theme.value = theme.value === 'dark' ? 'light' : 'dark'
}

function openCart() {
  const modal = document.getElementById('cartModal')

  if (modal) {
    Modal.getOrCreateInstance(modal).show()
  }
}

function openLogin() {
  const modal = document.getElementById('loginModal')

  if (modal) {
    Modal.getOrCreateInstance(modal).show()
  }
}

async function loadProducts() {
  loading.value = true
  error.value = false

  try {
    const response = await getProducts()
    products.value = response.data
  } catch (err) {
    error.value = true
    console.error('Error loading products:', err)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  try {
    const savedTheme = window.localStorage.getItem('marketgames-theme')
    if (savedTheme === 'light' || savedTheme === 'dark') {
      theme.value = savedTheme
    }
  } catch (storageError) {
    console.error('Unable to load theme preference.', storageError)
  }

  document.documentElement.dataset.theme = theme.value
  if (!isAdminRoute.value) {
    loadProducts()
  }
})
</script>

<template>
  <RouterView v-if="isAdminRoute" />
  <div v-else class="store-app">
    <AppHeader
      :locale="locale"
      :theme="theme"
      :v-model:search="search"
      :cart-count="cartCount"
      @cart-click="openCart"
      @login-click="openLogin"
      @toggle-language="toggleLanguage"
      @toggle-theme="toggleTheme"
    />

    <main>
      <HeroSection :locale="locale" />

      <div class="container">
        <section class="trust-strip">
          <div>
            <i class="bi bi-lightning-charge"></i>
            <span>{{ t('trust.digital') }}</span>
          </div>
          <div>
            <i class="bi bi-shield-check"></i>
            <span>{{ t('trust.secure') }}</span>
          </div>
          <div>
            <i class="bi bi-globe2"></i>
            <span>{{ t('trust.global') }}</span>
          </div>
          <div>
            <i class="bi bi-headset"></i>
            <span>{{ t('trust.support') }}</span>
          </div>
        </section>

        <CategoryList
          :locale="locale"
          :active-category="activeCategory"
          @select="selectCategory"
        />

        <section id="products" class="products-section">
          <div class="section-heading">
            <div>
              <span class="section-kicker">{{ t('products.kicker') }}</span>
              <h2>
                {{ t('products.featured') }} <span>{{ t('products.titleAccent') }}</span>
              </h2>
            </div>

            <span class="section-note">
              {{ t('products.count', { count: filteredProducts.length }) }}
            </span>
          </div>

          <div v-if="loading" class="state-message">
            <div class="spinner-border text-light" role="status">
              <span class="visually-hidden">{{ t('products.loading') }}</span>
            </div>
            <p>{{ t('products.loading') }}</p>
          </div>

          <div v-else-if="error" class="state-message">
            <p>{{ t('products.loadError') }}</p>
            <button
              class="btn btn-neon"
              @click="loadProducts"
            >
              {{ t('common.tryAgain') }}
            </button>
          </div>

          <div
            v-else-if="featuredProducts.length"
            class="row g-4"
          >
            <div
              v-for="product in featuredProducts"
              :key="product.id"
              class="col-12 col-sm-6 col-lg-3"
            >
              <ProductCard
                :product="product"
                :locale="locale"
                @add-to-cart="addToCart"
              />
            </div>
          </div>

          <div v-else class="state-message">
            <i class="bi bi-search fs-2"></i>
            <p>{{ t('products.notFound') }}</p>
            <button
              class="btn btn-outline-light"
              @click="search = ''; activeCategory = 'All'"
            >
              {{ t('products.clearFilters') }}
            </button>
          </div>
        </section>

        <section id="deals" class="promo-banner">
          <div class="promo-content">
            <span class="section-kicker">{{ t('promo.kicker') }}</span>
            <h2>{{ t('promo.title') }} <span>{{ t('promo.highlight') }}</span></h2>
            <p>
              {{ t('promo.description') }}
            </p>
            <a href="#products" class="btn btn-neon">
              {{ t('promo.explore') }}
              <i class="bi bi-arrow-right"></i>
            </a>
          </div>

          <div class="promo-decoration">
            <i class="bi bi-controller"></i>
          </div>
        </section>
      </div>
    </main>

    <footer class="site-footer">
      <div class="container footer-content">
        <a class="brand-logo" href="#home">
          <img
            src="/images/carla.jpeg"
            alt="CarlaCard logo"
            class="brand-mark"
          />
          CARLACARD<span class="brand-dot">.</span>
        </a>

        <p>
          {{ t('footer.tagline') }}
          <span>{{ t('footer.demo') }}</span>
        </p>

        <span class="copyright">
          © {{ new Date().getFullYear() }} CARLACARD
        </span>
      </div>
    </footer>

    <!-- Demo cart modal -->
    <div
      class="modal fade"
      id="cartModal"
      tabindex="-1"
      aria-labelledby="cartModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content cart-modal">
          <div class="modal-header">
            <h5 class="modal-title" id="cartModalLabel">
              <i class="bi bi-bag me-2"></i>
              {{ t('cart.title', { count: cartCount }) }}
            </h5>

            <button
              type="button"
              class="btn-close btn-close-white"
              data-bs-dismiss="modal"
              :aria-label="t('cart.close')"
            ></button>
          </div>

          <div class="modal-body">
            <div v-if="!cart.length" class="empty-cart">
              <i class="bi bi-bag-x"></i>
              <p>{{ t('cart.empty') }}</p>
            </div>

            <div
              v-for="item in cart"
              :key="item.id"
              class="cart-item"
            >
              <div>
                <strong>{{ translateProductText(locale, item.title) }}</strong>
                <p>{{ t('cart.quantity', { count: item.quantity }) }}</p>
              </div>

              <div class="text-end">
                <strong>
                  ${{ (item.price * item.quantity).toFixed(2) }}
                </strong>
                <button
                  class="remove-item"
                  @click="removeFromCart(item.id)"
                >
                  {{ t('cart.remove') }}
                </button>
              </div>
            </div>
          </div>

          <div v-if="cart.length" class="modal-footer cart-footer">
            <div class="cart-total">
              <span>{{ t('cart.subtotal') }}</span>
              <strong>${{ cartTotal.toFixed(2) }}</strong>
            </div>
            <p class="demo-notice">
              {{ t('cart.demo') }}
            </p>
            <button
              class="btn btn-neon w-100"
              data-bs-dismiss="modal"
            >
              {{ t('cart.continue') }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <div
      class="modal fade"
      id="loginModal"
      tabindex="-1"
      aria-labelledby="loginModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content cart-modal">
          <div class="modal-header">
            <h5 class="modal-title" id="loginModalLabel">
              <i class="bi bi-person me-2"></i>
              {{ t('auth.login') }}
            </h5>
            <button
              type="button"
              class="btn-close btn-close-white"
              data-bs-dismiss="modal"
              :aria-label="t('cart.close')"
            ></button>
          </div>
          <div class="modal-body">
            <p class="login-notice">{{ t('auth.unavailable') }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>