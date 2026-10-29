<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-lg font-bold text-[#001a4e]">
          Laporan Rekapitulasi Shift Petugas
        </h2>
        <p class="text-xs text-slate-500 mt-0.5">
          Tinjau total kendaraan dan pendapatan harian sebelum pergantian shift loket.
        </p>
      </div>

      <div class="flex items-center gap-3">
        <input
          v-model="selectedDate"
          type="date"
          @change="fetchDailyReport"
          class="px-3 py-2 text-xs rounded-lg border border-slate-300 font-mono font-bold bg-white"
        />
        <button
          @click="printReport"
          class="px-3.5 py-2 bg-[#001a4e] hover:bg-[#001033] text-white text-xs font-bold rounded-lg transition flex items-center gap-2 cursor-pointer shadow-xs"
        >
          <Printer class="w-4 h-4" />
          <span>Cetak Laporan</span>
        </button>
      </div>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">
          Total Masuk Hari Ini
        </span>
        <div class="text-2xl font-black font-mono text-slate-900">
          {{ reportData.total_masuk }}
        </div>
      </div>

      <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">
          Total Keluar Selesai
        </span>
        <div class="text-2xl font-black font-mono text-slate-900">
          {{ reportData.total_keluar }}
        </div>
      </div>

      <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">
          Total Pendapatan Tunai/Kasir
        </span>
        <div class="text-2xl font-black font-mono text-emerald-700">
          {{ formatRupiah(reportData.total_pendapatan) }}
        </div>
      </div>
    </div>

    <!-- Detail Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
      <div class="p-4 border-b border-slate-200 bg-slate-50/50">
        <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">
          Rincian Transaksi Selesai ({{ selectedDate }})
        </h3>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
              <th class="py-3 px-4">Kode Tiket</th>
              <th class="py-3 px-4">Nomor Polisi</th>
              <th class="py-3 px-4">Kendaraan</th>
              <th class="py-3 px-4">Waktu Masuk</th>
              <th class="py-3 px-4">Waktu Keluar</th>
              <th class="py-3 px-4">Durasi</th>
              <th class="py-3 px-4 text-right">Tarif</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="!reportData.detail || reportData.detail.length === 0">
              <td colspan="7" class="text-center py-8 text-slate-400">
                Belum ada transaksi keluar pada tanggal ini.
              </td>
            </tr>
            <tr
              v-for="item in reportData.detail"
              :key="item.kode_tiket"
              class="hover:bg-slate-50/80 transition"
            >
              <td class="py-3 px-4 font-mono font-bold text-[#001a4e]">
                {{ item.kode_tiket }}
              </td>
              <td class="py-3 px-4 font-mono font-bold text-slate-700">
                {{ item.nomor_polisi || '-' }}
              </td>
              <td class="py-3 px-4 capitalize">
                {{ item.jenis_kendaraan || '-' }}
              </td>
              <td class="py-3 px-4 font-mono text-slate-600 text-[11px]">
                {{ formatTime(item.waktu_masuk) }}
              </td>
              <td class="py-3 px-4 font-mono text-slate-600 text-[11px]">
                {{ formatTime(item.waktu_keluar) }}
              </td>
              <td class="py-3 px-4 font-mono text-slate-700">
                {{ item.durasi_menit !== null ? item.durasi_menit + ' m' : '-' }}
              </td>
              <td class="py-3 px-4 text-right font-mono font-bold text-emerald-700">
                {{ formatRupiah(item.biaya) }}
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
import { Printer } from 'lucide-vue-next'

definePageMeta({
  layout: 'petugas',
  middleware: 'auth',
})

const api = useApi()
const selectedDate = ref(new Date().toISOString().split('T')[0])
const reportData = ref<any>({
  total_masuk: 0,
  total_keluar: 0,
  total_pendapatan: 0,
  detail: [],
})

const fetchDailyReport = async () => {
  try {
    const res = await api.get(`/rekap/harian?date=${selectedDate.value}`)
    if (res.data?.success) {
      reportData.value = res.data.data
    }
  } catch (err) {
    console.error('Gagal mengambil laporan harian:', err)
  }
}

const printReport = () => {
  window.print()
}

const formatTime = (str: string) => {
  if (!str) return '-'
  return new Date(str).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
}

const formatRupiah = (val: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(val || 0)
}

onMounted(() => {
  fetchDailyReport()
})
</script>
