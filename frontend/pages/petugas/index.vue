<template>
  <div class="space-y-5 max-w-6xl mx-auto">
    <!-- Top Cockpit Header & Live Lane Status -->
    <div class="bg-[#001a4e] text-white p-5 rounded-xl border border-[#001033] shadow-md flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div class="space-y-1">
        <div class="flex items-center gap-2.5">
          <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
          <h2 class="text-base font-black tracking-wider uppercase text-white font-mono">
            PUSAT KENDALI OPERASIONAL GERBANG
          </h2>
          <span class="text-[10px] px-2 py-0.5 rounded bg-[#0099ff] text-white font-bold uppercase tracking-wider font-mono">
            POS AKTIF
          </span>
        </div>
        <p class="text-xs text-slate-300">
          Monitoring kapasitas slot parkir, arus lalu lintas kendaraan, dan pendapatan shift kasir.
        </p>
      </div>

      <!-- Quick Action Jumpers -->
      <div class="flex flex-wrap items-center gap-2">
        <button
          @click="fetchData"
          :disabled="isLoading"
          class="px-3 py-2 rounded-lg bg-white/10 hover:bg-white/15 text-slate-200 text-xs font-bold transition flex items-center gap-1.5 border border-white/5 cursor-pointer"
        >
          <RefreshCw class="w-3.5 h-3.5" :class="isLoading ? 'animate-spin text-[#ff9900]' : ''" />
          <span>Perbarui</span>
        </button>
        <NuxtLink
          to="/petugas/scan"
          class="px-3.5 py-2 rounded-lg bg-[#0099ff] hover:bg-[#0084de] text-white text-xs font-bold transition flex items-center gap-1.5 shadow-xs"
        >
          <Camera class="w-3.5 h-3.5" />
          <span>Scan Masuk</span>
        </NuxtLink>
        <NuxtLink
          to="/petugas/keluar"
          class="px-4 py-2 rounded-lg bg-[#ff9900] hover:bg-[#e68a00] text-slate-900 text-xs font-black uppercase tracking-wider transition flex items-center gap-1.5 shadow-xs"
        >
          <Receipt class="w-3.5 h-3.5" />
          <span>Pos Kasir Keluar</span>
        </NuxtLink>
      </div>
    </div>

    <!-- Live Hardware & Gate Diagnostic Strip -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs font-mono">
      <div class="bg-white p-3 rounded-lg border border-slate-200 shadow-xs flex items-center justify-between">
        <div class="flex items-center gap-2">
          <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
          <span class="font-bold text-slate-700">GATE IN 01:</span>
          <span class="text-emerald-700 font-semibold">DISPENSER READY</span>
        </div>
        <span class="text-[10px] text-slate-400">Palang Otomatis</span>
      </div>

      <div class="bg-white p-3 rounded-lg border border-slate-200 shadow-xs flex items-center justify-between">
        <div class="flex items-center gap-2">
          <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
          <span class="font-bold text-slate-700">GATE OUT 01:</span>
          <span class="text-emerald-700 font-semibold">KASIR STANDBY</span>
        </div>
        <span class="text-[10px] text-slate-400">Scanner USB OK</span>
      </div>

      <div class="bg-white p-3 rounded-lg border border-slate-200 shadow-xs flex items-center justify-between">
        <div class="flex items-center gap-2">
          <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
          <span class="font-bold text-slate-700">SERVER DATABASE:</span>
          <span class="text-emerald-700 font-semibold">TERHUBUNG (8000)</span>
        </div>
        <span class="text-[10px] text-slate-400 font-bold text-emerald-600">4ms</span>
      </div>
    </div>

    <!-- Real-time Lot Capacity & Occupancy Gauges -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- 1. Slot Parkir Mobil (Roda 4) -->
      <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs space-y-3">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-black uppercase tracking-wider text-slate-500 flex items-center gap-1.5">
            <Car class="w-4 h-4 text-[#0099ff]" />
            <span>Kapasitas Mobil</span>
          </span>
          <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono" :class="occupancyMobilPercent >= 90 ? 'bg-red-50 text-red-700' : 'bg-emerald-50 text-emerald-700'">
            {{ occupancyMobilPercent >= 90 ? 'HAMPIR PENUH' : 'TERSEDIA' }}
          </span>
        </div>

        <div class="flex items-baseline justify-between">
          <div class="text-3xl font-black font-mono text-[#001a4e]">
            {{ summary.tiket_aktif }}
            <span class="text-xs font-normal text-slate-400">/ 150 Slot</span>
          </div>
          <span class="text-xs font-mono font-bold text-slate-600">{{ occupancyMobilPercent }}%</span>
        </div>

        <!-- Visual Segmented Capacity Bar -->
        <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
          <div
            class="h-full rounded-full transition-all duration-500"
            :class="occupancyMobilPercent >= 90 ? 'bg-red-500' : (occupancyMobilPercent >= 70 ? 'bg-amber-500' : 'bg-[#0099ff]')"
            :style="{ width: `${Math.min(100, occupancyMobilPercent)}%` }"
          ></div>
        </div>

        <p class="text-[11px] text-slate-500 flex justify-between font-mono">
          <span>Tersedia: <strong>{{ Math.max(0, 150 - summary.tiket_aktif) }} Slot</strong></span>
          <span>Sesi Aktif: {{ summary.tiket_aktif }}</span>
        </p>
      </div>

      <!-- 2. Slot Parkir Motor (Roda 2) -->
      <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs space-y-3">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-black uppercase tracking-wider text-slate-500 flex items-center gap-1.5">
            <Bike class="w-4 h-4 text-[#ff9900]" />
            <span>Kapasitas Motor</span>
          </span>
          <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 text-[10px] font-bold font-mono">
            TERSEDIA
          </span>
        </div>

        <div class="flex items-baseline justify-between">
          <div class="text-3xl font-black font-mono text-[#001a4e]">
            0
            <span class="text-xs font-normal text-slate-400">/ 300 Slot</span>
          </div>
          <span class="text-xs font-mono font-bold text-slate-600">0%</span>
        </div>

        <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
          <div class="h-full bg-[#ff9900] rounded-full transition-all duration-500" style="width: 2%"></div>
        </div>

        <p class="text-[11px] text-slate-500 flex justify-between font-mono">
          <span>Tersedia: <strong>300 Slot</strong></span>
          <span>Sesi Aktif: 0</span>
        </p>
      </div>

      <!-- 3. Penerimaan Kas Shift Hari Ini -->
      <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs space-y-2">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-black uppercase tracking-wider text-slate-500 flex items-center gap-1.5">
            <BadgeDollarSign class="w-4 h-4 text-emerald-600" />
            <span>Pendapatan Hari Ini</span>
          </span>
          <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-600 text-[10px] font-bold font-mono">
            SHIFT 2
          </span>
        </div>

        <div class="text-2xl sm:text-3xl font-black font-mono text-emerald-700 py-1">
          {{ formatRupiah(summary.pendapatan_hari_ini) }}
        </div>

        <p class="text-[11px] text-slate-500 font-mono">
          Total Tiket Dicetak: <strong>{{ summary.tiket_hari_ini }} Kendaraan</strong>
        </p>
      </div>

      <!-- 4. Member Langganan Aktif -->
      <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs space-y-2">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-black uppercase tracking-wider text-slate-500 flex items-center gap-1.5">
            <CreditCard class="w-4 h-4 text-indigo-600" />
            <span>Member Langganan</span>
          </span>
          <span class="px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 text-[10px] font-bold font-mono">
            AKTIF
          </span>
        </div>

        <div class="text-3xl font-black font-mono text-slate-800 py-1">
          {{ summary.total_member }}
          <span class="text-xs font-normal text-slate-400">Pengguna</span>
        </div>

        <p class="text-[11px] text-slate-500 font-mono">
          Bebas Akses Gerbang Otomatis
        </p>
      </div>
    </div>

    <!-- Live Vehicle Traffic Stream (Arus Lalu Lintas Terkini) -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
      <div class="p-4 border-b border-slate-200 flex items-center justify-between bg-slate-50/70">
        <div class="flex items-center gap-2">
          <Activity class="w-4 h-4 text-[#0099ff]" />
          <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">
            Arus Lalu Lintas Kendaraan Terkini
          </h3>
        </div>
        <NuxtLink
          to="/petugas/ticket"
          class="text-xs font-bold text-[#0099ff] hover:text-[#0084de] transition flex items-center gap-1"
        >
          <span>Lihat Semua Tiket</span>
          <ArrowRight class="w-3.5 h-3.5" />
        </NuxtLink>
      </div>

      <!-- Compact Industrial Traffic Table -->
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[10px] font-mono">
              <th class="py-2.5 px-4">Waktu</th>
              <th class="py-2.5 px-4">Kode Sesi</th>
              <th class="py-2.5 px-4">Tipe</th>
              <th class="py-2.5 px-4">Plat Nomor</th>
              <th class="py-2.5 px-4">Kendaraan</th>
              <th class="py-2.5 px-4">Biaya</th>
              <th class="py-2.5 px-4 text-center">Status Gerbang</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 font-mono">
            <tr v-if="recentTickets.length === 0">
              <td colspan="7" class="text-center py-10 text-slate-400 font-sans text-xs">
                Belum ada transaksi lalu lintas kendaraan pada shift saat ini.
              </td>
            </tr>
            <tr
              v-for="t in recentTickets"
              :key="t.id"
              class="hover:bg-slate-50/80 transition"
            >
              <td class="py-2.5 px-4 text-slate-600 text-[11px]">
                {{ formatTimeOnly(t.waktu_masuk) }}
              </td>
              <td class="py-2.5 px-4 font-bold text-[#001a4e]">
                {{ t.kode_tiket }}
              </td>
              <td class="py-2.5 px-4 font-sans">
                <span
                  class="px-2 py-0.5 rounded text-[10px] font-bold uppercase"
                  :class="t.jenis === 'member' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-slate-100 text-slate-700'"
                >
                  {{ t.jenis === 'member' ? 'Member' : 'Umum' }}
                </span>
              </td>
              <td class="py-2.5 px-4 font-black text-slate-900">
                {{ t.nomor_polisi || '-' }}
              </td>
              <td class="py-2.5 px-4 capitalize font-sans text-slate-700">
                {{ t.jenis_kendaraan || 'Mobil' }}
              </td>
              <td class="py-2.5 px-4 font-bold" :class="t.total_bayar > 0 ? 'text-emerald-700' : 'text-slate-500'">
                {{ formatRupiah(t.total_bayar) }}
              </td>
              <td class="py-2.5 px-4 text-center font-sans">
                <span
                  class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase inline-flex items-center gap-1"
                  :class="t.status === 'masuk' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600'"
                >
                  <span class="w-1.5 h-1.5 rounded-full" :class="t.status === 'masuk' ? 'bg-emerald-500' : 'bg-slate-400'"></span>
                  <span>{{ t.status === 'masuk' ? 'Di Dalam' : 'Sudah Keluar' }}</span>
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import {
  Camera,
  Receipt,
  Car,
  Bike,
  BadgeDollarSign,
  CreditCard,
  RefreshCw,
  Activity,
  ArrowRight,
} from 'lucide-vue-next'

definePageMeta({
  layout: 'petugas',
  middleware: 'auth',
})

const api = useApi()
const isLoading = ref(false)
const summary = ref({
  tiket_aktif: 0,
  tiket_hari_ini: 0,
  pendapatan_hari_ini: 0,
  total_member: 0,
})
const recentTickets = ref<any[]>([])

const occupancyMobilPercent = computed(() => {
  const totalSlots = 150
  return Math.round((summary.value.tiket_aktif / totalSlots) * 100)
})

const fetchData = async () => {
  isLoading.value = true
  try {
    const [summaryRes, ticketRes] = await Promise.all([
      api.get('/rekap/summary'),
      api.get('/tiket'),
    ])

    if (summaryRes.data?.success) {
      summary.value = summaryRes.data.data
    }

    if (ticketRes.data) {
      recentTickets.value = (ticketRes.data || []).slice(0, 6)
    }
  } catch (err) {
    console.error('Gagal mengambil data dashboard:', err)
  } finally {
    isLoading.value = false
  }
}

const formatRupiah = (val: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(val || 0)
}

const formatTimeOnly = (dateStr: string) => {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB'
}

onMounted(() => {
  fetchData()
})
</script>
