<template>
  <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 sm:p-6 space-y-5 select-none font-sans">
    <!-- Top Header: Title, Subtitle, Date Picker & View Filters -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-slate-100">
      <div>
        <div class="flex items-center gap-2">
          <h2 class="text-base sm:text-lg font-bold text-slate-900 font-serif tracking-tight">
            Arus Kendaraan & Transaksi Harian
          </h2>
          <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-blue-50 text-[#3b82f6] border border-blue-200">
            Dribbble Analytic
          </span>
        </div>
        <p class="text-xs text-slate-500 mt-0.5">
          Grafik arus lalu lintas per jam serta total uang transaksi masuk/keluar pada tanggal yang dipilih.
        </p>
      </div>

      <div class="flex flex-wrap items-center gap-2.5">
        <!-- Date Picker for Specific Date Inspection -->
        <div class="flex items-center gap-2 bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200">
          <Calendar class="w-3.5 h-3.5 text-[#3b82f6] shrink-0" />
          <span class="text-xs font-semibold text-slate-600 hidden sm:inline">Tanggal:</span>
          <input
            v-model="selectedDate"
            type="date"
            @change="fetchChartData"
            class="text-xs font-mono font-bold bg-transparent text-slate-800 outline-none cursor-pointer"
          />
        </div>

        <!-- Metric Mode Filter -->
        <div class="inline-flex rounded-xl bg-slate-100 p-1 text-xs">
          <button
            type="button"
            @click="chartMode = 'all'"
            class="px-2.5 py-1 rounded-lg font-bold transition cursor-pointer"
            :class="chartMode === 'all' ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
          >
            Semua
          </button>
          <button
            type="button"
            @click="chartMode = 'traffic'"
            class="px-2.5 py-1 rounded-lg font-bold transition cursor-pointer"
            :class="chartMode === 'traffic' ? 'bg-[#3b82f6] text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
          >
            Arus (Unit)
          </button>
          <button
            type="button"
            @click="chartMode = 'revenue'"
            class="px-2.5 py-1 rounded-lg font-bold transition cursor-pointer"
            :class="chartMode === 'revenue' ? 'bg-emerald-600 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
          >
            Transaksi (Rp)
          </button>
        </div>
      </div>
    </div>

    <!-- Mini KPI Strip for Selected Date -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
      <!-- 1. Total Masuk -->
      <div class="p-3.5 rounded-xl bg-blue-50/50 border border-blue-100">
        <span class="text-[10px] font-bold text-[#3b82f6] uppercase tracking-wider block">
          Kendaraan Masuk
        </span>
        <div class="text-2xl font-black font-mono text-slate-900 mt-1">
          {{ chartData.total_masuk || 0 }} <span class="text-xs font-normal text-slate-500">unit</span>
        </div>
      </div>

      <!-- 2. Total Keluar -->
      <div class="p-3.5 rounded-xl bg-amber-50/50 border border-amber-100">
        <span class="text-[10px] font-bold text-amber-700 uppercase tracking-wider block">
          Kendaraan Keluar
        </span>
        <div class="text-2xl font-black font-mono text-slate-900 mt-1">
          {{ chartData.total_keluar || 0 }} <span class="text-xs font-normal text-slate-500">unit</span>
        </div>
      </div>

      <!-- 3. Total Omset Transaksi Uang -->
      <div class="p-3.5 rounded-xl bg-emerald-50/50 border border-emerald-100">
        <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider block">
          Total Transaksi (Duit)
        </span>
        <div class="text-xl sm:text-2xl font-black font-mono text-emerald-800 mt-1 truncate">
          {{ formatRupiah(chartData.total_pendapatan || 0) }}
        </div>
      </div>

      <!-- 4. Jam Tersibuk -->
      <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">
          Jam Tersibuk
        </span>
        <div class="text-sm sm:text-base font-black font-mono text-slate-800 mt-1.5 flex items-center gap-1.5">
          <Clock class="w-3.5 h-3.5 text-[#f59e0b] shrink-0" />
          <span class="truncate">{{ chartData.peak_hour || '09:00 - 10:00' }}</span>
        </div>
      </div>
    </div>

    <!-- Interactive SVG Chart Area -->
    <div class="relative w-full pt-2">
      <!-- Loading Overlay -->
      <div
        v-if="isLoading"
        class="absolute inset-0 bg-white/70 backdrop-blur-2xs flex items-center justify-center z-20 rounded-xl"
      >
        <Loader2 class="w-6 h-6 animate-spin text-[#3b82f6]" />
      </div>

      <!-- Legend Strip -->
      <div class="flex items-center justify-between text-xs text-slate-500 mb-3 px-1">
        <div class="flex items-center gap-4">
          <div v-if="chartMode !== 'revenue'" class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded bg-[#3b82f6]"></span>
            <span class="font-medium text-[11px] text-slate-700">Kendaraan Masuk</span>
          </div>
          <div v-if="chartMode !== 'revenue'" class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded bg-[#f59e0b]"></span>
            <span class="font-medium text-[11px] text-slate-700">Kendaraan Keluar</span>
          </div>
          <div v-if="chartMode !== 'traffic'" class="flex items-center gap-1.5">
            <span class="w-3 h-1.5 rounded-full bg-emerald-600"></span>
            <span class="font-medium text-[11px] text-emerald-800 font-bold">Total Transaksi Uang (Rp)</span>
          </div>
        </div>

        <span class="text-[11px] text-slate-400 font-mono hidden md:inline">
          Rentang: 24 Jam (00:00 - 23:00)
        </span>
      </div>

      <!-- Chart Canvas (SVG Clean Dribbble-style) -->
      <div class="w-full overflow-x-auto">
        <div class="min-w-[620px] relative">
          <!-- Floating Hover Tooltip -->
          <div
            v-if="hoveredBar"
            class="absolute top-0 right-4 z-30 bg-[#1e293b] text-white text-xs px-3.5 py-2.5 rounded-xl shadow-lg border border-slate-700 pointer-events-none transition-all space-y-1"
          >
            <div class="font-mono font-bold text-amber-400 text-[11px]">
              Pukul {{ hoveredBar.hour }} - {{ nextHour(hoveredBar.hour_num) }}
            </div>
            <div class="flex items-center justify-between gap-3 text-[11px]">
              <span class="text-slate-300">Kendaraan Masuk:</span>
              <strong class="font-mono text-[#60a5fa]">{{ hoveredBar.masuk }} unit</strong>
            </div>
            <div class="flex items-center justify-between gap-3 text-[11px]">
              <span class="text-slate-300">Kendaraan Keluar:</span>
              <strong class="font-mono text-[#fbbf24]">{{ hoveredBar.keluar }} unit</strong>
            </div>
            <div class="flex items-center justify-between gap-3 text-[11px] pt-1 border-t border-slate-700">
              <span class="text-slate-300">Total Transaksi:</span>
              <strong class="font-mono text-emerald-400">{{ formatRupiah(hoveredBar.pendapatan) }}</strong>
            </div>
          </div>

          <svg
            viewBox="0 0 960 260"
            class="w-full h-56 sm:h-64 overflow-visible"
            preserveAspectRatio="none"
          >
            <!-- Background Grid Lines -->
            <line x1="40" y1="30" x2="940" y2="30" stroke="#f1f5f9" stroke-width="1" stroke-dasharray="3 3" />
            <line x1="40" y1="80" x2="940" y2="80" stroke="#f1f5f9" stroke-width="1" stroke-dasharray="3 3" />
            <line x1="40" y1="130" x2="940" y2="130" stroke="#f1f5f9" stroke-width="1" stroke-dasharray="3 3" />
            <line x1="40" y1="180" x2="940" y2="180" stroke="#f1f5f9" stroke-width="1" stroke-dasharray="3 3" />
            <!-- Baseline -->
            <line x1="40" y1="210" x2="940" y2="210" stroke="#cbd5e1" stroke-width="1.5" />

            <!-- Area Path for Revenue (Emerald Gradient Spline) -->
            <path
              v-if="chartMode !== 'traffic' && revenueAreaPath"
              :d="revenueAreaPath"
              fill="rgba(16, 185, 129, 0.08)"
            />

            <!-- Line Path for Revenue -->
            <path
              v-if="chartMode !== 'traffic' && revenueLinePath"
              :d="revenueLinePath"
              fill="none"
              stroke="#059669"
              stroke-width="2.5"
              stroke-linecap="round"
              stroke-linejoin="round"
            />

            <!-- Revenue Data Points (Circles) -->
            <g v-if="chartMode !== 'traffic'">
              <circle
                v-for="(point, idx) in revenuePoints"
                :key="'pt-' + idx"
                :cx="point.x"
                :cy="point.y"
                r="3.5"
                fill="#ffffff"
                stroke="#059669"
                stroke-width="2"
                class="transition-all hover:scale-150 cursor-pointer"
              />
            </g>

            <!-- Hourly Traffic Bars (Masuk & Keluar side-by-side) -->
            <g v-if="chartMode !== 'revenue'">
              <g
                v-for="(h, idx) in hourlyData"
                :key="'bar-' + idx"
                @mouseenter="hoveredBar = h"
                @mouseleave="hoveredBar = null"
                class="cursor-pointer group"
              >
                <!-- Transparent Hover Hit Area -->
                <rect
                  :x="barX(idx) - 8"
                  y="20"
                  width="44"
                  height="190"
                  fill="transparent"
                  class="group-hover:fill-slate-100/40 transition-colors"
                />

                <!-- Bar Masuk (Blue #3B82F6) -->
                <rect
                  :x="barX(idx)"
                  :y="barY(h.masuk)"
                  width="12"
                  :height="barHeight(h.masuk)"
                  rx="3"
                  fill="#3b82f6"
                  class="transition-all group-hover:brightness-110"
                />

                <!-- Bar Keluar (Amber #F59E0B) -->
                <rect
                  :x="barX(idx) + 14"
                  :y="barY(h.keluar)"
                  width="12"
                  :height="barHeight(h.keluar)"
                  rx="3"
                  fill="#f59e0b"
                  class="transition-all group-hover:brightness-110"
                />
              </g>
            </g>

            <!-- X-Axis Labels (Every 3 hours: 00:00, 03:00, 06:00, 09:00, 12:00, 15:00, 18:00, 21:00) -->
            <g>
              <text
                v-for="tick in xTicks"
                :key="'tick-' + tick.hour"
                :x="tick.x"
                y="230"
                text-anchor="middle"
                font-family="monospace"
                font-size="10"
                fill="#64748b"
                font-weight="bold"
              >
                {{ tick.hour }}
              </text>
            </g>
          </svg>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Calendar, Clock, Loader2 } from 'lucide-vue-next'
import { formatRupiah } from '~/utils/formatters'

const api = useApi()

const selectedDate = ref(new Date().toISOString().split('T')[0])
const chartMode = ref<'all' | 'traffic' | 'revenue'>('all')
const isLoading = ref(false)
const hoveredBar = ref<any>(null)

const chartData = ref<any>({
  date: selectedDate.value,
  total_masuk: 0,
  total_keluar: 0,
  total_pendapatan: 0,
  pendapatan_non_member: 0,
  pendapatan_member: 0,
  peak_hour: '-',
  hourly: [],
})

const hourlyData = computed(() => {
  return chartData.value?.hourly || []
})

const maxTrafficValue = computed(() => {
  let max = 5
  hourlyData.value.forEach((h: any) => {
    if (h.masuk > max) max = h.masuk
    if (h.keluar > max) max = h.keluar
  })
  return Math.ceil(max * 1.25)
})

const maxRevenueValue = computed(() => {
  let max = 10000
  hourlyData.value.forEach((h: any) => {
    if (h.pendapatan > max) max = h.pendapatan
  })
  return Math.ceil(max * 1.25)
})

const barX = (index: number) => {
  // 24 slots across 40px to 920px -> slot width ~36.6px
  return 55 + index * 36.5
}

const barHeight = (val: number) => {
  if (!val || val <= 0) return 0
  const max = maxTrafficValue.value
  return Math.min(170, Math.max(4, (val / max) * 170))
}

const barY = (val: number) => {
  const h = barHeight(val)
  return 210 - h
}

// Revenue Line Points & Smooth Path
const revenuePoints = computed(() => {
  const max = maxRevenueValue.value
  return hourlyData.value.map((h: any, idx: number) => {
    const x = barX(idx) + 13
    const val = Number(h.pendapatan) || 0
    const yRatio = val / max
    const y = 205 - yRatio * 155
    return { x, y, val, hour: h.hour }
  })
})

const revenueLinePath = computed(() => {
  const pts = revenuePoints.value
  if (!pts || pts.length === 0) return ''
  let d = `M ${pts[0].x} ${pts[0].y}`
  for (let i = 1; i < pts.length; i++) {
    const prev = pts[i - 1]
    const cur = pts[i]
    const cpX1 = prev.x + (cur.x - prev.x) / 2
    const cpY1 = prev.y
    const cpX2 = cpX1
    const cpY2 = cur.y
    d += ` C ${cpX1} ${cpY1}, ${cpX2} ${cpY2}, ${cur.x} ${cur.y}`
  }
  return d
})

const revenueAreaPath = computed(() => {
  const line = revenueLinePath.value
  if (!line || revenuePoints.value.length === 0) return ''
  const first = revenuePoints.value[0]
  const last = revenuePoints.value[revenuePoints.value.length - 1]
  return `${line} L ${last.x} 210 L ${first.x} 210 Z`
})

const xTicks = computed(() => {
  const ticks = []
  const step = 3
  for (let i = 0; i < 24; i += step) {
    ticks.push({
      hour: sprintfHour(i),
      x: barX(i) + 13,
    })
  }
  return ticks
})

const sprintfHour = (h: number) => {
  return `${String(h).padStart(2, '0')}:00`
}

const nextHour = (hNum: number) => {
  const next = (Number(hNum) + 1) % 24
  return `${String(next).padStart(2, '0')}:00`
}

const fetchChartData = async () => {
  isLoading.value = true
  try {
    const res = await api.get(`/rekap/chart?date=${selectedDate.value}`)
    if (res.data?.success) {
      chartData.value = res.data.data
    }
  } catch (err) {
    console.error('Gagal mengambil data chart rekap:', err)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchChartData()
})
</script>
