<template>
  <div class="space-y-6 max-w-6xl mx-auto select-none font-sans pb-8">
    <!-- Top Kiosk Operational Banner -->
    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full bg-[#0066ff] animate-pulse"></span>
          <h1 class="text-xl sm:text-2xl font-normal text-slate-900 font-serif tracking-tight">
            Terminal Masuk Gerbang
          </h1>
          <span class="px-2.5 py-0.5 rounded-full bg-[#fefce8] text-slate-900 border border-[#ffe600] text-[10px] font-bold">
            Kiosk Mandiri & Member
          </span>
        </div>
        <p class="text-xs text-slate-500 mt-1">
          Layanan terpadu: cetak tiket karcis pengunjung umum dan validasi pemindaian kartu member aktif.
        </p>
      </div>

      <div class="flex flex-wrap items-center gap-3">
        <!-- Live Date & Clock -->
        <div class="text-right hidden sm:block text-xs">
          <div class="text-slate-500 font-medium">Petugas: <strong class="text-slate-800">{{ auth.user.value?.name || 'Petugas 1' }}</strong></div>
          <div class="font-mono font-bold text-slate-800 text-[11px]">{{ liveDate }} &bull; {{ liveTime }} WIB</div>
        </div>

        <!-- Tombol Cepat Kembali ke Dashboard -->
        <NuxtLink
          to="/petugas"
          class="flex items-center gap-2 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold rounded-xl transition border border-slate-300 shadow-2xs cursor-pointer"
        >
          <ArrowLeft class="w-3.5 h-3.5 text-[#0066ff]" />
          <span>Dashboard Petugas</span>
        </NuxtLink>
      </div>
    </div>

    <!-- Main Dual Column Layout (Sebelahan: Kiri Non-Member, Kanan Member) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">
      
      <!-- ======================================================== -->
      <!-- KOLOM KIRI: PENGENDARA NON-MEMBER (AMBIL TIKET PARKIR)    -->
      <!-- ======================================================== -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 sm:p-8 flex flex-col justify-between space-y-6">
        
        <!-- STATE 1A: Standby Siap Cetak Tiket -->
        <div v-if="!tiketData" class="flex flex-col justify-between h-full space-y-6">
          <!-- Header Kartu Kiri -->
          <div class="text-center space-y-2">
            <span class="inline-block text-[10px] font-bold uppercase tracking-wider text-slate-950 px-3 py-1 rounded-full bg-[#ffe600] border border-[#e6cf00]">
              PENGENDARA NON-MEMBER (UMUM)
            </span>
            <h2 class="text-3xl sm:text-4xl font-normal text-slate-900 tracking-tight font-serif pt-1">
              Selamat Datang
            </h2>
            <p class="text-xs sm:text-sm text-slate-600 max-w-sm mx-auto leading-relaxed">
              Silakan tekan tombol di bawah untuk mencetak tiket parkir masuk Anda.
            </p>
          </div>

          <!-- Tombol Cetak Tiket Besar (Bersih, Non AI-Slop, 3 Warna) -->
          <div class="max-w-sm mx-auto w-full space-y-4 py-4">
            <button
              @click="ambilTiket"
              :disabled="isLoadingTicket || !isBackendOnline"
              class="w-full py-6 px-6 rounded-2xl font-bold text-xl tracking-wide uppercase transition active:scale-[0.98] shadow-md cursor-pointer flex items-center justify-center gap-3 disabled:opacity-60 disabled:cursor-not-allowed bg-[#0066ff] hover:bg-[#0052cc] text-white border-2 border-blue-500"
            >
              <Printer v-if="!isLoadingTicket" class="w-7 h-7 shrink-0 text-[#ffe600]" />
              <Loader2 v-else class="w-7 h-7 animate-spin text-[#ffe600]" />
              <span>{{ isLoadingTicket ? 'Mencetak Tiket...' : 'AMBIL TIKET PARKIR' }}</span>
            </button>

            <div class="text-center space-y-1">
              <p class="text-xs text-slate-600 font-medium">
                Tarif Parkir: <strong class="text-slate-900">Rp 3.000 / Jam</strong>
              </p>
              <p class="text-[11px] text-slate-500">
                Palang pintu gerbang masuk akan terbuka otomatis setelah tiket keluar
              </p>
            </div>
          </div>

          <!-- Error Notification -->
          <div v-if="ticketErrorMessage" class="p-3.5 rounded-xl bg-red-50 border border-red-200 text-xs text-red-700 flex items-start gap-2">
            <AlertTriangle class="w-4 h-4 text-red-600 shrink-0 mt-0.5" />
            <div class="leading-relaxed">{{ ticketErrorMessage }}</div>
          </div>

          <!-- Footer Strip Info -->
          <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500 font-mono">
            <span>Sistem Karcis: <strong class="text-[#0066ff]">Aktif (Online)</strong></span>
            <span class="text-slate-700 font-bold">Palang: Otomatis</span>
          </div>
        </div>

        <!-- STATE 1B: Tiket Selesai Dicetak (Preview & Countdown) -->
        <div v-else class="space-y-5 text-center flex flex-col justify-between h-full">
          <div class="space-y-2 pb-4 border-b border-dashed border-slate-300 flex flex-col items-center">
            <LottieAnimation animationPath="/lottie/check.json" width="w-14" height="h-14" :loop="false" />
            <h3 class="text-xl sm:text-2xl font-normal text-slate-900 font-serif">
              Tiket Berhasil Dicetak &mdash; Palang Terbuka!
            </h3>
            <p class="text-xs text-slate-500">Ambil struk karcis fisik dan pengendara dipersilakan masuk.</p>
          </div>

          <!-- Detail Tiket -->
          <div class="space-y-3 max-w-xs mx-auto w-full">
            <div>
              <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">
                NOMOR TIKET RESMI
              </span>
              <span class="text-4xl font-bold font-mono text-[#0066ff]">
                {{ tiketData.kode_tiket }}
              </span>
            </div>

            <div class="p-3 bg-white rounded-2xl border-2 border-slate-200 shadow-xs inline-block">
              <QrcodeVue :value="tiketData.qr_code" :size="130" level="M" />
            </div>

            <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 text-xs font-mono space-y-1.5 text-left">
              <div class="flex justify-between text-slate-600">
                <span>Waktu Masuk:</span>
                <span class="font-bold text-slate-900">{{ formatDateTime(tiketData.waktu_masuk) }}</span>
              </div>
              <div class="flex justify-between text-slate-600">
                <span>Tarif Dasar:</span>
                <span class="font-bold text-[#0066ff]">Rp 3.000 / Jam</span>
              </div>
              <div class="flex justify-between text-slate-600">
                <span>Petugas Pos:</span>
                <span class="font-bold text-slate-800">{{ auth.user.value?.name || 'Petugas 1' }}</span>
              </div>
            </div>
          </div>

          <!-- Countdown Bar & Reset Action -->
          <div class="pt-3 border-t border-slate-100 space-y-3">
            <div class="text-xs text-slate-600 font-mono">
              Reset otomatis dalam: <strong class="text-[#0066ff] font-bold">{{ countdown }} detik</strong>
            </div>
            <div class="flex items-center justify-center gap-2">
              <button
                @click="cetakStruk"
                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer border border-slate-300"
              >
                <Printer class="w-3.5 h-3.5 text-[#0066ff]" />
                <span>Cetak Fisik</span>
              </button>
              <button
                @click="resetKiosk"
                class="px-5 py-2 bg-[#0066ff] hover:bg-[#0052cc] text-white rounded-xl text-xs font-bold transition cursor-pointer shadow-xs"
              >
                <span>Kendaraan Berikutnya</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ======================================================== -->
      <!-- KOLOM KANAN: PENGENDARA MEMBER (SCAN QR & VALIDASI)       -->
      <!-- ======================================================== -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 sm:p-8 flex flex-col justify-between space-y-6">
        
        <!-- Header Kartu Kanan -->
        <div class="text-center space-y-2">
          <span class="inline-block text-[10px] font-bold uppercase tracking-wider text-slate-900 px-3 py-1 rounded-full bg-[#ebf3ff] border border-[#0066ff]/30">
            PENGENDARA MEMBER (LANGGANAN)
          </span>
          <h2 class="text-3xl sm:text-4xl font-normal text-slate-900 tracking-tight font-serif pt-1">
            Scan Kartu Member
          </h2>
          <p class="text-xs sm:text-sm text-slate-600 max-w-sm mx-auto leading-relaxed">
            Arahkan QR Code kartu ke kamera atau scan dengan barcode scanner USB.
          </p>
        </div>

        <!-- Kamera Viewport & Kontrol -->
        <div class="space-y-3">
          <div class="flex items-center justify-between text-xs">
            <span class="font-bold text-slate-800 flex items-center gap-1.5">
              <Camera class="w-3.5 h-3.5 text-[#0066ff]" />
              <span>Kamera Scanner Member</span>
            </span>

            <div class="flex items-center gap-2">
              <select
                v-if="cameraList.length > 1"
                v-model="selectedCameraId"
                @change="changeCamera"
                class="text-[11px] px-2.5 py-1 rounded-lg border border-slate-300 bg-white text-slate-700 outline-none max-w-32 truncate"
              >
                <option v-for="cam in cameraList" :key="cam.id" :value="cam.id">
                  {{ cam.label || 'Kamera ' + cam.id }}
                </option>
              </select>

              <button
                @click="toggleCamera"
                class="text-xs font-bold px-3 py-1.5 rounded-xl border transition cursor-pointer flex items-center gap-1.5 shadow-2xs"
                :class="isScanning ? 'bg-red-50 text-red-700 border-red-200 hover:bg-red-100' : 'bg-[#0066ff] text-white border-blue-600 hover:bg-[#0052cc]'"
              >
                <Loader2 v-if="isStartingCamera" class="w-3.5 h-3.5 animate-spin" />
                <span>{{ isScanning ? 'Matikan Kamera' : 'Nyalakan Kamera' }}</span>
              </button>
            </div>
          </div>

          <!-- Kamera Container -->
          <div class="relative bg-slate-900 rounded-2xl overflow-hidden min-h-48 flex items-center justify-center border border-slate-800">
            <div id="reader-kiosk" class="w-full"></div>

            <!-- Placeholder jika kamera off -->
            <div v-if="!isScanning && !isStartingCamera" class="p-6 text-center text-slate-400 space-y-2">
              <CameraOff class="w-8 h-8 mx-auto opacity-50 text-slate-400" />
              <p class="text-xs font-bold text-slate-200">Kamera Scanner Standby</p>
              <p class="text-[11px] text-slate-400 max-w-xs">
                Klik "Nyalakan Kamera" atau gunakan alat Barcode Scanner USB di bawah ini.
              </p>
            </div>
          </div>

          <!-- Alert Error Kamera -->
          <div v-if="cameraError" class="p-3 rounded-xl bg-amber-50 border border-amber-300 text-[11px] text-amber-900 leading-relaxed">
            <strong>Kendala Kamera:</strong> {{ cameraError }}
          </div>
        </div>

        <!-- Input Barcode Scanner USB & Input Manual Kode -->
        <div class="space-y-1.5 pt-1">
          <div class="flex items-center justify-between text-xs">
            <label for="kiosk-manual-qr-input" class="font-bold text-slate-700 flex items-center gap-1.5">
              <ScanLine class="w-3.5 h-3.5 text-[#0066ff]" />
              <span>Barcode Scanner USB / Input Kode:</span>
            </label>
            <span class="text-[10px] font-mono text-slate-900 bg-[#fefce8] px-2 py-0.5 rounded-full border border-[#ffe600] font-bold">
              USB Siap Tembak
            </span>
          </div>

          <div class="flex items-center gap-2">
            <input
              id="kiosk-manual-qr-input"
              v-model="manualQr"
              type="text"
              placeholder="Arahkan barcode scanner atau ketik kode member..."
              class="flex-1 px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:border-[#0066ff] focus:ring-2 focus:ring-[#0066ff]/20 outline-none font-mono"
              @keyup.enter="handleScan(manualQr)"
            />
            <button
              @click="handleScan(manualQr)"
              :disabled="isLoadingMember || !manualQr"
              class="px-4 py-2.5 bg-[#0066ff] hover:bg-[#0052cc] text-white text-xs font-bold rounded-xl transition disabled:opacity-60 cursor-pointer shadow-xs"
            >
              {{ isLoadingMember ? 'Memeriksa...' : 'Verifikasi' }}
            </button>
          </div>
        </div>

        <!-- Hasil Verifikasi Petugas (Mengetahui Member Asli vs Palsu) -->
        <div>
          <!-- 1. Kondisi ERROR (Member Palsu, Expired, Belum Lunas, Anti-passback) -->
          <div
            v-if="memberErrorResult"
            class="bg-red-50 border-2 border-red-200 rounded-2xl p-4 text-red-900 space-y-2.5 animate-in fade-in"
          >
            <div class="flex items-center justify-between pb-2 border-b border-red-200 font-bold text-xs text-red-700">
              <div class="flex items-center gap-1.5">
                <AlertTriangle class="w-4 h-4 text-red-600 shrink-0" />
                <span>AKSES DITOLAK: KARTU TIDAK VALID</span>
              </div>
              <button @click="clearMemberResult" class="text-red-500 hover:text-red-700 text-xs font-bold cursor-pointer">
                &times;
              </button>
            </div>

            <p class="text-xs font-bold leading-relaxed text-red-800">
              {{ memberErrorResult }}
            </p>
            <p class="text-[10px] text-red-600">
              Catatan Petugas: Pastikan kartu resmi, iuran langganan aktif, dan tidak melanggar anti-passback.
            </p>
          </div>

          <!-- 2. Kondisi SUKSES (Member Asli & Valid) -->
          <div
            v-else-if="memberSuccessResult"
            class="bg-[#f0f6ff] border-2 border-[#0066ff] rounded-2xl p-4 text-slate-900 space-y-2.5 animate-in fade-in"
          >
            <div class="flex items-center justify-between pb-2 border-b border-blue-200 font-bold text-xs text-[#0066ff]">
              <div class="flex items-center gap-1.5">
                <LottieAnimation animationPath="/lottie/check.json" width="w-5" height="h-5" :loop="false" />
                <span>MEMBER ASLI & AKTIF &mdash; PALANG TERBUKA</span>
              </div>
              <span class="font-mono bg-[#ffe600] text-slate-950 px-2 py-0.5 rounded-lg text-[10px] font-bold">
                {{ memberSuccessResult.tiket.kode_tiket }}
              </span>
            </div>

            <div class="grid grid-cols-2 gap-2 text-xs bg-white p-3 rounded-xl border border-slate-200">
              <div>
                <span class="text-slate-400 text-[10px] block">Nama Pemilik:</span>
                <span class="font-bold text-slate-900">{{ memberSuccessResult.member.nama }}</span>
              </div>
              <div>
                <span class="text-slate-400 text-[10px] block">Kode Member:</span>
                <span class="font-mono font-bold text-[#0066ff]">{{ memberSuccessResult.member.kode }}</span>
              </div>
              <div>
                <span class="text-slate-400 text-[10px] block">Perusahaan:</span>
                <span class="font-medium text-slate-700">{{ memberSuccessResult.member.perusahaan || '-' }}</span>
              </div>
              <div>
                <span class="text-slate-400 text-[10px] block">Masa Berlaku:</span>
                <span class="font-mono font-bold text-[#0066ff]">{{ memberSuccessResult.member.expired_at }}</span>
              </div>
            </div>

            <button
              @click="clearMemberResult"
              class="w-full py-2 bg-[#0066ff] hover:bg-[#0052cc] text-white text-xs font-bold rounded-xl transition cursor-pointer shadow-xs"
            >
              Siap untuk Kendaraan Berikutnya
            </button>
          </div>

          <!-- 3. Standby Info jika belum scan -->
          <div v-else class="text-center py-2 text-[11px] text-slate-400">
            Menunggu pemindaian kartu member saat pengendara mendekati gerbang masuk...
          </div>
        </div>
      </div>

    </div>

    <!-- Minimalist Footer Status -->
    <div class="flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 pt-3 border-t border-slate-200">
      <div class="font-medium">Sistem Gerbang Masuk Terpadu &bull; Pos Operasional</div>
      <div class="flex items-center gap-3 font-mono text-[11px]">
        <span class="text-slate-600 font-bold">Palang Pintu: Siap Beroperasi</span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import {
  Printer,
  Loader2,
  AlertTriangle,
  Camera,
  CameraOff,
  ArrowLeft,
  ScanLine,
} from 'lucide-vue-next'
import { Html5Qrcode } from 'html5-qrcode'
import QrcodeVue from 'qrcode.vue'
import LottieAnimation from '~/components/LottieAnimation.vue'
import { formatDateTime } from '~/utils/formatters'

// Terintegrasi dalam layout Petugas dengan sidebar yang tertutup secara default
definePageMeta({
  layout: 'petugas',
  middleware: 'auth',
})

const auth = useAuth()
const api = useApi()

// Realtime Date & Clock
const liveDate = ref('')
const liveTime = ref('')
let clockTimer: any = null

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

// Health Check
const isBackendOnline = ref(true)
let healthCheckTimer: any = null

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

// ==========================================
// LOGIKA 1: NON-MEMBER (CETAK TIKET)
// ==========================================
const isLoadingTicket = ref(false)
const ticketErrorMessage = ref('')
const tiketData = ref<any>(null)
const countdown = ref(15)
let countdownInterval: any = null

const ambilTiket = async () => {
  isLoadingTicket.value = true
  ticketErrorMessage.value = ''

  try {
    const res = await api.post('/tiket')
    if (res.data?.success) {
      tiketData.value = res.data.data
      startCountdown()
    } else {
      ticketErrorMessage.value = res.data?.message || 'Gagal membuat tiket.'
    }
  } catch (err: any) {
    ticketErrorMessage.value =
      err.response?.data?.message ||
      'Sistem gerbang sedang offline atau gangguan jaringan. Silakan hubungi petugas pos.'
  } finally {
    isLoadingTicket.value = false
  }
}

const startCountdown = () => {
  if (countdownInterval) clearInterval(countdownInterval)
  countdown.value = 15
  countdownInterval = setInterval(() => {
    if (countdown.value > 1) {
      countdown.value--
    } else {
      resetKiosk()
    }
  }, 1000)
}

const resetKiosk = () => {
  tiketData.value = null
  ticketErrorMessage.value = ''
  if (countdownInterval) clearInterval(countdownInterval)
  countdown.value = 15
}

const cetakStruk = () => {
  window.print()
}

// ==========================================
// LOGIKA 2: MEMBER (SCAN QR & VALIDASI ASLI)
// ==========================================
const isScanning = ref(false)
const isStartingCamera = ref(false)
const cameraError = ref('')
const cameraList = ref<any[]>([])
const selectedCameraId = ref('')
const manualQr = ref('')
const isLoadingMember = ref(false)
const memberErrorResult = ref('')
const memberSuccessResult = ref<any>(null)

let html5QrCode: Html5Qrcode | null = null

const toggleCamera = async () => {
  if (isScanning.value) {
    await stopCamera()
  } else {
    await startCamera()
  }
}

const startCamera = async () => {
  cameraError.value = ''
  isStartingCamera.value = true

  try {
    if (!html5QrCode) {
      html5QrCode = new Html5Qrcode('reader-kiosk')
    }

    let cameraIdToUse = selectedCameraId.value

    try {
      const devices = await Html5Qrcode.getCameras()
      if (devices && devices.length > 0) {
        cameraList.value = devices
        if (!cameraIdToUse) {
          cameraIdToUse = devices[0].id
          selectedCameraId.value = cameraIdToUse
        }
      }
    } catch (e) {
      console.warn('Gagal membaca list perangkat kamera:', e)
    }

    const config = {
      fps: 10,
      qrbox: { width: 200, height: 200 },
      aspectRatio: 1.333,
    }

    const onScanSuccess = (decodedText: string) => {
      handleScan(decodedText)
    }

    if (cameraIdToUse) {
      try {
        await html5QrCode.start(cameraIdToUse, config, onScanSuccess, () => {})
      } catch {
        await html5QrCode.start({ facingMode: 'user' }, config, onScanSuccess, () => {})
      }
    } else {
      await html5QrCode.start({ facingMode: 'user' }, config, onScanSuccess, () => {})
    }

    isScanning.value = true
    cameraError.value = ''
  } catch (err: any) {
    isScanning.value = false
    if (err?.name === 'NotAllowedError' || err?.message?.toLowerCase().includes('permission')) {
      cameraError.value = 'Izin kamera diblokir browser. Izinkan kamera di sebelah kiri bilah URL, lalu coba lagi.'
    } else if (err?.name === 'NotReadableError' || err?.message?.toLowerCase().includes('in use')) {
      cameraError.value = 'Kamera sedang digunakan oleh aplikasi lain. Tutup aplikasi tersebut lalu coba lagi.'
    } else {
      cameraError.value = `Gagal mengakses kamera: ${err?.message || err}. Gunakan input scanner USB di bawah.`
    }
  } finally {
    isStartingCamera.value = false
  }
}

const changeCamera = async () => {
  if (isScanning.value) {
    await stopCamera()
    await startCamera()
  }
}

const stopCamera = async () => {
  if (html5QrCode && isScanning.value) {
    try {
      await html5QrCode.stop()
    } catch (err) {
      console.warn('Error saat menghentikan scanner kamera:', err)
    }
    isScanning.value = false
  }
}

const handleScan = async (code: string) => {
  if (!code || isLoadingMember.value) return
  isLoadingMember.value = true
  memberErrorResult.value = ''
  memberSuccessResult.value = null

  try {
    const res = await api.post('/scan', { qr_code: code.trim() })
    if (res.data?.success) {
      memberSuccessResult.value = res.data.data
      manualQr.value = ''
    } else {
      memberErrorResult.value = res.data?.message || 'Akses ditolak.'
    }
  } catch (err: any) {
    memberErrorResult.value =
      err.response?.data?.message ||
      'Kode QR tidak valid atau kartu member tidak ditemukan.'
  } finally {
    isLoadingMember.value = false
  }
}

const clearMemberResult = () => {
  memberErrorResult.value = ''
  memberSuccessResult.value = null
  manualQr.value = ''
}

// Global Barcode Scanner (HID USB) Keyboard Wedge Listener
let barcodeBuffer = ''
let lastKeyTime = 0

const handleGlobalKeyDown = (e: KeyboardEvent) => {
  const activeEl = document.activeElement as HTMLElement
  if (
    activeEl &&
    (activeEl.tagName === 'INPUT' || activeEl.tagName === 'TEXTAREA') &&
    activeEl.id !== 'kiosk-manual-qr-input'
  ) {
    return
  }

  const currentTime = Date.now()
  if (e.key === 'Enter') {
    if (barcodeBuffer.trim().length >= 3) {
      handleScan(barcodeBuffer.trim())
      barcodeBuffer = ''
      e.preventDefault()
    }
  } else if (e.key.length === 1) {
    if (currentTime - lastKeyTime > 80) {
      barcodeBuffer = ''
    }
    barcodeBuffer += e.key
    lastKeyTime = currentTime
  }
}

// Lifecycle Hooks
onMounted(() => {
  auth.initAuth()
  updateClock()
  clockTimer = setInterval(updateClock, 1000)

  checkBackendHealth()
  healthCheckTimer = setInterval(checkBackendHealth, 5000)

  window.addEventListener('keydown', handleGlobalKeyDown)
})

onUnmounted(() => {
  if (clockTimer) clearInterval(clockTimer)
  if (countdownInterval) clearInterval(countdownInterval)
  if (healthCheckTimer) clearInterval(healthCheckTimer)
  stopCamera()
  window.removeEventListener('keydown', handleGlobalKeyDown)
})
</script>
