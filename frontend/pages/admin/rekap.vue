<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-lg font-bold text-[#001a4e]">
          Laporan Rekapitulasi & Analisis Pendapatan
        </h2>
        <p class="text-xs text-slate-500 mt-0.5">
          Tinjau performa volume kendaraan dan omset parkir berkala.
        </p>
      </div>

      <div class="flex items-center gap-2">
        <button
          @click="exportPdf"
          class="px-3.5 py-2 rounded-lg bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold transition flex items-center gap-2 cursor-pointer shadow-xs"
        >
          <FileDown class="w-4 h-4" />
          <span>Export PDF</span>
        </button>
      </div>
    </div>

    <!-- Period Tabs & Date Filter Strip -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
      <!-- Tabs (Harian, Mingguan, Bulanan) -->
      <div class="inline-flex rounded-lg bg-slate-100 p-1">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          @click="changeTab(tab.id)"
          class="px-4 py-1.5 rounded-md text-xs font-bold transition cursor-pointer"
          :class="activeTab === tab.id ? 'bg-white text-[#001a4e] shadow-xs' : 'text-slate-500 hover:text-slate-800'"
        >
          {{ tab.label }}
        </button>
      </div>

      <!-- Date Input -->
      <div class="flex items-center gap-2 w-full sm:w-auto">
        <span class="text-xs font-semibold text-slate-500">Pilih Tanggal:</span>
        <input
          v-model="selectedDate"
          type="date"
          @change="fetchReport"
          class="px-3 py-1.5 text-xs rounded-lg border border-slate-300 font-mono font-bold bg-white"
        />
      </div>
    </div>

    <!-- Summary Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">
          Total Kendaraan Masuk
        </span>
        <div class="text-2xl font-black font-mono text-slate-900">
          {{ summaryTotals.total_masuk }}
        </div>
      </div>

      <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">
          Total Kendaraan Keluar
        </span>
        <div class="text-2xl font-black font-mono text-slate-900">
          {{ summaryTotals.total_keluar }}
        </div>
      </div>

      <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">
          Total Pendapatan Akumulasi
        </span>
        <div class="text-2xl font-black font-mono text-emerald-700">
          {{ formatRupiah(summaryTotals.total_pendapatan) }}
        </div>
      </div>
    </div>

    <!-- Table of Periodic Detail -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
      <div class="p-4 border-b border-slate-200 bg-slate-50/50">
        <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">
          Tabel Rincian Periode ({{ activeTabLabel }})
        </h3>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
              <th class="py-3 px-4">{{ activeTab === 'harian' ? 'Kode Tiket' : 'Tanggal / Periode' }}</th>
              <th class="py-3 px-4">{{ activeTab === 'harian' ? 'Plat Nomor' : 'Hari' }}</th>
              <th class="py-3 px-4">{{ activeTab === 'harian' ? 'Kendaraan' : 'Total Masuk' }}</th>
              <th class="py-3 px-4">{{ activeTab === 'harian' ? 'Waktu Masuk' : 'Total Keluar' }}</th>
              <th class="py-3 px-4 text-right">Pendapatan</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <!-- Harian Detail Row -->
            <template v-if="activeTab === 'harian'">
              <tr v-if="!reportData.detail || reportData.detail.length === 0">
                <td colspan="5" class="text-center py-8 text-slate-400">
                  Tidak ada transaksi parkir pada tanggal ini.
                </td>
              </tr>
              <tr v-for="item in reportData.detail" :key="item.kode_tiket" class="hover:bg-slate-50/80 transition">
                <td class="py-3 px-4 font-mono font-bold text-[#001a4e]">{{ item.kode_tiket }}</td>
                <td class="py-3 px-4 font-mono font-bold text-slate-700">{{ item.nomor_polisi || '-' }}</td>
                <td class="py-3 px-4 capitalize">{{ item.jenis_kendaraan || '-' }}</td>
                <td class="py-3 px-4 font-mono text-slate-600 text-[11px]">{{ formatTime(item.waktu_masuk) }}</td>
                <td class="py-3 px-4 text-right font-mono font-bold text-emerald-700">{{ formatRupiah(item.biaya) }}</td>
              </tr>
            </template>

            <!-- Mingguan / Bulanan Rows -->
            <template v-else>
              <tr v-if="!reportData.detail || reportData.detail.length === 0">
                <td colspan="5" class="text-center py-8 text-slate-400">
                  Tidak ada data untuk periode ini.
                </td>
              </tr>
              <tr v-for="item in reportData.detail" :key="item.tanggal" class="hover:bg-slate-50/80 transition">
                <td class="py-3 px-4 font-mono font-bold text-slate-900">{{ item.tanggal }}</td>
                <td class="py-3 px-4 font-medium text-slate-700">{{ item.nama_hari || '-' }}</td>
                <td class="py-3 px-4 font-mono">{{ item.total_masuk }} unit</td>
                <td class="py-3 px-4 font-mono">{{ item.total_keluar }} unit</td>
                <td class="py-3 px-4 text-right font-mono font-bold text-emerald-700">{{ formatRupiah(item.total_pendapatan) }}</td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { FileDown } from 'lucide-vue-next'
import jsPDF from 'jspdf'
import autoTable from 'jspdf-autotable'

definePageMeta({
  layout: 'admin',
  middleware: 'auth',
})

const api = useApi()
const activeTab = ref<'harian' | 'mingguan' | 'bulanan'>('harian')
const selectedDate = ref(new Date().toISOString().split('T')[0])
const reportData = ref<any>({})

const tabs = [
  { id: 'harian', label: 'Rekap Harian' },
  { id: 'mingguan', label: 'Rekap Mingguan' },
  { id: 'bulanan', label: 'Rekap Bulanan' },
]

const activeTabLabel = computed(() => {
  return tabs.find((t) => t.id === activeTab.value)?.label || ''
})

const summaryTotals = computed(() => {
  if (activeTab.value === 'harian') {
    return {
      total_masuk: reportData.value?.total_masuk || 0,
      total_keluar: reportData.value?.total_keluar || 0,
      total_pendapatan: reportData.value?.total_pendapatan || 0,
    }
  } else {
    return {
      total_masuk: reportData.value?.ringkasan?.total_masuk || 0,
      total_keluar: reportData.value?.ringkasan?.total_keluar || 0,
      total_pendapatan: reportData.value?.ringkasan?.total_pendapatan || 0,
    }
  }
})

const changeTab = (tab: any) => {
  activeTab.value = tab
  fetchReport()
}

const fetchReport = async () => {
  try {
    const res = await api.get(`/rekap/${activeTab.value}?date=${selectedDate.value}`)
    if (res.data?.success) {
      reportData.value = res.data.data
    }
  } catch (err) {
    console.error('Gagal mengambil laporan rekap:', err)
  }
}

const exportPdf = () => {
  const doc = new jsPDF()

  // Header Title
  doc.setFontSize(16)
  doc.setFont('helvetica', 'bold')
  doc.text('E-PARKIR - LAPORAN REKAPITULASI PENDAPATAN', 14, 20)

  doc.setFontSize(10)
  doc.setFont('helvetica', 'normal')
  doc.text(`Periode: ${activeTabLabel.value} (${selectedDate.value})`, 14, 27)
  doc.text(`Total Kendaraan Masuk: ${summaryTotals.value.total_masuk} | Keluar: ${summaryTotals.value.total_keluar}`, 14, 33)
  doc.text(`Total Pendapatan: ${formatRupiah(summaryTotals.value.total_pendapatan)}`, 14, 39)

  let tableData = []
  let tableHeaders = []

  if (activeTab.value === 'harian') {
    tableHeaders = [['Kode', 'Plat Nomor', 'Kendaraan', 'Waktu Masuk', 'Tarif']]
    tableData = (reportData.value?.detail || []).map((d: any) => [
      d.kode_tiket,
      d.nomor_polisi || '-',
      d.jenis_kendaraan || '-',
      d.waktu_masuk,
      formatRupiah(d.biaya),
    ])
  } else {
    tableHeaders = [['Tanggal', 'Hari', 'Masuk', 'Keluar', 'Total Pendapatan']]
    tableData = (reportData.value?.detail || []).map((d: any) => [
      d.tanggal,
      d.nama_hari || '-',
      d.total_masuk + ' unit',
      d.total_keluar + ' unit',
      formatRupiah(d.total_pendapatan),
    ])
  }

  autoTable(doc, {
    startY: 46,
    head: tableHeaders,
    body: tableData,
    theme: 'grid',
    styles: { fontSize: 8 },
    headStyles: { fillColor: [0, 26, 78] },
  })

  doc.save(`Laporan_Parkir_${activeTab.value}_${selectedDate.value}.pdf`)
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
  fetchReport()
})
</script>
