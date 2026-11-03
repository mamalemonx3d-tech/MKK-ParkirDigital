<template>
  <div class="min-h-screen bg-slate-100 flex flex-col justify-between p-4 sm:p-8 select-none text-slate-800">
    <!-- Top Header -->
    <header class="max-w-4xl mx-auto w-full bg-white rounded-xl border border-slate-200 shadow-xs px-6 py-4 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <BrandLogo :showSubtitle="true" subtitle="Terminal Masuk Gerbang" size="sm" />
      </div>

      <div class="text-xs text-slate-500 text-right">
        <div>{{ liveDate }}</div>
        <div class="font-bold text-slate-800 text-sm font-mono">{{ liveTime }} WIB</div>
      </div>
    </header>

    <!-- Main Kiosk Body -->
    <main class="max-w-2xl mx-auto w-full my-auto py-8">
      <!-- STATE 1: Ready & Waiting for driver to press -->
      <div v-if="!tiketData" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8 sm:p-12 text-center space-y-8">
        <!-- Header Info -->
        <div class="space-y-2">
          <h1 class="text-3xl sm:text-4xl font-extrabold text-[#001a4e] tracking-tight">
            Selamat Datang
          </h1>
          <p class="text-sm text-slate-600 max-w-md mx-auto">
            Silakan tekan tombol di bawah untuk mencetak tiket parkir masuk Anda.
          </p>
        </div>

        <!-- Touch Push Button (Large, Professional, Clean) -->
        <div class="max-w-md mx-auto space-y-3">
          <button
            @click="ambilTiket"
            :disabled="isLoading || !isBackendOnline"
            class="w-full py-6 px-8 rounded-xl font-bold text-xl sm:text-2xl tracking-wide uppercase transition active:scale-[0.99] shadow-sm cursor-pointer flex items-center justify-center gap-3 disabled:opacity-60 disabled:cursor-not-allowed"
            :class="isLoading ? 'bg-slate-300 text-slate-600' : 'bg-[#001a4e] hover:bg-[#001033] text-white'"
          >
            <Printer v-if="!isLoading" class="w-7 h-7 shrink-0 text-[#ff9900]" />
            <Loader2 v-else class="w-7 h-7 animate-spin" />
            <span>{{ isLoading ? 'Mencetak Tiket...' : 'AMBIL TIKET PARKIR' }}</span>
          </button>

          <p class="text-xs text-slate-500 font-medium">
            Tarif Parkir: <strong>Rp 3.000 / Jam</strong> &bull; Palang pintu akan terbuka otomatis
          </p>
        </div>

        <!-- Error Alert Banner -->
        <div v-if="errorMessage" class="p-4 rounded-xl bg-red-50 border border-red-200 text-xs text-red-700 text-left flex items-start gap-2.5">
          <AlertTriangle class="w-4 h-4 text-red-600 shrink-0 mt-0.5" />
          <div class="leading-relaxed">{{ errorMessage }}</div>
        </div>

        <!-- Member Quick Notice Strip -->
        <div class="pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
          <div class="flex items-center gap-2">
            <span class="font-bold text-slate-700">Pengendara Member:</span>
            <span>Arahkan kartu QR Anda ke scanner gerbang</span>
          </div>
          <NuxtLink to="/petugas/scan" class="text-[#0099ff] hover:underline font-bold">
            Buka Scanner Member &rarr;
          </NuxtLink>
        </div>
      </div>

      <!-- STATE 2: Ticket Printed & Active Countdown -->
      <div v-else class="bg-white rounded-2xl border-2 border-slate-300 shadow-md p-8 sm:p-10 space-y-6">
        <!-- Receipt Header -->
        <div class="text-center pb-5 border-b border-dashed border-slate-300 space-y-1.5">
          <h2 class="text-xl font-bold text-emerald-700 flex items-center justify-center gap-2">
            <CheckCircle2 class="w-5 h-5 text-emerald-600" />
            <span>Tiket Berhasil Dicetak — Silakan Masuk</span>
          </h2>
          <p class="text-xs text-slate-500">Ambil tiket dan harap melintas dengan hati-hati.</p>
        </div>

        <!-- Ticket Body Details -->
        <div class="space-y-4 max-w-sm mx-auto text-center">
          <div>
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">
              NOMOR TIKET
            </span>
            <span class="text-5xl font-black font-mono text-[#001a4e] tracking-tight">
              {{ tiketData.kode_tiket }}
            </span>
          </div>

          <!-- QR Code for Exit Scanner -->
          <div class="flex justify-center py-2">
            <div class="p-3 bg-white rounded-xl border border-slate-200 shadow-xs inline-block">
              <QrcodeVue :value="tiketData.qr_code" :size="160" level="M" />
            </div>
          </div>

          <div class="bg-slate-50 p-3.5 rounded-lg border border-slate-200 text-xs font-mono space-y-1.5">
            <div class="flex justify-between text-slate-600">
              <span>Waktu Masuk:</span>
              <span class="font-bold text-slate-900">{{ formatDateTime(tiketData.waktu_masuk) }}</span>
            </div>
            <div class="flex justify-between text-slate-600">
              <span>Tarif:</span>
              <span class="font-bold text-slate-900">Rp 3.000 / Jam</span>
            </div>
          </div>
        </div>

        <!-- Receipt Safety Note -->
        <p class="text-[11px] text-slate-400 text-center leading-relaxed">
          Harap simpan tiket ini untuk proses pembayaran di pos kasir keluar.
        </p>

        <!-- Actions & Countdown -->
        <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
          <span class="text-slate-500">
            Layar kembali ke awal dalam <strong>{{ countdown }} detik</strong>
          </span>

          <div class="flex items-center gap-2">
            <button
              @click="cetakStruk"
              class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg font-bold transition flex items-center gap-1.5 cursor-pointer border border-slate-200"
            >
              <Printer class="w-3.5 h-3.5" />
              <span>Cetak Tiket</span>
            </button>
            <button
              @click="resetKiosk"
              class="px-5 py-2 bg-[#001a4e] hover:bg-[#001033] text-white rounded-lg font-bold transition cursor-pointer shadow-xs"
            >
              <span>Selesai</span>
            </button>
          </div>
        </div>
      </div>
    </main>

    <!-- Kiosk Clean Footer -->
    <footer class="max-w-4xl mx-auto w-full text-center text-xs text-slate-500 pt-4 flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-slate-200">
      <div>
        <span>&copy; 2026 Secure Parking System &bull; Seluruh Hak Cipta Dilindungi</span>
      </div>
      <div class="flex items-center gap-4 text-xs font-medium">
        <NuxtLink to="/katalog-member" class="text-slate-600 hover:text-slate-900">
          Katalog Member
        </NuxtLink>
        <span>&bull;</span>
        <NuxtLink to="/" class="text-slate-600 hover:text-slate-900">
          Menu Utama
        </NuxtLink>
        <span>&bull;</span>
        <NuxtLink to="/login" class="text-slate-600 hover:text-slate-900">
          Login Petugas
        </NuxtLink>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { Printer, Loader2, AlertTriangle, CheckCircle2 } from 'lucide-vue-next'
import QrcodeVue from 'qrcode.vue'
import BrandLogo from '~/components/BrandLogo.vue'
import { formatDateTime } from '~/utils/formatters'

definePageMeta({
  layout: false,
})

const api = useApi()
const isLoading = ref(false)
const isBackendOnline = ref(true)
const errorMessage = ref('')
const tiketData = ref<any>(null)
const countdown = ref(15)
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
  countdown.value = 15
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
</script>
