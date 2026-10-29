<template>
  <div class="min-h-screen bg-slate-100 flex flex-col md:flex-row text-slate-800">
    <!-- Sidebar Navigation -->
    <aside class="w-full md:w-64 bg-[#001a4e] text-white flex flex-col shrink-0 md:min-h-screen">
      <!-- Brand Header -->
      <div class="p-5 border-b border-white/10 flex items-center justify-between">
        <BrandLogo :showSubtitle="true" subtitle="Super Admin Panel" />
      </div>

      <!-- Navigation Links -->
      <nav class="flex-1 p-3 space-y-1">
        <NuxtLink
          to="/admin/dashboard"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition"
          :class="$route.path === '/admin/dashboard' ? 'bg-[#0099ff] text-white font-semibold' : 'text-slate-300 hover:bg-white/5 hover:text-white'"
        >
          <LayoutDashboard class="w-4 h-4 shrink-0" />
          <span>Dashboard Analitik</span>
        </NuxtLink>

        <NuxtLink
          to="/admin/users"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition"
          :class="$route.path.startsWith('/admin/users') ? 'bg-[#0099ff] text-white font-semibold' : 'text-slate-300 hover:bg-white/5 hover:text-white'"
        >
          <Users class="w-4 h-4 shrink-0" />
          <span>Manajemen Petugas</span>
        </NuxtLink>

        <NuxtLink
          to="/admin/member"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition"
          :class="$route.path === '/admin/member' ? 'bg-[#0099ff] text-white font-semibold' : 'text-slate-300 hover:bg-white/5 hover:text-white'"
        >
          <CreditCard class="w-4 h-4 shrink-0" />
          <span>Data Langganan Member</span>
        </NuxtLink>

        <NuxtLink
          to="/admin/rekap"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition"
          :class="$route.path === '/admin/rekap' ? 'bg-[#0099ff] text-white font-semibold' : 'text-slate-300 hover:bg-white/5 hover:text-white'"
        >
          <FileText class="w-4 h-4 shrink-0" />
          <span>Laporan & Rekapitulasi</span>
        </NuxtLink>

        <div class="pt-4 pb-1 px-3 text-[11px] font-semibold text-slate-400 tracking-wider uppercase">
          Akses Operasional
        </div>

        <NuxtLink
          to="/petugas"
          class="flex items-center gap-3 px-3.5 py-2 rounded-lg text-xs font-medium text-slate-300 hover:bg-white/5 hover:text-white transition"
        >
          <ArrowLeftRight class="w-3.5 h-3.5 shrink-0 text-[#ff9900]" />
          <span>Buka Antarmuka Petugas</span>
        </NuxtLink>

        <NuxtLink
          to="/user"
          target="_blank"
          class="flex items-center gap-3 px-3.5 py-2 rounded-lg text-xs font-medium text-slate-300 hover:bg-white/5 hover:text-white transition"
        >
          <QrCode class="w-3.5 h-3.5 shrink-0 text-[#0099ff]" />
          <span>Buka Kiosk Tiket Masuk</span>
        </NuxtLink>
      </nav>

      <!-- User Profile & Logout Bottom Bar -->
      <div class="p-4 border-t border-white/10 bg-black/20">
        <div class="flex items-center justify-between">
          <div class="truncate pr-2">
            <p class="text-xs font-semibold text-white truncate">{{ auth.user.value?.name || 'Administrator' }}</p>
            <span class="inline-block text-[10px] font-medium text-[#ff9900] uppercase tracking-wider">
              Super Admin
            </span>
          </div>
          <button
            @click="auth.logout"
            title="Keluar dari akun"
            class="p-2 rounded-md hover:bg-white/10 text-slate-300 hover:text-white transition cursor-pointer"
          >
            <LogOut class="w-4 h-4" />
          </button>
        </div>
      </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0">
      <!-- Topbar Header -->
      <header class="bg-white border-b border-slate-200 h-16 px-6 flex items-center justify-between sticky top-0 z-30">
        <div class="flex items-center gap-2">
          <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
          <h1 class="text-sm font-bold text-slate-800">
            Sistem Informasi Pengelolaan Parkir Digital
          </h1>
        </div>

        <div class="flex items-center gap-4 text-xs text-slate-500 font-mono">
          <span>{{ currentFormattedTime }}</span>
        </div>
      </header>

      <!-- Page Content -->
      <main class="flex-1 p-6 overflow-y-auto">
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
  currentFormattedTime.value = now.toLocaleDateString('id-ID', {
    weekday: 'short',
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  }) + ' - ' + now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' })
}

onMounted(() => {
  updateClock()
  timer = setInterval(updateClock, 1000)
})

onUnmounted(() => {
  if (timer) clearInterval(timer)
})
</script>
