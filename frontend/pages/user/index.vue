<template>
  <div class="min-h-screen bg-[#001033] flex flex-col justify-between p-4 sm:p-6 text-slate-100 select-none">
    <!-- Top Hardware Terminal Bar -->
    <header class="max-w-4xl mx-auto w-full flex items-center justify-between border-b border-white/10 pb-4">
      <div class="flex items-center gap-3">
        <BrandLogo :showSubtitle="true" subtitle="Dispenser Tiket Masuk Otomatis" />
      </div>

      <div class="flex items-center gap-4 text-right">
        <!-- Gate Ready Light Indicator -->
        <div class="flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-mono font-bold border transition"
             :class="isBackendOnline ? 'bg-emerald-950/60 text-emerald-400 border-emerald-500/40 shadow-[0_0_10px_rgba(52,211,153,0.2)]' : 'bg-red-950/60 text-red-400 border-red-500/40'">
          <span class="w-2.5 h-2.5 rounded-full" :class="isBackendOnline ? 'bg-emerald-400 animate-pulse' : 'bg-red-500'"></span>
          <span>{{ isBackendOnline ? 'GERBANG SIAP' : 'OFFLINE' }}</span>
        </div>

        <div>
          <div class="text-[11px] text-slate-400 font-mono">{{ liveDate }}</div>
          <div class="text-xl font-black font-mono text-[#0099ff] tracking-tight">{{ liveTime }}</div>
        </div>
      </div>
    </header>

    <!-- Main Kiosk Body -->
    <main class="max-w-xl mx-auto w-full my-auto py-6">
      <!-- 3-Step Driver Guidance Infographic Banner -->
      <div class="mb-5 grid grid-cols-3 gap-2 text-center text-[11px] font-bold uppercase tracking-wider font-mono">
        <div class="py-2 px-1 rounded-lg border transition" :class="!tiketData ? 'bg-[#ff9900] text-slate-950 border-[#ff9900] shadow-sm' : 'bg-white/5 text-slate-400 border-white/10'">
          1. TEKAN TOMBOL
        </div>
        <div class="py-2 px-1 rounded-lg border transition" :class="tiketData ? 'bg-[#0099ff] text-white border-[#0099ff] shadow-sm' : 'bg-white/5 text-slate-400 border-white/10'">
          2. AMBIL TIKET
        </div>
        <div class="py-2 px-1 rounded-lg border transition" :class="tiketData ? 'bg-emerald-500 text-slate-950 border-emerald-500 shadow-sm' : 'bg-white/5 text-slate-400 border-white/10'">
          3. PALANG TERANGKAT
        </div>
      </div>

      <!-- STATE 1: Ready & Waiting for driver to press -->
      <div v-if="!tiketData" class="bg-[#001a4e]/90 rounded-2xl border-2 border-white/10 p-8 text-center shadow-2xl backdrop-blur-xs relative overflow-hidden">
        <!-- Decorative Ambient Light -->
        <div class="absolute -top-12 -left-12 w-36 h-36 bg-[#0099ff]/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -bottom-12 -right-12 w-36 h-36 bg-[#ff9900]/10 rounded-full blur-2xl pointer-events-none"></div>

        <div class="mb-4">
          <span class="inline-block text-[11px] font-mono font-bold uppercase tracking-widest text-[#ff9900] px-3 py-1 rounded-full bg-[#ff9900]/10 border border-[#ff9900]/20">
            PINTU MASUK 01 &bull; TARIF RP 3.000 / JAM
          </span>
        </div>

        <h1 class="text-3xl sm:text-4xl font-black text-white tracking-tight mb-2">
          SILAKAN MASUK
        </h1>
        <p class="text-xs sm:text-sm text-slate-300 mb-8 max-w-sm mx-auto leading-relaxed">
          Tekan tombol di bawah untuk mencetak tiket parkir Anda. Palang gerbang akan otomatis terangkat.
        </p>

        <!-- Huge Tactile Push Button -->
        <button
          @click="ambilTiket"
          :disabled="isLoading"
          class="w-full py-6 px-8 rounded-2xl font-black text-xl sm:text-2xl tracking-wider uppercase transition transform active:scale-98 shadow-xl cursor-pointer flex items-center justify-center gap-3 relative overflow-hidden group"
          :class="isLoading ? 'bg-slate-700 text-slate-400 cursor-not-allowed' : 'bg-gradient-to-r from-[#ff9900] to-[#e68a00] hover:from-[#e68a00] hover:to-[#cc7a00] text-slate-950 ring-4 ring-[#ff9900]/30'"
        >
          <Printer v-if="!isLoading" class="w-7 h-7 stroke-[2.5] group-hover:scale-110 transition-transform" />
          <Loader2 v-else class="w-7 h-7 animate-spin" />
          <span>{{ isLoading ? 'Mencetak Tiket...' : 'TEKAN UNTUK TIKET' }}</span>
        </button>

        <!-- Error Alert Banner -->
        <div v-if="errorMessage" class="mt-5 p-3.5 rounded-xl bg-red-950/80 border border-red-500 text-xs text-red-200 text-left flex items-start gap-2.5">
          <AlertTriangle class="w-4 h-4 text-red-400 shrink-0 mt-0.5" />
          <div class="leading-relaxed">{{ errorMessage }}</div>
        </div>

        <!-- Footer Terminal Notice -->
        <div class="mt-8 pt-5 border-t border-white/10 flex items-center justify-between text-[11px] text-slate-400 font-mono">
          <span>Kamera LPR & Sensor Aktif</span>
          <span>Dilarang Menempel</span>
        </div>
      </div>

      <!-- STATE 2: Ticket Printed & Active Countdown -->
      <div v-else class="bg-white text-slate-900 rounded-2xl p-6 sm:p-8 shadow-2xl border-2 border-slate-300 relative overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <!-- Physical Receipt Look Header -->
        <div class="text-center pb-4 border-b-2 border-dashed border-slate-300 space-y-1">
          <p class="font-black text-sm tracking-widest text-[#001a4e]">SECURE PARKING</p>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">TIKET MASUK PARKIR RESMI</p>
          <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-300 text-[10px] font-bold uppercase font-mono">
            <CheckCircle2 class="w-3 h-3 text-emerald-600" />
            <span>Palang Terbuka — Silakan Melintas</span>
          </div>
        </div>

        <!-- Ticket Body Details -->
        <div class="my-5 text-center space-y-3">
          <div>
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest block">
              NOMOR TIKET ANDA
            </span>
            <span class="text-5xl font-black font-mono text-[#001a4e] tracking-tight">
              {{ tiketData.kode_tiket }}
            </span>
          </div>

          <!-- QR Code for Exit Scanner -->
          <div class="flex justify-center py-1">
            <div class="p-3 bg-white rounded-xl border-2 border-slate-300 shadow-sm inline-block">
              <QrcodeVue :value="tiketData.qr_code" :size="150" level="M" />
            </div>
          </div>

          <div class="bg-slate-50 p-3 rounded-lg border border-slate-200 text-xs font-mono space-y-1">
            <div class="flex justify-between text-slate-600">
              <span>Waktu Masuk:</span>
              <span class="font-bold text-slate-900">{{ formatDateTime(tiketData.waktu_masuk) }}</span>
            </div>
            <div class="flex justify-between text-slate-600">
              <span>Tarif Awal:</span>
              <span class="font-bold text-slate-900">Rp 3.000 / Jam</span>
            </div>
          </div>
        </div>

        <!-- Receipt Safety Note -->
        <p class="text-[11px] text-slate-500 text-center leading-relaxed pb-4 border-b border-dashed border-slate-300">
          Simpan tiket ini untuk scan di pos kasir gerbang keluar. Kehilangan tiket dikenakan denda sesuai ketentuan.
        </p>

        <!-- Actions & 30-Second Progress Countdown -->
        <div class="mt-5 space-y-3">
          <!-- Countdown bar with distinct timer -->
          <div class="space-y-1">
            <div class="flex justify-between text-[11px] font-mono text-slate-500">
              <span>Layar otomatis reset dalam:</span>
              <span class="font-bold text-[#001a4e]">{{ countdown }} Detik</span>
            </div>
            <div class="bg-slate-200 rounded-full h-2 overflow-hidden">
              <div
                class="bg-[#ff9900] h-full transition-all duration-1000 ease-linear rounded-full"
                :style="{ width: `${(countdown / 30) * 100}%` }"
              ></div>
            </div>
          </div>

          <div class="flex flex-col sm:flex-row gap-2.5 pt-1">
            <button
              @click="cetakStruk"
              class="flex-1 py-3 px-4 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 cursor-pointer shadow-xs"
            >
              <Printer class="w-4 h-4 text-[#ff9900]" />
              <span>Cetak Tiket Fisik</span>
            </button>
            <button
              @click="resetKiosk"
              class="py-3 px-6 bg-[#001a4e] hover:bg-[#001033] text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 cursor-pointer shadow-xs"
            >
              <span>Selesai & Lewat</span>
            </button>
          </div>
        </div>
      </div>
    </main>

    <!-- Kiosk Footer Info -->
    <footer class="max-w-4xl mx-auto w-full text-center text-xs text-slate-500 font-mono pt-4 border-t border-white/10 flex items-center justify-between">
      <span>Terminal Gerbang Masuk &bull; Secure Parking System</span>
      <span>Bantuan Teknis: Hubungi Petugas Pos</span>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { Ticket, Printer, Loader2, AlertTriangle, CheckCircle2 } from 'lucide-vue-next'
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
const countdown = ref(30)
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
    const res = await api.get('/health')
    if (res.data?.status === 'ok') {
      isBackendOnline.value = true
    }
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
      'Server backend belum terhubung di port 8000.'
  } finally {
    isLoading.value = false
  }
}

const startCountdown = () => {
  countdown.value = 30
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
