<template>
  <div
    ref="lottieContainer"
    class="inline-flex items-center justify-center select-none"
    :class="[width, height]"
  ></div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch } from 'vue'

const props = withDefaults(
  defineProps<{
    animationPath?: string
    loop?: boolean
    autoplay?: boolean
    width?: string
    height?: string
  }>(),
  {
    animationPath: '/lottie/check.json',
    loop: true,
    autoplay: true,
    width: 'w-24',
    height: 'h-24',
  }
)

const lottieContainer = ref<HTMLDivElement | null>(null)
let animInstance: any = null

const loadAnimation = async () => {
  if (process.client && lottieContainer.value) {
    if (animInstance) {
      animInstance.destroy()
      animInstance = null
    }

    try {
      const lottieModule = await import('lottie-web')
      const lottie = lottieModule.default || lottieModule

      animInstance = lottie.loadAnimation({
        container: lottieContainer.value,
        renderer: 'svg',
        loop: props.loop,
        autoplay: props.autoplay,
        path: props.animationPath,
      })
    } catch (err) {
      console.warn('Lottie animation failed to load:', err)
    }
  }
}

watch(
  () => props.animationPath,
  () => {
    loadAnimation()
  }
)

onMounted(() => {
  loadAnimation()
})

onUnmounted(() => {
  if (animInstance) {
    animInstance.destroy()
    animInstance = null
  }
})
</script>
