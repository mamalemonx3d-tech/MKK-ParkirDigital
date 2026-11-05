<template>
  <div class="min-h-screen bg-[#f8fafc] flex flex-col text-slate-900 font-sans">
    <!-- Top Navigation Header -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-2xs">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <NuxtLink to="/" class="flex items-center gap-3">
          <BrandLogo :showSubtitle="true" subtitle="Sistem Operasional Parkir" size="sm" />
        </NuxtLink>

        <nav class="flex items-center gap-3">
          <NuxtLink
            to="/user"
            class="px-3.5 py-1.5 text-xs font-bold rounded-xl border border-slate-300 text-slate-700 hover:border-[#0066ff] hover:text-[#0066ff] transition"
          >
            Kiosk Tiket Masuk
          </NuxtLink>
          <NuxtLink
            to="/login"
            class="px-4 py-1.5 text-xs font-bold rounded-xl bg-[#0066ff] text-white hover:bg-[#0052cc] transition shadow-xs"
          >
            Portal Staf
          </NuxtLink>
        </nav>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col">
      <slot />
    </main>

    <!-- Minimalist Footer -->
    <footer class="bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-500">
      <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-2">
        <span class="font-medium">Sistem Parkir Digital &mdash; Mode Operasional Standar</span>
        <span class="font-mono text-[11px] text-slate-400">Versi 1.0.0</span>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import BrandLogo from '~/components/BrandLogo.vue'
</script>
