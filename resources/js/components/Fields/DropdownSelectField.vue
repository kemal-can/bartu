<template>
  <form-field-group :field="field" :field-id="fieldId" :form="form">
    <dropdown-select
      :model-value="value"
      @change="value = $event[field.valueKey]"
      :items="options"
      v-bind="field.attributes"
      :label-key="field.labelKey"
      :value-key="field.valueKey"
    >
    </dropdown-select>
  </form-field-group>
</template>
<script>
import FormField from '@/components/Form/FormField'
import InteractsWithOptions from '@/mixins/InteractsWithOptions'
import isObject from 'lodash/isObject'
export default {
  mixins: [FormField, InteractsWithOptions],
  methods: {
    /*
     * Set the initial value for the field
     */
    setInitialValue() {
      this.value = isObject(this.field.value)
        ? this.field.value[this.field.valueKey] || ''
        : this.field.value || ''
    },

    /**
     * Update the field's internal value
     *
     * The numeric field does not expect null value
     */
    handleChange(value) {
      this.value = isObject(value)
        ? value[this.field.valueKey] || ''
        : value || ''
      this.realInitialValue = this.value
    },
  },
  created() {
    this.getOptions(this.field).then(options => this.setOptions(options))
  },
}
</script>
