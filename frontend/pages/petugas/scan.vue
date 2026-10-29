<template>
  <div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs flex items-center justify-between">
      <div>
        <h2 class="text-lg font-bold text-[#001a4e]">
          Pintu Masuk Gerbang: Scan Kartu Member
        </h2>
        <p class="text-xs text-slate-500 mt-0.5">
          Arahkan QR Code kartu member ke kamera atau masukkan kode secara manual.
        </p>
      </div>
      <span class="px-3 py-1 rounded-full text-xs font-bold bg-[#e6f4ff] text-[#0099ff] border border-[#0099ff]/30">
        Pintu Masuk 1
      </span>
    </div>

    <!-- Scanner & Result Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
      <!-- Left: Camera Viewport & Manual Input -->
      <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
          <span class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
            <Camera class="w-4 h-4 text-[#0099ff]" />
            <span>Kamera Scanner</span>
          </span>

          <div class="flex items-center gap-2">
            <!-- Camera Device Selector if multiple available -->
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

        <!-- Camera Error Banner -->
        <div v-if="cameraError" class="p-3.5 rounded-lg bg-amber-50 border border-amber-300 text-xs text-amber-900 space-y-1">
          <div class="font-bold flex items-center gap-1.5 text-amber-950">
            <AlertTriangle class="w-4 h-4 text-amber-600 shrink-0" />
            <span>Kendala Akses Kamera</span>
          </div>
          <p class="leading-relaxed text-[11px]">{{ cameraError }}</p>
        </div>

        <!-- Video Reader Element Container -->
        <div class="relative bg-slate-900 rounded-lg overflow-hidden min-h-64 flex items-center justify-center border border-slate-800">
          <div id="reader" class="w-full"></div>

          <!-- Overlay when Camera is OFF -->
          <div v-if="!isScanning && !isStartingCamera" class="p-6 text-center text-slate-400 space-y-3">
            <CameraOff class="w-10 h-10 mx-auto opacity-40 text-slate-400" />
            <div class="space-y-1">
              <p class="text-xs font-bold text-slate-200">Kamera Scanner Belum Aktif</p>
              <p class="text-[11px] text-slate-400 max-w-xs mx-auto">
                Klik tombol di bawah ini untuk mengaktifkan webcam/kamera laptop Anda.
              </p>
            </div>
            <button
              @click="startCamera"
              class="px-4 py-2 bg-[#0099ff] hover:bg-[#0084de] text-white text-xs font-bold rounded-lg cursor-pointer shadow-xs"
            >
              Aktifkan Scanner Sekarang
            </button>
          </div>

          <!-- Overlay when Camera is STARTING -->
          <div v-if="isStartingCamera" class="p-6 text-center text-slate-300 space-y-2">
            <Loader2 class="w-8 h-8 mx-auto animate-spin text-[#0099ff]" />
            <p class="text-xs font-semibold">Menginisialisasi perangkat kamera...</p>
          </div>
        </div>

        <!-- Manual Input Alternative & USB Barcode Scanner -->
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
            Mendukung tembakan barcode scanner USB atau ketik manual kode kartu member.
          </p>
          <div class="flex gap-2">
            <input
              id="scanner-manual-input"
              v-model="manualQr"
              type="text"
              placeholder="Arahkan barcode scanner atau ketik kode..."
              class="flex-1 px-3 py-2 text-xs rounded-lg border border-slate-300 focus:border-[#0099ff] outline-none font-mono"
              @keyup.enter="handleScan(manualQr)"
            />
            <button
              @click="handleScan(manualQr)"
              :disabled="isLoading || !manualQr"
              class="px-4 py-2 bg-[#001a4e] hover:bg-[#001033] text-white text-xs font-bold rounded-lg transition disabled:opacity-60 cursor-pointer"
            >
              Proses
            </button>
          </div>
        </div>
      </div>

      <!-- Right: Scan Feedback & Gate Status -->
      <div class="space-y-4">
        <!-- Error State -->
        <div v-if="errorResult" class="bg-red-50 border border-red-200 rounded-xl p-5 text-red-800 space-y-3">
          <div class="flex items-center gap-2 font-bold text-sm text-red-900">
            <AlertTriangle class="w-5 h-5 text-red-600" />
            <span>Akses Ditolak</span>
          </div>
          <p class="text-xs leading-relaxed">{{ errorResult }}</p>
          <div class="pt-2">
            <button
              @click="clearResult"
              class="text-xs font-bold px-3 py-1.5 bg-red-200 hover:bg-red-300 rounded text-red-900 cursor-pointer"
            >
              Tutup Pesan
            </button>
          </div>
        </div>

        <!-- Success State -->
        <div v-else-if="successResult" class="bg-emerald-50 border border-emerald-200 rounded-xl p-5 text-emerald-900 space-y-4">
          <div class="flex items-center justify-between pb-3 border-b border-emerald-200">
            <div class="flex items-center gap-2 font-bold text-sm text-emerald-800">
              <CheckCircle2 class="w-5 h-5 text-emerald-600" />
              <span>Akses Diterima - Palang Terbuka</span>
            </div>
            <span class="px-2.5 py-0.5 rounded text-[11px] font-mono font-bold bg-emerald-200 text-emerald-900">
              {{ successResult.tiket.kode_tiket }}
            </span>
          </div>

          <div class="space-y-2 text-xs">
            <div class="flex justify-between">
              <span class="text-slate-600">Nama Member:</span>
              <span class="font-bold text-slate-900">{{ successResult.member.nama }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-slate-600">Kode Member:</span>
              <span class="font-mono font-bold text-slate-900">{{ successResult.member.kode }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-slate-600">Perusahaan:</span>
              <span class="font-semibold text-slate-800">{{ successResult.member.perusahaan }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-slate-600">Masa Berlaku:</span>
              <span class="font-mono text-slate-800">{{ successResult.member.expired_at }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-slate-600">Waktu Masuk:</span>
              <span class="font-mono font-bold text-emerald-700">{{ formatTime(successResult.tiket.waktu_masuk) }}</span>
            </div>
          </div>

          <div class="pt-2 flex gap-2">
            <button
              @click="clearResult"
              class="w-full py-2 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold rounded-lg transition cursor-pointer"
            >
              Siap untuk Kendaraan Berikutnya
            </button>
          </div>
        </div>

        <!-- Idle State -->
        <div v-else class="bg-white p-6 rounded-xl border border-slate-200 shadow-xs text-center text-slate-400 py-12">
          <ScanLine class="w-10 h-10 mx-auto mb-3 opacity-40 text-slate-500" />
          <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
            Menunggu Pemindaian
          </h4>
          <p class="text-xs text-slate-500 max-w-xs mx-auto">
            Scan kartu member pengendara saat mendekati palang pintu masuk.
          </p>
        </div>

        <!-- Operational Rules Card -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 text-xs text-slate-500 space-y-1">
          <span class="font-bold text-slate-700 block mb-1">Ketentuan Masuk Member:</span>
          <p>• Kartu harus berstatus aktif dan belum melewati batas tanggal berlaku.</p>
          <p>• <strong>Anti-Passback:</strong> Satu kartu tidak dapat digunakan masuk kembali jika kendaraan belum keluar.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import {
  Camera,
  CameraOff,
  AlertTriangle,
  CheckCircle2,
  ScanLine,
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
const isLoading = ref(false)
const manualQr = ref('')
const errorResult = ref('')
const successResult = ref<any>(null)
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
      html5QrCode = new Html5Qrcode('reader')
    }

    // 1. Coba ambil daftar kamera yang tersedia di perangkat
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
      console.warn('Gagal membaca list perangkat, mencoba fallback kamera default:', e)
    }

    // Konfigurasi scanner
    const config = {
      fps: 10,
      qrbox: { width: 220, height: 220 },
      aspectRatio: 1.333,
    }

    // 2. Jalankan kamera
    const onScanSuccess = (decodedText: string) => {
      handleScan(decodedText)
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
    console.error('Error saat menyalakan scanner kamera:', err)
    isScanning.value = false

    if (err?.name === 'NotAllowedError' || err?.message?.toLowerCase().includes('permission')) {
      cameraError.value = 'Izin kamera diblokir oleh browser Brave/Chrome. Silakan klik ikon gembok/kamera di sebelah kiri bilah URL browser dan pilih "Izinkan" (Allow), lalu refresh.'
    } else if (err?.name === 'NotReadableError' || err?.message?.toLowerCase().includes('in use')) {
      cameraError.value = 'Kamera sedang digunakan oleh aplikasi lain (Zoom/OBS/dll) atau terblokir di sistem operasi Windows. Tutup aplikasi yang menggunakan kamera lalu coba lagi.'
    } else {
      cameraError.value = `Tidak dapat membuka kamera: ${err?.message || err}. Pastikan webcam terpasang dan browser memiliki izin.`
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
      console.warn('Error saat menghentikan scanner:', err)
    }
    isScanning.value = false
  }
}

const handleScan = async (code: string) => {
  if (!code || isLoading.value) return
  isLoading.value = true
  errorResult.value = ''
  successResult.value = null

  try {
    const res = await api.post('/scan', { qr_code: code })
    if (res.data?.success) {
      successResult.value = res.data.data
      manualQr.value = ''
    } else {
      errorResult.value = res.data?.message || 'Akses ditolak.'
    }
  } catch (err: any) {
    errorResult.value = err.response?.data?.message || 'Kode QR tidak dikenali atau masa berlaku habis.'
  } finally {
    isLoading.value = false
  }
}

const clearResult = () => {
  errorResult.value = ''
  successResult.value = null
  manualQr.value = ''
}

const formatTime = (timeStr: string) => {
  if (!timeStr) return '-'
  return new Date(timeStr).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB'
}

// Global Barcode Scanner (HID Keyboard Wedge) Listener
let barcodeBuffer = ''
let lastKeyTime = 0

const handleGlobalKeyDown = (e: KeyboardEvent) => {
  const activeEl = document.activeElement as HTMLElement
  if (activeEl && (activeEl.tagName === 'INPUT' || activeEl.tagName === 'TEXTAREA') && activeEl.id !== 'scanner-manual-input') {
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

onMounted(() => {
  window.addEventListener('keydown', handleGlobalKeyDown)
})

onUnmounted(() => {
  stopCamera()
  window.removeEventListener('keydown', handleGlobalKeyDown)
})
</script>
