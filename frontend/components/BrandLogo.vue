<template>
  <div class="inline-flex flex-col items-start select-none">
    <!-- Logo Badge / Transparent Image -->
    <div
      class="inline-flex items-center transition"
      :class="[
        isDarkSurface
          ? 'py-0.5'
          : 'bg-[#001a4e] px-3.5 py-1.5 rounded-xl border border-slate-700/50 shadow-sm'
      ]"
    >
      <img
        src="/logo.webp"
        alt="Secure Parking"
        class="w-auto object-contain transition-all"
        :class="sizeClass"
        loading="eager"
      />
    </div>

    <!-- Optional Subtitle -->
    <span
      v-if="showSubtitle"
      class="text-[10px] font-bold tracking-widest uppercase mt-1 pl-0.5"
      :class="isDarkSurface ? 'text-slate-300' : 'text-slate-600'"
    >
      {{ subtitle || 'Sistem Parkir Digital' }}
    </span>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = withDefaults(
  defineProps<{
    subtitle?: string
    showSubtitle?: boolean
    isDarkSurface?: boolean
    size?: 'sm' | 'md' | 'lg' | 'xl'
  }>(),
  {
    showSubtitle: false,
    isDarkSurface: false,
    size: 'md',
  }
)

const sizeClass = computed(() => {
  switch (props.size) {
    case 'sm':
      return 'h-7'
    case 'lg':
      return 'h-11'
    case 'xl':
      return 'h-14'
    case 'md':
    default:
      return 'h-9'
  }
})
</script>
