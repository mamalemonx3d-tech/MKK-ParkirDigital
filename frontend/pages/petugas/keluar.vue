<template>
  <div class="space-y-5 max-w-6xl mx-auto">
    <!-- Clean Enterprise Sub-Header -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2">
          <h2 class="text-lg font-bold text-[#001a4e]">
            Pos Kasir Gerbang Keluar
          </h2>
          <span class="text-[11px] px-2.5 py-0.5 rounded bg-slate-100 text-slate-700 font-mono font-bold border border-slate-200">
            Pintu Keluar 01
          </span>
        </div>
        <p class="text-xs text-slate-500 mt-0.5">
          Pemindaian tiket, perhitungan durasi otomatis, dan pemrosesan pembayaran parkir.
        </p>
      </div>

      <!-- Functional Gate Status & Emergency Toggle -->
      <div class="flex items-center gap-2">
        <div
          class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-mono font-bold border transition"
          :class="barrierGateStatus === 'open' ? 'bg-emerald-50 text-emerald-700 border-emerald-300' : 'bg-slate-50 text-slate-700 border-slate-200'"
        >
          <span class="w-2 h-2 rounded-full" :class="barrierGateStatus === 'open' ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400'"></span>
          <span>{{ barrierGateStatus === 'open' ? 'PALANG TERBUKA' : 'PALANG TERTUTUP' }}</span>
        </div>

        <button
          @click="toggleManualBarrier"
          class="text-xs font-bold px-3 py-1.5 rounded-lg border transition cursor-pointer"
          :class="barrierGateStatus === 'open' ? 'bg-red-50 text-red-700 border-red-200 hover:bg-red-100' : 'bg-slate-100 text-slate-700 border-slate-300 hover:bg-slate-200'"
        >
          {{ barrierGateStatus === 'open' ? 'Kunci Palang' : 'Buka Manual' }}
        </button>
      </div>
    </div>

    <!-- Main POS Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
      <!-- Left Column: Scanner Module & Manual Search (5 cols) -->
      <div class="lg:col-span-5 space-y-4">
        <!-- Scanner Card -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5 space-y-4">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
              <Camera class="w-4 h-4 text-[#0099ff]" />
              <span>Kamera Scanner Keluar</span>
            </span>

            <div class="flex items-center gap-2">
              <select
                v-if="cameraList.length > 1"
                v-model="selectedCameraId"
                @change="changeCamera"
                class="text-[11px] px-2 py-1 rounded border border-slate-300 bg-white text-slate-700 outline-none max-w-32 truncate"
              >
                <option v-for="cam in cameraList" :key="cam.id" :value="cam.id">
                  {{ cam.label || 'Kamera ' + cam.id }}
                </option>
              </select>
              <button
                @click="toggleCamera"
                class="text-xs font-bold px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-800 transition cursor-pointer flex items-center gap-1.5"
              >
                <Loader2 v-if="isStartingCamera" class="w-3.5 h-3.5 animate-spin text-[#0099ff]" />
                <span>{{ isScanning ? 'Matikan Kamera' : 'Nyalakan Kamera' }}</span>
              </button>
            </div>
          </div>

          <!-- Camera Error Alert Banner if blocked -->
          <div v-if="cameraError" class="p-3 rounded-lg bg-amber-50 border border-amber-300 text-xs text-amber-900 space-y-1">
            <div class="font-bold flex items-center gap-1.5 text-amber-950">
              <AlertTriangle class="w-4 h-4 text-amber-600 shrink-0" />
              <span>Akses Kamera Dibutuhkan</span>
            </div>
            <p class="leading-relaxed text-[11px]">{{ cameraError }}</p>
          </div>

          <!-- Video Reader Box -->
          <div class="relative bg-slate-900 rounded-lg overflow-hidden min-h-48 flex items-center justify-center border border-slate-800">
            <div id="exit-reader" class="w-full"></div>
            <div v-if="!isScanning && !isStartingCamera" class="p-6 flex flex-col items-center justify-center text-slate-400 text-center space-y-2">
              <CameraOff class="w-8 h-8 opacity-40 text-slate-400" />
              <p class="text-xs font-semibold text-slate-200">Kamera Scanner Tidak Aktif</p>
              <p class="text-[11px] text-slate-400 max-w-xs">
                Anda dapat menggunakan scanner barcode USB langsung atau menyalakan kamera.
              </p>
              <button
                @click="startCamera"
                class="mt-1 px-3.5 py-1.5 bg-[#001a4e] hover:bg-[#001033] text-white text-xs font-bold rounded-lg cursor-pointer transition shadow-xs"
              >
                Aktifkan Kamera
              </button>
            </div>
            <div v-if="isStartingCamera" class="p-6 text-center text-slate-300 space-y-2">
              <Loader2 class="w-8 h-8 mx-auto animate-spin text-[#0099ff]" />
              <p class="text-xs font-semibold">Mengakses perangkat video...</p>
            </div>
          </div>

          <!-- Barcode Gun & Manual Ticket Search Field -->
          <div class="pt-3 border-t border-slate-100">
            <label class="text-xs font-bold text-slate-700 flex items-center justify-between mb-1.5">
              <span class="flex items-center gap-1.5">
                <ScanBarcode class="w-4 h-4 text-[#0099ff]" />
                <span>Input Barcode Scanner USB / Kode Tiket</span>
              </span>
              <span class="text-[10px] text-slate-400 font-mono font-medium">Auto-focus</span>
            </label>
            <div class="flex gap-2">
              <input
                id="scanner-search-input"
                v-model="searchCode"
                type="text"
                placeholder="Tembak barcode atau ketik kode tiket..."
                class="flex-1 px-3 py-2 text-xs font-mono font-bold rounded-lg border border-slate-300 focus:border-[#0099ff] focus:ring-1 focus:ring-[#0099ff] outline-none"
                @keyup.enter="lookupTicket(searchCode)"
              />
              <button
                @click="lookupTicket(searchCode)"
                :disabled="isLoadingLookup || !searchCode"
                class="px-4 py-2 bg-[#001a4e] hover:bg-[#001033] text-white text-xs font-bold rounded-lg transition disabled:opacity-60 cursor-pointer shadow-xs"
              >
                Cari
              </button>
            </div>
            <p class="text-[10px] text-slate-400 mt-1.5">
              Mendukung pemindaian langsung dari barcode scanner fisik (USB/Bluetooth).
            </p>
          </div>
        </div>
      </div>

      <!-- Right Column: Cashier Terminal & Transaction (7 cols) -->
      <div class="lg:col-span-7 bg-white rounded-xl border border-slate-200 shadow-xs p-5 space-y-5">
        <!-- Error Message Notification -->
        <div v-if="errorMessage" class="p-3.5 rounded-lg bg-red-50 border border-red-200 text-xs text-red-800 flex items-center justify-between gap-2">
          <div class="flex items-center gap-2">
            <AlertCircle class="w-4 h-4 shrink-0 text-red-600" />
            <span class="font-medium">{{ errorMessage }}</span>
          </div>
          <button @click="errorMessage = ''" class="text-red-500 hover:text-red-700 font-bold text-xs cursor-pointer">&times;</button>
        </div>

        <!-- ========================================== -->
        <!-- STATE 1: SUCCESSFUL CHECKOUT (Struk Kasir) -->
        <!-- ========================================== -->
        <div v-if="successPayment" class="space-y-4">
          <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-300 text-emerald-950 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <LottieAnimation animationPath="/lottie/check.json" width="w-12" height="h-12" :loop="false" />
              <div>
                <h3 class="text-sm font-bold text-emerald-900">Transaksi Berhasil Diproses</h3>
                <p class="text-xs text-emerald-800">Palang pintu terbuka. Silakan serahkan struk kepada pengendara.</p>
              </div>
            </div>
            <span class="px-3 py-1 rounded bg-emerald-200 text-emerald-900 font-mono font-bold text-xs uppercase">
              Lunas
            </span>
          </div>

          <!-- Struk Fisik / Invoice Preview -->
          <div class="bg-slate-50 border border-slate-300 rounded-xl p-5 font-mono text-xs text-slate-800 space-y-3 shadow-inner">
            <div class="text-center pb-3 border-b border-dashed border-slate-300 space-y-0.5">
              <p class="font-bold text-sm tracking-widest text-[#001a4e]">SECURE PARKING</p>
              <p class="text-[11px] font-bold text-slate-700 uppercase">STRUK PEMBAYARAN PARKIR RESMI</p>
              <p class="text-[10px] text-slate-400">NO: INV-PKR-{{ String(successPayment.ticketId || '001').padStart(4, '0') }} &bull; {{ formatDateTime(new Date().toISOString()) }}</p>
            </div>

            <div class="space-y-1.5 text-[11px]">
              <div class="flex justify-between">
                <span class="text-slate-500">Petugas Kasir:</span>
                <span class="font-bold text-slate-800">{{ auth.user.value?.name || 'Petugas Kasir' }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-500">Nomor Polisi:</span>
                <span class="font-bold text-slate-900">{{ successPayment.plat || '-' }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-500">Kategori:</span>
                <span class="font-bold uppercase" :class="successPayment.type === 'member' ? 'text-emerald-700' : 'text-slate-800'">
                  {{ successPayment.type === 'member' ? 'Member Tetap (Bebas Tarif)' : 'Pengunjung Umum' }}
                </span>
              </div>
              <div class="flex justify-between" v-if="successPayment.type !== 'member'">
                <span class="text-slate-500">Total Tarif:</span>
                <span class="font-bold text-[#001a4e] text-xs">{{ formatRupiah(successPayment.total) }}</span>
              </div>
              <div class="flex justify-between" v-if="cashReceived > 0">
                <span class="text-slate-500">Uang Diterima:</span>
                <span>{{ formatRupiah(cashReceived) }}</span>
              </div>
              <div class="flex justify-between" v-if="cashReceived > 0 && calculatedChange >= 0">
                <span class="text-slate-500">Uang Kembalian:</span>
                <span class="font-bold text-emerald-700">{{ formatRupiah(calculatedChange) }}</span>
              </div>
            </div>

            <div class="pt-2 border-t border-dashed border-slate-300 text-center text-[10px] text-slate-400">
              Terima kasih atas kunjungan Anda &bull; Harap simpan struk ini
            </div>
          </div>

          <!-- Action Buttons for Success State -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
            <button
              @click="cetakStrukKeluar"
              class="py-2.5 px-4 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-lg transition flex items-center justify-center gap-2 cursor-pointer shadow-xs"
            >
              <Printer class="w-4 h-4 text-[#ff9900]" />
              <span>Cetak Struk Kasir</span>
            </button>
            <button
              @click="resetTransaction"
              class="py-2.5 px-4 bg-[#0099ff] hover:bg-[#0084de] text-white text-xs font-bold uppercase tracking-wider rounded-lg transition flex items-center justify-center gap-2 cursor-pointer shadow-xs"
            >
              <CheckCircle2 class="w-4 h-4" />
              <span>Transaksi Berikutnya [Enter]</span>
            </button>
          </div>
        </div>

        <!-- ========================================== -->
        <!-- STATE 2: ACTIVE TICKET CALCULATED          -->
        <!-- ========================================== -->
        <div v-else-if="activeTicket" class="space-y-5">
          <!-- Ticket Header Identification Strip -->
          <div class="flex items-center justify-between pb-3 border-b border-slate-200">
            <div>
              <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">ID Sesi Tiket</span>
              <div class="flex items-center gap-2 mt-0.5">
                <span class="text-2xl font-black font-mono text-[#001a4e]">
                  {{ activeTicket.type === 'member' ? activeTicket.data.kode : activeTicket.data.kode_tiket }}
                </span>
                <span
                  class="px-2.5 py-0.5 rounded text-[11px] font-bold uppercase tracking-wider"
                  :class="activeTicket.type === 'member' ? 'bg-indigo-50 text-indigo-800 border border-indigo-200' : 'bg-amber-50 text-amber-800 border border-amber-200'"
                >
                  {{ activeTicket.type === 'member' ? 'Member Tetap' : 'Pengunjung Umum' }}
                </span>
              </div>
            </div>

            <!-- Elapsed Duration Indicator -->
            <div class="text-right">
              <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Durasi Sesi</span>
              <span class="text-sm font-bold font-mono text-slate-800">
                {{ activeTicket.type === 'member' ? '-' : `${activeTicket.data.durasi_menit} Menit (${activeTicket.data.durasi_jam} Jam)` }}
              </span>
            </div>
          </div>

          <!-- Total Biaya Parkir Display -->
          <div class="bg-[#001a4e] rounded-xl p-5 border border-[#001033] shadow-xs text-center">
            <span class="text-xs font-bold uppercase tracking-widest text-slate-300 block mb-1">
              TOTAL BIAYA PARKIR
            </span>
            <div class="text-4xl md:text-5xl font-black font-mono text-emerald-400 tracking-tight py-1">
              {{ formatRupiah(activeTicket.type === 'member' ? 0 : activeTicket.data.total_bayar) }}
            </div>
            <div class="text-[11px] font-mono text-slate-300 mt-1 flex items-center justify-center gap-4">
              <span>Waktu Masuk: {{ formatDateTime(activeTicket.data.waktu_masuk) }}</span>
              <span>&bull;</span>
              <span>Tarif: Rp 3.000 / Jam</span>
            </div>
          </div>

          <!-- Checkout Form: Plat Nomor & Vehicle Type -->
          <form @submit.prevent="submitCheckout" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <!-- Plat Nomor -->
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                  <span>Plat Nomor Kendaraan</span>
                  <span class="text-[10px] text-slate-400 font-normal">Wajib Diisi</span>
                </label>
                <input
                  v-model="checkoutForm.plat_nomor"
                  type="text"
                  required
                  placeholder="Contoh: B 1234 ABC"
                  class="w-full px-3.5 py-2.5 text-sm uppercase font-mono font-bold text-slate-900 rounded-lg border border-slate-300 focus:border-[#0099ff] focus:ring-1 focus:ring-[#0099ff] outline-none transition"
                  @input="checkoutForm.plat_nomor = checkoutForm.plat_nomor.toUpperCase()"
                />
              </div>

              <!-- Jenis Kendaraan Selector -->
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                  Jenis Kendaraan
                </label>
                <select
                  v-model="checkoutForm.jenis_kendaraan"
                  required
                  class="w-full px-3.5 py-2.5 text-xs font-bold rounded-lg border border-slate-300 focus:border-[#0099ff] focus:ring-1 focus:ring-[#0099ff] outline-none bg-white transition cursor-pointer"
                >
                  <option value="mobil">Mobil (Roda 4)</option>
                  <option value="motor">Sepeda Motor (Roda 2)</option>
                </select>
              </div>
            </div>

            <!-- Kalkulator Uang Kembalian Kasir -->
            <div v-if="activeTicket.type !== 'member'" class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3">
              <div class="flex items-center justify-between">
                <label class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                  <BadgeDollarSign class="w-4 h-4 text-[#ff9900]" />
                  <span>Kalkulator Kembalian Kasir</span>
                </label>
                <span class="text-[11px] font-mono font-bold" :class="calculatedChange >= 0 ? 'text-emerald-700' : 'text-amber-700'">
                  {{ calculatedChange >= 0 ? `Kembalian: ${formatRupiah(calculatedChange)}` : `Kurang: ${formatRupiah(Math.abs(calculatedChange))}` }}
                </span>
              </div>

              <!-- Cash Input & Quick Denomination Chips -->
              <div class="space-y-2">
                <input
                  v-model.number="cashReceived"
                  type="number"
                  placeholder="Nominal uang diterima dari pengendara..."
                  class="w-full px-3 py-2 text-xs font-mono font-bold rounded-lg border border-slate-300 focus:border-[#0099ff] outline-none"
                />

                <!-- Quick Cash Buttons -->
                <div class="flex flex-wrap gap-1.5">
                  <button
                    type="button"
                    @click="cashReceived = activeTicket.data.total_bayar"
                    class="px-2.5 py-1 text-[11px] font-bold rounded bg-white border border-slate-300 hover:border-[#0099ff] hover:text-[#0099ff] transition cursor-pointer"
                  >
                    Uang Pas
                  </button>
                  <button
                    v-for="amt in quickDenominations"
                    :key="amt"
                    type="button"
                    @click="cashReceived = amt"
                    class="px-2.5 py-1 text-[11px] font-mono font-bold rounded bg-white border border-slate-300 hover:border-[#0099ff] hover:text-[#0099ff] transition cursor-pointer"
                  >
                    {{ formatRupiah(amt) }}
                  </button>
                </div>
              </div>
            </div>

            <!-- Action Buttons for Checkout -->
            <div class="pt-2 flex gap-3">
              <button
                type="submit"
                :disabled="isSubmitting"
                class="flex-1 py-3 px-5 bg-[#001a4e] hover:bg-[#001033] text-white text-xs font-bold uppercase tracking-wider rounded-xl transition flex items-center justify-center gap-2 cursor-pointer shadow-xs disabled:opacity-60"
              >
                <Loader2 v-if="isSubmitting" class="w-4 h-4 animate-spin" />
                <Check v-else class="w-4 h-4 stroke-[3]" />
                <span>{{ isSubmitting ? 'Memproses Transaksi...' : (activeTicket.type === 'member' ? 'Buka Palang Member (Rp 0)' : 'Selesaikan Pembayaran & Buka Palang') }}</span>
              </button>

              <button
                type="button"
                @click="resetTransaction"
                class="px-5 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition cursor-pointer border border-slate-200"
              >
                Batal [Esc]
              </button>
            </div>
          </form>
        </div>

        <!-- ========================================== -->
        <!-- STATE 3: IDLE / MENUNGGU TIKET             -->
        <!-- ========================================== -->
        <div v-else class="text-center py-16 px-4 space-y-4">
          <div class="w-14 h-14 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto border border-slate-200">
            <ScanBarcode class="w-7 h-7 text-slate-500" />
          </div>
          <div class="space-y-1">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">
              Pos Kasir Siap Melayani Kendaraan
            </h3>
            <p class="text-xs text-slate-500 max-w-md mx-auto">
              Scan barcode tiket parkir atau kartu member menggunakan barcode scanner USB, atau nyalakan kamera scanner di sebelah kiri.
            </p>
          </div>

          <div class="inline-flex items-center gap-4 text-[11px] font-mono text-slate-400 bg-slate-50 px-4 py-2 rounded-lg border border-slate-200">
            <span>[Tembak Barcode] Baca Tiket</span>
            <span>&bull;</span>
            <span>[Enter] Konfirmasi</span>
            <span>&bull;</span>
            <span>[Esc] Batal</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue'
import {
  ScanBarcode,
  Camera,
  CameraOff,
  AlertCircle,
  AlertTriangle,
  CheckCircle2,
  Printer,
  Check,
  Loader2,
  BadgeDollarSign,
} from 'lucide-vue-next'
import { Html5Qrcode } from 'html5-qrcode'
import { formatRupiah, formatDateTime } from '~/utils/formatters'

definePageMeta({
  layout: 'petugas',
  middleware: 'auth',
})

const auth = useAuth()
const api = useApi()

const isScanning = ref(false)
const isStartingCamera = ref(false)
const isLoadingLookup = ref(false)
const isSubmitting = ref(false)
const searchCode = ref('')
const errorMessage = ref('')
const cameraError = ref('')
const cameraList = ref<{ id: string; label: string }[]>([])
const selectedCameraId = ref('')
const activeTicket = ref<any>(null)
const successPayment = ref<any>(null)
const barrierGateStatus = ref<'closed' | 'open'>('closed')
const cashReceived = ref<number>(0)
let html5QrCode: Html5Qrcode | null = null

const checkoutForm = reactive({
  plat_nomor: '',
  jenis_kendaraan: 'mobil',
})

// Quick denominations for cashier
const quickDenominations = [5000, 10000, 20000, 50000, 100000]

// Calculated change
const calculatedChange = computed(() => {
  if (!activeTicket.value || activeTicket.value.type === 'member') return 0
  const total = activeTicket.value.data.total_bayar || 0
  if (!cashReceived.value) return 0
  return cashReceived.value - total
})

const toggleManualBarrier = () => {
  if (barrierGateStatus.value === 'closed') {
    if (confirm('Buka palang gerbang secara manual?')) {
      barrierGateStatus.value = 'open'
    }
  } else {
    barrierGateStatus.value = 'closed'
  }
}

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
      html5QrCode = new Html5Qrcode('exit-reader')
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
      console.warn('Gagal membaca daftar perangkat scanner keluar:', e)
    }

    const config = {
      fps: 10,
      qrbox: { width: 220, height: 220 },
      aspectRatio: 1.333,
    }

    const onScanSuccess = (decodedText: string) => {
      lookupTicket(decodedText)
    }

    if (cameraIdToUse) {
      try {
        await html5QrCode.start(cameraIdToUse, config, onScanSuccess, () => {})
      } catch (idErr) {
        console.warn('Gagal dengan ID kamera spesifik, mencoba fallback:', idErr)
        await html5QrCode.start({ facingMode: 'user' }, config, onScanSuccess, () => {})
      }
    } else {
      await html5QrCode.start({ facingMode: 'user' }, config, onScanSuccess, () => {})
    }

    isScanning.value = true
    cameraError.value = ''
  } catch (err: any) {
    console.error('Gagal menyalakan scanner keluar:', err)
    isScanning.value = false

    if (err?.name === 'NotAllowedError' || err?.message?.toLowerCase().includes('permission')) {
      cameraError.value = 'Izin kamera diblokir browser. Izinkan akses kamera pada peramban lalu coba lagi.'
    } else if (err?.name === 'NotReadableError' || err?.message?.toLowerCase().includes('in use')) {
      cameraError.value = 'Kamera sedang digunakan aplikasi lain. Tutup aplikasi tersebut lalu coba lagi.'
    } else {
      cameraError.value = `Tidak dapat membuka kamera: ${err?.message || err}. Pastikan perangkat terhubung.`
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
      console.warn('Error stop scanner:', err)
    }
    isScanning.value = false
  }
}

