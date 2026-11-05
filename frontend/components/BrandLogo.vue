<template>
  <div class="inline-flex flex-col items-start select-none">
    <!-- Compact Mode (For Collapsed Sidebar) -->
    <div
      v-if="compact"
      class="w-10 h-10 rounded-xl bg-[#f59e0b] text-slate-950 flex items-center justify-center font-black text-lg shadow-xs"
      title="Secure Parking"
    >
      <span class="font-serif">P</span>
    </div>

    <!-- Full Mode -->
    <template v-else>
      <div
        class="inline-flex items-center transition"
        :class="[
          isDarkSurface
            ? 'py-0.5'
            : 'bg-[#1e293b] px-3.5 py-1.5 rounded-xl shadow-xs border border-slate-700'
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
        class="text-[10px] font-bold tracking-wider uppercase mt-1 pl-0.5"
        :class="isDarkSurface ? 'text-[#f59e0b]' : 'text-slate-600'"
      >
        {{ subtitle || 'Sistem Parkir Digital' }}
      </span>
    </template>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = withDefaults(
  defineProps<{
    subtitle?: string
    showSubtitle?: boolean
    isDarkSurface?: boolean
    compact?: boolean
    size?: 'sm' | 'md' | 'lg' | 'xl'
  }>(),
  {
    subtitle: '',
    showSubtitle: false,
    isDarkSurface: false,
    compact: false,
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
