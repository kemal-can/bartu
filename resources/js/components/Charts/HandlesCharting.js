/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
import map from 'lodash/map'

export default {
  data: () => ({
    chartData: [],
  }),
  props: {
    card: {
      required: true,
      type: Object,
    },
    requestQueryString: {
      type: Object,
      default() {
        return {}
      },
    },
  },
  computed: {
    /**
     * Card color variant
     *
     * @return {String}
     */
    variant() {
      return this.card.color || 'chart-primary'
    },

    /**
     * Indicates wheteher there chart data to display
     *
     * @return {Boolean}
     */
    hasChartData() {
      const totalSeries = this.chartData.series.length
      if (totalSeries === 0) {
        return false
      }

      let anySerieHasData = false
      for (let i = 0; i < totalSeries; i++) {
        if (this.chartData.series[i].length > 0) {
          anySerieHasData = this.chartData.series[i].some(val => val.value > 0)

          if (anySerieHasData) {
            break
          }
        }
      }
      return anySerieHasData
    },
  },
  methods: {
    /**
     * Prepare the card component
     *
     * @param  {Object} result
     *
     * @return {Void}
     */
    prepareComponent(result) {
      this.chartData = {
        labels: map(result, data => data.label),
        series: [
          map(result, data => {
            return {
              meta: data.label,
              value: data.value,
            }
          }),
        ],
      }
    },
  },
  created() {
    this.prepareComponent(this.card.result)
  },
}
