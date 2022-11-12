<template>
  <div class="mt-1 flex space-x-2">
    <i-form-radio
      v-for="option in options"
      v-model="value"
      :value="option[rule.valueKey]"
      :key="option[rule.valueKey]"
      :disabled="readOnly"
      :id="rule.id + '_' + option[rule.valueKey] + '_' + index"
      :name="rule.id + '_' + option[rule.valueKey]"
      :label="option[rule.labelKey]"
    />
  </div>
</template>
<script>
import Type from './Type'
import InteractsWithOptions from '@/mixins/InteractsWithOptions'
export default {
  mixins: [Type, InteractsWithOptions],
  computed: {
    value: {
      get() {
        return this.query.value
      },
      set(value) {
        this.updateValue(value)
      },
    },
  },
  created() {
    this.getOptions(this.rule).then(options => this.setOptions(options))
  },
}
</script>
