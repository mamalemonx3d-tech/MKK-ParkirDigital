<template>
  <div class="space-y-6">
    <!-- Header Banner -->
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-xl font-bold text-[#001a4e]">
          Dashboard Utama Administrator
        </h2>
        <p class="text-xs text-slate-500 mt-1">
          Ringkasan operasional harian seluruh gerbang parkir, transaksi, dan staf.
        </p>
      </div>

      <div class="flex items-center gap-2">
        <NuxtLink
          to="/admin/rekap"
          class="px-3.5 py-2 rounded-lg bg-[#0099ff] hover:bg-[#0084de] text-white text-xs font-bold transition flex items-center gap-2"
        >
          <FileSpreadsheet class="w-4 h-4" />
          <span>Lihat Rekapitulasi</span>
        </NuxtLink>
        <NuxtLink
          to="/admin/users"
          class="px-3.5 py-2 rounded-lg bg-[#001a4e] hover:bg-[#001033] text-white text-xs font-bold transition flex items-center gap-2"
        >
          <UserPlus class="w-4 h-4" />
          <span>Kelola Petugas</span>
        </NuxtLink>
      </div>
    </div>

    <!-- Metrics Cards (5 columns) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
      <!-- 1. Tiket Aktif -->
      <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">
          Kendaraan di Dalam
        </span>
        <div class="text-2xl font-black font-mono text-[#001a4e]">
          {{ summary.tiket_aktif }}
        </div>
        <span class="text-[10px] text-slate-400 mt-1 block">Sesi parkir aktif</span>
      </div>

      <!-- 2. Masuk Hari Ini -->
      <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">
          Masuk Hari Ini
        </span>
        <div class="text-2xl font-black font-mono text-slate-800">
          {{ summary.tiket_hari_ini }}
        </div>
        <span class="text-[10px] text-slate-400 mt-1 block">Total tercetak</span>
      </div>

      <!-- 3. Pendapatan Hari Ini -->
      <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">
          Omset Hari Ini
        </span>
        <div class="text-2xl font-black font-mono text-emerald-700 truncate">
          {{ formatRupiah(summary.pendapatan_hari_ini) }}
        </div>
        <span class="text-[10px] text-slate-400 mt-1 block">Kasir + Iuran Member</span>
      </div>

      <!-- 4. Total Petugas -->
      <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">
          Petugas Terdaftar
        </span>
        <div class="text-2xl font-black font-mono text-slate-800">
          {{ summary.total_petugas }}
        </div>
        <span class="text-[10px] text-slate-400 mt-1 block">Akun operasional</span>
      </div>

      <!-- 5. Total Member -->
      <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">
          Langganan Member
        </span>
        <div class="text-2xl font-black font-mono text-indigo-700">
          {{ summary.total_member }}
        </div>
        <span class="text-[10px] text-slate-400 mt-1 block">Pelanggan aktif</span>
      </div>
    </div>

    <!-- Recent Tickets Activity Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
      <div class="p-5 border-b border-slate-200 flex items-center justify-between">
        <div>
          <h3 class="text-sm font-bold text-slate-900">Aktivitas Tiket Parkir Terkini</h3>
          <p class="text-xs text-slate-500 mt-0.5">Daftar kendaraan yang baru masuk atau keluar.</p>
        </div>
        <NuxtLink
          to="/petugas/ticket"
          class="text-xs font-semibold text-[#0099ff] hover:underline"
        >
          Lihat Semua Tiket &rarr;
        </NuxtLink>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
              <th class="py-3 px-4">Kode</th>
              <th class="py-3 px-4">Kategori</th>
              <th class="py-3 px-4">Plat Nomor</th>
              <th class="py-3 px-4">Kendaraan</th>
              <th class="py-3 px-4">Waktu Masuk</th>
              <th class="py-3 px-4">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="recentTickets.length === 0">
              <td colspan="6" class="text-center py-8 text-slate-400">
                Belum ada aktivitas parkir.
              </td>
            </tr>
            <tr
              v-for="t in recentTickets.slice(0, 8)"
              :key="t.id"
              class="hover:bg-slate-50/80 transition"
            >
              <td class="py-3 px-4 font-mono font-bold text-[#001a4e]">
                {{ t.kode_tiket }}
              </td>
              <td class="py-3 px-4">
                <span
                  class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider"
                  :class="t.jenis === 'member' ? 'bg-indigo-50 text-indigo-700' : 'bg-slate-100 text-slate-700'"
                >
                  {{ t.jenis === 'member' ? 'Member' : 'Umum' }}
                </span>
              </td>
              <td class="py-3 px-4 font-mono font-bold text-slate-800">
                {{ t.nomor_polisi || '-' }}
              </td>
              <td class="py-3 px-4 capitalize">
                {{ t.jenis_kendaraan || '-' }}
              </td>
              <td class="py-3 px-4 text-slate-600 font-mono text-[11px]">
                {{ formatDateTime(t.waktu_masuk) }}
              </td>
              <td class="py-3 px-4">
                <span
                  class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                  :class="t.status === 'masuk' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600'"
                >
                  {{ t.status === 'masuk' ? 'Di Dalam' : 'Keluar' }}
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
import { FileSpreadsheet, UserPlus } from 'lucide-vue-next'

definePageMeta({
  layout: 'admin',
  middleware: 'auth',
})

const api = useApi()
const summary = ref({
  tiket_aktif: 0,
  tiket_hari_ini: 0,
  pendapatan_hari_ini: 0,
  total_petugas: 0,
  total_member: 0,
})
const recentTickets = ref<any[]>([])

const fetchData = async () => {
  try {
    const [sumRes, tickRes] = await Promise.all([
      api.get('/rekap/summary'),
      api.get('/tiket'),
    ])

    if (sumRes.data?.success) {
      summary.value = sumRes.data.data
    }
    recentTickets.value = tickRes.data || []
  } catch (err) {
    console.error('Gagal mengambil data admin dashboard:', err)
  }
}

const formatDateTime = (str: string) => {
  if (!str) return '-'
  return new Date(str).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const formatRupiah = (val: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(val || 0)
}

onMounted(() => {
  fetchData()
})
</script>
