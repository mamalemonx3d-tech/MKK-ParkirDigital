<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-lg font-bold text-[#001a4e]">
          Iuran & Tagihan Member Parkir
        </h2>
        <p class="text-xs text-slate-500 mt-0.5">
          Kelola pembayaran tagihan berkala anggota langganan.
        </p>
      </div>
      <button
        @click="fetchTagihan"
        class="px-3.5 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-2 cursor-pointer"
      >
        <RefreshCw class="w-3.5 h-3.5" :class="isLoading ? 'animate-spin' : ''" />
        <span>Perbarui Tagihan</span>
      </button>
    </div>

    <!-- Table Tagihan -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
              <th class="py-3 px-4">Member</th>
              <th class="py-3 px-4">Instansi</th>
              <th class="py-3 px-4">Jumlah Tagihan</th>
              <th class="py-3 px-4">Tanggal Tagihan</th>
              <th class="py-3 px-4">Tanggal Pembayaran</th>
              <th class="py-3 px-4">Status</th>
              <th class="py-3 px-4 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="tagihanList.length === 0">
              <td colspan="7" class="text-center py-8 text-slate-400">
                Belum ada tagihan member yang tercatat.
              </td>
            </tr>
            <tr
              v-for="t in tagihanList"
              :key="t.id"
              class="hover:bg-slate-50/80 transition"
            >
              <td class="py-3 px-4">
                <span class="font-bold text-slate-900 block">{{ t.member?.nama || '-' }}</span>
                <span class="text-[10px] font-mono text-slate-400">{{ t.member?.kode || '-' }}</span>
              </td>
              <td class="py-3 px-4 text-slate-600">
                {{ t.member?.perusahaan || '-' }}
              </td>
              <td class="py-3 px-4 font-mono font-bold text-slate-800">
                {{ formatRupiah(t.jumlah) }}
              </td>
              <td class="py-3 px-4 font-mono text-slate-700">
                {{ t.tanggal_tagihan }}
              </td>
              <td class="py-3 px-4 font-mono text-slate-700">
                {{ t.tanggal_bayar || '-' }}
              </td>
              <td class="py-3 px-4">
                <span
                  class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                  :class="t.status === 'lunas' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200'"
                >
                  {{ t.status === 'lunas' ? 'Lunas' : 'Belum Lunas' }}
                </span>
              </td>
              <td class="py-3 px-4 text-center">
                <button
                  v-if="t.status !== 'lunas'"
                  @click="bayarTagihan(t.id)"
                  class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] rounded transition cursor-pointer"
                >
                  Bayar Sekarang
                </button>
                <span v-else class="text-[11px] text-slate-400 font-medium">Tuntas</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { RefreshCw } from 'lucide-vue-next'

definePageMeta({
  layout: 'petugas',
  middleware: 'auth',
})

const api = useApi()
const tagihanList = ref<any[]>([])
const isLoading = ref(false)

const fetchTagihan = async () => {
  isLoading.value = true
  try {
    const res = await api.get('/tagihan')
    tagihanList.value = res.data || []
  } catch (err) {
    console.error('Gagal mengambil daftar tagihan:', err)
  } finally {
    isLoading.value = false
  }
}

const bayarTagihan = async (id: number) => {
  if (!confirm('Konfirmasi penerimaan pembayaran tagihan ini?')) return
  try {
    const res = await api.post(`/tagihan/${id}/bayar`)
    if (res.data?.success) {
      await fetchTagihan()
    }
  } catch (err: any) {
    alert(err.response?.data?.message || 'Gagal memproses pembayaran.')
  }
}

const formatRupiah = (val: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(val || 0)
}

onMounted(() => {
  fetchTagihan()
})
</script>
