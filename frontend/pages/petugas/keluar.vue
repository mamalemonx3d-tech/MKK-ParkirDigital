<template>
  <div class="max-w-5xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs flex items-center justify-between">
      <div>
        <h2 class="text-lg font-bold text-[#001a4e]">
          Pos Kasir Gerbang Keluar & Pembayaran
        </h2>
        <p class="text-xs text-slate-500 mt-0.5">
          Scan QR tiket pengunjung atau kartu member untuk kalkulasi tarif dan membuka palang keluar.
        </p>
      </div>
      <div class="flex items-center gap-2">
        <span class="px-3 py-1 rounded-full text-xs font-bold bg-[#fff5e6] text-[#ff9900] border border-[#ff9900]/30 font-mono">
          Tarif: Rp 3.000 / Jam
        </span>
      </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
      <!-- Left Column: Scanner & Quick Search (5 cols) -->
      <div class="lg:col-span-5 bg-white p-5 rounded-xl border border-slate-200 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
          <span class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
            <ScanBarcode class="w-4 h-4 text-[#ff9900]" />
            <span>Pemindaian Tiket</span>
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
              <Loader2 v-if="isStartingCamera" class="w-3.5 h-3.5 animate-spin text-[#ff9900]" />
              <span>{{ isScanning ? 'Matikan Kamera' : 'Buka Kamera' }}</span>
            </button>
          </div>
        </div>

        <!-- Camera Error Banner -->
        <div v-if="cameraError" class="p-3.5 rounded-lg bg-amber-50 border border-amber-300 text-xs text-amber-900 space-y-1">
          <div class="font-bold flex items-center gap-1.5 text-amber-950">
            <AlertTriangle class="w-4 h-4 text-amber-600 shrink-0" />
            <span>Kendala Akses Kamera</span>
          </div>
          <p class="leading-relaxed text-[11px]">{{ cameraError }}</p>
        </div>

        <!-- Camera Container -->
        <div class="relative bg-slate-900 rounded-lg overflow-hidden min-h-60 flex items-center justify-center border border-slate-800">
          <div id="exit-reader" class="w-full"></div>
          <div v-if="!isScanning && !isStartingCamera" class="absolute inset-0 flex flex-col items-center justify-center text-slate-400 p-4 text-center">
            <CameraOff class="w-8 h-8 mb-2 opacity-50" />
            <p class="text-xs font-medium text-slate-300">Kamera scanner kasir nonaktif.</p>
            <button
              @click="startCamera"
              class="mt-3 px-4 py-2 bg-[#ff9900] hover:bg-[#e68a00] text-slate-900 text-xs font-bold rounded-lg cursor-pointer transition shadow-xs"
            >
              Nyalakan Scanner
            </button>
          </div>
          <div v-if="isStartingCamera" class="p-6 text-center text-slate-300 space-y-2">
            <Loader2 class="w-8 h-8 mx-auto animate-spin text-[#ff9900]" />
            <p class="text-xs font-semibold">Menginisialisasi kamera...</p>
          </div>
        </div>

        <!-- Manual QR Search Form -->
        <div class="pt-3 border-t border-slate-100">
          <label class="block text-xs font-semibold text-slate-700 mb-1.5">
            Cari Kode QR / Barcode Tiket
          </label>
          <div class="flex gap-2">
            <input
              v-model="searchCode"
              type="text"
              placeholder="Masukkan kode QR tiket..."
              class="flex-1 px-3 py-2 text-xs rounded-lg border border-slate-300 focus:border-[#ff9900] outline-none font-mono"
              @keyup.enter="lookupTicket(searchCode)"
            />
            <button
              @click="lookupTicket(searchCode)"
              :disabled="isLoadingLookup || !searchCode"
              class="px-4 py-2 bg-[#ff9900] hover:bg-[#e68a00] text-slate-900 text-xs font-bold rounded-lg transition disabled:opacity-60 cursor-pointer"
            >
              Cari
            </button>
          </div>
        </div>
      </div>

      <!-- Right Column: POS Billing Breakdown & Checkout (7 cols) -->
      <div class="lg:col-span-7 bg-white p-6 rounded-xl border border-slate-200 shadow-xs space-y-6">
        <!-- Error Alert -->
        <div v-if="errorMessage" class="p-3.5 rounded-lg bg-red-50 border border-red-200 text-xs text-red-800 flex items-center gap-2">
          <AlertCircle class="w-4 h-4 shrink-0 text-red-600" />
          <span>{{ errorMessage }}</span>
        </div>

        <!-- Success Checkout Alert -->
        <div v-if="successPayment" class="p-5 rounded-xl bg-emerald-50 border border-emerald-300 text-emerald-900 space-y-3">
          <div class="flex items-center gap-2 font-bold text-sm">
            <CheckCircle2 class="w-5 h-5 text-emerald-600" />
            <span>Transaksi Selesai &mdash; Palang Terbuka</span>
          </div>
          <p class="text-xs">
            Kendaraan telah dicatat keluar dari sistem. Silakan cetak struk jika pengendara membutuhkan bukti pembayaran.
          </p>
          <div class="flex gap-2 pt-2">
            <button
              @click="cetakStrukKeluar"
              class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold rounded-lg transition flex items-center gap-2 cursor-pointer"
            >
              <Printer class="w-4 h-4" />
              <span>Cetak Struk Kasir</span>
            </button>
            <button
              @click="resetTransaction"
              class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 text-xs font-bold rounded-lg transition cursor-pointer"
            >
              Transaksi Baru
            </button>
          </div>
        </div>

        <!-- Active Scanned Ticket State -->
        <div v-else-if="activeTicket" class="space-y-6">
          <!-- Ticket Header Pill -->
          <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div>
              <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Jenis Tiket</span>
              <span class="text-sm font-bold text-[#001a4e] uppercase">
                {{ activeTicket.type === 'member' ? 'Pelanggan Langganan (Member)' : 'Pengendara Umum (Reguler)' }}
              </span>
            </div>
            <span
              class="px-3 py-1 rounded text-xs font-bold uppercase tracking-wider"
              :class="activeTicket.type === 'member' ? 'bg-indigo-100 text-indigo-800' : 'bg-amber-100 text-amber-900'"
            >
              {{ activeTicket.type === 'member' ? 'Member' : activeTicket.data.kode_tiket }}
            </span>
          </div>

          <!-- Billing Details Table -->
          <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 space-y-2.5 text-xs">
            <div class="flex justify-between">
              <span class="text-slate-500">Waktu Masuk:</span>
              <span class="font-mono font-bold text-slate-800">{{ formatDateTime(activeTicket.data.waktu_masuk) }}</span>
            </div>
            <div v-if="activeTicket.type === 'ticket'" class="flex justify-between">
              <span class="text-slate-500">Durasi Parkir:</span>
              <span class="font-mono font-bold text-slate-800">
                {{ activeTicket.data.durasi_menit }} Menit ({{ activeTicket.data.durasi_jam }} Jam)
              </span>
            </div>
            <div v-if="activeTicket.type === 'member'" class="flex justify-between">
              <span class="text-slate-500">Nama Member:</span>
              <span class="font-bold text-slate-800">{{ activeTicket.data.nama }} ({{ activeTicket.data.perusahaan }})</span>
            </div>
            <div class="flex justify-between pt-2 border-t border-slate-200 items-baseline">
              <span class="text-sm font-bold text-slate-700">Total Tagihan:</span>
              <span class="text-2xl font-black font-mono" :class="activeTicket.type === 'member' ? 'text-indigo-700' : 'text-emerald-700'">
                {{ formatRupiah(activeTicket.type === 'member' ? 0 : activeTicket.data.total_bayar) }}
              </span>
            </div>
          </div>

          <!-- Checkout Input Form -->
          <form @submit.prevent="submitCheckout" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <!-- Plat Nomor -->
              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                  Nomor Polisi (Plat Nomor)
                </label>
                <input
                  v-model="checkoutForm.plat_nomor"
                  type="text"
                  required
                  placeholder="B 1234 ABC"
                  class="w-full px-3.5 py-2.5 text-xs uppercase font-mono font-bold rounded-lg border border-slate-300 focus:border-[#ff9900] outline-none"
                />
              </div>

              <!-- Jenis Kendaraan -->
              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                  Jenis Kendaraan
                </label>
                <select
                  v-model="checkoutForm.jenis_kendaraan"
                  required
                  class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-lg border border-slate-300 focus:border-[#ff9900] outline-none bg-white"
                >
                  <option value="mobil">Mobil (Roda 4)</option>
                  <option value="motor">Sepeda Motor (Roda 2)</option>
                </select>
              </div>
            </div>

            <div class="pt-2 flex gap-3">
              <button
                type="submit"
                :disabled="isSubmitting"
                class="flex-1 py-3 px-4 bg-[#001a4e] hover:bg-[#001033] text-white text-xs font-bold rounded-lg transition flex items-center justify-center gap-2 cursor-pointer shadow-xs disabled:opacity-70"
              >
                <Loader2 v-if="isSubmitting" class="w-4 h-4 animate-spin" />
                <Check v-else class="w-4 h-4" />
                <span>{{ isSubmitting ? 'Memproses...' : (activeTicket.type === 'member' ? 'Selesaikan Sesi Member (Rp 0)' : 'Terima Pembayaran & Buka Palang') }}</span>
              </button>

              <button
                type="button"
                @click="resetTransaction"
                class="px-4 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition cursor-pointer"
              >
                Batal
              </button>
            </div>
          </form>
        </div>

        <!-- Empty / Idle State -->
        <div v-else class="text-center py-16 text-slate-400">
          <Receipt class="w-12 h-12 mx-auto mb-3 opacity-30 text-slate-600" />
          <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
            Menunggu Tiket Keluar
          </h4>
          <p class="text-xs text-slate-500 max-w-sm mx-auto">
            Scan tiket fisik atau kartu member pengendara pada kamera di sebelah kiri untuk melihat rincian biaya.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, onUnmounted } from 'vue'
