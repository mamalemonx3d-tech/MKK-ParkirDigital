<template>
  <div class="flex items-center justify-center min-h-[60vh] text-slate-500 text-sm">
    <div class="flex items-center gap-2">
      <Loader2 class="w-5 h-5 animate-spin text-[#3b82f6]" />
      <span>Mengalihkan ke Pos Kasir Keluar...</span>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Loader2 } from 'lucide-vue-next'

definePageMeta({
  layout: 'petugas',
  middleware: 'auth',
})

await navigateTo('/petugas/keluar', { replace: true })
</script>
