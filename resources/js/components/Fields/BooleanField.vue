<template>
  <form-field-group :field="field" :form="form" :field-id="fieldId">
    <i-form-checkbox
      :id="field.name"
      v-model:checked="value"
      :disabled="isReadonly"
      :name="field.name"
      :value="field.trueValue"
      v-bind="field.attributes"
      :unchecked-value="field.falseValue"
    >
      <span v-html="label"></span>
    </i-form-checkbox>
  </form-field-group>
</template>
<script>
import FormField from '@/components/Form/FormField'
export default {
  mixins: [FormField],
  data: () => ({
    label: null,
  }),
  created() {
    // Store the label in data and leave the actual field label
    // empty so it won't be added in form-group label
    this.label = this.field.label
    this.field.label = null
  },
}
</script>
