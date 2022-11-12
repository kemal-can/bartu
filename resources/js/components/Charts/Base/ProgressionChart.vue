<template>
  <div ref="chart" class="ct-chart" />
</template>
<script>
import Chartist from 'chartist'
import 'chartist-plugin-tooltips'
import 'chartist/dist/chartist.css'
import 'chartist-plugin-tooltips/dist/chartist-plugin-tooltip.css'

import { formatMoney } from '@/utils'

export default {
  name: 'progression-chart',
  props: ['chartData', 'amountValue'],
  data: () => ({
    chartist: null,
  }),
  watch: {
    chartData: function (newData, oldData) {
      this.refreshChart()
    },
  },
  methods: {
    refreshChart() {
      this.chartist.update(this.chartData)
    },
    destroy() {
      if (this.chartist) {
        this.chartist.detach()
      }
    },
  },
  mounted() {
    this.chartist = new Chartist.Line(this.$refs.chart, this.chartData, {
      lineSmooth: Chartist.Interpolation.none(),
      fullWidth: true,
      showPoint: true,
      showLine: true,
      showArea: true,
      chartPadding: {
        top: 10,
        right: 1,
        bottom: 1,
        left: 1,
      },
      low: 0,
      axisX: {
        showGrid: false,
        showLabel: true,
        offset: 0,
      },
      axisY: {
        showGrid: false,
        showLabel: true,
        offset: 0,
      },
      plugins: [
        Chartist.plugins.tooltip({
          anchorToPoint: false,
          // appendToBody: true,
          transformTooltipTextFnc: value => {
            if (this.amountValue) {
              return formatMoney(value)
            }

            return value
          },
        }),
      ],
    })
  },
  beforeUnmount() {
    this.destroy()
  },
}
</script>
