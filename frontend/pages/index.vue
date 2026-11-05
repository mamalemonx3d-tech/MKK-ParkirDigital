<template>
  <div class="flex-1 flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-8 font-sans">
    <div class="max-w-4xl mx-auto w-full space-y-8">
      <!-- Title & Header -->
      <div class="text-center space-y-3">
        <div class="inline-flex justify-center mb-2">
          <BrandLogo :showSubtitle="false" size="lg" />
        </div>
        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-normal text-slate-900 tracking-tight font-serif">
          Sistem Pengelolaan Parkir Digital
        </h1>
        <p class="text-sm text-slate-600 max-w-xl mx-auto">
          Silakan pilih gerbang operasional masuk/keluar atau masuk ke portal petugas untuk mengelola transaksi.
        </p>
      </div>

      <!-- Action Cards Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- 1. Pintu Masuk (Kiosk & Member) -->
        <div class="bg-white rounded-2xl border-2 border-slate-200 hover:border-[#3b82f6] shadow-xs hover:shadow-md transition flex flex-col p-6">
          <div class="w-12 h-12 rounded-xl bg-[#eff6ff] text-[#3b82f6] flex items-center justify-center mb-4">
            <Ticket class="w-6 h-6" />
          </div>
          <h2 class="text-lg font-bold text-slate-900 mb-1 font-sans">
            Kiosk Tiket Masuk
          </h2>
          <p class="text-xs text-slate-500 mb-6 flex-1 leading-relaxed">
            Terminal pos gerbang masuk untuk mencetak karcis non-member dan pemindaian cepat QR kartu member.
          </p>
          <NuxtLink
            to="/user"
            class="w-full py-2.5 px-4 bg-[#3b82f6] hover:bg-[#2563eb] text-white border border-blue-600 text-xs font-bold rounded-xl text-center transition flex items-center justify-center gap-2 shadow-xs"
          >
            <span>Buka Layar Kiosk</span>
            <ArrowRight class="w-4 h-4 text-[#f59e0b]" />
          </NuxtLink>
        </div>

        <!-- 2. Pos Kasir Keluar -->
        <div class="bg-white rounded-2xl border-2 border-slate-200 hover:border-[#f59e0b] shadow-xs hover:shadow-md transition flex flex-col p-6">
          <div class="w-12 h-12 rounded-xl bg-[#fef3c7] text-slate-900 border border-[#f59e0b] flex items-center justify-center mb-4 font-bold">
            <Receipt class="w-6 h-6 text-[#3b82f6]" />
          </div>
          <h2 class="text-lg font-bold text-slate-900 mb-1 font-sans">
            Pos Kasir Keluar
          </h2>
          <p class="text-xs text-slate-500 mb-6 flex-1 leading-relaxed">
            Loket verifikasi tiket, pemindaian QR keluar, kalkulasi durasi otomatis, dan pembayaran kasir.
          </p>
          <NuxtLink
            to="/petugas/keluar"
            class="w-full py-2.5 px-4 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl text-center transition flex items-center justify-center gap-2 shadow-xs"
          >
            <span>Buka Loket Kasir</span>
            <ArrowRight class="w-4 h-4 text-[#f59e0b]" />
          </NuxtLink>
        </div>

        <!-- 3. Portal Petugas / Admin -->
        <div class="bg-white rounded-2xl border-2 border-slate-200 hover:border-[#3b82f6] shadow-xs hover:shadow-md transition flex flex-col p-6">
          <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center mb-4">
            <ShieldCheck class="w-6 h-6 text-[#3b82f6]" />
          </div>
          <h2 class="text-lg font-bold text-slate-900 mb-1 font-sans">
            Portal Staf & Admin
          </h2>
          <p class="text-xs text-slate-500 mb-6 flex-1 leading-relaxed">
            Login untuk petugas loket dan administrator untuk memantau rekap transaksi harian dan data akun.
          </p>
          <NuxtLink
            to="/login"
            class="w-full py-2.5 px-4 bg-[#3b82f6] hover:bg-[#2563eb] text-white border border-blue-600 text-xs font-bold rounded-xl text-center transition flex items-center justify-center gap-2 shadow-xs"
          >
            <span>Masuk ke Akun</span>
            <ArrowRight class="w-4 h-4 text-[#f59e0b]" />
          </NuxtLink>
        </div>
      </div>

      <!-- Banner Pengendara: Katalog Member -->
      <div class="bg-[#1e293b] rounded-2xl p-6 text-white border border-slate-700 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-4">
          <div class="w-11 h-11 rounded-xl bg-white/10 text-[#f59e0b] flex items-center justify-center shrink-0">
            <CreditCard class="w-6 h-6" />
          </div>
          <div>
            <h3 class="text-base font-bold text-white">Pengendara: Cek Status Kartu Member & Katalog</h3>
            <p class="text-xs text-slate-300 mt-0.5">Lihat paket langganan bulanan motor/mobil dan cek masa aktif kartu Anda secara online.</p>
          </div>
        </div>
        <NuxtLink
          to="/katalog-member"
          class="px-5 py-2.5 rounded-xl bg-[#f59e0b] hover:bg-[#d97706] text-slate-950 text-xs font-bold transition flex items-center gap-2 shrink-0 shadow-xs"
        >
          <span>Buka Katalog Member</span>
          <ArrowRight class="w-4 h-4" />
        </NuxtLink>
      </div>

      <!-- Quick Status Strip -->
      <div class="bg-white rounded-2xl border border-slate-200 p-4 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs">
        <div class="flex items-center gap-2.5">
          <div
            class="w-2.5 h-2.5 rounded-full"
            :class="isServerOnline ? 'bg-[#3b82f6] animate-pulse' : 'bg-red-500'"
          ></div>
          <span class="text-slate-800 font-bold">
            {{ isServerOnline ? 'Koneksi Server API Normal' : 'Menghubungkan ke Server API...' }}
          </span>
        </div>
        <div class="flex items-center gap-4 text-slate-500 font-mono text-[11px]">
          <span>Tarif Standar: <strong class="text-slate-800">Rp 3.000 / Jam</strong></span>
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