import {
  ScanBarcode,
  CameraOff,
  AlertCircle,
  AlertTriangle,
  CheckCircle2,
  Receipt,
  Printer,
  Check,
  Loader2,
} from 'lucide-vue-next'
import { Html5Qrcode } from 'html5-qrcode'

definePageMeta({
  layout: 'petugas',
  middleware: 'auth',
})

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
let html5QrCode: Html5Qrcode | null = null

const checkoutForm = reactive({
  plat_nomor: '',
  jenis_kendaraan: 'mobil',
})

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
      cameraError.value = 'Izin kamera diblokir browser. Klik ikon gembok/kamera di sebelah kiri URL browser dan pilih "Izinkan" (Allow).'
    } else if (err?.name === 'NotReadableError' || err?.message?.toLowerCase().includes('in use')) {
      cameraError.value = 'Kamera sedang digunakan aplikasi lain (Kamera Windows/Zoom/OBS) atau terblokir di Windows Privacy Settings. Tutup aplikasi yang menggunakan kamera lalu coba lagi.'
    } else {
      cameraError.value = `Tidak dapat membuka kamera: ${err?.message || err}. Pastikan webcam terhubung dan izin diberikan.`
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

  try {
    const res = await api.post('/keluar/scan', { qr_code: code })
    if (res.data?.success) {
      activeTicket.value = res.data
      searchCode.value = ''
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
}

const cetakStrukKeluar = () => {
  window.print()
}

const formatDateTime = (str: string) => {
  if (!str) return '-'
  return new Date(str).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  }) + ' WIB'
}

const formatRupiah = (val: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(val || 0)
}

onUnmounted(() => {
  stopCamera()
})
</script>
