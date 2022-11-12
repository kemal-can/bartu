<template>
  <div
    :class="[
      'border-l-4 px-4 py-3',
      borderClasses,
      bgClasses,
      {
        'mt-5 mb-4': depth > 1,
      },
    ]"
  >
    <div
      class="flex items-center justify-between sm:flex-nowrap"
      :class="{ 'mb-4': readOnly }"
    >
      <i18n-t
        scope="global"
        class="flex w-full flex-wrap items-start text-sm font-medium text-neutral-800 dark:text-neutral-100 sm:items-center"
        :keypath="
          depth <= 1
            ? 'filters.show_matching_records_conditions'
            : 'filters.or_match_any_conditions'
        "
        tag="div"
      >
        <template #condition v-if="depth > 1">
          {{ $t('filters.conditions.' + previousMatchType) }}
        </template>
        <template #match_type>
          <select
            class="border-1 mx-1 rounded-md border-neutral-300 bg-none py-0 px-2 text-sm focus:shadow-none focus:ring-primary-500 dark:border-neutral-500 dark:bg-neutral-500 dark:text-white"
            :class="{ 'pointer-events-none': readOnly }"
            :value="query.condition"
            @input="
              $store.commit('filters/UPDATE_QUERY_CONDITION', {
                query: query,
                value: $event.target.value,
              })
            "
          >
            <option value="and">{{ labels.matchTypeAll }}</option>
            <option value="or">{{ labels.matchTypeAny }}</option>
          </select>
        </template>
      </i18n-t>
      <i-button-icon
        @click.prevent.stop="remove"
        icon="X"
        class="mt-px"
        v-if="depth > 1"
      />
    </div>
    <div>
      <div
        :class="[
          'mt-3 flex w-full items-center',
          { 'mb-7': totalChildren > 0, hidden: readOnly },
        ]"
      >
        <i-custom-select
          class="w-56"
          size="sm"
          :placeholder="labels.addRule"
          :clearable="false"
          :options="rules"
          v-model="selectedRule"
          @option:selected="addRule"
        />
        <a
          class="link ml-3 shrink-0 text-sm"
          href="#"
          v-show="totalChildren > 0"
          v-if="depth < maxDepth"
          @click="addGroup"
          v-text="labels.addGroup"
        />
      </div>

      <component
        v-for="(child, index) in children"
        :is="child.type"
        :key="child.query.rule + '-' + index"
        :query="child.query"
        :max-depth="maxDepth"
        :read-only="readOnly"
        :previous-match-type="query.condition"
        :rule="getRuleById(child.query.rule)"
        :rules="rules"
        :index="index"
        :depth="nextDepth"
        :labels="labels"
        @child-deletion-requested="removeChild"
      />
    </div>
  </div>
</template>
<script>
import Rule from './QueryBuilderRule'
import find from 'lodash/find'
export default {
  name: 'group',
  emits: ['child-deletion-requested'],
  components: { Rule },
  props: [
    'index',
    'query',
    'rules',
    'maxDepth',
    'depth',
    'labels',
    'readOnly',
    'previousMatchType',
  ],
  data: () => ({
    selectedRule: '',
  }),

  computed: {
    /**
     * Get the group border class based in it's depth
     */
    borderClasses() {
      return {
        'border-neutral-200 dark:border-neutral-500': this.depth === 1,
        'border-info-400 dark:border-info-500': this.depth === 2,
        'border-primary-400 dark:primary-info-500': this.depth > 2,
      }
    },

    /**
     * Get the group background class based in it's depth
     */
    bgClasses() {
      return {
        'bg-neutral-50 dark:bg-neutral-800': this.depth === 1,
        'bg-neutral-100 dark:bg-neutral-900': this.depth === 2,
        'bg-neutral-200 dark:bg-neutral-800': this.depth > 2,
      }
    },

    /**
     * Get the number of total child rules in the group
     *
     * @return {Number}
     */
    totalChildren() {
      return this.children.length
    },

    /**
     * Query children
     *
     * @type {Object}
     */
    children: {
      get() {
        return this.query.children
      },
      set(value) {
        this.$store.commit('filters/SET_QUERY_CHILDREN', {
          query: this.query,
          children: value,
        })
      },
    },

    /**
     * Get the next depth
     *
     * @return {Number}
     */
    nextDepth() {
      return this.depth + 1
    },
  },
  methods: {
    /**
     * Find rule by id
     * @param  {Number|String} ruleId
     *
     * @return {null|Object}
     */
    getRuleById(ruleId) {
      return find(this.rules, ['id', ruleId])
    },

    /**
     * Add new rule
     */
    addRule() {
      let selectedOperand = null
      let selectedOperator = this.selectedRule.operators[0]

      if (this.selectedRule.operands && this.selectedRule.operands.length > 0) {
        selectedOperand =
          this.selectedRule.operands[0][this.selectedRule.operands[0].valueKey]

        if (this.selectedRule.operands[0].rule) {
          selectedOperator = this.selectedRule.operands[0].rule.operators[0]
        }
      }

      this.$store.commit('filters/ADD_QUERY_CHILD', {
        query: this.query,
        child: {
          type: 'rule',
          query: {
            type: this.selectedRule.type,
            rule: this.selectedRule.id,
            operator: selectedOperator,
            operand: selectedOperand,
            value: null,
          },
        },
      })

      this.selectedRule = ''
    },

    /**
     * Add new group
     */
    addGroup() {
      if (this.depth < this.maxDepth) {
        this.$store.commit('filters/ADD_QUERY_GROUP', this.query)
      }
    },

    /**
     * Remove group
     *
     * @return {Void}
     */
    remove() {
      this.$emit('child-deletion-requested', this.index)
    },

    /**
     * Remove child
     *
     * @param  {Number} index
     *
     * @return {Void}
     */
    removeChild(index) {
      this.$store.commit('filters/REMOVE_QUERY_CHILD', {
        query: this.query,
        index: index,
      })
    },
  },
}
</script>
