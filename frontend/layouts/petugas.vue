<template>
  <div class="min-h-screen bg-slate-100 flex flex-col md:flex-row text-slate-800">
    <!-- Sidebar Navigation -->
    <aside class="w-full md:w-64 bg-[#001a4e] text-white flex flex-col shrink-0 md:min-h-screen">
      <!-- Brand Header -->
      <div class="p-5 border-b border-white/10 flex items-center justify-between">
        <BrandLogo :showSubtitle="true" subtitle="Pos Operasional Gerbang" />
      </div>

      <!-- Navigation Links -->
      <nav class="flex-1 p-3 space-y-1 overflow-y-auto">
        <NuxtLink
          to="/petugas"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition"
          :class="$route.path === '/petugas' ? 'bg-[#0099ff] text-white font-semibold' : 'text-slate-300 hover:bg-white/5 hover:text-white'"
        >
          <LayoutDashboard class="w-4 h-4 shrink-0" />
          <span>Dashboard Operasional</span>
        </NuxtLink>

        <div class="pt-3 pb-1 px-3 text-[11px] font-semibold text-[#ff9900] tracking-wider uppercase">
          Lalu Lintas Kendaraan
        </div>

        <NuxtLink
          to="/petugas/scan"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition"
          :class="$route.path === '/petugas/scan' ? 'bg-[#0099ff] text-white font-semibold' : 'text-slate-300 hover:bg-white/5 hover:text-white'"
        >
          <Camera class="w-4 h-4 shrink-0" />
          <span>Pintu Masuk (Scan Member)</span>
        </NuxtLink>

        <NuxtLink
          to="/petugas/keluar"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition"
          :class="$route.path === '/petugas/keluar' ? 'bg-[#ff9900] text-slate-900 font-bold' : 'text-slate-300 hover:bg-white/5 hover:text-white'"
        >
          <Receipt class="w-4 h-4 shrink-0" />
          <span>Pos Kasir Keluar</span>
        </NuxtLink>

        <NuxtLink
          to="/petugas/ticket"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition"
          :class="$route.path === '/petugas/ticket' ? 'bg-[#0099ff] text-white font-semibold' : 'text-slate-300 hover:bg-white/5 hover:text-white'"
        >
          <Ticket class="w-4 h-4 shrink-0" />
          <span>Daftar Tiket Sesi Parkir</span>
        </NuxtLink>

        <div class="pt-3 pb-1 px-3 text-[11px] font-semibold text-[#ff9900] tracking-wider uppercase">
          Layanan Pelanggan
        </div>

        <NuxtLink
          to="/petugas/member"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition"
          :class="$route.path === '/petugas/member' ? 'bg-[#0099ff] text-white font-semibold' : 'text-slate-300 hover:bg-white/5 hover:text-white'"
        >
          <CreditCard class="w-4 h-4 shrink-0" />
          <span>Registrasi Member</span>
        </NuxtLink>

        <NuxtLink
          to="/petugas/member/tagihan"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition"
          :class="$route.path === '/petugas/member/tagihan' ? 'bg-[#0099ff] text-white font-semibold' : 'text-slate-300 hover:bg-white/5 hover:text-white'"
        >
          <BadgeDollarSign class="w-4 h-4 shrink-0" />
          <span>Iuran & Tagihan Member</span>
        </NuxtLink>

        <NuxtLink
          to="/petugas/laporan"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition"
          :class="$route.path === '/petugas/laporan' ? 'bg-[#0099ff] text-white font-semibold' : 'text-slate-300 hover:bg-white/5 hover:text-white'"
        >
          <FileText class="w-4 h-4 shrink-0" />
          <span>Laporan Shift Harian</span>
        </NuxtLink>

        <!-- Admin Backlink if super_admin -->
        <div v-if="auth.isSuperAdmin.value" class="pt-3 pb-1 px-3 text-[11px] font-semibold text-slate-400 tracking-wider uppercase">
          Panel Administrator
        </div>
        <NuxtLink
          v-if="auth.isSuperAdmin.value"
          to="/admin/dashboard"
          class="flex items-center gap-3 px-3.5 py-2 rounded-lg text-xs font-medium text-slate-300 hover:bg-white/5 hover:text-white transition"
        >
          <ShieldAlert class="w-3.5 h-3.5 shrink-0 text-[#0099ff]" />
          <span>Kembali ke Super Admin</span>
        </NuxtLink>
      </nav>

      <!-- User Profile & Logout Bottom Bar -->
      <div class="p-4 border-t border-white/10 bg-black/20">
        <div class="flex items-center justify-between">
          <div class="truncate pr-2">
            <p class="text-xs font-semibold text-white truncate">{{ auth.user.value?.name || 'Petugas Gerbang' }}</p>
            <span class="inline-block text-[10px] font-medium text-[#0099ff] uppercase tracking-wider">
              {{ auth.user.value?.role === 'super_admin' ? 'Administrator' : 'Petugas Pos' }}
            </span>
          </div>
          <button
            @click="auth.logout"
            title="Keluar dari sesi"
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
        <div class="flex items-center gap-3">
          <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
          <span class="text-xs font-bold text-slate-700 tracking-wide uppercase">
            Status Gerbang: Siap Operasi
          </span>
        </div>

        <div class="flex items-center gap-4 text-xs font-mono text-slate-600">
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
  Camera,
  Receipt,
  Ticket,
  CreditCard,
  BadgeDollarSign,
  FileText,
  ShieldAlert,
  LogOut,
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
