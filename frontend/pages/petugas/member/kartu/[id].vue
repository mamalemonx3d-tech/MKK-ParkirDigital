<template>
  <div class="min-h-screen bg-[#f8fafc] text-[#0f172a] font-sans flex flex-col justify-between print:min-h-0 print:bg-white print:block">
    <!-- Top Action Bar (Hidden on Print) -->
    <header class="no-print print:hidden bg-white border-b border-slate-200 px-6 py-4 sticky top-0 z-30 shadow-2xs">
      <div class="max-w-4xl mx-auto flex items-center justify-between">
        <NuxtLink
          to="/petugas/member"
          class="text-xs font-bold text-slate-600 hover:text-[#3b82f6] flex items-center gap-1.5 transition cursor-pointer"
        >
          <ArrowLeft class="w-4 h-4" />
          <span>Kembali ke Data Member</span>
        </NuxtLink>

        <div class="flex items-center gap-3">
          <span class="text-xs text-slate-500 font-medium hidden sm:inline">
            Pratinjau Kartu Fisik Berlangganan
          </span>
          <button
            @click="printCard"
            class="px-4 py-2.5 bg-[#3b82f6] hover:bg-[#2563eb] text-white border border-blue-600 text-xs font-bold rounded-xl transition flex items-center gap-2 cursor-pointer shadow-xs active:scale-[0.98]"
          >
            <Printer class="w-4 h-4" />
            <span>Cetak Kartu Fisik</span>
          </button>
        </div>
      </div>
    </header>

    <!-- Center Card Preview Canvas -->
    <main class="flex-1 flex items-center justify-center p-4 sm:p-8 print:p-0 print:m-0 print:block">
      <!-- Loading State -->
      <div v-if="isLoading" class="text-center py-12 text-slate-400 no-print print:hidden">
        <Loader2 class="w-8 h-8 animate-spin mx-auto mb-2 text-[#3b82f6]" />
        <span class="text-xs font-semibold">Memuat data kartu member...</span>
      </div>

      <!-- Physical Member Access Card Container -->
      <div v-else-if="member" class="card-print-container w-full max-w-xl flex justify-center print:max-w-none print:w-full">
        <!-- Physical Card (Preserved exactly as preview on print) -->
        <div
          id="printable-card"
          class="bg-white rounded-2xl border-2 border-slate-300 shadow-xl overflow-hidden aspect-16/10 flex flex-col justify-between p-6 sm:p-7 relative w-full select-none"
        >
          <!-- Top Card Header Strip with Logo & Card ID -->
          <div class="flex items-start justify-between">
            <BrandLogo :showSubtitle="true" subtitle="Kartu Akses Parkir Berlangganan" />
            <div class="text-right">
              <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">ID KARTU</span>
              <span class="text-base sm:text-lg font-black font-mono text-[#3b82f6]">{{ member.kode }}</span>
            </div>
          </div>

          <!-- Center Card Info & QR Code -->
          <div class="my-auto flex items-center justify-between gap-6 py-4">
            <div class="space-y-1">
              <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Pemegang Kartu</span>
              <h3 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight leading-tight">
                {{ member.nama }}
              </h3>
              <p class="text-xs sm:text-sm font-semibold text-slate-600">
                {{ member.perusahaan }}
              </p>

              <div class="pt-3">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Berlaku Hingga</span>
                <span
                  class="text-xs sm:text-sm font-mono font-bold"
                  :class="isExpired ? 'text-red-600' : 'text-emerald-700'"
                >
                  {{ formatDate(member.expired_at) }}
                </span>
              </div>
            </div>

            <!-- QR Code for Gate Scanner -->
            <div class="p-3 bg-white rounded-xl border border-slate-200 shadow-xs shrink-0">
              <QrcodeVue :value="member.qr_code || member.kode" :size="124" level="M" />
            </div>
          </div>

          <!-- Bottom Card Strip -->
          <div class="pt-3 border-t border-slate-200 flex items-center justify-between text-[10px] text-slate-400 font-medium">
            <span>Arahkan kode QR ke kamera pintu gerbang masuk.</span>
            <span
              class="font-mono font-bold uppercase"
              :class="isExpired ? 'text-red-600' : (member.status === 'lunas' ? 'text-emerald-600' : 'text-amber-600')"
            >
              STATUS: {{ isExpired ? 'EXPIRED' : (member.status === 'lunas' ? 'AKTIF' : 'BELUM LUNAS') }}
            </span>
          </div>
        </div>
      </div>
    </main>

    <!-- Bottom Help Footer (Hidden on Print) -->
    <footer class="no-print print:hidden text-center py-4 text-xs text-slate-400">
      Gunakan browser print (Ctrl + P) untuk mencetak langsung ke printer ID Card atau printer thermal standar.
    </footer>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { ArrowLeft, Printer, Loader2 } from 'lucide-vue-next'
import QrcodeVue from 'qrcode.vue'
import BrandLogo from '~/components/BrandLogo.vue'
import { formatDate } from '~/utils/formatters'

definePageMeta({
  layout: false, // Bebas dari layout petugas agar print tidak mencetak sidebar/header luar
  middleware: 'auth',
})

const route = useRoute()
const api = useApi()
const member = ref<any>(null)
const isLoading = ref(true)

const isExpired = computed(() => {
  if (!member.value) return false
  if (member.value.status === 'expired' || member.value.computed_status === 'expired') return true
  if (member.value.expired_at) {
    const expDate = new Date(member.value.expired_at)
    const today = new Date()
    today.setHours(0, 0, 0, 0)
    return expDate < today
  }
  return false
})

const fetchMemberDetail = async () => {
  isLoading.value = true
  try {
    const res = await api.get(`/members/${route.params.id}`)
    member.value = res.data
  } catch (err) {
    console.error('Gagal mengambil data kartu member:', err)
  } finally {
    isLoading.value = false
  }
}

const printCard = () => {
  window.print()
}

onMounted(() => {
  fetchMemberDetail()
})
</script>

<style>
@media print {
  @page {
    size: auto;
    margin: 10mm;
  }

  /* Sembunyikan elemen non-cetak */
  .no-print,
  .print\:hidden,
  header,
  footer,
  aside,
  nav {
    display: none !important;
  }

  body, html {
    background: #ffffff !important;
    margin: 0 !important;
    padding: 0 !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  /* Pusatkan kartu pada halaman cetak */
  .card-print-container {
    display: flex !important;
    justify-content: center !important;
    align-items: center !important;
    width: 100% !important;
    padding-top: 10mm !important;
    margin: 0 auto !important;
  }

  #printable-card {
    width: 110mm !important;
    max-width: 110mm !important;
    aspect-ratio: 16 / 10 !important;
    margin: 0 auto !important;
    border: 2px solid #cbd5e1 !important;
    border-radius: 16px !important;
    background-color: #ffffff !important;
    box-shadow: none !important;
    page-break-inside: avoid !important;
    break-inside: avoid !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
}
</style>
