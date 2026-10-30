<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'
import { translate } from '../i18n'

defineProps({
  locale: {
    type: String,
    default: 'en',
  },
})

const heroSlides = [
  {
    image: `${import.meta.env.BASE_URL}images/heroimg/slide-1.jpg`,
    copy: 'hero.slide.gaming',
  },
  {
    image: `${import.meta.env.BASE_URL}images/heroimg/slide-2.jpg`,
    copy: 'hero.slide.giftCards',
  },
    {
    image: `${import.meta.env.BASE_URL}images/heroimg/slide-3.jpg`,
    copy: 'hero.slide.giftCards',
  },

]
const activeSlide = ref(0)
let carouselTimer

function showSlide(index) {
  activeSlide.value = (index + heroSlides.length) % heroSlides.length
}

onMounted(() => {
  if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    carouselTimer = window.setInterval(() => {
      showSlide(activeSlide.value + 1)
    }, 5000)
  }
})

onBeforeUnmount(() => {
  window.clearInterval(carouselTimer)
})
</script>

<template>
  <section class="hero-section" id="home">
    <div class="container">
      <div class="hero-panel">
        <div class="hero-grid"></div>

        <div class="hero-art">
          <div class="hero-image-frame" aria-live="off">
            <div
              class="hero-image-track"
              :style="{ transform: `translateX(-${activeSlide * 100}%)` }"
            >
              <div v-for="(slide, index) in heroSlides" :key="slide.copy" class="hero-slide">
                <img :src="slide.image" :alt="`Hero slide ${index + 1}`" />
              </div>
            </div>
          </div>
        </div>

        <div
          class="hero-carousel-controls"
          role="group"
          :aria-label="translate(locale, 'hero.carouselLabel')"
        >
          <button
            type="button"
            class="hero-carousel-arrow hero-carousel-previous"
            :aria-label="translate(locale, 'hero.previousSlide')"
            @click="showSlide(activeSlide - 1)"
          >
            <i class="bi bi-chevron-left"></i>
          </button>

          <div class="hero-carousel-indicators">
            <button
              v-for="(_, index) in heroSlides"
              :key="index"
              type="button"
              class="hero-carousel-dot"
              :class="{ 'is-active': activeSlide === index }"
              :aria-label="translate(locale, 'hero.goToSlide', { number: index + 1 })"
              :aria-current="activeSlide === index ? 'true' : undefined"
              @click="showSlide(index)"
            ></button>
          </div>

          <button
            type="button"
            class="hero-carousel-arrow hero-carousel-next"
            :aria-label="translate(locale, 'hero.nextSlide')"
            @click="showSlide(activeSlide + 1)"
          >
            <i class="bi bi-chevron-right"></i>
          </button>
        </div>
      </div>
    </div>
  </section>
</template>