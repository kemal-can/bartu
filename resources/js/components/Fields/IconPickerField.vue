<template>
  <form-field-group :field="field" :field-id="fieldId" :form="form">
    <i-icon-picker v-model="value" v-bind="field.attributes" />
  </form-field-group>
</template>
<script>
import FormField from '@/components/Form/FormField'
export default {
  mixins: [FormField],
}
</script>