const lookupTicket = async (code: string) => {
  if (!code || isLoadingLookup.value) return
  isLoadingLookup.value = true
  errorMessage.value = ''
  successPayment.value = null
  barrierGateStatus.value = 'closed'
  cashReceived.value = 0

  try {
    const res = await api.post('/keluar/scan', { qr_code: code })
    if (res.data?.success) {
      activeTicket.value = res.data
      searchCode.value = ''
      if (res.data.type !== 'member') {
        cashReceived.value = res.data.data.total_bayar
      }
    } else {
      errorMessage.value = res.data?.message || 'Data tiket tidak ditemukan.'
    }
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Kode tidak valid atau kendaraan sudah keluar sebelumnya.'
  } finally {
    isLoadingLookup.value = false
  }
}

const submitCheckout = async () => {
  if (!activeTicket.value || isSubmitting.value) return
  isSubmitting.value = true
  errorMessage.value = ''

  try {
    if (activeTicket.value.type === 'member') {
      const res = await api.post('/keluar/member', {
        member_id: activeTicket.value.data.member_id,
        tiket_id: activeTicket.value.data.tiket_id,
        plat_nomor: checkoutForm.plat_nomor,
        jenis_kendaraan: checkoutForm.jenis_kendaraan,
      })
      if (res.data?.success) {
        successPayment.value = {
          type: 'member',
          data: activeTicket.value.data,
          plat: checkoutForm.plat_nomor,
        }
        barrierGateStatus.value = 'open'
        activeTicket.value = null
      }
    } else {
      const res = await api.post('/keluar/bayar', {
        tiket_id: activeTicket.value.data.id,
        plat_nomor: checkoutForm.plat_nomor,
        jenis_kendaraan: checkoutForm.jenis_kendaraan,
      })
      if (res.data?.success) {
        successPayment.value = {
          type: 'ticket',
          data: activeTicket.value.data,
          total: res.data.data?.total_bayar,
          plat: checkoutForm.plat_nomor,
        }
        barrierGateStatus.value = 'open'
        activeTicket.value = null
      }
    }
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Gagal menyelesaikan pembayaran.'
  } finally {
    isSubmitting.value = false
  }
}

