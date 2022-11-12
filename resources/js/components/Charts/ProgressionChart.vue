<template>
  <card
    no-body
    class="h-52"
    :card="card"
    :request-query-string="requestQueryString"
    @retrieved="prepareComponent($event.card.result)"
  >
    <template v-for="(_, name) in $slots" v-slot:[name]="slotData"
      ><slot :name="name" v-bind="slotData"
    /></template>
    <div class="relative" :class="variant">
      <base-progression-chart
        class="px-0.5"
        :chart-data="chartData"
        v-if="hasChartData"
        :amount-value="card.amount_value"
      />
      <p
        v-else
        class="mt-12 text-center text-neutral-500 dark:text-neutral-300"
        v-t="'app.not_enough_data'"
      />
    </div>
  </card>
</template>
<script>
import BaseProgressionChart from './Base/ProgressionChart'
import HandlesCharting from './HandlesCharting'
export default {
  mixins: [HandlesCharting],
  components: { BaseProgressionChart },
}
</script>
