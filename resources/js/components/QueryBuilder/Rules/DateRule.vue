<template>
  <div
    v-if="!isDateIsOperator && !isDateWasOperator"
    class="flex items-center space-x-2"
  >
    <date-picker
      v-if="!isBetween"
      size="sm"
      class="flex-1"
      :placeholder="$t('filters.placeholders.select_date')"
      :model-value="query.value"
      @input="updateValue($event)"
      :disabled="readOnly"
    />
    <date-picker
      :placeholder="$t('filters.placeholders.select_date')"
      :model-value="query.value[0]"
      v-if="isBetween"
      size="sm"
      @input="updateValue([$event, query.value[1]])"
      :disabled="readOnly"
    />
    <icon
      icon="ArrowRight"
      class="h-4 w-4 shrink-0 text-neutral-600"
      v-if="isBetween"
    />
    <date-picker
      v-if="isBetween"
      size="sm"
      :placeholder="$t('filters.placeholders.select_date')"
      :min-date="query.value[0] || null"
      :disabled="readOnly || !query.value[0]"
      :model-value="query.value[1]"
      @input="updateValue([query.value[0], $event])"
    />
  </div>
  <i-form-select
    v-else
    size="sm"
    :model-value="query.value"
    @input="updateValue($event)"
    :disabled="readOnly"
  >
    <option value=""></option>
    <option
      :value="operator.value"
      v-for="operator in operatorIsOrWasOptions"
      :key="operator.value"
    >
      {{ operator.text }}
    </option>
  </i-form-select>
</template>
<script>
import Type from './Type'
import map from 'lodash/map'
export default {
  mixins: [Type],
  data: () => ({
    value: null,
  }),
  computed: {
    /**
     * Get the IS or WAS operator options
     *
     * @return {Array}
     */
    operatorIsOrWasOptions() {
      return this.isDateIsOperator
        ? this.isOperatorOptions
        : this.wasOperatorOptions
    },

    /**
     * Indicates whether the operator is IS
     *
     * @return {Boolean}
     */
    isDateIsOperator() {
      return this.query.operator === 'is'
    },

    /**
     * Indicates whether the operator is WAS
     *
     * @return {Boolean}
     */
    isDateWasOperator() {
      return this.query.operator === 'was'
    },

    /**
     * Get the IS operator options
     *
     * @return {Array}
     */
    isOperatorOptions() {
      return map(this.rule.operatorsOptions['is'], (option, value) => ({
        value: value,
        text: option,
      }))
    },

    /**
     * Get the WAS operator options
     *
     * @return {Array}
     */
    wasOperatorOptions() {
      return map(this.rule.operatorsOptions['was'], (option, value) => ({
        value: value,
        text: option,
      }))
    },
  },
}
</script>
