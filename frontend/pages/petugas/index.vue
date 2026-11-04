<template>
  <div class="space-y-5 max-w-6xl mx-auto">
    <!-- Clean Enterprise Header -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2">
          <h2 class="text-lg font-bold text-[#001a4e]">
            Dashboard Operasional Petugas
          </h2>
        </div>
        <p class="text-xs text-slate-500 mt-0.5">
          Ringkasan arus kendaraan masuk/keluar, sesi parkir aktif, dan transaksi loket kasir.
        </p>
      </div>

      <!-- Quick Action Buttons -->
      <div class="flex flex-wrap items-center gap-2">
        <button
          @click="fetchData"
          :disabled="isLoading"
          class="px-3 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer border border-slate-200"
        >
          <RefreshCw class="w-3.5 h-3.5" :class="isLoading ? 'animate-spin text-[#0099ff]' : ''" />
          <span>Perbarui</span>
        </button>
        <NuxtLink
          to="/user"
          class="px-3.5 py-2 rounded-lg bg-[#001a4e] hover:bg-[#001033] text-white text-xs font-bold transition flex items-center gap-1.5 shadow-xs"
        >
          <Camera class="w-3.5 h-3.5" />
          <span>Pintu Masuk (Kiosk)</span>
        </NuxtLink>
        <NuxtLink
          to="/petugas/keluar"
          class="px-4 py-2 rounded-lg bg-[#0099ff] hover:bg-[#0084de] text-white text-xs font-bold transition flex items-center gap-1.5 shadow-xs"
        >
          <Receipt class="w-3.5 h-3.5" />
          <span>Buka Kasir Keluar</span>
        </NuxtLink>
      </div>
    </div>

    <!-- Real Data Metrics Grid (4 Clean Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- 1. Kendaraan di Dalam (Sesi Parkir Aktif) -->
      <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs space-y-2">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 flex items-center gap-1.5">
            <Car class="w-4 h-4 text-[#0099ff]" />
            <span>Kendaraan di Dalam</span>
          </span>
          <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-blue-50 text-blue-700">
            AKTIF
          </span>
        </div>

        <div class="text-3xl font-black font-mono text-[#001a4e] py-1">
          {{ summary.tiket_aktif }}
          <span class="text-xs font-normal text-slate-400">Kendaraan</span>
        </div>

        <p class="text-[11px] text-slate-500">
          Sesi parkir yang belum keluar gerbang
        </p>
      </div>

      <!-- 2. Masuk Hari Ini -->
      <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs space-y-2">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 flex items-center gap-1.5">
            <Activity class="w-4 h-4 text-emerald-600" />
            <span>Masuk Hari Ini</span>
          </span>
          <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 text-[10px] font-bold font-mono">
            HARIAN
          </span>
        </div>

        <div class="text-3xl font-black font-mono text-slate-800 py-1">
          {{ summary.tiket_hari_ini }}
          <span class="text-xs font-normal text-slate-400">Tiket</span>
        </div>

        <p class="text-[11px] text-slate-500">
          Total kendaraan masuk sejak pukul 00:00
        </p>
      </div>

      <!-- 3. Penerimaan Kas Hari Ini -->
      <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs space-y-2">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 flex items-center gap-1.5">
            <BadgeDollarSign class="w-4 h-4 text-amber-600" />
            <span>Pendapatan Hari Ini</span>
          </span>
          <span class="px-2 py-0.5 rounded bg-amber-50 text-amber-700 text-[10px] font-bold font-mono">
            KASIR
          </span>
        </div>

        <div class="text-2xl sm:text-3xl font-black font-mono text-emerald-700 py-1">
          {{ formatRupiah(summary.pendapatan_hari_ini) }}
        </div>

        <p class="text-[11px] text-slate-500">
          Penerimaan dari tiket keluar hari ini
        </p>
      </div>

      <!-- 4. Member Langganan Aktif -->
      <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs space-y-2">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 flex items-center gap-1.5">
            <CreditCard class="w-4 h-4 text-indigo-600" />
            <span>Member Terdaftar</span>
          </span>
          <span class="px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 text-[10px] font-bold font-mono">
            TOTAL
          </span>
        </div>

        <div class="text-3xl font-black font-mono text-slate-800 py-1">
          {{ summary.total_member }}
          <span class="text-xs font-normal text-slate-400">Akun</span>
        </div>

        <p class="text-[11px] text-slate-500">
          Pelanggan langganan kartu barcode
        </p>
      </div>
    </div>

    <!-- Live Vehicle Traffic Stream (Arus Lalu Lintas Terkini) -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
      <div class="p-4 border-b border-slate-200 flex items-center justify-between bg-slate-50/70">
        <div class="flex items-center gap-2">
          <Activity class="w-4 h-4 text-[#001a4e]" />
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
              <th class="py-2.5 px-4">Kode Tiket</th>
              <th class="py-2.5 px-4">Kategori</th>
              <th class="py-2.5 px-4">Plat Nomor</th>
              <th class="py-2.5 px-4">Kendaraan</th>
              <th class="py-2.5 px-4">Biaya</th>
              <th class="py-2.5 px-4 text-center">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 font-mono">
            <tr v-if="recentTickets.length === 0">
              <td colspan="7" class="text-center py-10 text-slate-400 font-sans text-xs">
                Belum ada transaksi lalu lintas kendaraan hari ini.
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
              <td class="py-2.5 px-4 font-bold text-slate-900">
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
                  class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase inline-flex items-center gap-1"
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
import { ref, onMounted } from 'vue'
import {
  Camera,
  Receipt,
  Car,
  BadgeDollarSign,
  CreditCard,
  RefreshCw,
  Activity,
  ArrowRight,
} from 'lucide-vue-next'
import { formatRupiah } from '~/utils/formatters'

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
      recentTickets.value = (ticketRes.data || []).slice(0, 8)
    }
  } catch (err) {
    console.error('Gagal mengambil data dashboard:', err)
  } finally {
    isLoading.value = false
  }
}

const formatTimeOnly = (dateStr: string) => {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB'
}

onMounted(() => {
  fetchData()
})
</script>
