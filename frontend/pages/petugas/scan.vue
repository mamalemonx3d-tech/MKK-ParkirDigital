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
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
            <Camera class="w-4 h-4 text-[#0099ff]" />
            <span>Kamera Scanner</span>
          </span>
          <button
            @click="toggleCamera"
            class="text-xs font-semibold px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 transition cursor-pointer"
          >
            {{ isScanning ? 'Matikan Kamera' : 'Nyalakan Kamera' }}
          </button>
        </div>

        <!-- Video Reader Element Container -->
        <div class="relative bg-slate-900 rounded-lg overflow-hidden aspect-4/3 flex items-center justify-center border border-slate-800">
          <div id="reader" class="w-full h-full"></div>
          <div v-if="!isScanning" class="absolute inset-0 flex flex-col items-center justify-center text-slate-400 p-4 text-center">
            <CameraOff class="w-8 h-8 mb-2 opacity-50" />
            <p class="text-xs">Kamera scanner belum aktif.</p>
            <button
              @click="toggleCamera"
              class="mt-3 px-3 py-1.5 bg-[#0099ff] hover:bg-[#0084de] text-white text-xs font-bold rounded cursor-pointer"
            >
              Aktifkan Scanner
            </button>
          </div>
        </div>

        <!-- Manual Input Alternative -->
        <div class="pt-3 border-t border-slate-100">
          <label class="block text-xs font-semibold text-slate-700 mb-1.5">
            Input QR Code Manual
          </label>
          <div class="flex gap-2">
            <input
              v-model="manualQr"
              type="text"
              placeholder="Tempel atau ketik kode QR member..."
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
} from 'lucide-vue-next'
import { Html5Qrcode } from 'html5-qrcode'

definePageMeta({
  layout: 'petugas',
  middleware: 'auth',
})

const api = useApi()
const isScanning = ref(false)
const isLoading = ref(false)
const manualQr = ref('')
const errorResult = ref('')
const successResult = ref<any>(null)
let html5QrCode: Html5Qrcode | null = null

const toggleCamera = async () => {
  if (isScanning.value) {
    stopCamera()
  } else {
    startCamera()
  }
}

const startCamera = async () => {
  try {
    if (!html5QrCode) {
      html5QrCode = new Html5Qrcode('reader')
    }
    await html5QrCode.start(
      { facingMode: 'environment' },
      { fps: 10, qrbox: { width: 250, height: 250 } },
      (decodedText) => {
        handleScan(decodedText)
      },
      () => {}
    )
    isScanning.value = true
  } catch (err) {
    console.warn('Gagal menyalakan scanner kamera:', err)
    isScanning.value = false
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

onMounted(() => {
  // Biarkan pengguna menyalakan kamera secara manual agar tidak mengganggu izin browser
})

onUnmounted(() => {
  stopCamera()
})
</script>
