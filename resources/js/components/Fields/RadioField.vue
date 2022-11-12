<template>
  <form-field-group :field="field" :field-id="fieldId" :form="form">
    <div
      :class="{
        'flex items-center space-x-2': field.inline,
        'space-y-1': !field.inline,
      }"
    >
      <i-form-radio
        v-for="(option, index) in options"
        :key="options[index][field.valueKey]"
        :name="field.attribute"
        :value="options[index][field.valueKey]"
        :id="'radio-' + options[index][field.valueKey]"
        v-bind="field.attributes"
        v-model="value"
        :disabled="isReadonly"
        :label="options[index][field.labelKey]"
      />
    </div>
  </form-field-group>
</template>
<script>
import FormField from '@/components/Form/FormField'
import InteractsWithOptions from '@/mixins/InteractsWithOptions'
export default {
  mixins: [FormField, InteractsWithOptions],
  created() {
    this.getOptions(this.field).then(options => this.setOptions(options))
  },
}
</script>