const resetTransaction = () => {
  activeTicket.value = null
  successPayment.value = null
  errorMessage.value = ''
  searchCode.value = ''
  checkoutForm.plat_nomor = ''
  barrierGateStatus.value = 'closed'
  cashReceived.value = 0
}

const cetakStrukKeluar = () => {
  window.print()
}

// Global Barcode Scanner & Hotkey Keyboard Listener
let barcodeBuffer = ''
let lastKeyTime = 0

const handleGlobalKeyDown = (e: KeyboardEvent) => {
  if (e.key === 'Escape') {
    resetTransaction()
    return
  }

  const activeEl = document.activeElement as HTMLElement
  if (activeEl && (activeEl.tagName === 'INPUT' || activeEl.tagName === 'TEXTAREA') && activeEl.id !== 'scanner-search-input') {
    return
  }

  const currentTime = Date.now()
  if (e.key === 'Enter') {
    if (barcodeBuffer.trim().length >= 3) {
      lookupTicket(barcodeBuffer.trim())
      barcodeBuffer = ''
      e.preventDefault()
    } else if (successPayment.value) {
      resetTransaction()
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

onMounted(() => {
  window.addEventListener('keydown', handleGlobalKeyDown)
})

onUnmounted(() => {
  stopCamera()
  window.removeEventListener('keydown', handleGlobalKeyDown)
})
</script>
