<script setup>
import { translate } from '../i18n'

defineProps({
  locale: {
    type: String,
    default: 'en',
  },
  category: {
    type: Object,
    required: true,
  },
  activeCategory: {
    type: String,
    default: 'All',
  },
})

const emit = defineEmits(['select'])
</script>

<template>
  <button
    type="button"
    class="category-chip"
    :class="{
      selected: activeCategory === category.name,
      'has-image': category.image,
    }"
    @click="emit('select')"
  >
    <span class="category-image-wrap">
      <img v-if="category.image" class="category-image" :src="category.image" alt="" />
      <i v-else :class="`bi ${category.icon}`"></i>
    </span>
    <span class="category-name">{{ translate(locale, `category.${category.name}`) }}</span>
  </button>
</template>
