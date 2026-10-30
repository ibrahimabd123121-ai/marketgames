<script setup>
import { translate, translateProductText } from '../i18n'

defineProps({
  locale: {
    type: String,
    default: 'en',
  },
  product: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['add-to-cart'])
</script>

<template>
  <article class="product-card">
    <div class="product-image-wrap">
      <img
        class="product-image"
        :src="product.image"
        :alt="translateProductText(locale, product.title)"
        loading="lazy"
      />

      <span
        v-if="product.badge"
        class="product-badge"
        :class="`badge-${product.badge.toLowerCase()}`"
      >
        {{ translateProductText(locale, product.badge) }}
      </span>

      <span class="product-region">
        <i class="bi bi-globe2"></i>
        {{ translateProductText(locale, product.region) }}
      </span>

      <button
        class="quick-add"
        type="button"
        :aria-label="translate(locale, 'product.addToCart', { title: translateProductText(locale, product.title) })"
        @click="emit('add-to-cart', product)"
      >
        <i class="bi bi-plus-lg"></i>
      </button>
    </div>

    <div class="product-info">
      <div class="product-category">
        {{ translateProductText(locale, product.category) }}
      </div>

      <h3>{{ translateProductText(locale, product.title) }}</h3>
      <p class="product-subtitle">{{ translateProductText(locale, product.subtitle) }}</p>

      <div class="product-bottom">
        <div class="product-price">
          <strong>${{ product.price.toFixed(2) }}</strong>

          <del v-if="product.oldPrice">
            ${{ product.oldPrice.toFixed(2) }}
          </del>
        </div>

        <button
          class="add-button"
          type="button"
          @click="emit('add-to-cart', product)"
        >
          {{ translate(locale, 'product.add') }} <i class="bi bi-plus-lg"></i>
        </button>
      </div>
    </div>
  </article>
</template>