<template>
  <div class="space-y-6">
    <!-- Top Welcome Banner -->
    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-xl font-bold text-[#001a4e]">
          Dashboard Operasional Pos Gerbang
        </h2>
        <p class="text-xs text-slate-500 mt-1">
          Pantau volume kendaraan masuk dan keluar secara langsung di area parkir.
        </p>
      </div>

      <div class="flex items-center gap-2">
        <NuxtLink
          to="/petugas/scan"
          class="px-3.5 py-2 rounded-lg bg-[#0099ff] hover:bg-[#0084de] text-white text-xs font-bold transition flex items-center gap-2"
        >
          <Camera class="w-4 h-4" />
          <span>Scan Masuk</span>
        </NuxtLink>
        <NuxtLink
          to="/petugas/keluar"
          class="px-3.5 py-2 rounded-lg bg-[#ff9900] hover:bg-[#e68a00] text-slate-900 text-xs font-bold transition flex items-center gap-2"
        >
          <Receipt class="w-4 h-4" />
          <span>Pos Kasir Keluar</span>
        </NuxtLink>
      </div>
    </div>

    <!-- Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- 1. Tiket Aktif (Kendaraan di dalam) -->
      <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
        <div class="flex items-center justify-between mb-3">
          <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Kendaraan Aktif</span>
          <div class="p-2 rounded-lg bg-[#e6f4ff] text-[#0099ff]">
            <Car class="w-4 h-4" />
          </div>
        </div>
        <div class="text-2xl font-black font-mono text-[#001a4e]">
          {{ summary.tiket_aktif }}
        </div>
        <p class="text-[11px] text-slate-400 mt-1">Sesi parkir belum keluar</p>
      </div>

      <!-- 2. Tiket Hari Ini -->
      <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
        <div class="flex items-center justify-between mb-3">
          <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Masuk Hari Ini</span>
          <div class="p-2 rounded-lg bg-emerald-50 text-emerald-600">
            <Ticket class="w-4 h-4" />
          </div>
        </div>
        <div class="text-2xl font-black font-mono text-slate-800">
          {{ summary.tiket_hari_ini }}
        </div>
        <p class="text-[11px] text-slate-400 mt-1">Total tiket dicetak hari ini</p>
      </div>

      <!-- 3. Pendapatan Kasir Hari Ini -->
      <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
        <div class="flex items-center justify-between mb-3">
          <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Pendapatan Hari Ini</span>
          <div class="p-2 rounded-lg bg-[#fff5e6] text-[#ff9900]">
            <BadgeDollarSign class="w-4 h-4" />
          </div>
        </div>
        <div class="text-2xl font-black font-mono text-emerald-700">
          {{ formatRupiah(summary.pendapatan_hari_ini) }}
        </div>
        <p class="text-[11px] text-slate-400 mt-1">Total pembayaran selesai</p>
      </div>

      <!-- 4. Total Member -->
      <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
        <div class="flex items-center justify-between mb-3">
          <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Langganan Member</span>
          <div class="p-2 rounded-lg bg-indigo-50 text-indigo-600">
            <CreditCard class="w-4 h-4" />
          </div>
        </div>
        <div class="text-2xl font-black font-mono text-slate-800">
          {{ summary.total_member }}
        </div>
        <p class="text-[11px] text-slate-400 mt-1">Member aktif terdaftar</p>
      </div>
    </div>

    <!-- Quick Shortcuts / Operating Stations -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-xs">
        <div class="flex items-center gap-3 mb-4">
          <div class="p-2.5 rounded-lg bg-[#e6f4ff] text-[#0099ff]">
            <ScanLine class="w-5 h-5" />
          </div>
          <div>
            <h3 class="text-sm font-bold text-slate-900">Operasi Gerbang Masuk</h3>
            <p class="text-xs text-slate-500">Scan QR kartu fisik pengendara member yang masuk.</p>
          </div>
        </div>
        <p class="text-xs text-slate-600 leading-relaxed mb-4">
          Sistem akan memvalidasi masa berlaku kartu dan memeriksa status anti-passback untuk memastikan kartu tidak sedang aktif digunakan oleh kendaraan lain.
        </p>
        <NuxtLink
          to="/petugas/scan"
          class="inline-flex items-center gap-2 px-4 py-2 bg-[#001a4e] text-white text-xs font-bold rounded-lg hover:bg-[#001033] transition"
        >
          <span>Buka Pintu Masuk</span>
          <ArrowRight class="w-3.5 h-3.5" />
        </NuxtLink>
      </div>

      <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-xs">
        <div class="flex items-center gap-3 mb-4">
          <div class="p-2.5 rounded-lg bg-[#fff5e6] text-[#ff9900]">
            <ReceiptText class="w-5 h-5" />
          </div>
          <div>
            <h3 class="text-sm font-bold text-slate-900">Operasi Pos Keluar & Kasir</h3>
            <p class="text-xs text-slate-500">Scan tiket umum atau kartu member sebelum palang dibuka.</p>
          </div>
        </div>
        <p class="text-xs text-slate-600 leading-relaxed mb-4">
          Secara otomatis mengkalkulasi durasi waktu parkir per jam (Rp 3.000/jam) untuk pengendara non-member, serta membebaskan biaya (Rp 0) untuk pengendara member.
        </p>
        <NuxtLink
          to="/petugas/keluar"
          class="inline-flex items-center gap-2 px-4 py-2 bg-[#ff9900] text-slate-900 text-xs font-bold rounded-lg hover:bg-[#e68a00] transition"
        >
          <span>Buka Pos Keluar</span>
          <ArrowRight class="w-3.5 h-3.5" />
        </NuxtLink>
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
  Ticket,
  BadgeDollarSign,
  CreditCard,
  ScanLine,
  ReceiptText,
  ArrowRight,
} from 'lucide-vue-next'

definePageMeta({
  layout: 'petugas',
  middleware: 'auth',
})

const api = useApi()
const summary = ref({
  tiket_aktif: 0,
  tiket_hari_ini: 0,
  pendapatan_hari_ini: 0,
  total_member: 0,
})

const fetchSummary = async () => {
  try {
    const res = await api.get('/rekap/summary')
    if (res.data?.success) {
      summary.value = res.data.data
    }
  } catch (err) {
    console.error('Gagal mengambil ringkasan petugas:', err)
  }
}

const formatRupiah = (val: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(val || 0)
}

onMounted(() => {
  fetchSummary()
})
</script>
