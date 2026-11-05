<template>
  <div class="h-screen w-screen overflow-hidden flex flex-col md:flex-row bg-[#f8fafc] text-[#0f172a] font-sans">
    <!-- ========================================== -->
    <!-- 1. MOBILE BACKDROP OVERLAY                 -->
    <!-- ========================================== -->
    <div
      v-if="isMobileMenuOpen"
      @click="isMobileMenuOpen = false"
      class="fixed inset-0 bg-slate-950/60 backdrop-blur-2xs z-50 md:hidden transition-opacity"
    ></div>

    <!-- ========================================== -->
    <!-- 2. SIDEBAR (DESKTOP FIXED & MOBILE DRAWER) -->
    <!-- ========================================== -->
    <aside
      class="fixed inset-y-0 left-0 z-50 w-72 md:w-64 bg-[#1e293b] text-white flex flex-col shrink-0 h-screen border-r border-slate-700/60 transition-transform duration-300 ease-in-out md:static md:translate-x-0"
      :class="isMobileMenuOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-full md:translate-x-0'"
    >
      <!-- Brand Header -->
      <div class="p-5 border-b border-slate-700/60 flex items-center justify-between">
        <BrandLogo :showSubtitle="true" subtitle="Super Admin Panel" :isDarkSurface="true" size="sm" />
        <!-- Close button on mobile -->
        <button
          @click="isMobileMenuOpen = false"
          type="button"
          class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700/60 md:hidden cursor-pointer"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <!-- Navigation Links (Scrollable if menu is long) -->
      <nav class="flex-1 p-3 space-y-1 overflow-y-auto">
        <NuxtLink
          to="/admin/dashboard"
          @click="isMobileMenuOpen = false"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition"
          :class="$route.path === '/admin/dashboard' ? 'bg-[#f59e0b] text-slate-950 shadow-xs' : 'text-slate-300 hover:bg-slate-700/60 hover:text-white'"
        >
          <LayoutDashboard class="w-4 h-4 shrink-0" />
          <span>Dashboard Analitik</span>
        </NuxtLink>

        <NuxtLink
          to="/admin/users"
          @click="isMobileMenuOpen = false"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition"
          :class="$route.path.startsWith('/admin/users') ? 'bg-[#f59e0b] text-slate-950 shadow-xs' : 'text-slate-300 hover:bg-slate-700/60 hover:text-white'"
        >
          <Users class="w-4 h-4 shrink-0" />
          <span>Manajemen Petugas</span>
        </NuxtLink>

        <NuxtLink
          to="/admin/rekap"
          @click="isMobileMenuOpen = false"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition"
          :class="$route.path === '/admin/rekap' ? 'bg-[#f59e0b] text-slate-950 shadow-xs' : 'text-slate-300 hover:bg-slate-700/60 hover:text-white'"
        >
          <FileText class="w-4 h-4 shrink-0" />
          <span>Laporan & Rekapitulasi</span>
        </NuxtLink>

        <NuxtLink
          to="/admin/profile"
          @click="isMobileMenuOpen = false"
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
          <NuxtLink
            to="/admin/profile"
            @click="isMobileMenuOpen = false"
            class="truncate pr-2 hover:opacity-85 transition cursor-pointer flex-1"
          >
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

    <!-- ========================================== -->
    <!-- 3. MAIN CONTENT AREA (H-SCREEN FIXED BODY)  -->
    <!-- ========================================== -->
    <div class="flex-1 h-screen flex flex-col min-w-0 overflow-hidden">
      <!-- Topbar Header -->
      <header class="bg-white border-b border-slate-200 h-16 px-4 sm:px-6 flex items-center justify-between shrink-0 z-30 shadow-2xs">
        <div class="flex items-center gap-3">
          <!-- Mobile Hamburger Toggle Button -->
          <button
            @click="isMobileMenuOpen = true"
            type="button"
            class="p-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 transition md:hidden cursor-pointer"
            title="Buka Menu"
          >
            <Menu class="w-5 h-5 text-slate-800" />
          </button>

          <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-[#3b82f6] animate-pulse"></span>
            <h1 class="text-xs sm:text-sm font-bold text-[#0f172a] font-sans tracking-tight truncate max-w-[200px] sm:max-w-none">
              Sistem Informasi Pengelolaan Parkir Digital
            </h1>
          </div>
        </div>

        <div class="flex items-center gap-3 text-xs font-mono text-slate-700">
          <span class="bg-slate-50 px-2.5 sm:px-3 py-1.5 rounded-lg border border-slate-200 font-bold text-[10px] sm:text-[11px] truncate">
            {{ currentFormattedTime }}
          </span>
        </div>
      </header>

      <!-- Scrollable Main Page Content (Only this scrolls) -->
      <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 bg-[#f8fafc]">
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
  User,
  LogOut,
  Menu,
  X,
} from 'lucide-vue-next'
import BrandLogo from '~/components/BrandLogo.vue'

const auth = useAuth()
const isMobileMenuOpen = ref(false)

const currentFormattedTime = ref('')
let timer: any = null

const updateClock = () => {
  const now = new Date()
  currentFormattedTime.value = now.toLocaleDateString('id-ID', {
    weekday: 'short',
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

onMounted(() => {
  updateClock()
  timer = setInterval(updateClock, 1000)
})

onUnmounted(() => {
  if (timer) clearInterval(timer)
})
</script>
