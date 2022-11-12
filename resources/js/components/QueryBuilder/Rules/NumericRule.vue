<template>
  <i-form-numeric-input
    v-if="!isBetween"
    size="sm"
    :placeholder="placeholder"
    :model-value="query.value"
    @input="updateValue($event)"
    :read-only="readOnly"
  />
  <div class="flex items-center space-x-2" v-else>
    <i-form-numeric-input
      size="sm"
      :placeholder="placeholder"
      :model-value="query.value[0]"
      @input="updateValue([$event, query.value[1]])"
      :read-only="readOnly"
    />
    <icon icon="ArrowRight" class="h-4 w-4 shrink-0 text-neutral-600" />
    <i-form-numeric-input
      size="sm"
      :placeholder="placeholder"
      :model-value="query.value[1]"
      @input="updateValue([query.value[0], $event])"
      :read-only="readOnly"
    />
  </div>
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
  created() {
    // Prevents warning for vue numeric because if query.value is null
    // will throw validation warning in console
    if (this.query.value === null) {
      this.updateValue(0)
    }
  },
}
</script>
