<script setup>
import { translate } from '../i18n'

const props = defineProps({
  locale: {
    type: String,
    default: 'en',
  },
  theme: {
    type: String,
    default: 'dark',
  },
  cartCount: {
    type: Number,
    default: 0,
  },
  search: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['update:search', 'cart-click', 'login-click', 'toggle-language', 'toggle-theme'])
const t = (key) => translate(props.locale, key)
</script>

<template>
  <header class="site-header">
    <nav class="navbar navbar-expand-lg">
      <div class="container">
        <a class="navbar-brand brand-logo" href="#home">
          <img
            src="/images/carla.jpeg"
            alt="CarlaCard logo"
            class="brand-mark"
          />
          CARLACARD<span class="brand-dot">.</span>
        </a>

        <button
          class="navbar-toggler custom-toggler"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#mainNavigation"
          aria-controls="mainNavigation"
          aria-expanded="false"
          :aria-label="t('nav.toggle')"
        >
          <i class="bi bi-list"></i>
        </button>

        <div class="collapse navbar-collapse" id="mainNavigation">
          <div class="navbar-nav mx-auto gap-lg-3">
            <a class="nav-link active" href="#home">{{ t('nav.home') }}</a>
            <a class="nav-link" href="#categories">{{ t('nav.categories') }}</a>
            <a class="nav-link" href="#products">{{ t('nav.products') }}</a>
            <a class="nav-link" href="#deals">{{ t('nav.deals') }}</a>
          </div>

          <div class="header-actions">
            <label class="header-search">
              <i class="bi bi-search"></i>
              <input
                :value="search"
                @input="emit('update:search', $event.target.value)"
                type="search"
                :placeholder="t('search.placeholder')"
                :aria-label="t('search.label')"
              />
              <kbd>/</kbd>
            </label>

            <button
              class="login-button"
              type="button"
              @click="emit('login-click')"
            >
              <i class="bi bi-person"></i>
              {{ t('auth.login') }}
            </button>

            <button
              class="language-button"
              type="button"
              :aria-label="t('language.aria')"
              @click="emit('toggle-language')"
            >
              {{ t('language.switch') }}
            </button>

            <button
              class="theme-button"
              type="button"
              :aria-label="t(theme === 'dark' ? 'theme.switchToLight' : 'theme.switchToDark')"
              :aria-pressed="theme === 'light'"
              @click="emit('toggle-theme')"
            >
              <i :class="theme === 'dark' ? 'bi bi-sun' : 'bi bi-moon-stars'"></i>
            </button>

            <button
              class="cart-button"
              type="button"
              :aria-label="t('cart.open')"
              @click="emit('cart-click')"
            >
              <i class="bi bi-bag"></i>
              <span class="cart-count">{{ cartCount }}</span>
            </button>
          </div>
        </div>
      </div>
    </nav>
  </header>
</template>