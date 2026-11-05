<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl font-normal text-[#0f172a] font-serif tracking-tight">
          Data Langganan Member Parkir
        </h1>
        <p class="text-xs text-slate-500 mt-0.5">
          Kelola kartu akses langganan (Tarif tetap: <strong>Rp 150.000 / bulan</strong>), edit identitas & PT, pantau total terbayarkan, masa berlaku, dan status kartu.
        </p>
      </div>
      <div class="flex items-center gap-2">
        <button
          @click="openAddModal"
          class="px-4 py-2.5 rounded-xl bg-[#3b82f6] hover:bg-[#2563eb] text-white border border-blue-600 text-xs font-bold transition flex items-center gap-2 cursor-pointer shadow-xs active:scale-[0.98]"
        >
          <UserPlus class="w-4 h-4" />
          <span>Daftar Member Baru</span>
        </button>
      </div>
    </div>

    <!-- Filters & Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
      <!-- Search Input -->
      <div class="relative flex-1 max-w-sm">
        <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari ID kartu, nama pemegang, atau PT..."
          class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-300 focus:border-[#3b82f6] focus:ring-2 focus:ring-[#3b82f6]/20 outline-none transition"
        />
      </div>

      <!-- Status Filter Dropdown -->
      <div class="flex items-center gap-2">
        <span class="text-xs font-semibold text-slate-600 shrink-0">Filter Status:</span>
        <select
          v-model="statusFilter"
          class="px-3 py-2 text-xs rounded-xl border border-slate-300 bg-white text-slate-800 font-semibold focus:border-[#3b82f6] outline-none transition"
        >
          <option value="all">Semua Status ({{ members.length }})</option>
          <option value="lunas">Lunas ({{ countByStatus('lunas') }})</option>
          <option value="belum">Belum Lunas ({{ countByStatus('belum') }})</option>
          <option value="expired">Expired ({{ countByStatus('expired') }})</option>
        </select>
      </div>
    </div>

    <!-- Members Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
              <th class="py-3.5 px-4">ID Kartu</th>
              <th class="py-3.5 px-4">Pemegang Kartu</th>
              <th class="py-3.5 px-4">Perusahaan / PT</th>
              <th class="py-3.5 px-4">Harga 1 Bulan</th>
              <th class="py-3.5 px-4">Total Terbayarkan</th>
              <th class="py-3.5 px-4">Masa Berlaku</th>
              <th class="py-3.5 px-4 text-center">Status Kartu</th>
              <th class="py-3.5 px-4 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="filteredMembers.length === 0">
              <td colspan="8" class="text-center py-12 text-slate-400">
                Tidak ada data member yang sesuai filter.
              </td>
            </tr>
            <tr
              v-for="m in filteredMembers"
              :key="m.id"
              class="hover:bg-slate-50/80 transition"
            >
              <td class="py-3 px-4 font-mono font-bold text-[#3b82f6]">
                {{ m.kode }}
              </td>
              <td class="py-3 px-4 font-bold text-slate-900">
                {{ m.nama }}
              </td>
              <td class="py-3 px-4 text-slate-600 font-medium">
                {{ m.perusahaan }}
              </td>
              <td class="py-3 px-4 font-mono font-semibold text-slate-800">
                <span class="inline-flex items-center gap-1">
                  {{ formatRupiah(FIXED_MONTHLY_RATE) }}
                  <span class="text-[10px] text-slate-400 font-sans">/bln</span>
                </span>
              </td>
              <td class="py-3 px-4 font-mono font-bold text-emerald-700">
                {{ formatRupiah(m.total_terbayar ?? 0) }}
              </td>
              <td class="py-3 px-4 font-mono text-slate-700">
                {{ formatDate(m.expired_at) }}
              </td>
              <td class="py-3 px-4 text-center">
                <!-- Status Badge: Lunas, Belum Lunas, Expired -->
                <span
                  v-if="effectiveStatus(m) === 'lunas'"
                  class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200"
                >
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                  Lunas
                </span>
                <span
                  v-else-if="effectiveStatus(m) === 'expired'"
                  class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-red-50 text-red-700 border border-red-200"
                >
                  <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>
                  Expired
                </span>
                <span
                  v-else
                  class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-50 text-amber-800 border border-amber-300"
                >
                  <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                  Belum Lunas
                </span>
              </td>
              <td class="py-3 px-4 text-center">
                <div class="inline-flex items-center gap-1">
                  <!-- Cetak Kartu Fisik -->
                  <NuxtLink
                    :to="`/petugas/member/kartu/${m.id}`"
                    title="Cetak Kartu Fisik"
                    class="p-1.5 rounded-lg hover:bg-blue-50 text-[#3b82f6] border border-transparent hover:border-blue-200 transition cursor-pointer"
                  >
                    <CreditCard class="w-4 h-4" />
                  </NuxtLink>

                  <!-- Edit Kartu Member (Nama, PT, Masa Berlaku, Total Terbayar) -->
                  <button
                    @click="openEditModal(m)"
                    title="Edit Kartu Member (Nama, PT, Masa Berlaku)"
                    class="p-1.5 rounded-lg hover:bg-amber-50 text-amber-600 border border-transparent hover:border-amber-200 transition cursor-pointer"
                  >
                    <Pencil class="w-4 h-4" />
                  </button>

                  <!-- Tandai Lunas -->
                  <button
                    v-if="effectiveStatus(m) !== 'lunas'"
                    @click="markLunas(m.id)"
                    title="Tandai Pembayaran Lunas"
                    class="p-1.5 rounded-lg hover:bg-emerald-50 text-emerald-600 border border-transparent hover:border-emerald-200 transition cursor-pointer"
                  >
                    <Check class="w-4 h-4" />
                  </button>

                  <!-- Hapus Member -->
                  <button
                    @click="deleteMember(m.id)"
                    title="Hapus Member"
                    class="p-1.5 rounded-lg hover:bg-red-50 text-red-500 border border-transparent hover:border-red-200 transition cursor-pointer"
                  >
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Form: Tambah Member Baru (Fixed Rate 150.000 / Bulan) -->
    <div
      v-if="isAddModalOpen"
      class="fixed inset-0 z-50 bg-slate-950/50 backdrop-blur-xs flex items-center justify-center p-4 font-sans"
    >
      <div class="bg-white rounded-2xl border border-slate-200 max-w-lg w-full p-6 shadow-xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <div>
            <h3 class="text-base font-bold text-slate-900 font-serif">Pendaftaran Kartu Member Baru</h3>
            <p class="text-xs text-slate-500">Tarif langganan fixed <strong>Rp 150.000 / bulan</strong>.</p>
          </div>
          <button @click="isAddModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="submitAddMember" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap Pemegang</label>
            <input
              v-model="addForm.nama"
              type="text"
              required
              placeholder="Contoh: Rakha Al-Rifad"
              class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:border-[#3b82f6] outline-none"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Perusahaan / PT / Instansi</label>
            <input
              v-model="addForm.perusahaan"
              type="text"
              required
              placeholder="Contoh: PT Equity Tower"
              class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:border-[#3b82f6] outline-none"
            />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Harga 1 Bulan (Fixed)</label>
              <input
                :value="formatRupiah(FIXED_MONTHLY_RATE)"
                type="text"
                disabled
                class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-600 outline-none font-mono font-bold cursor-not-allowed"
              />
              <span class="text-[10px] text-slate-400 mt-0.5 block">Tarif tetap Rp 150.000 / bln</span>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Durasi Berlangganan</label>
              <select
                v-model.number="addForm.durasi_bulan"
                @change="calculateAddPricing"
                class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 bg-white font-semibold focus:border-[#3b82f6] outline-none"
              >
                <option :value="1">1 Bulan (Rp 150.000)</option>
                <option :value="2">2 Bulan (Bayar Sekaligus: Rp 300.000)</option>
                <option :value="3">3 Bulan (1 Triwulan: Rp 450.000)</option>
                <option :value="6">6 Bulan (Semester: Rp 900.000)</option>
                <option :value="12">12 Bulan (1 Tahun: Rp 1.800.000)</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Total Terbayarkan (Rp)</label>
              <input
                v-model.number="addForm.total_terbayar"
                type="number"
                min="0"
                required
                class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:border-[#3b82f6] outline-none font-mono font-bold text-emerald-700"
              />
              <span class="text-[10px] text-slate-400 mt-0.5 block">
                Otomatis dihitung ({{ addForm.durasi_bulan }} × Rp 150.000)
              </span>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Status Pembayaran</label>
              <select
                v-model="addForm.status"
                class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 bg-white font-semibold focus:border-[#3b82f6] outline-none"
              >
                <option value="lunas">Lunas</option>
                <option value="belum">Belum Lunas</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Batas Masa Berlaku</label>
            <input
              v-model="addForm.expired_at"
              type="date"
              required
              class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:border-[#3b82f6] outline-none font-mono"
            />
            <span class="text-[10px] text-slate-400 mt-0.5 block">
              Otomatis diatur +{{ addForm.durasi_bulan }} bulan ke depan.
            </span>
          </div>

          <div class="pt-3 border-t border-slate-100 flex gap-2">
            <button
              type="submit"
              :disabled="isSubmitting"
              class="flex-1 py-2.5 px-4 bg-[#3b82f6] hover:bg-[#2563eb] text-white border border-blue-600 text-xs font-bold rounded-xl transition cursor-pointer disabled:opacity-60 shadow-xs active:scale-[0.98]"
            >
              {{ isSubmitting ? 'Menyimpan...' : 'Simpan Data Member' }}
            </button>
            <button
              type="button"
              @click="isAddModalOpen = false"
              class="py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition cursor-pointer"
            >
              Batal
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Form: Edit Member (Nama, PT, Masa Berlaku, Total Terbayar, Status) -->
    <div
      v-if="isEditModalOpen"
      class="fixed inset-0 z-50 bg-slate-950/50 backdrop-blur-xs flex items-center justify-center p-4 font-sans"
    >
      <div class="bg-white rounded-2xl border border-slate-200 max-w-lg w-full p-6 shadow-xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <div>
            <h3 class="text-base font-bold text-slate-900 font-serif">Edit Data Kartu Member</h3>
            <p class="text-xs text-slate-500 font-mono">ID Kartu: <strong class="text-[#3b82f6]">{{ editForm.kode }}</strong></p>
          </div>
          <button @click="isEditModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="submitEditMember" class="space-y-4">
          <!-- 1. Edit Nama Pemegang -->
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap Pemegang</label>
            <input
              v-model="editForm.nama"
              type="text"
              required
              class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:border-[#3b82f6] outline-none"
            />
          </div>

          <!-- 2. Edit Perusahaan / PT -->
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Perusahaan / PT / Instansi</label>
            <input
              v-model="editForm.perusahaan"
              type="text"
              required
              class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:border-[#3b82f6] outline-none"
            />
          </div>

          <!-- Pricing & Payment -->
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Harga 1 Bulan (Fixed)</label>
              <input
                :value="formatRupiah(FIXED_MONTHLY_RATE)"
                type="text"
                disabled
                class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-600 outline-none font-mono font-bold cursor-not-allowed"
              />
              <span class="text-[10px] text-slate-400 mt-0.5 block">Tarif tetap Rp 150.000 / bln</span>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Total Terbayarkan (Rp)</label>
              <input
                v-model.number="editForm.total_terbayar"
                type="number"
                min="0"
                required
                class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:border-[#3b82f6] outline-none font-mono font-bold text-emerald-700"
              />
              <span class="text-[10px] text-slate-400 mt-0.5 block">Dapat disesuaikan manual</span>
            </div>
          </div>

          <!-- Quick Extension Buttons (Bayar Langsung 2 Bulan, 1 Bulan, dll) -->
          <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 space-y-2">
            <span class="text-[11px] font-bold text-slate-700 block">
              Perpanjang Langganan (Bayar Sekaligus):
            </span>
            <div class="flex flex-wrap gap-2">
              <button
                type="button"
                @click="applyMultiMonthExtension(1)"
                class="px-3 py-1.5 rounded-lg text-xs font-bold bg-white border border-slate-300 hover:border-[#3b82f6] hover:text-[#3b82f6] transition cursor-pointer shadow-2xs"
              >
                +1 Bulan (Rp 150.000)
              </button>
              <button
                type="button"
                @click="applyMultiMonthExtension(2)"
                class="px-3 py-1.5 rounded-lg text-xs font-bold bg-blue-50 border border-blue-300 text-blue-700 hover:bg-blue-100 transition cursor-pointer shadow-2xs"
              >
                +2 Bulan (Bayar Langsung 2 Bulan: Rp 300.000)
              </button>
              <button
                type="button"
                @click="applyMultiMonthExtension(3)"
                class="px-3 py-1.5 rounded-lg text-xs font-bold bg-white border border-slate-300 hover:border-[#3b82f6] hover:text-[#3b82f6] transition cursor-pointer shadow-2xs"
              >
                +3 Bulan (Rp 450.000)
              </button>
              <button
                type="button"
                @click="applyMultiMonthExtension(6)"
                class="px-3 py-1.5 rounded-lg text-xs font-bold bg-white border border-slate-300 hover:border-[#3b82f6] hover:text-[#3b82f6] transition cursor-pointer shadow-2xs"
              >
                +6 Bulan (Rp 900.000)
              </button>
            </div>
          </div>

          <!-- 3. Edit Masa Berlaku & 4. Edit Status Kartu -->
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Masa Berlaku (Expired Date)</label>
              <input
                v-model="editForm.expired_at"
                type="date"
                required
                class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:border-[#3b82f6] outline-none font-mono"
              />
              <span class="text-[10px] text-slate-400 mt-0.5 block">Pilih tanggal bebas</span>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Status Kartu Member</label>
              <select
                v-model="editForm.status"
                class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 bg-white font-semibold focus:border-[#3b82f6] outline-none"
              >
                <option value="lunas">Lunas</option>
                <option value="belum">Belum Lunas</option>
                <option value="expired">Expired</option>
              </select>
            </div>
          </div>

          <div class="pt-3 border-t border-slate-100 flex gap-2">
            <button
              type="submit"
              :disabled="isSubmitting"
              class="flex-1 py-2.5 px-4 bg-[#3b82f6] hover:bg-[#2563eb] text-white border border-blue-600 text-xs font-bold rounded-xl transition cursor-pointer disabled:opacity-60 shadow-xs active:scale-[0.98]"
            >
              {{ isSubmitting ? 'Menyimpan...' : 'Simpan Perubahan Member' }}
            </button>
            <button
              type="button"
              @click="isEditModalOpen = false"
              class="py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition cursor-pointer"
            >
              Batal
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { UserPlus, CreditCard, Pencil, Check, Trash2, X, Search } from 'lucide-vue-next'
import { formatRupiah, formatDate } from '~/utils/formatters'

definePageMeta({
  layout: 'petugas',
  middleware: 'auth',
})

const FIXED_MONTHLY_RATE = 150000

const api = useApi()
const { askConfirm, askAlert } = useConfirm()

const members = ref<any[]>([])
const isAddModalOpen = ref(false)
const isEditModalOpen = ref(false)
const isSubmitting = ref(false)

const searchQuery = ref('')
const statusFilter = ref<'all' | 'lunas' | 'belum' | 'expired'>('all')

const addForm = reactive({
  nama: '',
  perusahaan: '',
  harga: FIXED_MONTHLY_RATE,
  durasi_bulan: 1,
  total_terbayar: FIXED_MONTHLY_RATE,
  status: 'lunas',
  expired_at: '',
})

const editForm = reactive({
  id: 0,
  kode: '',
  nama: '',
  perusahaan: '',
  harga: FIXED_MONTHLY_RATE,
  total_terbayar: 0,
  status: 'lunas',
  expired_at: '',
})

const effectiveStatus = (m: any): 'lunas' | 'belum' | 'expired' => {
  if (m.computed_status === 'expired' || m.status === 'expired') return 'expired'
  if (m.expired_at) {
    const expDate = new Date(m.expired_at)
    const today = new Date()
    today.setHours(0, 0, 0, 0)
    if (expDate < today) return 'expired'
  }
  return m.status === 'lunas' ? 'lunas' : 'belum'
}

const countByStatus = (status: 'lunas' | 'belum' | 'expired') => {
  return members.value.filter(m => effectiveStatus(m) === status).length
}

const filteredMembers = computed(() => {
  return members.value.filter(m => {
    // 1. Filter status
    if (statusFilter.value !== 'all') {
      if (effectiveStatus(m) !== statusFilter.value) return false
    }

    // 2. Search query
    if (searchQuery.value.trim()) {
      const q = searchQuery.value.toLowerCase().trim()
      const matchKode = m.kode?.toLowerCase().includes(q)
      const matchNama = m.nama?.toLowerCase().includes(q)
      const matchPT = m.perusahaan?.toLowerCase().includes(q)
      if (!matchKode && !matchNama && !matchPT) return false
    }

    return true
  })
})

const fetchMembers = async () => {
  try {
    const res = await api.get('/members')
    members.value = Array.isArray(res.data) ? res.data : (res.data?.data || [])
  } catch (err) {
    console.error('Gagal mengambil data member:', err)
  }
}

const calculateAddPricing = () => {
  const durasi = Number(addForm.durasi_bulan) || 1
  addForm.harga = FIXED_MONTHLY_RATE
  addForm.total_terbayar = FIXED_MONTHLY_RATE * durasi

  // Otomatis atur tanggal kadaluarsa dari hari ini
  const d = new Date()
  d.setMonth(d.getMonth() + durasi)
  addForm.expired_at = d.toISOString().split('T')[0]
}

const openAddModal = () => {
  addForm.nama = ''
  addForm.perusahaan = ''
  addForm.harga = FIXED_MONTHLY_RATE
  addForm.durasi_bulan = 1
  addForm.status = 'lunas'
  calculateAddPricing()
  isAddModalOpen.value = true
}

const submitAddMember = async () => {
  isSubmitting.value = true
  try {
    const res = await api.post('/members', addForm)
    if (res.data?.success) {
      isAddModalOpen.value = false
      await fetchMembers()
    }
  } catch (err: any) {
    await askAlert(err.response?.data?.message || 'Gagal menyimpan data member.', 'Gagal Menyimpan', 'danger')
  } finally {
    isSubmitting.value = false
  }
}

const openEditModal = (m: any) => {
  editForm.id = m.id
  editForm.kode = m.kode
  editForm.nama = m.nama
  editForm.perusahaan = m.perusahaan
  editForm.harga = FIXED_MONTHLY_RATE
  editForm.total_terbayar = Number(m.total_terbayar) || 0
  editForm.status = effectiveStatus(m)
  editForm.expired_at = m.expired_at ? m.expired_at.split('T')[0] : ''
  isEditModalOpen.value = true
}

const applyMultiMonthExtension = (months: number) => {
  // Tambahkan durasi ke tanggal expired_at yang ada
  let baseDate = editForm.expired_at ? new Date(editForm.expired_at) : new Date()
  if (isNaN(baseDate.getTime()) || baseDate < new Date()) {
    baseDate = new Date()
  }
  baseDate.setMonth(baseDate.getMonth() + months)
  editForm.expired_at = baseDate.toISOString().split('T')[0]

  // Tambahkan total terbayar dengan (Rp 150.000 * bulan yang ditambah)
  const addition = FIXED_MONTHLY_RATE * months
  editForm.total_terbayar = (Number(editForm.total_terbayar) || 0) + addition
  editForm.status = 'lunas'
}

const submitEditMember = async () => {
  isSubmitting.value = true
  try {
    const res = await api.put(`/members/${editForm.id}`, editForm)
    if (res.data?.success) {
      isEditModalOpen.value = false
      await fetchMembers()
    }
  } catch (err: any) {
    await askAlert(err.response?.data?.message || 'Gagal memperbarui data member.', 'Gagal Memperbarui', 'danger')
  } finally {
    isSubmitting.value = false
  }
}

const markLunas = async (id: number) => {
  try {
    await api.put(`/members/${id}/lunas`)
    await fetchMembers()
  } catch (err: any) {
    await askAlert(err.response?.data?.message || 'Gagal mengubah status member.', 'Gagal', 'danger')
  }
}

const deleteMember = async (id: number) => {
  const ok = await askConfirm({
    title: 'Hapus Kartu Member',
    message: 'Apakah Anda yakin ingin menghapus kartu member langganan ini? QR Code dan seluruh sesi parkir terkait akan dinonaktifkan.',
    type: 'danger',
    confirmText: 'Ya, Hapus Member',
    cancelText: 'Batal',
  })
  if (!ok) return

  try {
    await api.delete(`/members/${id}`)
    await fetchMembers()
  } catch (err: any) {
    await askAlert(err.response?.data?.message || 'Gagal menghapus member.', 'Gagal Menghapus', 'danger')
  }
}

onMounted(() => {
  fetchMembers()
})
</script>
