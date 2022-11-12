<template>
  <div class="flex snap-x space-x-1 overflow-x-auto" style="font-size: 0.96rem">
    <rule-display
      v-for="(rule, index) in group.children"
      :key="rule.query.rule"
      :index="index"
      :condition="group.condition"
      :identifier="identifier"
      :view="view"
      :rule="rule"
    />
  </div>
</template>

<script>
import RuleDisplay from '@/components/QueryBuilder/RuleDisplay'

export default {
  components: { RuleDisplay },
  props: {
    view: { required: true, type: String },
    identifier: { required: true, type: String },
  },
  computed: {
    /**
     * Get the table query builder rules
     *
     * @return {Boolean}
     */
    group() {
      return this.$store.getters['filters/getBuilderRules'](
        this.identifier,
        this.view
      )
    },
  },
}
</script>
