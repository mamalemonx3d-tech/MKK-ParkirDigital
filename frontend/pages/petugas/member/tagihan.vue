<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-lg font-normal text-[#0f172a] font-serif tracking-tight">
          Iuran & Tagihan Member Parkir
        </h1>
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

    <!-- Filter Strip -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex flex-col sm:flex-row items-center gap-3">
      <div class="relative flex-1 w-full">
        <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
        <input
          v-model="searchQuery"
          @input="currentPage = 1"
          type="text"
          placeholder="Cari nama member, kode, atau PT..."
          class="w-full pl-9 pr-3.5 py-2 text-xs rounded-lg border border-slate-300 focus:border-[#3b82f6] outline-none"
        />
      </div>

      <select
        v-model="statusFilter"
        @change="currentPage = 1"
        class="w-full sm:w-44 px-3 py-2 text-xs rounded-lg border border-slate-300 focus:border-[#3b82f6] outline-none bg-white font-medium"
      >
        <option value="all">Semua Status</option>
        <option value="lunas">Lunas</option>
        <option value="belum">Belum Lunas</option>
      </select>
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
            <tr v-if="filteredTagihan.length === 0">
              <td colspan="7" class="text-center py-8 text-slate-400">
                Belum ada tagihan member yang sesuai filter.
              </td>
            </tr>
            <tr
              v-for="t in paginatedTagihan"
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
                <div class="inline-flex items-center gap-1.5 justify-center">
                  <button
                    v-if="t.status !== 'lunas'"
                    @click="bayarTagihan(t.id)"
                    class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] rounded transition cursor-pointer"
                  >
                    Bayar Sekarang
                  </button>
                  <button
                    v-else
                    @click="bukaInvoice(t)"
                    class="px-2.5 py-1 bg-slate-100 hover:bg-[#e6f4ff] text-[#3b82f6] font-bold text-[11px] rounded transition cursor-pointer inline-flex items-center gap-1"
                    title="Cetak Invoice Kuitansi"
                  >
                    <Receipt class="w-3.5 h-3.5" />
                    <span>Invoice</span>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <PaginationControl
        v-model:currentPage="currentPage"
        :totalItems="filteredTagihan.length"
        :itemsPerPage="itemsPerPage"
      />
    </div>

    <!-- Modal Invoice Kuitansi Member Resmi -->
    <div
      v-if="activeInvoice"
      class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4"
    >
      <div class="bg-white rounded-2xl border border-slate-200 max-w-lg w-full p-6 sm:p-8 shadow-2xl space-y-6 relative print:border-none print:shadow-none print:max-w-full">
        <!-- Close button (hidden in print) -->
        <button
          @click="activeInvoice = null"
          class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 print:hidden cursor-pointer"
        >
          <X class="w-5 h-5" />
        </button>

        <!-- Printable Invoice Area -->
        <div id="printable-invoice" class="space-y-6">
          <!-- Invoice Header -->
          <div class="flex items-start justify-between border-b-2 border-slate-200 pb-4">
            <div class="flex items-center gap-3">
              <LottieAnimation animationPath="/lottie/check.json" width="w-12" height="h-12" :loop="false" class="print:hidden" />
              <div>
                <BrandLogo :showSubtitle="true" subtitle="Kuitansi Resmi Iuran Member" />
                <p class="text-[10px] text-slate-400 font-mono mt-1">SISTEM PENGELOLAAN PARKIR TERPADU</p>
              </div>
            </div>
            <div class="text-right">
              <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400 block">KODE INVOICE</span>
              <p class="text-xs font-mono font-black text-[#3b82f6]">
                INV-MBR-{{ String(activeInvoice.id).padStart(4, '0') }}-{{ activeInvoice.tanggal_bayar ? activeInvoice.tanggal_bayar.replace(/-/g, '') : '2026' }}
              </p>
              <span class="inline-block mt-1 px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-300">
                LUNAS / PAID
              </span>
            </div>
          </div>

          <!-- Member & Cashier Metadata Grid -->
          <div class="grid grid-cols-2 gap-4 text-xs">
            <div class="space-y-1">
              <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Diterbitkan Untuk:</span>
              <p class="font-black text-slate-900 text-sm">{{ activeInvoice.member?.nama || '-' }}</p>
              <p class="font-mono text-slate-600">ID: {{ activeInvoice.member?.kode || '-' }}</p>
              <p class="text-slate-500">{{ activeInvoice.member?.perusahaan || '-' }}</p>
            </div>
            <div class="space-y-1 text-right">
              <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Detail Transaksi:</span>
              <p class="text-slate-700">Tgl Bayar: <span class="font-mono font-bold">{{ activeInvoice.tanggal_bayar || '-' }}</span></p>
              <p class="text-slate-700">Tgl Tagihan: <span class="font-mono">{{ activeInvoice.tanggal_tagihan || '-' }}</span></p>
              <p class="text-slate-700">Kasir: <span class="font-bold">{{ auth.user.value?.name || 'Petugas Kasir' }}</span></p>
            </div>
          </div>

          <!-- Line Items Table -->
          <div class="border rounded-xl border-slate-200 overflow-hidden">
            <table class="w-full text-left text-xs border-collapse">
              <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase text-[10px]">
                <tr>
                  <th class="py-2.5 px-4">Deskripsi Pembayaran</th>
                  <th class="py-2.5 px-4 text-right">Jumlah</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr>
                  <td class="py-3 px-4">
                    <p class="font-bold text-slate-800">Iuran Berlangganan Parkir Member</p>
                    <p class="text-[10px] text-slate-400">Periode Tagihan: {{ activeInvoice.tanggal_tagihan }}</p>
                  </td>
                  <td class="py-3 px-4 text-right font-mono font-bold text-slate-900">
                    {{ formatRupiah(activeInvoice.jumlah) }}
                  </td>
                </tr>
              </tbody>
              <tfoot class="bg-slate-50 border-t border-slate-200 font-mono">
                <tr>
                  <td class="py-3 px-4 font-bold text-slate-800 uppercase text-xs">Total Pembayaran</td>
                  <td class="py-3 px-4 text-right font-black text-sm text-emerald-700">
                    {{ formatRupiah(activeInvoice.jumlah) }}
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>

          <!-- Signature & Notice -->
          <div class="pt-2 flex items-end justify-between text-[11px] text-slate-500">
            <div class="space-y-1">
              <p class="italic">Simpan kuitansi ini sebagai bukti pembayaran iuran resmi.</p>
              <p class="text-[10px] text-slate-400">Dicetak melalui Secure Parking Digital Management.</p>
            </div>
            <div class="text-center w-36">
              <p class="text-[10px] text-slate-400 mb-8">Petugas Kasir Pos,</p>
              <p class="font-bold border-t border-slate-300 pt-1 text-slate-800">{{ auth.user.value?.name || 'Petugas Kasir' }}</p>
            </div>
          </div>
        </div>

        <!-- Action Buttons (hidden in print) -->
        <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3 print:hidden">
          <button
            @click="activeInvoice = null"
            class="px-4 py-2 rounded-lg border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-bold transition cursor-pointer"
          >
            Tutup
          </button>
          <button
            @click="cetakInvoice"
            class="px-5 py-2 rounded-lg bg-[#3b82f6] hover:bg-[#2563eb] text-white border border-blue-600 text-xs font-bold transition flex items-center gap-2 cursor-pointer shadow-xs"
          >
            <Printer class="w-4 h-4 text-[#f59e0b]" />
            <span>Cetak Invoice</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { RefreshCw, Receipt, X, Printer, Search } from 'lucide-vue-next'
