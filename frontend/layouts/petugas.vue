<template>
  <div class="min-h-screen bg-[#f8fafc] flex flex-col md:flex-row text-slate-900 font-sans">
    <!-- Sidebar Navigation with Aqua 00FFFF Scheme -->
    <aside
      class="bg-[#00ffff] text-slate-950 flex flex-col shrink-0 md:min-h-screen transition-all duration-300 ease-in-out relative z-40 border-r border-cyan-400/60"
      :class="isCollapsed ? 'w-full md:w-16' : 'w-full md:w-64'"
    >
      <!-- Brand Header & Toggle Button -->
      <div
        class="border-b border-slate-900/15 flex items-center transition-all duration-300"
        :class="isCollapsed ? 'p-3 justify-center flex-col gap-2' : 'p-4 justify-between'"
      >
        <NuxtLink to="/petugas" class="focus:outline-none">
          <BrandLogo
            :showSubtitle="!isCollapsed"
            subtitle="Pos Operasional"
            :isDarkSurface="false"
            :compact="isCollapsed"
            size="sm"
          />
        </NuxtLink>

        <!-- Toggle Collapse/Expand Button -->
        <button
          @click="toggleSidebar"
          type="button"
          :title="isCollapsed ? 'Buka Sidebar (Menu Lengkap)' : 'Tutup Sidebar (Perluas Layar)'"
          class="p-1.5 rounded-lg bg-slate-900 text-[#00ffff] hover:bg-slate-800 transition cursor-pointer shadow-2xs"
        >
          <ChevronRight v-if="isCollapsed" class="w-4 h-4 text-[#ffe600]" />
          <ChevronLeft v-else class="w-4 h-4 text-[#00ffff]" />
        </button>
      </div>

      <!-- Navigation Links -->
      <nav class="flex-1 p-2 space-y-1 overflow-y-auto">
        <!-- Dashboard -->
        <NuxtLink
          to="/petugas"
          :title="isCollapsed ? 'Dashboard Operasional' : undefined"
          class="flex items-center gap-3 rounded-xl text-xs font-bold transition"
          :class="[
            isCollapsed ? 'justify-center p-2.5' : 'px-3 py-2.5',
            $route.path === '/petugas'
              ? 'bg-[#ffe600] text-slate-950 shadow-xs border border-slate-900/15'
              : 'text-slate-800 hover:bg-black/10 hover:text-slate-950'
          ]"
        >
          <LayoutDashboard class="w-4 h-4 shrink-0 text-slate-900" />
          <span v-if="!isCollapsed" class="truncate">Dashboard Operasional</span>
        </NuxtLink>

        <!-- Category: Lalu Lintas Kendaraan -->
        <div v-if="!isCollapsed" class="pt-3 pb-1 px-3 text-[10px] font-bold text-slate-900 tracking-wider uppercase">
          Lalu Lintas Kendaraan
        </div>
        <div v-else class="my-2 border-t border-slate-900/15"></div>

        <!-- Kiosk Tiket & Member -->
        <NuxtLink
          to="/user"
          :title="isCollapsed ? 'Pintu Masuk (Kiosk & Member)' : undefined"
          class="flex items-center gap-3 rounded-xl text-xs font-bold transition"
          :class="[
            isCollapsed ? 'justify-center p-2.5' : 'px-3 py-2.5',
            ['/user', '/petugas/scan'].includes($route.path)
              ? 'bg-[#ffe600] text-slate-950 shadow-xs border border-slate-900/15'
              : 'text-slate-800 hover:bg-black/10 hover:text-slate-950'
          ]"
        >
          <Camera class="w-4 h-4 shrink-0 text-slate-900" />
          <span v-if="!isCollapsed" class="truncate">Pintu Masuk (Kiosk & Member)</span>
        </NuxtLink>

        <!-- Pos Kasir Keluar -->
        <NuxtLink
          to="/petugas/keluar"
          :title="isCollapsed ? 'Pos Kasir Keluar' : undefined"
          class="flex items-center gap-3 rounded-xl text-xs font-bold transition"
          :class="[
            isCollapsed ? 'justify-center p-2.5' : 'px-3 py-2.5',
            $route.path === '/petugas/keluar'
              ? 'bg-[#ffe600] text-slate-950 shadow-xs border border-slate-900/15'
              : 'text-slate-800 hover:bg-black/10 hover:text-slate-950'
          ]"
        >
          <Receipt class="w-4 h-4 shrink-0 text-slate-900" />
          <span v-if="!isCollapsed" class="truncate">Pos Kasir Keluar</span>
        </NuxtLink>

        <!-- Daftar Tiket Parkir -->
        <NuxtLink
          to="/petugas/ticket"
          :title="isCollapsed ? 'Daftar Tiket Parkir' : undefined"
          class="flex items-center gap-3 rounded-xl text-xs font-bold transition"
          :class="[
            isCollapsed ? 'justify-center p-2.5' : 'px-3 py-2.5',
            $route.path === '/petugas/ticket'
              ? 'bg-[#ffe600] text-slate-950 shadow-xs border border-slate-900/15'
              : 'text-slate-800 hover:bg-black/10 hover:text-slate-950'
          ]"
        >
          <Ticket class="w-4 h-4 shrink-0 text-slate-900" />
          <span v-if="!isCollapsed" class="truncate">Daftar Tiket Parkir</span>
        </NuxtLink>

        <!-- Category: Layanan Pelanggan -->
        <div v-if="!isCollapsed" class="pt-3 pb-1 px-3 text-[10px] font-bold text-slate-900 tracking-wider uppercase">
          Layanan Pelanggan
        </div>
        <div v-else class="my-2 border-t border-slate-900/15"></div>

        <!-- Registrasi Member -->
        <NuxtLink
          to="/petugas/member"
          :title="isCollapsed ? 'Registrasi Member' : undefined"
          class="flex items-center gap-3 rounded-xl text-xs font-bold transition"
          :class="[
            isCollapsed ? 'justify-center p-2.5' : 'px-3 py-2.5',
            $route.path === '/petugas/member'
              ? 'bg-[#ffe600] text-slate-950 shadow-xs border border-slate-900/15'
              : 'text-slate-800 hover:bg-black/10 hover:text-slate-950'
          ]"
        >
          <CreditCard class="w-4 h-4 shrink-0 text-slate-900" />
          <span v-if="!isCollapsed" class="truncate">Registrasi Member</span>
        </NuxtLink>

        <!-- Iuran Tagihan Member -->
        <NuxtLink
          to="/petugas/member/tagihan"
          :title="isCollapsed ? 'Iuran & Tagihan Member' : undefined"
          class="flex items-center gap-3 rounded-xl text-xs font-bold transition"
          :class="[
            isCollapsed ? 'justify-center p-2.5' : 'px-3 py-2.5',
            $route.path === '/petugas/member/tagihan'
              ? 'bg-[#ffe600] text-slate-950 shadow-xs border border-slate-900/15'
              : 'text-slate-800 hover:bg-black/10 hover:text-slate-950'
          ]"
        >
          <BadgeDollarSign class="w-4 h-4 shrink-0 text-slate-900" />
          <span v-if="!isCollapsed" class="truncate">Iuran & Tagihan Member</span>
        </NuxtLink>

        <!-- Laporan Shift Kasir -->
        <NuxtLink
          to="/petugas/laporan"
          :title="isCollapsed ? 'Laporan Shift Kasir' : undefined"
          class="flex items-center gap-3 rounded-xl text-xs font-bold transition"
          :class="[
            isCollapsed ? 'justify-center p-2.5' : 'px-3 py-2.5',
            $route.path === '/petugas/laporan'
              ? 'bg-[#ffe600] text-slate-950 shadow-xs border border-slate-900/15'
              : 'text-slate-800 hover:bg-black/10 hover:text-slate-950'
          ]"
        >
          <FileText class="w-4 h-4 shrink-0 text-slate-900" />
          <span v-if="!isCollapsed" class="truncate">Laporan Shift Kasir</span>
        </NuxtLink>

        <!-- Kelola Profil Akun -->
        <NuxtLink
          to="/petugas/profile"
          :title="isCollapsed ? 'Kelola Profil Akun' : undefined"
          class="flex items-center gap-3 rounded-xl text-xs font-bold transition"
          :class="[
            isCollapsed ? 'justify-center p-2.5' : 'px-3 py-2.5',
            $route.path === '/petugas/profile'
              ? 'bg-[#ffe600] text-slate-950 shadow-xs border border-slate-900/15'
              : 'text-slate-800 hover:bg-black/10 hover:text-slate-950'
          ]"
        >
          <User class="w-4 h-4 shrink-0 text-slate-900" />
          <span v-if="!isCollapsed" class="truncate">Kelola Profil Akun</span>
        </NuxtLink>

        <!-- Admin Backlink if super_admin -->
        <template v-if="auth.isSuperAdmin.value">
          <div v-if="!isCollapsed" class="pt-3 pb-1 px-3 text-[10px] font-bold text-slate-900 tracking-wider uppercase">
            Super Admin
          </div>
          <div v-else class="my-2 border-t border-slate-900/15"></div>

          <NuxtLink
            to="/admin/dashboard"
            :title="isCollapsed ? 'Kembali ke Panel Super Admin' : undefined"
            class="flex items-center gap-3 rounded-xl text-xs font-bold text-slate-900 hover:bg-black/10 transition"
            :class="isCollapsed ? 'justify-center p-2.5' : 'px-3 py-2'"
          >
            <ShieldAlert class="w-4 h-4 shrink-0 text-slate-900" />
            <span v-if="!isCollapsed" class="truncate">Panel Super Admin</span>
          </NuxtLink>
        </template>
      </nav>

      <!-- User Profile & Logout Bottom Bar -->
      <div class="p-3 border-t border-slate-900/15 bg-black/5">
        <div class="flex items-center" :class="isCollapsed ? 'justify-center' : 'justify-between'">
          <NuxtLink
            v-if="!isCollapsed"
            to="/petugas/profile"
            class="truncate pr-2 hover:opacity-85 transition cursor-pointer flex-1"
          >
            <p class="text-xs font-bold text-slate-950 truncate">{{ auth.user.value?.name || 'Petugas Pos' }}</p>
            <span class="inline-block text-[9px] font-bold text-slate-900 bg-[#ffe600] px-1.5 py-0.2 rounded uppercase tracking-wider mt-0.5">
              {{ auth.user.value?.role === 'super_admin' ? 'Administrator' : 'Petugas Gerbang' }}
            </span>
          </NuxtLink>

          <button
            @click="auth.logout"
            :title="`Keluar (${auth.user.value?.name || 'Petugas'})`"
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
        <div class="flex items-center gap-3">
          <!-- Quick Sidebar Toggle on Topbar -->
          <button
            @click="toggleSidebar"
            type="button"
            class="p-2 rounded-xl border border-slate-200 hover:bg-[#e6ffff] hover:border-cyan-400 text-slate-800 transition cursor-pointer flex items-center gap-1.5 text-xs font-bold"
            :title="isCollapsed ? 'Perluas Menu Sidebar' : 'Tutup Menu Sidebar'"
          >
            <PanelLeftClose v-if="!isCollapsed" class="w-4 h-4 text-slate-900" />
            <PanelLeftOpen v-else class="w-4 h-4 text-slate-900" />
            <span class="hidden sm:inline text-[11px]">{{ isCollapsed ? 'Buka Menu' : 'Tutup Menu' }}</span>
          </button>

          <div class="flex items-center gap-2 text-xs">
            <span class="font-bold text-slate-900">POS OPERASIONAL GERBANG</span>
            <span class="text-slate-300 hidden sm:inline">&bull;</span>
            <span class="hidden sm:inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-[#fefce8] text-slate-900 border border-[#ffe600] font-bold text-[10px]">
              <span class="w-1.5 h-1.5 rounded-full bg-[#00ffff] animate-pulse"></span>
              Shift Aktif
            </span>
          </div>
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
import { ref, onMounted, onUnmounted, watch } from 'vue'
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
  User,
  ChevronLeft,
  ChevronRight,
  PanelLeftClose,
  PanelLeftOpen,
} from 'lucide-vue-next'
import BrandLogo from '~/components/BrandLogo.vue'

const auth = useAuth()
const route = useRoute()

// Persistent reactive sidebar collapse state
const isCollapsed = useState<boolean>('petugas-sidebar-collapsed', () => false)

const toggleSidebar = () => {
  isCollapsed.value = !isCollapsed.value
}

// Automatically collapse sidebar on /user (Kiosk page) as requested: "agar tetap ada sidebar namun sidebarnya hanya menutup"
watch(
  () => route.path,
  (newPath) => {
    if (newPath === '/user') {
      isCollapsed.value = true
    }
  },
  { immediate: true }
)

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
