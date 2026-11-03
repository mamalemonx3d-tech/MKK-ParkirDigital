<template>
  <div class="flex-1 flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto w-full">
      <!-- Title & Header -->
      <div class="text-center mb-10">
        <div class="inline-flex justify-center mb-4">
          <BrandLogo :showSubtitle="false" size="lg" />
        </div>
        <h2 class="text-2xl sm:text-3xl font-bold text-[#001a4e] tracking-tight">
          Sistem Pengelolaan Parkir Digital
        </h2>
        <p class="mt-2 text-sm text-slate-600 max-w-xl mx-auto">
          Silakan pilih gerbang operasional atau masuk ke portal petugas untuk mengelola transaksi.
        </p>
      </div>

      <!-- Action Cards Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- 1. Kiosk Tiket Masuk -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs hover:border-[#0099ff] hover:shadow-md transition flex flex-col p-6">
          <div class="w-12 h-12 rounded-lg bg-[#e6f4ff] text-[#0099ff] flex items-center justify-center mb-4">
            <Ticket class="w-6 h-6" />
          </div>
          <h3 class="text-base font-bold text-slate-900 mb-1">
            Kiosk Tiket Masuk
          </h3>
          <p class="text-xs text-slate-500 mb-6 flex-1 leading-relaxed">
            Antarmuka mandiri bagi pengendara umum untuk mencetak tiket parkir di pintu masuk.
          </p>
          <NuxtLink
            to="/user"
            class="w-full py-2.5 px-4 bg-[#0099ff] hover:bg-[#0084de] text-white text-xs font-bold rounded-lg text-center transition flex items-center justify-center gap-2"
          >
            <span>Buka Layar Kiosk</span>
            <ArrowRight class="w-4 h-4" />
          </NuxtLink>
        </div>

        <!-- 2. Pos Kasir Keluar -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs hover:border-[#ff9900] hover:shadow-md transition flex flex-col p-6">
          <div class="w-12 h-12 rounded-lg bg-[#fff5e6] text-[#ff9900] flex items-center justify-center mb-4">
            <Receipt class="w-6 h-6" />
          </div>
          <h3 class="text-base font-bold text-slate-900 mb-1">
            Pos Kasir Keluar
          </h3>
          <p class="text-xs text-slate-500 mb-6 flex-1 leading-relaxed">
            Loket verifikasi tiket, pemindaian QR keluar, perhitungan durasi, dan pencatatan pembayaran.
          </p>
          <NuxtLink
            to="/petugas/keluar"
            class="w-full py-2.5 px-4 bg-[#001a4e] hover:bg-[#001033] text-white text-xs font-bold rounded-lg text-center transition flex items-center justify-center gap-2"
          >
            <span>Buka Loket Kasir</span>
            <ArrowRight class="w-4 h-4" />
          </NuxtLink>
        </div>

        <!-- 3. Portal Petugas / Admin -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs hover:border-slate-400 hover:shadow-md transition flex flex-col p-6">
          <div class="w-12 h-12 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center mb-4">
            <ShieldCheck class="w-6 h-6" />
          </div>
          <h3 class="text-base font-bold text-slate-900 mb-1">
            Portal Staf & Admin
          </h3>
          <p class="text-xs text-slate-500 mb-6 flex-1 leading-relaxed">
            Login untuk petugas loket dan administrator untuk memantau rekap harian, data member, dan akun.
          </p>
          <NuxtLink
            to="/login"
            class="w-full py-2.5 px-4 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-lg text-center transition flex items-center justify-center gap-2"
          >
            <span>Masuk ke Akun</span>
            <ArrowRight class="w-4 h-4" />
          </NuxtLink>
        </div>
      </div>

      <!-- Banner Pengendara: Katalog & Cek Status Member -->
      <div class="mt-6 bg-[#001a4e] rounded-xl p-5 text-white shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4 border border-[#001033]">
        <div class="flex items-center gap-3.5">
          <div class="w-10 h-10 rounded-lg bg-white/10 text-[#0099ff] border border-white/10 flex items-center justify-center shrink-0">
            <CreditCard class="w-5 h-5 text-[#ff9900]" />
          </div>
          <div>
            <h4 class="text-sm font-bold text-white">Pengendara: Cek Status Kartu Member & Katalog</h4>
            <p class="text-xs text-slate-300">Lihat paket langganan bulanan motor/mobil dan cek masa aktif kartu Anda secara online.</p>
          </div>
        </div>
        <NuxtLink
          to="/katalog-member"
          class="px-4 py-2 rounded-lg bg-[#0099ff] hover:bg-[#0084de] text-white text-xs font-bold transition flex items-center gap-2 shrink-0 shadow-xs"
        >
          <span>Buka Katalog Member</span>
          <ArrowRight class="w-4 h-4" />
        </NuxtLink>
      </div>

      <!-- Quick Status Strip (Dynamic Backend Health) -->
      <div class="mt-8 bg-white rounded-xl border border-slate-200 p-4 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs">
        <div class="flex items-center gap-2.5">
          <div
            class="w-2.5 h-2.5 rounded-full"
            :class="isServerOnline ? 'bg-emerald-500 animate-pulse' : 'bg-red-500'"
          ></div>
          <span class="text-slate-700 font-medium">
            {{ isServerOnline ? 'Koneksi Server API Normal' : 'Menghubungkan ke Server API...' }}
          </span>
        </div>
        <div class="flex items-center gap-5 text-slate-500 font-mono text-[11px]">
          <span>Tarif Standar: Rp 3.000 / Jam</span>
          <span>&bull;</span>
          <span>Dukungan: Motor & Mobil</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Ticket, Receipt, ShieldCheck, ArrowRight, CreditCard } from 'lucide-vue-next'
import BrandLogo from '~/components/BrandLogo.vue'

const api = useApi()
const isServerOnline = ref(true)

onMounted(async () => {
  try {
    const res = await api.get('/health')
    if (res.data?.status === 'ok') {
      isServerOnline.value = true
    }
  } catch {
    isServerOnline.value = false
  }
})
</script>
