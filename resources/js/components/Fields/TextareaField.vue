<template>
  <form-field-group :field="field" :field-id="fieldId" :form="form">
    <i-form-textarea
      :id="fieldId"
      v-model="value"
      :disabled="isReadonly"
      :name="field.attribute"
      v-bind="field.attributes"
    />
  </form-field-group>
</template>
<script>
import FormField from '@/components/Form/FormField'
export default {
  mixins: [FormField],
}
</script>
