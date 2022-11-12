<template>
  <div
    class="relative justify-between last:!mb-0 odd:my-4 odd:border-t odd:border-neutral-200 odd:pt-4 dark:odd:border-neutral-600 sm:flex sm:items-center"
  >
    <div
      class="mb-1 flex w-full flex-col justify-start sm:mr-3 sm:mb-0 sm:w-auto sm:flex-row sm:items-center sm:space-x-3"
    >
      <label
        class="mb-1 whitespace-nowrap text-sm font-medium text-neutral-800 dark:text-neutral-200 sm:mb-0"
        v-text="rule.label"
      />
      <i-custom-select
        v-if="showOperands"
        class="mb-1 w-52 sm:mb-0"
        size="sm"
        :disabled="readOnly"
        v-model="selectFieldOperand"
        :option-label-provider="operand => operand[operand.labelKey]"
        :options="rule.operands"
        @option:selected="
          $store.commit('filters/UPDATE_QUERY_OPERAND', {
            query: query,
            value: $event[$event.valueKey],
          })
        "
      >
      </i-custom-select>
      <i-form-select
        size="sm"
        v-show="!hasOnlyOneOperator"
        v-if="!rule.isStatic"
        :value="query.operator"
        @input="
          $store.commit('filters/UPDATE_QUERY_OPERATOR', {
            query: query,
            value: $event,
          })
        "
        :disabled="readOnly"
        class="w-auto bg-none"
      >
        <option
          v-for="operator in operators"
          :value="operator"
          :key="operator"
          :selected="operator == query.operator"
        >
          {{
            labels.operatorLabels[operator]
              ? labels.operatorLabels[operator]
              : operator
          }}
        </option>
      </i-form-select>
    </div>
    <div class="grow" v-show="!isNullableOperator">
      <component
        :is="operand ? operand.rule.component : rule.component"
        :query="query"
        :rule="operand ? operand.rule : rule"
        :index="index"
        :labels="labels"
        :operand="operand"
        :read-only="readOnly"
        :operator="query.operator"
        :is-nullable="isNullableOperator"
        :is-between="isBetweenOperator"
      />
    </div>
    <i-button-icon
      @click="remove"
      v-if="!readOnly"
      icon="X"
      class="absolute right-0 top-1 sm:relative sm:right-auto sm:top-auto sm:ml-3"
      icon-class="h-4 w-4 sm:h-5 sm:w-5"
    />
  </div>
</template>
<script>
import find from 'lodash/find'
import cloneDeep from 'lodash/cloneDeep'

import NumericRule from './Rules/NumericRule'
import CheckboxRule from './Rules/CheckboxRule'
import DateRule from './Rules/DateRule'
import NumberRule from './Rules/NumberRule'
import RadioRule from './Rules/RadioRule'
import SelectRule from './Rules/SelectRule'
import MultiSelectRule from './Rules/MultiSelectRule'
import TextRule from './Rules/TextRule'
import StaticRule from './Rules/StaticRule'
import NullableRule from './Rules/NullableRule'
import { isNullableOperator, isBetweenOperator } from './Utils'
export default {
  inheritAttrs: false,
  emits: ['child-deletion-requested'],
  name: 'rule',
  components: {
    NumericRule,
    CheckboxRule,
    DateRule,
    NumberRule,
    RadioRule,
    SelectRule,
    MultiSelectRule,
    TextRule,
    StaticRule,
    NullableRule,
  },
  props: ['query', 'index', 'rule', 'labels', 'readOnly'],
  data: () => ({
    selectFieldOperand: null,
  }),
  computed: {
    /**
     * Inicates whether the rule as operand with rule
     *
     * @return {Boolean}
     */
    hasOperandWithRule() {
      return this.operand && this.operand.rule
    },

    /**
     * Get the rule operators
     *
     * @return {Array}
     */
    operators() {
      if (!this.hasOperandWithRule) {
        return this.rule.operators
      }

      return this.operand.rule.operators
    },

    /**
     * Get the selected opereand
     *
     * @return {Object|null}
     */
    operand() {
      return find(this.rule.operands, ['value', this.query.operand])
    },

    /**
     * Indicates whether the rules has only one operator
     *
     * @return {Boolean}
     */
    hasOnlyOneOperator() {
      return this.operators.length === 1
    },

    /**
     * Indicates whether the operands should be shown
     *
     * @return {Boolean}
     */
    showOperands() {
      if (
        this.rule.isStatic ||
        (this.rule.hasOwnProperty('hideOperands') && this.rule.hideOperands)
      ) {
        return false
      }

      return this.rule.operands && this.rule.operands.length > 0
    },

    /**
     * Indicates whether the rule operator is between
     *
     * @return {Boolean}
     */
    isBetweenOperator() {
      return isBetweenOperator(this.query.operator)
    },

    /**
     * Indicates whether the rule operator is nullable
     *
     * @return {Boolean}
     */
    isNullableOperator() {
      return isNullableOperator(this.query.operator)
    },
  },
  methods: {
    /**
     * Request rule remove
     *
     * @return {Void}
     */
    remove: function () {
      this.$emit('child-deletion-requested', this.index)
    },
  },
  mounted() {
    this.selectFieldOperand = this.operand && cloneDeep(this.operand)
  },
}
</script>
