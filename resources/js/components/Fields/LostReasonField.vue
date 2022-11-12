<template>
  <form-field-group :field="field" :form="form" :field-id="fieldId">
    <main-lost-reason-field v-model="value" :attribute="field.attribute" />
  </form-field-group>
</template>
<script>
import MainLostReasonField from '@/views/Deals/LostReasonField'
import FormField from '@/components/Form/FormField'
export default {
  mixins: [FormField],
  components: { MainLostReasonField },
}
</script>
