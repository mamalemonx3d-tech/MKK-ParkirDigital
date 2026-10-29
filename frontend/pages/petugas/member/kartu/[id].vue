<template>
  <div class="max-w-xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between no-print">
      <NuxtLink
        to="/petugas/member"
        class="text-xs font-semibold text-slate-600 hover:text-[#001a4e] flex items-center gap-1"
      >
        <ArrowLeft class="w-4 h-4" />
        <span>Kembali ke Data Member</span>
      </NuxtLink>

      <button
        @click="printCard"
        class="px-4 py-2 bg-[#001a4e] hover:bg-[#001033] text-white text-xs font-bold rounded-lg transition flex items-center gap-2 cursor-pointer shadow-xs"
      >
        <Printer class="w-4 h-4" />
        <span>Cetak Kartu Fisik</span>
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="text-center py-12 text-slate-400">
      <Loader2 class="w-8 h-8 animate-spin mx-auto mb-2 text-[#0099ff]" />
      <span class="text-xs">Memuat data kartu...</span>
    </div>

    <!-- Physical Member Access Card Preview -->
    <div
      v-else-if="member"
      id="printable-card"
      class="bg-white rounded-2xl border-2 border-slate-200 shadow-xl overflow-hidden aspect-16/10 flex flex-col justify-between p-6 relative print:border-none print:shadow-none"
    >
      <!-- Top Card Strip with Secure Parking Branding -->
      <div class="flex items-start justify-between">
        <BrandLogo :showSubtitle="true" subtitle="Kartu Akses Parkir Berlangganan" />
        <div class="text-right">
          <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">ID KARTU</span>
          <span class="text-base font-black font-mono text-[#001a4e]">{{ member.kode }}</span>
        </div>
      </div>

      <!-- Center Card Info & QR Code -->
      <div class="my-auto flex items-center justify-between gap-6 py-4">
        <div class="space-y-1">
          <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Pemegang Kartu</span>
          <h3 class="text-xl font-black text-slate-900 tracking-tight leading-tight">
            {{ member.nama }}
          </h3>
          <p class="text-xs font-semibold text-slate-600">
            {{ member.perusahaan }}
          </p>

          <div class="pt-3">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Berlaku Hingga</span>
            <span class="text-xs font-mono font-bold text-emerald-700">
              {{ member.expired_at }}
            </span>
          </div>
        </div>

        <!-- QR Code for Gate Scanner -->
        <div class="p-3 bg-white rounded-xl border border-slate-200 shadow-xs shrink-0">
          <QrcodeVue :value="member.qr_code" :size="120" level="M" />
        </div>
      </div>

      <!-- Bottom Card Strip -->
      <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-[10px] text-slate-400 font-medium">
        <span>Arahkan kode QR ke kamera pintu gerbang masuk.</span>
        <span class="font-mono">STATUS: AKTIF</span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { ArrowLeft, Printer, Loader2 } from 'lucide-vue-next'
import QrcodeVue from 'qrcode.vue'
import BrandLogo from '~/components/BrandLogo.vue'

definePageMeta({
  layout: 'petugas',
  middleware: 'auth',
})

const route = useRoute()
const api = useApi()
const member = ref<any>(null)
const isLoading = ref(true)

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

<style scoped>
@media print {
  .no-print {
    display: none !important;
  }
  body {
    background: white !important;
  }
}
</style>
