<template>
  <div class="radar-chart" ref="chartRef" style="width: 100%; height: 320px;"></div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'
import * as echarts from 'echarts'

const props = defineProps({
  data: {
    type: Array,
    default: () => []
  }
})

const chartRef = ref(null)
let chartInstance = null

function initChart() {
  if (!chartRef.value) return
  if (chartInstance) chartInstance.dispose()

  chartInstance = echarts.init(chartRef.value)

  const items = props.data?.length ? props.data : []
  const labels = items.length
    ? items.map(d => d.nameAr || d.name || '')
    : ['الترطيب', 'النقاء', 'المرونة', 'التصبغ', 'الحساسية']
  const values = items.length
    ? items.map(d => d.value || 0)
    : [0, 0, 0, 0, 0]

  const option = {
    radar: {
      center: ['50%', '50%'],
      radius: '65%',
      indicator: labels.map((name) => ({
        name,
        max: 100
      })),
      axisName: {
        color: '#64748b',
        fontSize: 11,
        fontFamily: 'Tajawal'
      },
      splitArea: {
        areaStyle: {
          color: ['rgba(26, 136, 112, 0.02)', 'rgba(26, 136, 112, 0.04)']
        }
      }
    },
    series: [
      {
        type: 'radar',
        data: [
          {
            value: values,
            name: 'نتيجة التحليل',
            areaStyle: {
              color: 'rgba(26, 136, 112, 0.2)'
            },
            lineStyle: {
              color: '#1a8870',
              width: 2
            },
            itemStyle: {
              color: '#1a8870',
              borderColor: '#fff',
              borderWidth: 2
            }
          }
        ]
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

watch(() => props.data, () => {
  initChart()
}, { deep: true })

function handleResize() {
  if (chartInstance) chartInstance.resize()
}
</script>

<style lang="scss" scoped>
.radar-chart {
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
