<template>
  <div class="space-y-6">
    <!-- Header Banner -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl font-normal text-[#0f172a] font-serif tracking-tight">
          Dashboard Monitoring & Manajemen
        </h1>
        <p class="text-xs text-slate-500 mt-1">
          Pantau arus lalu lintas kendaraan (sedang parkir, masuk, dan keluar), grafik tren transaksi, serta staf petugas.
        </p>
      </div>

      <div class="flex items-center gap-2.5">
        <NuxtLink
          to="/admin/users"
          class="px-3.5 py-2.5 rounded-xl bg-[#3b82f6] hover:bg-[#2563eb] text-white border border-blue-600 text-xs font-bold transition flex items-center gap-2 cursor-pointer shadow-xs active:scale-[0.98]"
        >
          <UserPlus class="w-4 h-4" />
          <span>Tambah & Kelola Petugas</span>
        </NuxtLink>

        <NuxtLink
          to="/admin/rekap"
          class="px-3.5 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 text-xs font-bold transition flex items-center gap-2 cursor-pointer shadow-xs"
        >
          <FileSpreadsheet class="w-4 h-4 text-[#3b82f6]" />
          <span>Laporan Transaksi</span>
        </NuxtLink>
      </div>
    </div>

    <!-- Metrics Cards (Lalu Lintas Kendaraan & Ringkasan Transaksi) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
      <!-- 1. Sedang Parkir / Belum Keluar -->
      <div class="bg-white p-5 rounded-2xl border-2 border-blue-500/30 bg-blue-50/20 shadow-xs relative overflow-hidden">
        <div class="flex items-center justify-between mb-2">
          <span class="text-[11px] font-bold text-blue-700 uppercase tracking-wider block">
            Sedang Parkir (Belum Keluar)
          </span>
          <span class="w-2.5 h-2.5 rounded-full bg-[#3b82f6] animate-pulse"></span>
        </div>
        <div class="text-3xl font-black font-mono text-[#3b82f6]">
          {{ summary.sedang_parkir ?? summary.tiket_aktif ?? 0 }}
        </div>
        <span class="text-[11px] text-slate-500 mt-1 block font-medium">Kendaraan aktif di dalam</span>
      </div>

      <!-- 2. Masuk Hari Ini -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-2">
          Masuk Hari Ini
        </span>
        <div class="text-3xl font-black font-mono text-slate-900">
          {{ summary.masuk_hari_ini ?? summary.tiket_hari_ini ?? 0 }}
        </div>
        <span class="text-[11px] text-slate-400 mt-1 block">Total tiket tercetak hari ini</span>
      </div>

      <!-- 3. Keluar Hari Ini -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-2">
          Keluar Hari Ini
        </span>
        <div class="text-3xl font-black font-mono text-emerald-700">
          {{ summary.keluar_hari_ini ?? 0 }}
        </div>
        <span class="text-[11px] text-slate-400 mt-1 block">Transaksi selesai hari ini</span>
      </div>

      <!-- 4. Total Sudah Keluar (Selesai) -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-2">
          Total Sudah Keluar
        </span>
        <div class="text-3xl font-black font-mono text-slate-700">
          {{ summary.total_sudah_keluar ?? 0 }}
        </div>
        <span class="text-[11px] text-slate-400 mt-1 block">Kumulatif seluruh tiket keluar</span>
      </div>

      <!-- 5. Omset Transaksi Hari Ini -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-2">
          Omset Transaksi Hari Ini
        </span>
        <div class="text-2xl font-black font-mono text-amber-600 truncate">
          {{ formatRupiah(summary.pendapatan_hari_ini) }}
        </div>
        <span class="text-[11px] text-slate-400 mt-1 block">Non-member + Iuran</span>
      </div>
    </div>

    <!-- Dribbble-Style Interactive Traffic & Revenue Chart with Date Filter -->
    <TrafficRevenueChart />

    <!-- Recent Traffic & Vehicles Table with Filter Tabs -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
      <!-- Table Header & Status Filter Tabs -->
      <div class="p-5 border-b border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h3 class="text-sm font-bold text-slate-900">Arus Kendaraan & Status Parkir Terkini</h3>
          <p class="text-xs text-slate-500 mt-0.5">
            Daftar kendaraan dengan pembulatan durasi jam, waktu keluar, dan staf petugas loket.
          </p>
        </div>

        <!-- Filter Tab Buttons -->
        <div class="flex items-center gap-1.5 bg-slate-100 p-1 rounded-xl">
          <button
            type="button"
            @click="activeTab = 'all'"
            class="px-3 py-1.5 rounded-lg text-xs font-bold transition"
            :class="activeTab === 'all' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
          >
            Semua ({{ allTickets.length }})
          </button>
          <button
            type="button"
            @click="activeTab = 'masuk'"
            class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5"
            :class="activeTab === 'masuk' ? 'bg-[#3b82f6] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'"
          >
            <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse" v-if="activeTab === 'masuk'"></span>
            Sedang Parkir ({{ ticketsDiDalam.length }})
          </button>
          <button
            type="button"
            @click="activeTab = 'keluar'"
            class="px-3 py-1.5 rounded-lg text-xs font-bold transition"
            :class="activeTab === 'keluar' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'"
          >
            Sudah Keluar ({{ ticketsKeluar.length }})
          </button>
        </div>
      </div>

      <!-- Table Body -->
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
              <th class="py-3.5 px-4">Kode Tiket</th>
              <th class="py-3.5 px-4">Tipe Akses</th>
              <th class="py-3.5 px-4">Plat Nomor</th>
              <th class="py-3.5 px-4">Kendaraan</th>
              <th class="py-3.5 px-4">Waktu Masuk</th>
              <th class="py-3.5 px-4">Waktu Keluar</th>
              <th class="py-3.5 px-4">Durasi Parkir</th>
              <th class="py-3.5 px-4">Petugas</th>
              <th class="py-3.5 px-4">Total Biaya</th>
              <th class="py-3.5 px-4 text-center">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="filteredTickets.length === 0">
              <td colspan="10" class="text-center py-10 text-slate-400">
                Tidak ada data kendaraan pada filter ini.
              </td>
            </tr>
            <tr
              v-for="t in filteredTickets.slice(0, 15)"
              :key="t.id"
              class="hover:bg-slate-50/80 transition"
            >
              <td class="py-3 px-4 font-mono font-bold text-[#3b82f6]">
                {{ t.kode_tiket }}
              </td>
              <td class="py-3 px-4">
                <span
                  class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                  :class="t.jenis === 'member' ? 'bg-amber-100 text-amber-900 border border-amber-300' : 'bg-slate-100 text-slate-700 border border-slate-200'"
                >
                  {{ t.jenis === 'member' ? 'Member' : 'Non-Member' }}
                </span>
              </td>
              <td class="py-3 px-4 font-mono font-bold text-slate-800">
                {{ t.nomor_polisi || '-' }}
              </td>
              <td class="py-3 px-4 capitalize">
                {{ t.jenis_kendaraan || 'Mobil / Motor' }}
              </td>
              <td class="py-3 px-4 text-slate-600 font-mono text-[11px]">
                {{ formatDateTime(t.waktu_masuk) }}
              </td>
              <td class="py-3 px-4 text-slate-600 font-mono text-[11px]">
                <span v-if="t.waktu_keluar" class="text-slate-700 font-semibold">{{ formatDateTime(t.waktu_keluar) }}</span>
                <span v-else class="text-slate-400 font-medium">-</span>
              </td>
              <!-- Durasi Parkir (Ceil Rounding: <1h -> 1 Jam; 1h30m -> 2 Jam) -->
              <td class="py-3 px-4 font-mono text-slate-700 font-bold">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-slate-100 border border-slate-200 text-[11px]">
                  {{ formatDurasiJam(t.durasi_menit, t.waktu_masuk, t.waktu_keluar) }}
                </span>
              </td>
              <!-- Petugas Parkir -->
              <td class="py-3 px-4">
                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-800 border border-slate-200">
                  <User class="w-3 h-3 text-[#3b82f6]" />
                  {{ t.petugas_nama || (t.status === 'keluar' ? 'Petugas 1' : 'Kiosk Mandiri') }}
                </span>
              </td>
              <td class="py-3 px-4 font-mono font-bold text-slate-800">
                {{ t.total_bayar ? formatRupiah(t.total_bayar) : (t.jenis === 'member' ? 'Gratis (Member)' : '-') }}
              </td>
              <td class="py-3 px-4 text-center">
                <span
                  v-if="t.status === 'masuk'"
                  class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-blue-50 text-[#3b82f6] border border-blue-200"
                >
                  <span class="w-1.5 h-1.5 rounded-full bg-[#3b82f6] animate-pulse"></span>
                  Sedang Parkir
                </span>
                <span
                  v-else
                  class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200"
                >
                  Sudah Keluar
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Table Footer Link -->
      <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between text-xs">
        <span class="text-slate-500">
          Menampilkan hingga 15 kendaraan terkini.
        </span>
        <NuxtLink
          to="/admin/rekap"
          class="font-bold text-[#3b82f6] hover:underline flex items-center gap-1"
        >
          <span>Buka Rekapitulasi Lengkap Seluruh Periode</span>
          <span>&rarr;</span>
        </NuxtLink>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { FileSpreadsheet, UserPlus, User } from 'lucide-vue-next'
import { formatRupiah, formatDateTime, formatDurasiJam } from '~/utils/formatters'
import TrafficRevenueChart from '~/components/TrafficRevenueChart.vue'

definePageMeta({
  layout: 'admin',
  middleware: 'auth',
})

const api = useApi()
const activeTab = ref<'all' | 'masuk' | 'keluar'>('all')

const summary = ref({
  sedang_parkir: 0,
  tiket_aktif: 0,
  masuk_hari_ini: 0,
  tiket_hari_ini: 0,
  keluar_hari_ini: 0,
  total_sudah_keluar: 0,
  pendapatan_hari_ini: 0,
  total_petugas: 0,
  total_member: 0,
})

const allTickets = ref<any[]>([])

const ticketsDiDalam = computed(() => {
  return allTickets.value.filter(t => t.status === 'masuk')
})

const ticketsKeluar = computed(() => {
  return allTickets.value.filter(t => t.status === 'keluar')
})

const filteredTickets = computed(() => {
  if (activeTab.value === 'masuk') return ticketsDiDalam.value
  if (activeTab.value === 'keluar') return ticketsKeluar.value
  return allTickets.value
})

const fetchData = async () => {
  try {
    const [sumRes, tickRes] = await Promise.all([
      api.get('/rekap/summary'),
      api.get('/tiket'),
    ])

    if (sumRes.data?.success) {
      summary.value = sumRes.data.data
    }
    allTickets.value = Array.isArray(tickRes.data) ? tickRes.data : (tickRes.data?.data || [])
  } catch (err) {
    console.error('Gagal mengambil data admin dashboard:', err)
  }
}

onMounted(() => {
  fetchData()
})
</script>
