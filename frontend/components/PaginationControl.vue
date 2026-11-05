<template>
  <div
    v-if="totalItems > 0"
    class="px-4 py-3 border-t border-slate-200/80 bg-white flex flex-col sm:flex-row items-center justify-between gap-3 text-xs select-none"
  >
    <!-- Summary Info -->
    <div class="text-slate-500 text-xs">
      Menampilkan
      <strong class="font-bold text-slate-800">{{ startItem }}</strong>
      -
      <strong class="font-bold text-slate-800">{{ endItem }}</strong>
      dari
      <strong class="font-bold text-slate-800">{{ totalItems }}</strong>
      data
    </div>

    <!-- Page Controls -->
    <div class="flex items-center gap-1.5">
      <!-- Previous Button -->
      <button
        type="button"
        @click="goToPage(currentPage - 1)"
        :disabled="currentPage <= 1"
        title="Halaman Sebelumnya"
        class="p-2 sm:px-3 sm:py-1.5 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50 transition cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-1 font-semibold"
      >
        <ChevronLeft class="w-4 h-4" />
        <span class="hidden sm:inline">Sebelumnya</span>
      </button>

      <!-- Mobile Page Indicator -->
      <div class="sm:hidden px-3 py-1 bg-slate-50 border border-slate-200 rounded-lg font-mono font-bold text-slate-800">
        {{ currentPage }} / {{ totalPages }}
      </div>

      <!-- Desktop Page Numbers -->
      <div class="hidden sm:flex items-center gap-1">
        <template v-for="(p, idx) in visiblePages" :key="'p-' + idx">
          <span
            v-if="p === '...'"
            class="px-2 py-1 text-slate-400 font-bold"
          >
            ...
          </span>
          <button
            v-else
            type="button"
            @click="goToPage(Number(p))"
            class="w-8 h-8 rounded-lg font-mono text-xs font-bold transition cursor-pointer flex items-center justify-center"
            :class="
              currentPage === p
                ? 'bg-[#3b82f6] text-white shadow-2xs'
                : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 border border-transparent hover:border-slate-200'
            "
          >
            {{ p }}
          </button>
        </template>
      </div>

      <!-- Next Button -->
      <button
        type="button"
        @click="goToPage(currentPage + 1)"
        :disabled="currentPage >= totalPages"
        title="Halaman Selanjutnya"
        class="p-2 sm:px-3 sm:py-1.5 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50 transition cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-1 font-semibold"
      >
        <span class="hidden sm:inline">Selanjutnya</span>
        <ChevronRight class="w-4 h-4" />
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { ChevronLeft, ChevronRight } from 'lucide-vue-next'

const props = withDefaults(
  defineProps<{
    currentPage: number
    totalItems: number
    itemsPerPage?: number
  }>(),
  {
    itemsPerPage: 10,
  }
)

const emit = defineEmits<{
  (e: 'update:currentPage', page: number): void
}>()

const totalPages = computed(() => {
  return Math.max(1, Math.ceil(props.totalItems / props.itemsPerPage))
})

const startItem = computed(() => {
  if (props.totalItems === 0) return 0
  return (props.currentPage - 1) * props.itemsPerPage + 1
})

const endItem = computed(() => {
  return Math.min(props.totalItems, props.currentPage * props.itemsPerPage)
})

const goToPage = (page: number) => {
  if (page >= 1 && page <= totalPages.value && page !== props.currentPage) {
    emit('update:currentPage', page)
  }
}

// Generate smart visible page range with ellipsis
const visiblePages = computed(() => {
  const total = totalPages.value
  const current = props.currentPage
  const delta = 1
  const range: (number | string)[] = []

  for (let i = 1; i <= total; i++) {
    if (i === 1 || i === total || (i >= current - delta && i <= current + delta)) {
      range.push(i)
    } else if (range[range.length - 1] !== '...') {
      range.push('...')
    }
  }

  return range
})
</script>
