<template>
  <span
    class="rounded-md border border-neutral-300 px-3 py-[0.4rem] text-neutral-700 dark:border-neutral-500 dark:text-neutral-100"
    v-if="index > 0"
  >
    {{ $t('filters.conditions.' + condition) }}
  </span>
  <div
    class="shrink-0 snap-end rounded-md border border-neutral-300 px-3 py-[0.4rem] text-neutral-700 dark:border-neutral-500 dark:text-neutral-100"
    v-if="rule.type === 'rule'"
  >
    <span v-if="!hasCustomDisplayAs">
      {{ original.label }}
      <span class="text-info-500 dark:text-info-400">{{
        labels.operatorLabels[query.operator]
      }}</span
      >&nbsp;<span class="font-medium"> {{ parsedLabel }}</span>
    </span>

    <span v-else>
      {{ parsedCustomDisplayAs }}
    </span>
  </div>

  <div class="flex shrink-0 space-x-1" v-if="isGroup">
    <rule-display
      v-for="(groupRule, index) in query.children"
      :key="groupRule.query.rule"
      :index="index"
      :condition="query.condition"
      :identifier="identifier"
      :view="view"
      :rule="groupRule"
    />
  </div>
</template>

<script>
import labels from '@/components/QueryBuilder/Labels'
import find from 'lodash/find'
import map from 'lodash/map'
import pickBy from 'lodash/pickBy'
import { isBetweenOperator } from './Utils'
import { formatMoney, formatNumber, isValueEmpty } from '@/utils'
export default {
  name: 'RuleDisplay',
  props: {
    rule: { type: Object, required: true },
    identifier: { type: String, required: true },
    index: {},
    view: {},
    condition: {},
  },
  data: () => ({
    labels: labels,
    parsedLabel: '',
  }),
  watch: {
    condition: function (newVal, oldVal) {
      // We don't parse any values when a group, as the RuleDisplay is either OR or AND
      if (this.isGroup) {
        return
      }

      this.parsedLabel = this.valueLabel()
    },
    query: {
      immediate: true,
      deep: true,
      handler: function (newVal, oldVal) {
        // We don't parse any values when a group, as the RuleDisplay is either OR or AND
        if (this.isGroup) {
          return
        }

        // Wait till everything is updated in the store e.q. values
        this.$nextTick(() => (this.parsedLabel = this.valueLabel()))
      },
    },
  },
  computed: {
    isGroup() {
      return this.rule.type === 'group'
    },
    parsedCustomDisplayAs() {
      const displayAs = this.getRuleAttribute('display_as')

      const replacer = string => {
        return string
          .replace(':value:', this.parsedLabel)
          .replace(
            ':operator:',
            this.labels.operatorLabels[this.query.operator]
          )
      }

      if (typeof displayAs === 'string') {
        return replacer(displayAs)
      }

      if (
        displayAs[0] &&
        Object.keys(displayAs).indexOf(this.query.value) === -1
      ) {
        return replacer(displayAs[0])
      }

      return replacer(displayAs[this.query.value])
    },

    query() {
      return this.rule.query
    },

    original() {
      const original = find(
        this.$store.state['filters'].rules[this.identifier],
        ['id', this.query.rule]
      )

      if (this.query.operand) {
        return find(original.operands, operand => {
          return operand.rule.id == this.query.operand
        })
      }
      return original
    },

    isBetween() {
      return isBetweenOperator(this.query.operator)
    },

    hasCustomDisplayAs() {
      if (this.isGroup) {
        return false
      }
      return Boolean(this.getRuleAttribute('display_as'))
    },
  },
  methods: {
    getRuleAttribute(attribute) {
      return this.query.operand
        ? this.original.rule[attribute]
        : this.original[attribute]
    },
    valueLabel() {
      let type = this.getRuleAttribute('type')

      if (['multi-select', 'checkbox'].indexOf(type) > -1) {
        return this.valueLabelWhenAcceptsMultiOptions()
      } else if (['radio', 'select'].indexOf(type) > -1) {
        return this.valueLabelWhenAcceptsOptions()
      } else if (type === 'date') {
        return this.valueLabelWhenDate()
      } else if (type === 'numeric') {
        return this.valueLabelWhenNumeric()
      } else if (type === 'number') {
        return this.valueLabelWhenNumber()
      } else if (this.isBetween) {
        return this.valueLabelWhenBetween()
      }

      return this.query.value
    },
    valueLabelWhenAcceptsOptions() {
      let selected =
        this.getRuleAttribute('options').filter(
          option =>
            option[this.getRuleAttribute('valueKey')] == this.query.value
        )[0] || null

      return selected ? selected[this.getRuleAttribute('labelKey')] : ''
    },
    valueLabelWhenAcceptsMultiOptions() {
      let selected = !this.query.value
        ? []
        : this.getRuleAttribute('options').filter(
            option =>
              this.query.value.indexOf(
                option[this.getRuleAttribute('valueKey')]
              ) > -1
          )

      return map(selected, this.getRuleAttribute('labelKey')).join(', ')
    },
    valueLabelWhenBetween() {
      return this.query.value ? this.query.value.join(' - ') : ''
    },
    formattedValueLabel(formatter) {
      if (this.isBetween) {
        if (isValueEmpty(this.query.value)) {
          return ''
        }

        return [
          this.query.value[0] ? formatter(this.query.value[0]) : '',
          this.query.value[1] ? formatter(this.query.value[1]) : '',
        ].join(' - ')
      }

      return this.query.value ? formatter(this.query.value) : ''
    },
    valueLabelWhenNumber() {
      return this.formattedValueLabel(formatNumber)
    },
    valueLabelWhenNumeric() {
      return this.formattedValueLabel(formatMoney)
    },
    valueLabelWhenDate() {
      if (this.query.operator === 'is' || this.query.operator === 'was') {
        let operatorOptions =
          this.original.operatorsOptions[this.query.operator]

        return pickBy(operatorOptions, (value, key) => key == this.query.value)[
          this.query.value
        ]
      }

      return this.formattedValueLabel(this.localizedDate)
    },
  },
}
</script>