import BrandLogo from '~/components/BrandLogo.vue'
import PaginationControl from '~/components/PaginationControl.vue'

definePageMeta({
  layout: 'petugas',
  middleware: 'auth',
})

const auth = useAuth()
const api = useApi()
const { askConfirm, askAlert } = useConfirm()
const tagihanList = ref<any[]>([])
const isLoading = ref(false)
const activeInvoice = ref<any>(null)
const searchQuery = ref('')
const statusFilter = ref<'all' | 'lunas' | 'belum'>('all')
const currentPage = ref(1)
const itemsPerPage = ref(10)

const filteredTagihan = computed(() => {
  return tagihanList.value.filter(t => {
    if (statusFilter.value !== 'all' && t.status !== statusFilter.value) {
      return false
    }
    if (searchQuery.value.trim()) {
      const q = searchQuery.value.toLowerCase().trim()
      const nama = t.member?.nama?.toLowerCase() || ''
      const kode = t.member?.kode?.toLowerCase() || ''
      const pt = t.member?.perusahaan?.toLowerCase() || ''
      if (!nama.includes(q) && !kode.includes(q) && !pt.includes(q)) {
        return false
      }
    }
    return true
  })
})

const paginatedTagihan = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  return filteredTagihan.value.slice(start, start + itemsPerPage.value)
})

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

const bukaInvoice = (t: any) => {
  activeInvoice.value = t
}

const cetakInvoice = () => {
  window.print()
}

const bayarTagihan = async (id: number) => {
  const ok = await askConfirm({
    title: 'Konfirmasi Pembayaran Tagihan',
    message: 'Apakah Anda yakin ingin memproses penerimaan pembayaran iuran member ini? Status akan otomatis menjadi LUNAS.',
    type: 'info',
    confirmText: 'Proses Pembayaran',
    cancelText: 'Batal',
  })
  if (!ok) return

  try {
    const res = await api.post(`/tagihan/${id}/bayar`)
    if (res.data?.success) {
      await fetchTagihan()
      if (res.data.data) {
        activeInvoice.value = res.data.data
      }
    }
  } catch (err: any) {
    await askAlert(err.response?.data?.message || 'Gagal memproses pembayaran.', 'Gagal Memproses', 'danger')
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
