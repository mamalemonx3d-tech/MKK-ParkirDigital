<template>
  <div class="min-h-screen bg-slate-100 flex flex-col text-slate-800">
    <!-- Top Navigation Header -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <NuxtLink to="/" class="flex items-center gap-3">
          <BrandLogo :showSubtitle="true" subtitle="Sistem Operasional Parkir" />
        </NuxtLink>

        <nav class="flex items-center gap-2">
          <NuxtLink
            to="/user"
            class="px-3 py-1.5 text-xs font-semibold rounded-md border border-slate-300 text-slate-700 hover:bg-slate-50 transition"
          >
            Kiosk Tiket Masuk
          </NuxtLink>
          <NuxtLink
            to="/login"
            class="px-3.5 py-1.5 text-xs font-semibold rounded-md bg-[#001a4e] text-white hover:bg-[#001033] transition"
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
        <span>Sistem Parkir Digital &mdash; Mode Operasional Standar</span>
        <span class="font-mono text-[11px] text-slate-400">Versi 1.0.0</span>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import BrandLogo from '~/components/BrandLogo.vue'
</script>
