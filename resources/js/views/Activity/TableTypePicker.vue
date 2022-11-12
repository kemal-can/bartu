<template>
  <div class="mt-1 flex w-full items-center overflow-x-auto py-1">
    <a
      href="#"
      v-show="value && !isDisabled"
      @click.prevent="value = null"
      class="link mr-3 border-r-2 border-neutral-200 pr-3 dark:border-neutral-600"
    >
      {{ $t('app.all') }}
    </a>
    <i-icon-picker
      class="min-w-max"
      :icons="formattedTypes"
      value-field="id"
      v-i-tooltip="
        typeRuleIsApplied ? $t('activity.filters.activity_type_disabled') : ''
      "
      :disabled="isDisabled"
      v-model="value"
    />
  </div>
</template>
<script>
import { mapState } from 'vuex'
import { useTypes } from '@/views/Activity/Composables/useTypes'
import { ref } from 'vue'

export default {
  emits: ['update:modelValue'],
  props: ['modelValue'],
  setup() {
    const { formatTypesForIcons } = useTypes()
    let value = ref(null)
    return { formatTypesForIcons, value }
  },
  watch: {
    value: function (newVal, oldVal) {
      this.$emit('update:modelValue', newVal)
    },
    // Remove selected type when the builder has rules and they are valid
    // to prevent errors in the filters
    hasBuilderRules: function (newVal, oldVal) {
      if (newVal && this.rulesAreValid) {
        this.value = undefined
      }
    },
    // The same for when rules become valid, when valid and has builder rules
    // remove selected type
    rulesAreValid: function (newVal, oldVal) {
      if (this.hasBuilderRules && newVal) {
        this.value = undefined
      }
    },
  },
  computed: {
    formattedTypes() {
      return this.formatTypesForIcons(this.types)
    },
    ...mapState({
      types: state => state.activities.types,
    }),
    /**
     * Indicates whether the query builder has rules
     *
     * @return {Boolean}
     */
    hasBuilderRules() {
      return (
        this.queryBuilderRules.children &&
        this.queryBuilderRules.children.length > 0
      )
    },

    /**
     * Indicates whether the type on the top is disabled
     *
     * @return {Boolean}
     */
    isDisabled() {
      return this.typeRuleIsApplied || !this.rulesAreValid
    },

    /**
     * Indicates whether there is activity type rules in the query builder
     *
     * @return {Boolean}
     */
    typeRuleIsApplied() {
      return Boolean(
        this.$store.getters['filters/findRuleInQueryBuilder'](
          'activities',
          'activities',
          'activity_type_id'
        )
      )
    },

    /**
     * Indicates whether the activity table ruels are valid
     *
     * @return {Boolean}
     */
    rulesAreValid() {
      return Boolean(
        this.$store.getters['filters/rulesAreValid']('activities', 'activities')
      )
    },

    /**
     * Get the query builder rules
     *
     * @return {Object}
     */
    queryBuilderRules() {
      return (
        this.$store.getters['filters/getBuilderRules'](
          'activities',
          'activities'
        ) || {}
      )
    },
  },

  created() {
    this.value = this.modelValue
  },
}
</script>
