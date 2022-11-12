<template>
  <form-field-group :field="field" :field-id="fieldId" :form="form">
    <app-timezone-field
      v-model="value"
      v-bind="field.attributes"
      :field-id="fieldId"
      :clearable="true"
      :name="field.attribute"
      :disabled="isReadonly"
    />
  </form-field-group>
</template>
<script>
import AppTimezoneField from '@/components/TimezoneField'
import FormField from '@/components/Form/FormField'
export default {
  components: { AppTimezoneField },
  mixins: [FormField],
  created() {
    // Set the imezones from the field options in store, so the AppTimezoneField won't make request
    // to actually retrieve the timezones from storage, e.q. useful also on web forms
    this.$store.commit('SET_TIMEZONES', this.field.timezones)
  },
}
</script>
