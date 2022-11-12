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
  name: 'presentation-chart',
  props: {
    chartData: null,
    amountValue: null,
    horizontal: null,
    axisYOffset: {
      type: Number,
      default: 30,
    },
    axisXOffset: {
      type: Number,
      default: 30,
    },
    onlyInteger: {
      type: Boolean,
      default: true,
    },
  },
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
    this.chartist = new Chartist.Bar(this.$refs.chart, this.chartData, {
      horizontalBars: this.horizontal,
      fullWidth: true,
      axisY: {
        offset: this.axisYOffset,
        onlyInteger: this.onlyInteger,
      },
      axisX: {
        onlyInteger: this.onlyInteger,
        offset: this.axisXOffset,
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
