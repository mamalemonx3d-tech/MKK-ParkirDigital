<template>
  <div class="min-h-screen bg-slate-900 flex flex-col justify-between p-4 sm:p-6 text-slate-100 select-none">
    <!-- Kiosk Top Header -->
    <header class="max-w-4xl mx-auto w-full flex items-center justify-between border-b border-slate-800 pb-4">
      <div class="flex items-center gap-3">
        <BrandLogo :showSubtitle="true" subtitle="Mesin Kiosk Pintu Masuk" />
      </div>

      <div class="text-right flex items-center gap-4">
        <!-- Backend Status Pill -->
        <div class="hidden sm:flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-mono border"
             :class="isBackendOnline ? 'bg-emerald-950/40 text-emerald-400 border-emerald-800' : 'bg-red-950/40 text-red-400 border-red-800 animate-pulse'">
          <span class="w-2 h-2 rounded-full" :class="isBackendOnline ? 'bg-emerald-400' : 'bg-red-400'"></span>
          <span>{{ isBackendOnline ? 'Server Online' : 'Server Offline (Nyalakan php artisan serve)' }}</span>
        </div>

        <div>
          <div class="text-xs text-slate-400 font-mono">{{ liveDate }}</div>
          <div class="text-xl font-bold font-mono text-[#0099ff]">{{ liveTime }}</div>
        </div>
      </div>
    </header>

    <!-- Main Kiosk Body -->
    <main class="max-w-xl mx-auto w-full my-auto py-8">
      <!-- State 1: Ready to take ticket -->
      <div v-if="!tiketData" class="bg-slate-800/90 rounded-2xl border border-slate-700 p-8 text-center shadow-2xl">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-700/80 text-[#0099ff] mb-6">
          <Ticket class="w-8 h-8" />
        </div>

        <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight mb-2">
          Selamat Datang
        </h1>
        <p class="text-sm text-slate-400 mb-6 max-w-sm mx-auto">
          Silakan tekan tombol di bawah ini untuk mencetak tiket parkir masuk Anda.
        </p>

        <!-- Offline Warning Banner -->
        <div v-if="!isBackendOnline" class="mb-6 p-3 rounded-xl bg-amber-950/60 border border-amber-800/80 text-xs text-amber-300 text-left space-y-1">
          <div class="font-bold flex items-center gap-1.5 text-amber-200">
            <AlertTriangle class="w-4 h-4 text-amber-400 shrink-0" />
            <span>Backend Belum Berjalan</span>
          </div>
          <p class="text-[11px] leading-relaxed text-amber-300/90">
            Buka terminal baru di folder backend lalu ketik:
            <code class="bg-black/40 px-1.5 py-0.5 rounded text-amber-100 font-mono">php artisan serve</code>
          </p>
        </div>

        <!-- Big Tactile Push Button -->
        <button
          @click="ambilTiket"
          :disabled="isLoading"
          class="w-full py-6 px-8 rounded-xl font-black text-lg sm:text-xl tracking-wider uppercase transition transform active:scale-98 shadow-lg cursor-pointer flex items-center justify-center gap-3"
          :class="isLoading ? 'bg-slate-700 text-slate-400 cursor-not-allowed' : 'bg-[#0099ff] hover:bg-[#0084de] text-white ring-4 ring-[#0099ff]/20'"
        >
          <Printer v-if="!isLoading" class="w-6 h-6 animate-pulse" />
          <Loader2 v-else class="w-6 h-6 animate-spin" />
          <span>{{ isLoading ? 'Mencetak Tiket...' : 'Ambil Tiket Masuk' }}</span>
        </button>

        <!-- Error Alert -->
        <div v-if="errorMessage" class="mt-4 p-3.5 rounded-lg bg-red-950/80 border border-red-700 text-xs text-red-200 text-left flex items-start gap-2">
          <AlertTriangle class="w-4 h-4 text-red-400 shrink-0 mt-0.5" />
          <div class="leading-relaxed">{{ errorMessage }}</div>
        </div>

        <div class="mt-8 flex items-center justify-center gap-6 text-xs text-slate-500 font-mono">
          <span>Tarif: Rp 3.000 / Jam</span>
          <span>•</span>
          <span>Palang Otomatis</span>
        </div>
      </div>

      <!-- State 2: Ticket Printed Receipt -->
      <div v-else class="bg-white text-slate-900 rounded-2xl p-6 sm:p-8 shadow-2xl border border-slate-300 animate-in fade-in zoom-in-95 duration-200">
        <!-- Physical Receipt Look -->
        <div class="text-center pb-4 border-b border-dashed border-slate-300">
          <div class="inline-flex justify-center mb-2">
            <BrandLogo :showSubtitle="false" />
          </div>
          <h2 class="text-xs font-bold text-slate-600 uppercase tracking-widest mt-1">
            Tiket Masuk Parkir
          </h2>
        </div>

        <!-- Ticket Info -->
        <div class="my-6 text-center space-y-4">
          <div>
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">
              Nomor Tiket
            </span>
            <span class="text-4xl font-black font-mono text-[#001a4e] tracking-tight">
              {{ tiketData.kode_tiket }}
            </span>
          </div>

          <!-- QR Code Display -->
          <div class="flex justify-center py-2">
            <div class="p-3 bg-white rounded-xl border border-slate-200 shadow-xs inline-block">
              <QrcodeVue :value="tiketData.qr_code" :size="160" level="M" />
            </div>
          </div>

          <div class="bg-slate-50 p-3 rounded-lg border border-slate-200 text-xs space-y-1 font-mono">
            <div class="flex justify-between text-slate-600">
              <span>Waktu Masuk:</span>
              <span class="font-bold text-slate-800">{{ formatDateTime(tiketData.waktu_masuk) }}</span>
            </div>
            <div class="flex justify-between text-slate-600">
              <span>Status:</span>
              <span class="font-bold text-emerald-700 uppercase">Aktif</span>
            </div>
          </div>
        </div>

        <!-- Receipt Note -->
        <p class="text-[11px] text-slate-500 text-center leading-relaxed pb-4 border-b border-dashed border-slate-300">
          Simpan tiket ini dengan baik. Tunjukkan tiket kepada petugas di pos kasir keluar saat menyelesaikan pembayaran.
        </p>

        <!-- Actions -->
        <div class="mt-6 flex flex-col sm:flex-row gap-3">
          <button
            @click="cetakStruk"
            class="flex-1 py-3 px-4 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-bold transition flex items-center justify-center gap-2 cursor-pointer"
          >
            <Printer class="w-4 h-4" />
            <span>Cetak Fisik</span>
          </button>
          <button
            @click="resetKiosk"
            class="py-3 px-5 bg-[#0099ff] hover:bg-[#0084de] text-white rounded-lg text-xs font-bold transition flex items-center justify-center gap-2 cursor-pointer"
          >
            <span>Selesai ({{ countdown }}s)</span>
          </button>
        </div>
      </div>
    </main>

    <!-- Kiosk Footer Info -->
    <footer class="max-w-4xl mx-auto w-full text-center text-xs text-slate-600 font-mono pt-4 border-t border-slate-800">
      Pos Gerbang Masuk &mdash; E-Parkir Terminal
    </footer>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { Ticket, Printer, Loader2, AlertTriangle } from 'lucide-vue-next'
import QrcodeVue from 'qrcode.vue'
import BrandLogo from '~/components/BrandLogo.vue'

definePageMeta({
  layout: false,
})

const api = useApi()
const isLoading = ref(false)
const isBackendOnline = ref(true)
const errorMessage = ref('')
const tiketData = ref<any>(null)
const countdown = ref(10)
let countdownInterval: any = null

const liveDate = ref('')
const liveTime = ref('')
let clockTimer: any = null
let healthCheckTimer: any = null

const updateClock = () => {
  const now = new Date()
  liveDate.value = now.toLocaleDateString('id-ID', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
  liveTime.value = now.toLocaleTimeString('id-ID', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
  })
}

const checkBackendHealth = async () => {
  try {
    // Ping ringan ke backend
    await fetch('http://127.0.0.1:8000/up', { method: 'GET', signal: AbortSignal.timeout(2000) })
    isBackendOnline.value = true
  } catch {
    isBackendOnline.value = false
  }
}

onMounted(() => {
  updateClock()
  clockTimer = setInterval(updateClock, 1000)

  checkBackendHealth()
  healthCheckTimer = setInterval(checkBackendHealth, 5000)
})

onUnmounted(() => {
  if (clockTimer) clearInterval(clockTimer)
  if (countdownInterval) clearInterval(countdownInterval)
  if (healthCheckTimer) clearInterval(healthCheckTimer)
})

const ambilTiket = async () => {
  isLoading.value = true
  errorMessage.value = ''

  try {
    const res = await api.post('/tiket')
    if (res.data?.success) {
      tiketData.value = res.data.data
      isBackendOnline.value = true
      startCountdown()
    } else {
      errorMessage.value = res.data?.message || 'Gagal membuat tiket.'
    }
  } catch (err: any) {
    isBackendOnline.value = false
    errorMessage.value =
      err.customMessage ||
      err.response?.data?.message ||
      'Server backend belum menyala. Silakan jalankan `php artisan serve` di terminal folder backend (port 8000).'
  } finally {
    isLoading.value = false
  }
}

const startCountdown = () => {
  countdown.value = 10
  if (countdownInterval) clearInterval(countdownInterval)
  countdownInterval = setInterval(() => {
    countdown.value--
    if (countdown.value <= 0) {
      resetKiosk()
    }
  }, 1000)
}

const resetKiosk = () => {
  if (countdownInterval) clearInterval(countdownInterval)
  tiketData.value = null
  errorMessage.value = ''
}

const cetakStruk = () => {
  window.print()
}

const formatDateTime = (dateStr: string) => {
  if (!dateStr) return '-'
  const d = new Date(dateStr)
  return d.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  }) + ' ' + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB'
}
</script>
