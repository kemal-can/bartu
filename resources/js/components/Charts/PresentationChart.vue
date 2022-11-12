<template>
  <card
    class="h-52"
    no-body
    :card="card"
    :request-query-string="requestQueryString"
    @retrieved="prepareComponent($event.card.result)"
  >
    <template v-for="(_, name) in $slots" v-slot:[name]="slotData"
      ><slot :name="name" v-bind="slotData"
    /></template>
    <div class="relative" :class="variant">
      <base-presentation-chart
        class="px-0.5"
        :chart-data="chartData"
        :amount-value="card.amount_value"
        :horizontal="card.horizontal"
        :only-integer="card.onlyInteger"
        v-if="hasChartData"
        :axis-y-offset="card.axisYOffset"
        :axis-x-offset="card.axisXOffset"
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
import BasePresentationChart from './Base/PresentationChart'
import HandlesCharting from './HandlesCharting'
export default {
  mixins: [HandlesCharting],
  components: { BasePresentationChart },
}
</script>
