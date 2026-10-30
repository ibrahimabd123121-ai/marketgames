<script setup>
import { translate } from '../i18n'
import CategoryCard from './CategoryCard.vue'
import accountsImage from '../images/productimg/accounts.jpg'
import gamesImage from '../images/productimg/games.jpg'
import giftcardImage from '../images/productimg/giftcard.jpg'
import microsoftImage from '../images/productimg/micrsoft.jpg'
import mtnImage from '../images/productimg/mtn.jpg'
import moviesImage from '../images/productimg/movies.jpg'
import numbersImage from '../images/productimg/numbers.jpg'
import paymentImage from '../images/productimg/pyment.jpg'
import socialImage from '../images/productimg/social.jpg'
import subscriptionImage from '../images/productimg/subscrion.jpg'
import syPaymentImage from '../images/productimg/sypayment.jpg'

defineProps({
  locale: {
    type: String,
    default: 'en',
  },
  activeCategory: {
    type: String,
    default: 'All',
  },
})

const emit = defineEmits(['select'])

const categories = [
  { name: 'All', icon: 'bi-grid-fill' },
  { name: 'accounts', icon: 'bi-nintendo-switch', image: accountsImage },
  { name: 'games', icon: 'bi-pc-display', image: gamesImage },
  { name: 'giftcard', icon: 'bi-stars', image: giftcardImage },
  { name: 'microsoft', icon: 'bi-gift', image: microsoftImage },
  { name: 'mtn', icon: 'bi-google', image: mtnImage },
  { name: 'movies', icon: 'bi-xbox', image: moviesImage },
  { name: 'numbers', icon: 'bi-xbox', image: numbersImage },
  { name: 'payment', icon: 'bi-xbox', image: paymentImage },
  { name: 'social', icon: 'bi-xbox', image: socialImage },
  { name: 'subscrion', icon: 'bi-xbox', image: subscriptionImage },
  { name: 'sypayment', icon: 'bi-xbox', image: syPaymentImage },
]
</script>

<template>
  <section id="categories" class="categories-section">
    <div class="section-heading">
      <div>
        <span class="section-kicker">{{ translate(locale, 'categories.kicker') }}</span>
        <h2>{{ translate(locale, 'categories.browse') }} <span>{{ translate(locale, 'nav.categories') }}</span></h2>
      </div>

      <span class="section-note">{{ translate(locale, 'categories.choose') }}</span>
    </div>

    <div class="category-list">
      <CategoryCard
        v-for="category in categories"
        :key="category.name"
        :locale="locale"
        :category="category"
        :active-category="activeCategory"
        @select="emit('select', $event)"
      />
    </div>
  </section>
</template>