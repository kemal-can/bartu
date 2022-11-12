<template>
  <i-form-input
    type="text"
    :placeholder="placeholder"
    :disabled="readOnly"
    size="sm"
    :model-value="query.value"
    @input="updateValue($event)"
  />
</template>
<script>
import Type from './Type'
export default {
  mixins: [Type],
  computed: {
    placeholder() {
      return this.$t('filters.placeholders.enter', {
        label: this.operand ? this.operand.label : this.rule.label,
      })
    },
  },
}
</script>
