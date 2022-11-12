<template>
  <i-form-input
    v-if="!isBetween"
    size="sm"
    type="number"
    :placeholder="placeholder"
    :disabled="readOnly"
    :model-value="query.value"
    @input="updateValue($event)"
  />
  <div class="flex items-center space-x-2" v-else>
    <i-form-input
      type="number"
      size="sm"
      :placeholder="placeholder"
      :disabled="readOnly"
      :model-value="query.value[0]"
      @input="updateValue([$event, query.value[1]])"
    />
    <icon icon="ArrowRight" class="h-4 w-4 shrink-0 text-neutral-600" />
    <i-form-input
      type="number"
      size="sm"
      :placeholder="placeholder"
      :disabled="readOnly"
      :model-value="query.value[1]"
      @input="updateValue([query.value[0], $event])"
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
}
</script>
