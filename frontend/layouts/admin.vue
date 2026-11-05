<template>
  <div class="min-h-screen bg-[#f8fafc] flex flex-col md:flex-row text-slate-900 font-sans">
    <!-- Sidebar Navigation with Aqua 00FFFF Scheme -->
    <aside class="w-full md:w-64 bg-[#00ffff] text-slate-950 flex flex-col shrink-0 md:min-h-screen border-r border-cyan-400/60">
      <!-- Brand Header -->
      <div class="p-5 border-b border-slate-900/15 flex items-center justify-between">
        <BrandLogo :showSubtitle="true" subtitle="Super Admin Panel" :isDarkSurface="false" size="sm" />
      </div>

      <!-- Navigation Links -->
      <nav class="flex-1 p-3 space-y-1 overflow-y-auto">
        <NuxtLink
          to="/admin/dashboard"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition"
          :class="$route.path === '/admin/dashboard' ? 'bg-[#ffe600] text-slate-950 shadow-xs border border-slate-900/15' : 'text-slate-800 hover:bg-black/10 hover:text-slate-950'"
        >
          <LayoutDashboard class="w-4 h-4 shrink-0 text-slate-900" />
          <span>Dashboard Analitik</span>
        </NuxtLink>

        <NuxtLink
          to="/admin/users"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition"
          :class="$route.path.startsWith('/admin/users') ? 'bg-[#ffe600] text-slate-950 shadow-xs border border-slate-900/15' : 'text-slate-800 hover:bg-black/10 hover:text-slate-950'"
        >
          <Users class="w-4 h-4 shrink-0 text-slate-900" />
          <span>Manajemen Petugas</span>
        </NuxtLink>

        <NuxtLink
          to="/admin/member"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition"
          :class="$route.path === '/admin/member' ? 'bg-[#ffe600] text-slate-950 shadow-xs border border-slate-900/15' : 'text-slate-800 hover:bg-black/10 hover:text-slate-950'"
        >
          <CreditCard class="w-4 h-4 shrink-0 text-slate-900" />
          <span>Data Langganan Member</span>
        </NuxtLink>

        <NuxtLink
          to="/admin/rekap"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition"
          :class="$route.path === '/admin/rekap' ? 'bg-[#ffe600] text-slate-950 shadow-xs border border-slate-900/15' : 'text-slate-800 hover:bg-black/10 hover:text-slate-950'"
        >
          <FileText class="w-4 h-4 shrink-0 text-slate-900" />
          <span>Laporan & Rekapitulasi</span>
        </NuxtLink>

        <NuxtLink
          to="/admin/profile"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition"
          :class="$route.path === '/admin/profile' ? 'bg-[#ffe600] text-slate-950 shadow-xs border border-slate-900/15' : 'text-slate-800 hover:bg-black/10 hover:text-slate-950'"
        >
          <User class="w-4 h-4 shrink-0 text-slate-900" />
          <span>Kelola Profil Akun</span>
        </NuxtLink>

        <div class="pt-4 pb-1 px-3 text-[10px] font-bold text-slate-900 tracking-wider uppercase">
          Akses Operasional
        </div>

        <NuxtLink
          to="/petugas"
          class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-xs font-bold text-slate-900 hover:bg-black/10 transition"
        >
          <ArrowLeftRight class="w-3.5 h-3.5 shrink-0 text-slate-900" />
          <span>Buka Antarmuka Petugas</span>
        </NuxtLink>

        <NuxtLink
          to="/user"
          class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-xs font-bold text-slate-900 hover:bg-black/10 transition"
        >
          <QrCode class="w-3.5 h-3.5 shrink-0 text-slate-900" />
          <span>Pintu Masuk (Kiosk & Member)</span>
        </NuxtLink>
      </nav>

      <!-- User Profile & Logout Bottom Bar -->
      <div class="p-4 border-t border-slate-900/15 bg-black/5">
        <div class="flex items-center justify-between">
          <NuxtLink to="/admin/profile" class="truncate pr-2 hover:opacity-85 transition cursor-pointer flex-1">
            <p class="text-xs font-bold text-slate-950 truncate">{{ auth.user.value?.name || 'Administrator' }}</p>
            <span class="inline-block text-[9px] font-bold text-slate-900 bg-[#ffe600] px-1.5 py-0.2 rounded uppercase tracking-wider mt-0.5">
              Super Admin
            </span>
          </NuxtLink>
          <button
            @click="auth.logout"
            title="Keluar dari akun"
            class="p-2 rounded-xl bg-slate-900 text-white hover:bg-slate-800 transition cursor-pointer shadow-2xs"
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
          <span class="w-2 h-2 rounded-full bg-[#00ffff] border border-cyan-400 animate-pulse"></span>
          <h1 class="text-sm font-bold text-slate-900 font-sans tracking-tight">
            Sistem Informasi Pengelolaan Parkir Digital
          </h1>
        </div>

        <div class="flex items-center gap-4 text-xs font-mono text-slate-700">
          <span class="bg-slate-100 px-3 py-1.5 rounded-lg border border-slate-200 font-bold text-[11px]">
            {{ currentFormattedTime }}
          </span>
        </div>
      </header>

      <!-- Page Content -->
      <main class="flex-1 p-4 sm:p-6 overflow-y-auto">
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
  CreditCard,
  FileText,
  LogOut,
  ArrowLeftRight,
  QrCode,
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
