<template>
  <form-field-group :field="field" :field-id="fieldId" :form="form">
    <form-field-input-group :field="field">
      <i-form-input
        :id="fieldId"
        v-model="value"
        :disabled="isReadonly"
        :type="field.inputType || 'text'"
        v-bind="field.attributes"
        :class="{
          'pl-11': field.inputGroupPrepend,
          'pr-11': field.inputGroupAppend,
        }"
      />
    </form-field-input-group>
  </form-field-group>
</template>
<script>
import FormField from '@/components/Form/FormField'
import FormFieldInputGroup from '@/components/Form/FormFieldInputGroup'
export default {
  mixins: [FormField],
  components: { FormFieldInputGroup },
}
</script>
