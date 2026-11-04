<template>
  <div class="space-y-6 max-w-5xl mx-auto">
    <!-- Header Pos Masuk Gerbang -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2.5">
          <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
          <h2 class="text-lg font-bold text-[#001a4e]">
            Pintu Masuk Gerbang & Kiosk Tiket
          </h2>
          <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200 font-mono">
            POS AKTIF
          </span>
        </div>
        <p class="text-xs text-slate-500 mt-1">
          Layanan masuk terpadu: Cetak tiket karcis untuk pengendara non-member atau validasi QR kartu member langganan.
        </p>
      </div>

      <!-- Quick Status & Clock -->
      <div class="flex items-center gap-4 text-xs font-mono text-slate-600 bg-slate-50 px-3.5 py-2 rounded-lg border border-slate-200">
        <div class="flex items-center gap-1.5">
          <User class="w-3.5 h-3.5 text-[#0099ff]" />
          <span class="font-bold text-slate-800">{{ auth.user.value?.name || 'Petugas Gerbang' }}</span>
        </div>
        <span class="text-slate-300">|</span>
        <span>{{ liveTime }} WIB</span>
      </div>
    </div>

    <!-- Mode Selector: 2 Opsi Jelas (Non-Member vs Member) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <!-- Opsi 1: Non-Member Button Card -->
      <button
        @click="activeMode = 'non_member'"
        class="p-4 rounded-xl border-2 text-left transition flex items-center justify-between cursor-pointer"
        :class="activeMode === 'non_member' ? 'bg-[#001a4e] text-white border-[#001a4e] shadow-md ring-2 ring-[#0099ff]/20' : 'bg-white text-slate-700 border-slate-200 hover:border-slate-300 hover:bg-slate-50'"
      >
        <div class="flex items-center gap-3.5">
          <div
            class="w-11 h-11 rounded-lg flex items-center justify-center shrink-0 transition"
            :class="activeMode === 'non_member' ? 'bg-[#0099ff] text-white' : 'bg-slate-100 text-slate-600'"
          >
            <Ticket class="w-6 h-6" />
          </div>
          <div>
            <div class="flex items-center gap-2">
              <span class="font-black text-sm uppercase tracking-wide">1. Cetak Tiket Non-Member</span>
              <span
                class="text-[9px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wider"
                :class="activeMode === 'non_member' ? 'bg-amber-400 text-slate-950 font-black' : 'bg-slate-100 text-slate-600'"
              >
                Karcis Umum
              </span>
            </div>
            <p class="text-xs mt-0.5" :class="activeMode === 'non_member' ? 'text-slate-200' : 'text-slate-500'">
              Tekan tombol cetak tiket untuk kendaraan non-member (Rp 3.000/jam).
            </p>
          </div>
        </div>
        <ChevronRight class="w-5 h-5 shrink-0" :class="activeMode === 'non_member' ? 'text-white' : 'text-slate-400'" />
      </button>

      <!-- Opsi 2: Member Button Card -->
      <button
        @click="activeMode = 'member'"
        class="p-4 rounded-xl border-2 text-left transition flex items-center justify-between cursor-pointer"
        :class="activeMode === 'member' ? 'bg-[#001a4e] text-white border-[#001a4e] shadow-md ring-2 ring-[#0099ff]/20' : 'bg-white text-slate-700 border-slate-200 hover:border-slate-300 hover:bg-slate-50'"
      >
        <div class="flex items-center gap-3.5">
          <div
            class="w-11 h-11 rounded-lg flex items-center justify-center shrink-0 transition"
            :class="activeMode === 'member' ? 'bg-emerald-500 text-white' : 'bg-slate-100 text-slate-600'"
          >
            <CreditCard class="w-6 h-6" />
          </div>
          <div>
            <div class="flex items-center gap-2">
              <span class="font-black text-sm uppercase tracking-wide">2. Scan QR Kartu Member</span>
              <span
                class="text-[9px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wider"
                :class="activeMode === 'member' ? 'bg-emerald-400 text-slate-950 font-black' : 'bg-slate-100 text-slate-600'"
              >
                Langganan
              </span>
            </div>
            <p class="text-xs mt-0.5" :class="activeMode === 'member' ? 'text-slate-200' : 'text-slate-500'">
              Pindai QR / ketik kode untuk validasi keaslian, masa aktif & anti-passback.
            </p>
          </div>
        </div>
        <ChevronRight class="w-5 h-5 shrink-0" :class="activeMode === 'member' ? 'text-white' : 'text-slate-400'" />
      </button>
    </div>

    <!-- ============================================================= -->
    <!-- KONTEN 1: PENGENDARA NON-MEMBER (CETAK TIKET KARCIS)         -->
    <!-- ============================================================= -->
    <div v-if="activeMode === 'non_member'" class="space-y-6">
      <!-- STATE 1A: Siap Cetak Tiket -->
      <div v-if="!tiketData" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8 sm:p-12 text-center space-y-6">
        <div class="space-y-2 max-w-md mx-auto">
          <span class="inline-block text-[11px] font-bold uppercase tracking-widest text-[#0099ff] px-3 py-1 rounded-full bg-[#e6f4ff] border border-[#0099ff]/20">
            Pintu Masuk 01 &bull; Karcis Non-Member
          </span>
          <h3 class="text-2xl sm:text-3xl font-black text-[#001a4e]">
            Pengendara Non-Member
          </h3>
          <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
            Klik tombol di bawah ini untuk mengeluarkan tiket parkir barcode bagi pengendara umum. Palang gerbang otomatis terangkat.
          </p>
        </div>

        <!-- Tombol Cetak Besar & Elegan -->
        <div class="max-w-md mx-auto space-y-3">
          <button
            @click="ambilTiket"
            :disabled="isLoadingTicket || !isBackendOnline"
            class="w-full py-6 px-8 rounded-xl font-black text-xl sm:text-2xl tracking-wide uppercase transition active:scale-[0.99] shadow-md cursor-pointer flex items-center justify-center gap-3 disabled:opacity-60 disabled:cursor-not-allowed bg-[#001a4e] hover:bg-[#001033] text-white border border-slate-700"
          >
            <Printer v-if="!isLoadingTicket" class="w-7 h-7 shrink-0 text-[#ff9900]" />
            <Loader2 v-else class="w-7 h-7 animate-spin" />
            <span>{{ isLoadingTicket ? 'Mencetak Tiket...' : 'AMBIL TIKET PARKIR' }}</span>
          </button>

          <div class="flex items-center justify-between text-xs text-slate-500 font-mono px-2">
            <span>Tarif: <strong>Rp 3.000 / Jam</strong></span>
            <span>Palang: <strong>TERKUNCI</strong></span>
          </div>
        </div>

        <!-- Error Notification -->
        <div v-if="ticketErrorMessage" class="max-w-md mx-auto p-4 rounded-xl bg-red-50 border border-red-200 text-xs text-red-700 text-left flex items-start gap-2.5">
          <AlertTriangle class="w-4 h-4 text-red-600 shrink-0 mt-0.5" />
          <div class="leading-relaxed">{{ ticketErrorMessage }}</div>
        </div>
      </div>

      <!-- STATE 1B: Tiket Selesai Dicetak (Preview & Countdown) -->
      <div v-else class="bg-white rounded-2xl border-2 border-slate-300 shadow-md p-6 sm:p-8 space-y-6">
        <div class="text-center pb-5 border-b border-dashed border-slate-300 space-y-2 flex flex-col items-center">
          <LottieAnimation animationPath="/lottie/check.json" width="w-16" height="h-16" :loop="false" />
          <h3 class="text-xl font-black text-emerald-800">
            Tiket Berhasil Dicetak — Palang Terbuka!
          </h3>
          <p class="text-xs text-slate-500">Silakan ambil tiket fisik dan pengendara dipersilakan melintas.</p>
        </div>

        <!-- Kartu Karcis Masuk Thermal Look -->
        <div class="space-y-4 max-w-sm mx-auto text-center">
          <div>
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">
              NOMOR TIKET RESMI
            </span>
            <span class="text-5xl font-black font-mono text-[#001a4e] tracking-tight">
              {{ tiketData.kode_tiket }}
            </span>
          </div>

          <div class="flex justify-center py-2">
            <div class="p-3 bg-white rounded-xl border border-slate-200 shadow-xs inline-block">
              <QrcodeVue :value="tiketData.qr_code" :size="150" level="M" />
            </div>
          </div>

          <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 text-xs font-mono space-y-1.5 text-left">
            <div class="flex justify-between text-slate-600">
              <span>Waktu Masuk:</span>
              <span class="font-bold text-slate-900">{{ formatDateTime(tiketData.waktu_masuk) }}</span>
            </div>
            <div class="flex justify-between text-slate-600">
              <span>Kategori:</span>
              <span class="font-bold text-slate-900 uppercase">Non-Member (Umum)</span>
            </div>
            <div class="flex justify-between text-slate-600">
              <span>Tarif Dasar:</span>
              <span class="font-bold text-emerald-700">Rp 3.000 / Jam</span>
            </div>
            <div class="flex justify-between text-slate-600">
              <span>Petugas Pos:</span>
              <span class="font-bold text-slate-800">{{ auth.user.value?.name || 'Petugas 1' }}</span>
            </div>
          </div>
        </div>

        <!-- Countdown Bar & Actions -->
        <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
          <span class="text-slate-500 font-mono">
            Reset otomatis dalam: <strong>{{ countdown }} detik</strong>
          </span>

          <div class="flex items-center gap-2.5">
            <button
              @click="cetakStruk"
              class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-lg font-bold transition flex items-center gap-1.5 cursor-pointer border border-slate-300"
            >
              <Printer class="w-4 h-4 text-[#ff9900]" />
              <span>Cetak Karcis Fisik</span>
            </button>
            <button
              @click="resetKiosk"
              class="px-5 py-2 bg-[#001a4e] hover:bg-[#001033] text-white rounded-lg font-bold transition cursor-pointer shadow-xs"
            >
              <span>Siap Kendaraan Berikutnya</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ============================================================= -->
    <!-- KONTEN 2: PENGENDARA MEMBER (VALIDASI QR & KEASLIAN KARTU)   -->
    <!-- ============================================================= -->
    <div v-else class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
        <!-- Kolom Kiri: Kamera Scanner & Input Barcode USB -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs space-y-4">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
            <span class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
              <Camera class="w-4 h-4 text-[#0099ff]" />
              <span>Kamera Scanner Member</span>
            </span>

            <div class="flex items-center gap-2">
              <select
                v-if="cameraList.length > 1"
                v-model="selectedCameraId"
                @change="changeCamera"
                class="text-[11px] px-2 py-1 rounded border border-slate-300 bg-white text-slate-700 outline-none max-w-36 truncate"
              >
                <option v-for="cam in cameraList" :key="cam.id" :value="cam.id">
                  {{ cam.label || 'Kamera ' + cam.id }}
                </option>
              </select>

              <button
                @click="toggleCamera"
                class="text-xs font-bold px-3 py-1.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-800 transition cursor-pointer flex items-center gap-1.5"
              >
                <Loader2 v-if="isStartingCamera" class="w-3.5 h-3.5 animate-spin" />
                <span>{{ isScanning ? 'Matikan Kamera' : 'Nyalakan Kamera' }}</span>
              </button>
            </div>
          </div>

          <!-- Camera Error Alert -->
          <div v-if="cameraError" class="p-3.5 rounded-lg bg-amber-50 border border-amber-300 text-xs text-amber-900 space-y-1">
            <div class="font-bold flex items-center gap-1.5 text-amber-950">
              <AlertTriangle class="w-4 h-4 text-amber-600 shrink-0" />
              <span>Kendala Akses Kamera</span>
            </div>
            <p class="leading-relaxed text-[11px]">{{ cameraError }}</p>
          </div>

          <!-- Kamera Viewport -->
          <div class="relative bg-slate-900 rounded-xl overflow-hidden min-h-64 flex items-center justify-center border border-slate-800">
            <div id="reader-kiosk" class="w-full"></div>

            <!-- Overlay Kamera Mati -->
            <div v-if="!isScanning && !isStartingCamera" class="p-6 text-center text-slate-400 space-y-3">
              <CameraOff class="w-10 h-10 mx-auto opacity-40 text-slate-400" />
              <div class="space-y-1">
                <p class="text-xs font-bold text-slate-200">Kamera Scanner Siaga</p>
                <p class="text-[11px] text-slate-400 max-w-xs mx-auto">
                  Arahkan QR Code kartu member ke kamera atau nyalakan pemindai di bawah.
                </p>
              </div>
              <button
                @click="startCamera"
                class="px-4 py-2 bg-[#0099ff] hover:bg-[#0084de] text-white text-xs font-bold rounded-lg cursor-pointer shadow-xs"
              >
                Aktifkan Scanner Kamera
              </button>
            </div>

            <!-- Overlay Inisialisasi Kamera -->
            <div v-if="isStartingCamera" class="p-6 text-center text-slate-300 space-y-2">
              <Loader2 class="w-8 h-8 mx-auto animate-spin text-[#0099ff]" />
              <p class="text-xs font-semibold">Menginisialisasi perangkat kamera...</p>
            </div>
          </div>

          <!-- Input Barcode Scanner USB / Ketik Manual Kode -->
          <div class="pt-3 border-t border-slate-100">
            <div class="flex items-center justify-between mb-1.5">
              <label class="block text-xs font-semibold text-slate-700">
                Input QR Code / Barcode Scanner USB
              </label>
              <span class="text-[10px] text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 font-medium">
                Scanner USB Siap
              </span>
            </div>
            <p class="text-[11px] text-slate-500 mb-2">
              Mendukung tembakan barcode scanner USB atau ketik kode kartu member (misal: <strong class="text-slate-800">MBR-001</strong>).
            </p>
            <div class="flex gap-2">
              <input
                id="kiosk-manual-qr-input"
                v-model="manualQr"
                type="text"
                placeholder="Arahkan barcode scanner atau ketik kode..."
                class="flex-1 px-3 py-2 text-xs rounded-lg border border-slate-300 focus:border-[#0099ff] outline-none font-mono"
                @keyup.enter="handleScan(manualQr)"
              />
              <button
                @click="handleScan(manualQr)"
                :disabled="isLoadingMember || !manualQr"
                class="px-4 py-2 bg-[#001a4e] hover:bg-[#001033] text-white text-xs font-bold rounded-lg transition disabled:opacity-60 cursor-pointer"
              >
                {{ isLoadingMember ? 'Memeriksa...' : 'Verifikasi' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Kolom Kanan: Hasil Verifikasi Petugas (Mengetahui Member Beneran atau Bukan) -->
        <div class="space-y-4">
          <!-- 1. Kondisi ERROR (Akses Ditolak / Member Palsu / Expired / Anti-passback) -->
          <div
            v-if="memberErrorResult"
            class="bg-red-50 border-2 border-red-200 rounded-xl p-6 text-red-900 space-y-4 animate-in fade-in"
          >
            <div class="flex items-center gap-2.5 font-black text-sm text-red-700 pb-3 border-b border-red-200">
              <AlertTriangle class="w-6 h-6 text-red-600 shrink-0" />
              <span>AKSES DITOLAK: KARTU MEMBER TIDAK VALID</span>
            </div>

            <div class="space-y-2">
              <p class="text-xs font-bold leading-relaxed text-red-800">
                {{ memberErrorResult }}
              </p>
              <p class="text-[11px] text-red-600">
                Petunjuk Petugas: Pastikan pengendara memiliki kartu fisik resmi, iuran aktif, dan tidak melanggar aturan anti-passback. Jika bukan member, arahkan ke tab "Cetak Tiket Non-Member".
              </p>
            </div>

            <div class="pt-2">
              <button
                @click="clearMemberResult"
                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-lg cursor-pointer transition shadow-xs"
              >
                Tutup Pesan & Coba Lagi
              </button>
            </div>
          </div>

          <!-- 2. Kondisi SUKSES (Member Asli, Terdaftar & Valid) -->
          <div
            v-else-if="memberSuccessResult"
            class="bg-emerald-50 border-2 border-emerald-300 rounded-xl p-6 text-emerald-950 space-y-4 animate-in fade-in"
          >
            <div class="flex items-center justify-between pb-3 border-b border-emerald-200">
              <div class="flex items-center gap-2.5 font-black text-sm text-emerald-800">
                <LottieAnimation animationPath="/lottie/check.json" width="w-8" height="h-8" :loop="false" />
                <span>MEMBER ASLI & AKTIF — PALANG TERBUKA</span>
              </div>
              <span class="px-2.5 py-0.5 rounded font-mono font-bold text-xs bg-emerald-200 text-emerald-900">
                {{ memberSuccessResult.tiket.kode_tiket }}
              </span>
            </div>

            <!-- Detail Member Terverifikasi -->
            <div class="bg-white p-4 rounded-xl border border-emerald-200 shadow-xs space-y-2.5 text-xs">
              <div class="flex justify-between items-center pb-2 border-b border-slate-100">
                <span class="text-slate-500">Nama Pemilik:</span>
                <span class="font-black text-slate-900 text-sm">{{ memberSuccessResult.member.nama }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-slate-500">Kode Member:</span>
                <span class="font-mono font-bold text-[#001a4e]">{{ memberSuccessResult.member.kode }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-slate-500">Perusahaan / Kantor:</span>
                <span class="font-semibold text-slate-800">{{ memberSuccessResult.member.perusahaan || '-' }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-slate-500">Batas Masa Berlaku:</span>
                <span class="font-mono font-bold text-emerald-700">{{ memberSuccessResult.member.expired_at }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-slate-500">Status Iuran:</span>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800">
                  {{ memberSuccessResult.member.status?.toUpperCase() || 'LUNAS' }}
                </span>
              </div>
              <div class="flex justify-between items-center pt-2 border-t border-slate-100">
                <span class="text-slate-500">Waktu Masuk Sesi:</span>
                <span class="font-mono font-bold text-slate-900">{{ formatDateTime(memberSuccessResult.tiket.waktu_masuk) }}</span>
              </div>
            </div>

            <div class="pt-2">
              <button
                @click="clearMemberResult"
                class="w-full py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold rounded-lg transition cursor-pointer shadow-xs"
              >
                Siap untuk Kendaraan Berikutnya
              </button>
            </div>
          </div>

          <!-- 3. Kondisi STANDBY (Menunggu Kartu Discan) -->
          <div v-else class="bg-white rounded-xl border border-slate-200 p-8 text-center space-y-4 shadow-xs">
            <div class="w-14 h-14 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
              <ScanLine class="w-8 h-8 text-[#0099ff] animate-pulse" />
            </div>
            <div class="space-y-1">
              <h4 class="text-sm font-bold text-slate-800">Menunggu Pemindaian Kartu Member</h4>
              <p class="text-xs text-slate-500 max-w-xs mx-auto leading-relaxed">
                Arahkan kode QR kartu member ke kamera scanner atau tembakkan barcode scanner USB di atas.
              </p>
            </div>
            <div class="text-[11px] text-slate-400 font-mono bg-slate-50 p-2.5 rounded-lg border border-slate-100">
              Sistem otomatis memvalidasi Anti-Passback & Masa Berlaku Kartu
            </div>
          </div>
        </div>
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
  Ticket,
  CreditCard,
  ChevronRight,
  User,
  ScanLine,
} from 'lucide-vue-next'
import { Html5Qrcode } from 'html5-qrcode'
import QrcodeVue from 'qrcode.vue'
import LottieAnimation from '~/components/LottieAnimation.vue'
import { formatDateTime } from '~/utils/formatters'

definePageMeta({
  layout: 'petugas',
  middleware: 'auth',
})

const auth = useAuth()
const api = useApi()

// Mode Operasi: 'non_member' | 'member'
const activeMode = ref<'non_member' | 'member'>('non_member')

// Realtime Clock
const liveTime = ref('')
let clockTimer: any = null

const updateClock = () => {
  const now = new Date()
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
      'Server backend belum terhubung di port 8000.'
  } finally {
    isLoadingTicket.value = false
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
  ticketErrorMessage.value = ''
}

const cetakStruk = () => {
  window.print()
}

// ==========================================
// LOGIKA 2: MEMBER (SCAN QR & VALIDASI)
// ==========================================
const isScanning = ref(false)
const isStartingCamera = ref(false)
const isLoadingMember = ref(false)
const manualQr = ref('')
const memberErrorResult = ref('')
const memberSuccessResult = ref<any>(null)
const cameraError = ref('')
const cameraList = ref<{ id: string; label: string }[]>([])
const selectedCameraId = ref('')
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
      qrbox: { width: 220, height: 220 },
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
      // Otomatis arahkan ke mode member jika scanner tembak kartu
      activeMode.value = 'member'
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
