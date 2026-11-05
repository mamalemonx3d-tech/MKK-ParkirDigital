<template>
  <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 font-sans select-none">
    <!-- Top Header (Inspired directly by user reference design) -->
    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 pb-4">
      <!-- Left: Title, Big Metric Number & Indicator -->
      <div>
        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">
          Arus Kendaraan & Transaksi
        </h3>
        <div class="flex items-baseline gap-2 mt-1">
          <span class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight font-sans">
            {{ (chartData.total_masuk || 0) + (chartData.total_keluar || 0) }}
          </span>
          <span class="text-xs font-semibold text-slate-400">total unit lalu lintas</span>
        </div>
        <div class="flex items-center gap-1.5 text-xs font-bold text-emerald-600 mt-1">
          <ArrowUpRight class="w-4 h-4 shrink-0" />
          <span>{{ formatRupiah(chartData.total_pendapatan || 0) }} total transaksi</span>
          <span class="text-slate-400 font-normal ml-1">
            ({{ chartData.total_masuk || 0 }} masuk • {{ chartData.total_keluar || 0 }} keluar)
          </span>
        </div>
      </div>

      <!-- Right: Clean Dropdown & Legend -->
      <div class="flex flex-col sm:items-end gap-2.5">
        <!-- Date Selector Dropdown Pill (mimicking the "30 days ▾" dropdown in the design) -->
        <div class="relative inline-flex items-center">
          <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl border border-slate-200 hover:border-slate-300 bg-white text-xs font-medium text-slate-700 shadow-2xs transition">
            <Calendar class="w-3.5 h-3.5 text-slate-400 shrink-0" />
            <input
              type="date"
              v-model="selectedDate"
              @change="fetchChartData"
              class="text-xs font-semibold text-slate-700 bg-transparent outline-none cursor-pointer"
            />
          </div>
        </div>

        <!-- Legend (Identical to reference: Blue square & Gray square) -->
        <div class="flex items-center gap-4 text-xs font-medium">
          <div class="flex items-center gap-1.5">
            <span class="w-2.5 h-2.5 rounded-xs bg-[#2563eb]"></span>
            <span class="text-slate-800 font-semibold">Kendaraan Masuk</span>
          </div>
          <div class="flex items-center gap-1.5">
            <span class="w-2.5 h-2.5 rounded-xs bg-[#94a3b8]"></span>
            <span class="text-slate-600">Kendaraan Keluar</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Chart Canvas Area -->
    <div class="relative w-full pt-2">
      <!-- Loading State -->
      <div
        v-if="isLoading"
        class="absolute inset-0 bg-white/70 backdrop-blur-2xs flex items-center justify-center z-20 rounded-xl"
      >
        <Loader2 class="w-6 h-6 animate-spin text-[#2563eb]" />
      </div>

      <!-- Floating Interactive Tooltip -->
      <div
        v-if="hoveredIndex !== null && currentHoverData"
        class="absolute top-1 right-2 z-30 bg-[#1e293b] text-white text-xs px-3.5 py-2.5 rounded-xl shadow-lg border border-slate-700 pointer-events-none transition-all space-y-1"
      >
        <div class="font-mono font-bold text-amber-400 text-[11px]">
          Pukul {{ currentHoverData.hour }} - {{ nextHour(currentHoverData.hour_num) }}
        </div>
        <div class="flex items-center justify-between gap-4 text-[11px]">
          <span class="text-slate-300 flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-xs bg-[#3b82f6]"></span>
            Masuk:
          </span>
          <strong class="font-mono text-[#60a5fa]">{{ currentHoverData.masuk }} unit</strong>
        </div>
        <div class="flex items-center justify-between gap-4 text-[11px]">
          <span class="text-slate-300 flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-xs bg-[#94a3b8]"></span>
            Keluar:
          </span>
          <strong class="font-mono text-slate-200">{{ currentHoverData.keluar }} unit</strong>
        </div>
        <div class="flex items-center justify-between gap-4 text-[11px] pt-1 border-t border-slate-700">
          <span class="text-slate-300">Transaksi:</span>
          <strong class="font-mono text-emerald-400">{{ formatRupiah(currentHoverData.pendapatan) }}</strong>
        </div>
      </div>

      <!-- SVG Line Chart (Clean, Minimalist with Landai Smooth Spline Lines) -->
      <div class="w-full overflow-x-auto">
        <div class="min-w-[620px] relative">
          <svg
            viewBox="0 0 920 230"
            class="w-full h-56 sm:h-64 overflow-visible"
            preserveAspectRatio="none"
            @mouseleave="hoveredIndex = null"
          >
            <defs>
              <!-- Soft Subtle Gradient for Masuk Area -->
              <linearGradient id="masukGradient" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#2563eb" stop-opacity="0.08" />
                <stop offset="100%" stop-color="#2563eb" stop-opacity="0" />
              </linearGradient>
            </defs>

            <!-- Horizontal Grid Lines & Y-Axis Labels (Identical to reference: 15, 10, 5, 0) -->
            <g v-for="(level, idx) in yLevels" :key="'grid-' + idx">
              <!-- Y-Axis Number on Left -->
              <text
                x="36"
                :y="level.y + 4"
                text-anchor="end"
                font-family="system-ui, -apple-system, sans-serif"
                font-size="11"
                fill="#94a3b8"
                font-weight="500"
              >
                {{ level.value }}
              </text>
              <!-- Faint Horizontal Line -->
              <line
                x1="45"
                :y1="level.y"
                x2="910"
                :y2="level.y"
                stroke="#f1f5f9"
                stroke-width="1.2"
              />
            </g>

            <!-- Baseline X-Axis Line -->
            <line
              x1="45"
              y1="190"
              x2="910"
              y2="190"
              stroke="#e2e8f0"
              stroke-width="1.2"
            />

            <!-- Soft Landai Area Fill for Blue (Masuk) -->
            <path
              v-if="masukAreaPath"
              :d="masukAreaPath"
              fill="url(#masukGradient)"
            />

            <!-- 1. Secondary Line: Kendaraan Keluar (Gray #94A3B8, Smooth Landai Spline) -->
            <path
              v-if="keluarLinePath"
              :d="keluarLinePath"
              fill="none"
              stroke="#94a3b8"
              stroke-width="2.5"
              stroke-linecap="round"
              stroke-linejoin="round"
            />

            <!-- 2. Primary Line: Kendaraan Masuk (Blue #2563EB, Smooth Landai Spline) -->
            <path
              v-if="masukLinePath"
              :d="masukLinePath"
              fill="none"
              stroke="#2563eb"
              stroke-width="2.5"
              stroke-linecap="round"
              stroke-linejoin="round"
            />

            <!-- Hover Vertical Guideline & Highlight Dots -->
            <g v-if="hoveredIndex !== null && hoverCoords">
              <!-- Vertical Crosshair Guideline -->
              <line
                :x1="hoverCoords.x"
                y1="30"
                :x2="hoverCoords.x"
                y2="190"
                stroke="#cbd5e1"
                stroke-width="1"
                stroke-dasharray="3 3"
              />
              <!-- Circle Dot on Keluar (Gray) -->
              <circle
                :cx="hoverCoords.x"
                :cy="hoverCoords.yKeluar"
                r="4.5"
                fill="#94a3b8"
                stroke="#ffffff"
                stroke-width="2"
              />
              <!-- Circle Dot on Masuk (Blue) -->
              <circle
                :cx="hoverCoords.x"
                :cy="hoverCoords.yMasuk"
                r="4.5"
                fill="#2563eb"
                stroke="#ffffff"
                stroke-width="2"
              />
            </g>

            <!-- Transparent Hover Hit Areas (Columns across each hour) -->
            <rect
              v-for="(h, idx) in hourlyData"
              :key="'hit-' + idx"
              :x="getX(idx) - (stepX / 2)"
              y="20"
              :width="stepX"
              height="180"
              fill="transparent"
              class="cursor-pointer"
              @mouseenter="hoveredIndex = idx"
            />

            <!-- X-Axis Labels (Bottom time labels: 00:00, 04:00, 08:00, 12:00, 16:00, 20:00, 23:00) -->
            <g>
              <text
                v-for="tick in xTicks"
                :key="'tick-' + tick.hour"
                :x="tick.x"
                y="212"
                text-anchor="middle"
                font-family="system-ui, -apple-system, sans-serif"
                font-size="11"
                fill="#94a3b8"
                font-weight="500"
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
import { Calendar, ArrowUpRight, Loader2 } from 'lucide-vue-next'
import { formatRupiah } from '~/utils/formatters'

const api = useApi()

const selectedDate = ref(new Date().toISOString().split('T')[0])
const isLoading = ref(false)
const hoveredIndex = ref<number | null>(null)

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

// Current hovered item
const currentHoverData = computed(() => {
  if (hoveredIndex.value === null || !hourlyData.value[hoveredIndex.value]) return null
  return hourlyData.value[hoveredIndex.value]
})

// Coordinate constants
const leftX = 55
const rightX = 900
const stepX = (rightX - leftX) / 23 // ~36.74px per hour
const baselineY = 190
const topY = 30
const usableHeight = baselineY - topY // 160px

const getX = (index: number) => {
  return leftX + index * stepX
}

// Compute dynamic maximum scale rounded nicely (e.g. 5, 10, 15, 20, etc.)
const maxScale = computed(() => {
  let max = 0
  hourlyData.value.forEach((h: any) => {
    if (h.masuk > max) max = h.masuk
    if (h.keluar > max) max = h.keluar
  })
  if (max <= 5) return 5
  if (max <= 10) return 10
  if (max <= 15) return 15
  if (max <= 20) return 20
  if (max <= 30) return 30
  if (max <= 50) return 50
  return Math.ceil(max / 10) * 10
})

// Y Levels for grid and labels (4 horizontal lines just like the reference photo: 15, 10, 5, 0)
const yLevels = computed(() => {
  const max = maxScale.value
  return [
    { value: max, y: topY },
    { value: Math.round((max * 2) / 3), y: topY + usableHeight * (1 / 3) },
    { value: Math.round(max / 3), y: topY + usableHeight * (2 / 3) },
    { value: 0, y: baselineY },
  ]
})

const getY = (val: number) => {
  const safeVal = Math.max(0, Number(val) || 0)
  const ratio = Math.min(1, safeVal / maxScale.value)
  return baselineY - ratio * usableHeight
}

// Data Points for Masuk
const masukPoints = computed(() => {
  return hourlyData.value.map((h: any, idx: number) => ({
    x: getX(idx),
    y: getY(h.masuk),
    val: h.masuk,
  }))
})

// Data Points for Keluar
const keluarPoints = computed(() => {
  return hourlyData.value.map((h: any, idx: number) => ({
    x: getX(idx),
    y: getY(h.keluar),
    val: h.keluar,
  }))
})

// Catmull-Rom to Cubic Bézier Spline generator for smooth "landai-landai" curves
const getSmoothSplinePath = (pts: { x: number; y: number }[], tension = 0.35) => {
  if (!pts || pts.length === 0) return ''
  if (pts.length === 1) return `M ${pts[0].x.toFixed(1)} ${pts[0].y.toFixed(1)}`

  let d = `M ${pts[0].x.toFixed(1)} ${pts[0].y.toFixed(1)}`
  const n = pts.length

  for (let i = 0; i < n - 1; i++) {
    const p0 = i > 0 ? pts[i - 1] : pts[i]
    const p1 = pts[i]
    const p2 = pts[i + 1]
    const p3 = i < n - 2 ? pts[i + 2] : p2

    let cp1x = p1.x + (p2.x - p0.x) * tension
    let cp1y = p1.y + (p2.y - p0.y) * tension

    let cp2x = p2.x - (p3.x - p1.x) * tension
    let cp2y = p2.y - (p3.y - p1.y) * tension

    // Clamp control points to baseline so curve does not dip below zero
    if (p1.y >= baselineY && p2.y >= baselineY) {
      cp1y = baselineY
      cp2y = baselineY
    }

    d += ` C ${cp1x.toFixed(1)} ${cp1y.toFixed(1)}, ${cp2x.toFixed(1)} ${cp2y.toFixed(1)}, ${p2.x.toFixed(1)} ${p2.y.toFixed(1)}`
  }
  return d
}

// Smooth Spline Lines
const masukLinePath = computed(() => getSmoothSplinePath(masukPoints.value))
const keluarLinePath = computed(() => getSmoothSplinePath(keluarPoints.value))

// Soft Area Fill for Masuk
const masukAreaPath = computed(() => {
  const line = masukLinePath.value
  const pts = masukPoints.value
  if (!line || pts.length === 0) return ''
  const first = pts[0]
  const last = pts[pts.length - 1]
  return `${line} L ${last.x.toFixed(1)} ${baselineY} L ${first.x.toFixed(1)} ${baselineY} Z`
})

// Coordinates for hover indicators
const hoverCoords = computed(() => {
  if (hoveredIndex.value === null) return null
  const idx = hoveredIndex.value
  const pMasuk = masukPoints.value[idx]
  const pKeluar = keluarPoints.value[idx]
  if (!pMasuk || !pKeluar) return null
  return {
    x: pMasuk.x,
    yMasuk: pMasuk.y,
    yKeluar: pKeluar.y,
  }
})

// X-Axis time ticks: 00:00, 04:00, 08:00, 12:00, 16:00, 20:00, 23:00
const xTicks = computed(() => {
  const hours = [0, 4, 8, 12, 16, 20, 23]
  return hours.map((h) => ({
    hour: `${String(h).padStart(2, '0')}:00`,
    x: getX(h),
  }))
})

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
