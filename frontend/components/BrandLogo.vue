<template>
  <div class="inline-flex flex-col items-start select-none">
    <div class="inline-flex items-stretch font-bold tracking-tight rounded overflow-hidden shadow-xs">
      <!-- Left Cyan Box with 3 Orange Bars and 'secure' / 'parkir' text -->
      <div class="bg-[#0099ff] text-white px-3 py-1.5 flex items-center gap-2">
        <!-- 3 Horizontal Orange Capsule Pills -->
        <div class="flex flex-col gap-0.5 justify-center">
          <span class="w-3.5 h-1 bg-[#ff9900] rounded-full inline-block"></span>
          <span class="w-3.5 h-1 bg-[#ff9900] rounded-full inline-block"></span>
          <span class="w-3.5 h-1 bg-[#ff9900] rounded-full inline-block"></span>
        </div>
        <span class="text-base font-extrabold tracking-tight lowercase">secure</span>
      </div>

      <!-- Right Deep Navy Box with bold capital 'P' -->
      <div class="bg-[#001a4e] text-white px-3 py-1.5 flex items-center justify-center font-black text-base border-l border-white/20">
        P
      </div>
    </div>
    
    <span v-if="showSubtitle" class="text-[11px] font-bold text-slate-800 tracking-wider uppercase mt-1 pl-0.5">
      {{ subtitle || 'Sistem Parkir Digital' }}
    </span>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  subtitle?: string
  showSubtitle?: boolean
}>()
</script>
