<template>
  <div>
    <group
      :index="0"
      :query="value"
      :rules="mergedRules"
      :max-depth="maxDepth"
      :read-only="readOnly"
      :depth="depth"
      :labels="mergedLabels"
    />
    <slot></slot>
  </div>
</template>
<script>
import Group from './QueryBuilderGroup'
import ruleTypes from './Rules/Types'
import defaultLabels from './Labels'

export default {
  mixins: [ruleTypes],
  components: { Group },
  props: {
    rules: Array,
    identifier: {
      type: String,
      required: true,
    },
    view: {
      type: String,
      required: true,
    },
    readOnly: {
      type: Boolean,
      default: false,
    },
    labels: {
      type: Object,
      default() {
        return defaultLabels
      },
    },
    maxDepth: {
      type: Number,
      default: 3, // only 3 is supported ATM
      validator: function (value) {
        return value >= 1
      },
    },
  },
  data: () => ({
    depth: 1,
  }),
  computed: {
    /**
     * Currently filter rules in the builder
     *
     * @type {Object}
     */
    value() {
      return this.$store.getters['filters/getBuilderRules'](
        this.identifier,
        this.view
      )
    },

    /**
     * Merged labels in case additional labels are passed as prop
     *
     * @return {Object}
     */
    mergedLabels() {
      return Object.assign({}, defaultLabels, this.labels)
    },

    /**
     * Merged rules
     *
     * @return {Array}
     */
    mergedRules() {
      let mergedRules = []

      if (!this.rules) {
        return mergedRules
      }

      this.rules.forEach(rule => {
        if (typeof this.ruleTypes[rule.type] !== 'undefined') {
          mergedRules.push(Object.assign({}, this.ruleTypes[rule.type], rule))
        } else {
          mergedRules.push(rule)
        }
      })

      return mergedRules
    },
  },
  created() {
    if (Object.keys(this.value).length === 0) {
      this.$store.commit('filters/RESET_BUILDER_RULES', {
        identifier: this.identifier,
        view: this.view,
      })
    }
  },
}
</script>
