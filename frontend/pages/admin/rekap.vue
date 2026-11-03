<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-lg font-bold text-[#001a4e]">
          Laporan Rekapitulasi & Kinerja Parkir
        </h2>
        <p class="text-xs text-slate-500 mt-0.5">
          Tinjau performa transaksi, pendapatan non-member & member, serta evaluasi seluruh petugas loket.
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
      <!-- Tabs -->
      <div class="inline-flex rounded-lg bg-slate-100 p-1 flex-wrap gap-1">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          @click="changeTab(tab.id)"
          class="px-3.5 py-1.5 rounded-md text-xs font-bold transition cursor-pointer"
          :class="activeTab === tab.id ? 'bg-white text-[#001a4e] shadow-xs' : 'text-slate-500 hover:text-slate-800'"
        >
          {{ tab.label }}
        </button>
      </div>

      <!-- Date Input (for harian / mingguan / bulanan / kategori) -->
      <div v-if="activeTab !== 'petugas'" class="flex items-center gap-2 w-full sm:w-auto">
        <span class="text-xs font-semibold text-slate-500">Pilih Tanggal:</span>
        <input
          v-model="selectedDate"
          type="date"
          @change="fetchReport"
          class="px-3 py-1.5 text-xs rounded-lg border border-slate-300 font-mono font-bold bg-white text-slate-800"
        />
      </div>
    </div>

    <!-- Summary Metrics Grid (4-box layout) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">
          Total Masuk Periode Ini
        </span>
        <div class="text-2xl font-black font-mono text-slate-900">
          {{ summaryTotals.total_masuk }} <span class="text-xs font-normal text-slate-400">unit</span>
        </div>
        <p class="text-[11px] text-slate-500 mt-1">
          Non-Member: <span class="font-bold text-slate-700">{{ summaryTotals.masuk_non_member }}</span> &bull; Member: <span class="font-bold text-[#0099ff]">{{ summaryTotals.masuk_member }}</span>
        </p>
      </div>

      <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">
          Pendapatan Non-Member
        </span>
        <div class="text-2xl font-black font-mono text-slate-900">
          {{ formatRupiah(summaryTotals.pendapatan_non_member) }}
        </div>
        <p class="text-[11px] text-slate-500 mt-1">Tarif parkir per jam (karcis)</p>
      </div>

      <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">
          Pendapatan Iuran Member
        </span>
        <div class="text-2xl font-black font-mono text-[#0099ff]">
          {{ formatRupiah(summaryTotals.pendapatan_member) }}
        </div>
        <p class="text-[11px] text-slate-500 mt-1">Pelunasan langganan bulanan</p>
      </div>

      <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">
          Total Omset Akumulasi
        </span>
        <div class="text-2xl font-black font-mono text-emerald-700">
          {{ formatRupiah(summaryTotals.total_pendapatan) }}
        </div>
        <p class="text-[11px] text-emerald-700 font-bold mt-1">100% Tercatat & Terverifikasi</p>
      </div>
    </div>

    <!-- TAB 1: KINERJA SEMUA PETUGAS -->
    <div v-if="activeTab === 'petugas'" class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
      <div class="p-4 border-b border-slate-200 bg-slate-50/50 flex items-center justify-between">
        <div>
          <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">
            Laporan Kinerja & Shift Semua Petugas Gerbang
          </h3>
          <p class="text-[11px] text-slate-500 mt-0.5">Daftar staf aktif operasional pos kasir, pembagian shift, dan kontribusi transaksi.</p>
        </div>
        <button
          @click="fetchReport"
          class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer"
        >
          <RefreshCw class="w-3.5 h-3.5" :class="isLoading ? 'animate-spin' : ''" />
          <span>Perbarui Data</span>
        </button>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
              <th class="py-3 px-4">Nama Petugas</th>
              <th class="py-3 px-4">Alamat Email</th>
              <th class="py-3 px-4">Kontak WhatsApp</th>
              <th class="py-3 px-4">Jadwal Shift</th>
              <th class="py-3 px-4">Tiket Shift Ini</th>
              <th class="py-3 px-4">Koleksi Shift</th>
              <th class="py-3 px-4 text-center">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="petugasList.length === 0">
              <td colspan="7" class="text-center py-8 text-slate-400">
                Belum ada data petugas parkir.
              </td>
            </tr>
            <tr v-for="p in petugasList" :key="p.id" class="hover:bg-slate-50/80 transition">
              <td class="py-3 px-4 font-bold text-slate-900 flex items-center gap-2">
                <div class="w-7 h-7 rounded-full bg-[#001a4e] text-white flex items-center justify-center font-bold text-xs uppercase">
                  {{ p.name.charAt(0) }}
                </div>
                <span>{{ p.name }}</span>
              </td>
              <td class="py-3 px-4 font-mono text-slate-700">{{ p.email }}</td>
              <td class="py-3 px-4 font-mono text-slate-600">{{ p.no_hp || '-' }}</td>
              <td class="py-3 px-4">
                <span class="px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-700">
                  {{ p.shift }}
                </span>
              </td>
              <td class="py-3 px-4 font-mono font-bold text-slate-800">{{ p.total_tiket_shift }} sesi</td>
              <td class="py-3 px-4 font-mono font-bold text-emerald-700">{{ formatRupiah(p.total_koleksi_shift) }}</td>
              <td class="py-3 px-4 text-center">
                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                  <span>{{ p.status }}</span>
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- TAB 2: LAPORAN NON-MEMBER & MEMBER -->
    <div v-else-if="activeTab === 'kategori'" class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Kartu 1: Non-Member -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6 space-y-4">
          <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
              <Ticket class="w-4 h-4 text-[#ff9900]" />
              <span>Pengendara Non-Member (Umum)</span>
            </h3>
            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200">
              Karcis Kertas
            </span>
          </div>

          <div class="grid grid-cols-2 gap-3 text-xs">
            <div class="bg-slate-50 p-3 rounded-lg border border-slate-100">
              <span class="text-slate-400 block text-[10px] font-bold uppercase">Volume Masuk</span>
              <span class="text-xl font-black font-mono text-slate-900">{{ summaryTotals.masuk_non_member }}</span>
            </div>
            <div class="bg-slate-50 p-3 rounded-lg border border-slate-100">
              <span class="text-slate-400 block text-[10px] font-bold uppercase">Volume Keluar</span>
              <span class="text-xl font-black font-mono text-slate-900">{{ summaryTotals.keluar_non_member }}</span>
            </div>
          </div>

          <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
            <span class="text-slate-500 font-medium">Total Pendapatan Tiket:</span>
            <span class="text-base font-black font-mono text-slate-900">{{ formatRupiah(summaryTotals.pendapatan_non_member) }}</span>
          </div>
        </div>

        <!-- Kartu 2: Member -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6 space-y-4">
          <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-sm font-bold text-[#001a4e] flex items-center gap-2">
              <CreditCard class="w-4 h-4 text-[#0099ff]" />
              <span>Pengendara Member (Langganan)</span>
            </h3>
            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-[#e6f4ff] text-[#0099ff] border border-[#0099ff]/30">
              QR RFID Kartu
            </span>
          </div>

          <div class="grid grid-cols-2 gap-3 text-xs">
            <div class="bg-slate-50 p-3 rounded-lg border border-slate-100">
              <span class="text-slate-400 block text-[10px] font-bold uppercase">Kunjungan Masuk</span>
              <span class="text-xl font-black font-mono text-slate-900">{{ summaryTotals.masuk_member }}</span>
            </div>
            <div class="bg-slate-50 p-3 rounded-lg border border-slate-100">
              <span class="text-slate-400 block text-[10px] font-bold uppercase">Kunjungan Keluar</span>
              <span class="text-xl font-black font-mono text-slate-900">{{ summaryTotals.keluar_member }}</span>
            </div>
          </div>

          <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
            <span class="text-slate-500 font-medium">Total Pendapatan Iuran:</span>
            <span class="text-base font-black font-mono text-[#0099ff]">{{ formatRupiah(summaryTotals.pendapatan_member) }}</span>
          </div>
        </div>
      </div>

      <!-- Detail Table for Kategori -->
      <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-200 bg-slate-50/50">
          <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">
            Rincian Sesi Parkir Menurut Kategori ({{ selectedDate }})
          </h3>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                <th class="py-3 px-4">Kode Tiket</th>
                <th class="py-3 px-4">Jenis Akun</th>
                <th class="py-3 px-4">Nomor Polisi</th>
                <th class="py-3 px-4">Kendaraan</th>
                <th class="py-3 px-4">Waktu Masuk</th>
                <th class="py-3 px-4">Status</th>
                <th class="py-3 px-4 text-right">Tarif</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-if="!reportData.detail || reportData.detail.length === 0">
                <td colspan="7" class="text-center py-8 text-slate-400">
                  Tidak ada transaksi parkir pada tanggal ini.
                </td>
              </tr>
              <tr v-for="item in reportData.detail" :key="item.kode_tiket" class="hover:bg-slate-50/80 transition">
                <td class="py-3 px-4 font-mono font-bold text-[#001a4e]">{{ item.kode_tiket }}</td>
                <td class="py-3 px-4">
                  <span
                    class="px-2 py-0.5 rounded text-[10px] font-bold uppercase"
                    :class="item.jenis === 'member' ? 'bg-[#e6f4ff] text-[#0099ff]' : 'bg-slate-100 text-slate-700'"
                  >
                    {{ item.jenis === 'member' ? 'Member' : 'Non-Member' }}
                  </span>
                </td>
                <td class="py-3 px-4 font-mono font-bold text-slate-700">{{ item.nomor_polisi || '-' }}</td>
                <td class="py-3 px-4 capitalize">{{ item.jenis_kendaraan || '-' }}</td>
                <td class="py-3 px-4 font-mono text-slate-600 text-[11px]">{{ formatTime(item.waktu_masuk) }}</td>
                <td class="py-3 px-4 uppercase text-[10px] font-bold" :class="item.status === 'keluar' ? 'text-slate-400' : 'text-emerald-600'">
                  {{ item.status }}
                </td>
                <td class="py-3 px-4 text-right font-mono font-bold text-emerald-700">{{ formatRupiah(item.biaya) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- TAB 3, 4, 5: HARIAN, MINGGUAN, BULANAN -->
    <div v-else class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
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
import { FileDown, Ticket, CreditCard, RefreshCw } from 'lucide-vue-next'
import jsPDF from 'jspdf'
import autoTable from 'jspdf-autotable'

definePageMeta({
  layout: 'admin',
  middleware: 'auth',
})

const api = useApi()
const activeTab = ref<'harian' | 'mingguan' | 'bulanan' | 'petugas' | 'kategori'>('harian')
const selectedDate = ref(new Date().toISOString().split('T')[0])
const reportData = ref<any>({})
const petugasList = ref<any[]>([])
const isLoading = ref(false)

const tabs = [
  { id: 'harian', label: 'Rekap Harian' },
  { id: 'mingguan', label: 'Rekap Mingguan' },
  { id: 'bulanan', label: 'Rekap Bulanan' },
  { id: 'kategori', label: 'Laporan Non-Member & Member' },
  { id: 'petugas', label: 'Laporan Semua Petugas' },
]

const activeTabLabel = computed(() => {
  return tabs.find((t) => t.id === activeTab.value)?.label || ''
})

const summaryTotals = computed(() => {
  if (activeTab.value === 'harian' || activeTab.value === 'kategori') {
    return {
      total_masuk: reportData.value?.total_masuk || 0,
      total_keluar: reportData.value?.total_keluar || 0,
      masuk_non_member: reportData.value?.masuk_non_member || 0,
      masuk_member: reportData.value?.masuk_member || 0,
      keluar_non_member: reportData.value?.keluar_non_member || 0,
      keluar_member: reportData.value?.keluar_member || 0,
      pendapatan_non_member: reportData.value?.pendapatan_non_member || 0,
      pendapatan_member: reportData.value?.pendapatan_member || 0,
      total_pendapatan: reportData.value?.total_pendapatan || 0,
    }
  } else if (activeTab.value === 'petugas') {
    const totalPetugasRevenue = petugasList.value.reduce((acc, p) => acc + (p.total_koleksi_shift || 0), 0)
    const totalPetugasTickets = petugasList.value.reduce((acc, p) => acc + (p.total_tiket_shift || 0), 0)
    return {
      total_masuk: totalPetugasTickets,
      total_keluar: totalPetugasTickets,
      masuk_non_member: '-',
      masuk_member: '-',
      keluar_non_member: '-',
      keluar_member: '-',
      pendapatan_non_member: totalPetugasRevenue,
      pendapatan_member: 0,
      total_pendapatan: totalPetugasRevenue,
    }
  } else {
    return {
      total_masuk: reportData.value?.ringkasan?.total_masuk || 0,
      total_keluar: reportData.value?.ringkasan?.total_keluar || 0,
      masuk_non_member: '-',
      masuk_member: '-',
      keluar_non_member: '-',
      keluar_member: '-',
      pendapatan_non_member: reportData.value?.ringkasan?.pendapatan_non_member || 0,
      pendapatan_member: reportData.value?.ringkasan?.pendapatan_member || 0,
      total_pendapatan: reportData.value?.ringkasan?.total_pendapatan || 0,
    }
  }
})

const changeTab = (tab: any) => {
  activeTab.value = tab
  fetchReport()
}

const fetchReport = async () => {
  isLoading.value = true
  try {
    if (activeTab.value === 'petugas') {
      const res = await api.get('/rekap/petugas')
      if (res.data?.success) {
        petugasList.value = res.data.data || []
      }
    } else {
      const endpoint = activeTab.value === 'kategori' ? 'harian' : activeTab.value
      const res = await api.get(`/rekap/${endpoint}?date=${selectedDate.value}`)
      if (res.data?.success) {
        reportData.value = res.data.data
      }
    }
  } catch (err) {
    console.error('Gagal mengambil laporan rekap:', err)
  } finally {
    isLoading.value = false
  }
}

const exportPdf = () => {
  const doc = new jsPDF()

  // Header Title
  doc.setFontSize(16)
  doc.setFont('helvetica', 'bold')
  doc.text('SECURE PARKING - LAPORAN REKAPITULASI RESMI', 14, 20)

  doc.setFontSize(10)
  doc.setFont('helvetica', 'normal')
  doc.text(`Kategori Laporan: ${activeTabLabel.value} (${selectedDate.value})`, 14, 27)
  doc.text(`Total Masuk: ${summaryTotals.value.total_masuk} | Total Keluar: ${summaryTotals.value.total_keluar}`, 14, 33)
  doc.text(`Total Omset: ${formatRupiah(summaryTotals.value.total_pendapatan)}`, 14, 39)

  let tableData = []
  let tableHeaders = []

  if (activeTab.value === 'petugas') {
    tableHeaders = [['Nama Petugas', 'Email', 'Shift', 'Tiket Shift', 'Koleksi Shift', 'Status']]
    tableData = petugasList.value.map((p: any) => [
      p.name,
      p.email,
      p.shift,
      p.total_tiket_shift + ' tiket',
      formatRupiah(p.total_koleksi_shift),
      p.status,
    ])
  } else if (activeTab.value === 'harian' || activeTab.value === 'kategori') {
    tableHeaders = [['Kode', 'Jenis', 'Plat Nomor', 'Kendaraan', 'Waktu Masuk', 'Tarif']]
    tableData = (reportData.value?.detail || []).map((d: any) => [
      d.kode_tiket,
      d.jenis === 'member' ? 'Member' : 'Non-Member',
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


onMounted(() => {
  fetchReport()
})
</script>
