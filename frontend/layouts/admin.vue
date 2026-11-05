<template>
  <div class="min-h-screen bg-[#f8fafc] flex flex-col md:flex-row text-[#0f172a] font-sans">
    <!-- Sidebar Navigation with #1E293B Solid Slate Blue -->
    <aside class="w-full md:w-64 bg-[#1e293b] text-white flex flex-col shrink-0 md:min-h-screen border-r border-slate-700/60">
      <!-- Brand Header -->
      <div class="p-5 border-b border-slate-700/60 flex items-center justify-between">
        <BrandLogo :showSubtitle="true" subtitle="Super Admin Panel" :isDarkSurface="true" size="sm" />
      </div>

      <!-- Navigation Links -->
      <nav class="flex-1 p-3 space-y-1 overflow-y-auto">
        <NuxtLink
          to="/admin/dashboard"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition"
          :class="$route.path === '/admin/dashboard' ? 'bg-[#f59e0b] text-slate-950 shadow-xs' : 'text-slate-300 hover:bg-slate-700/60 hover:text-white'"
        >
          <LayoutDashboard class="w-4 h-4 shrink-0" />
          <span>Dashboard Analitik</span>
        </NuxtLink>

        <NuxtLink
          to="/admin/users"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition"
          :class="$route.path.startsWith('/admin/users') ? 'bg-[#f59e0b] text-slate-950 shadow-xs' : 'text-slate-300 hover:bg-slate-700/60 hover:text-white'"
        >
          <Users class="w-4 h-4 shrink-0" />
          <span>Manajemen Petugas</span>
        </NuxtLink>

        <NuxtLink
          to="/admin/rekap"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition"
          :class="$route.path === '/admin/rekap' ? 'bg-[#f59e0b] text-slate-950 shadow-xs' : 'text-slate-300 hover:bg-slate-700/60 hover:text-white'"
        >
          <FileText class="w-4 h-4 shrink-0" />
          <span>Laporan & Rekapitulasi</span>
        </NuxtLink>

        <NuxtLink
          to="/admin/profile"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition"
          :class="$route.path === '/admin/profile' ? 'bg-[#f59e0b] text-slate-950 shadow-xs' : 'text-slate-300 hover:bg-slate-700/60 hover:text-white'"
        >
          <User class="w-4 h-4 shrink-0" />
          <span>Kelola Profil Akun</span>
        </NuxtLink>
      </nav>

      <!-- User Profile & Logout Bottom Bar -->
      <div class="p-4 border-t border-slate-700/60 bg-slate-900/40">
        <div class="flex items-center justify-between">
          <NuxtLink to="/admin/profile" class="truncate pr-2 hover:opacity-85 transition cursor-pointer flex-1">
            <p class="text-xs font-bold text-white truncate">{{ auth.user.value?.name || 'Administrator' }}</p>
            <span class="inline-block text-[10px] font-bold text-[#f59e0b] uppercase tracking-wider mt-0.5">
              Super Admin
            </span>
          </NuxtLink>
          <button
            @click="auth.logout"
            title="Keluar dari akun"
            class="p-2 rounded-xl bg-slate-700/60 hover:bg-slate-700 text-slate-200 transition cursor-pointer"
          >
            <LogOut class="w-4 h-4" />
          </button>
        </div>
      </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0">
      <!-- Topbar Header -->
      <header class="bg-white border-b border-slate-200 h-16 px-6 flex items-center justify-between sticky top-0 z-30 shadow-2xs">
        <div class="flex items-center gap-2">
          <span class="w-2 h-2 rounded-full bg-[#3b82f6] animate-pulse"></span>
          <h1 class="text-sm font-bold text-[#0f172a] font-sans tracking-tight">
            Sistem Informasi Pengelolaan Parkir Digital
          </h1>
        </div>

        <div class="flex items-center gap-4 text-xs font-mono text-slate-700">
          <span class="bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200 font-bold text-[11px]">
            {{ currentFormattedTime }}
          </span>
        </div>
      </header>

      <!-- Page Content Canvas -->
      <main class="flex-1 p-4 sm:p-6 overflow-y-auto bg-[#f8fafc]">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import {
  LayoutDashboard,
  Users,
  FileText,
  LogOut,
  User,
} from 'lucide-vue-next'
import BrandLogo from '~/components/BrandLogo.vue'

const auth = useAuth()
onMounted(() => {
  auth.initAuth()
})

const currentFormattedTime = ref('')
let timer: any = null

const updateClock = () => {
  const now = new Date()
  currentFormattedTime.value =
    now.toLocaleDateString('id-ID', {
      weekday: 'short',
      day: 'numeric',
      month: 'short',
      year: 'numeric',
    }) +
    ' - ' +
    now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' })
}

onMounted(() => {
  updateClock()
  timer = setInterval(updateClock, 1000)
})

onUnmounted(() => {
  if (timer) clearInterval(timer)
})
</script>
