<template>
  <div class="health-gauge" ref="chartRef" style="width: 100%; height: 220px;"></div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'
import * as echarts from 'echarts'

const props = defineProps({
  score: { type: Number, default: 0 },
  size: { type: String, default: '220px' }
})

const chartRef = ref(null)
let chartInstance = null

function initChart() {
  if (!chartRef.value) return
  if (chartInstance) chartInstance.dispose()

  chartInstance = echarts.init(chartRef.value)

  const score = Math.min(100, Math.max(0, props.score || 0))

  const option = {
    series: [
      {
        type: 'gauge',
        startAngle: 210,
        endAngle: -30,
        center: ['50%', '55%'],
        radius: '90%',
        min: 0,
        max: 100,
        splitNumber: 10,
        axisLine: {
          show: true,
          lineStyle: {
            width: 16,
            color: [
              [0.3, '#ef4444'],
              [0.6, '#f59e0b'],
              [0.8, '#22c55e'],
              [1, '#1a8870']
            ]
          }
        },
        pointer: {
          icon: 'path://M12.8,0.7l12,40.1H0.7L12.8,0.7z',
          length: '60%',
          width: 8,
          offsetCenter: [0, '-10%'],
          itemStyle: {
            color: 'auto'
          }
        },
        axisTick: {
          length: 10,
          lineStyle: {
            color: 'auto',
            width: 2
          }
        },
        splitLine: {
          length: 24,
          lineStyle: {
            color: 'auto',
            width: 4
          }
        },
        axisLabel: {
          color: '#94a3b8',
          fontSize: 10,
          distance: 18,
          formatter: '{value}'
        },
        title: {
          offsetCenter: [0, '70%'],
          fontSize: 14,
          color: '#64748b',
          fontFamily: 'Tajawal'
        },
        detail: {
          valueAnimation: true,
          formatter: '{value}%',
          color: '#1e293b',
          fontSize: 28,
          fontWeight: 'bold',
          offsetCenter: [0, '42%'],
          fontFamily: 'Tajawal'
        },
        data: [{ value: score, name: 'المعدل الصحي' }]
      }
    ]
  }

  chartInstance.setOption(option)
}

onMounted(() => {
  initChart()
  window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
  if (chartInstance) chartInstance.dispose()
})

watch(() => props.score, () => {
  if (chartInstance) {
    chartInstance.setOption({
      series: [{ data: [{ value: Math.min(100, Math.max(0, props.score || 0)) }] }]
    })
  }
})

function handleResize() {
  if (chartInstance) chartInstance.resize()
}
</script>

<style lang="scss" scoped>
.health-gauge {
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
