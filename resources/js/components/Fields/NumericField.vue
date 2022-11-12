<template>
  <form-field-group :field="field" :form="form" :field-id="fieldId">
    <form-field-input-group :field="field">
      <i-form-numeric-input
        :id="fieldId"
        v-model="value"
        :disabled="isReadonly"
        @blur="checkValue"
        :class="{
          'pl-14': field.inputGroupPrepend,
          'pr-14': field.inputGroupAppend,
        }"
        :name="field.attribute"
        v-bind="field.attributes"
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
  methods: {
    /**
     * Set the value to empty string when zero
     *
     * Helps when just focusing on the input and focus-out and this will ensure the value is saved as null not as 0
     *
     * @return {Void}
     */
    checkValue() {
      this.$nextTick(() =>
        this.$nextTick(() => {
          if (this.value == 0) {
            this.value = ''
          }
        })
      )
    },

    /**
     * Update the field's internal value
     *
     * The numeric field does not expect null value
     */
    handleChange(value) {
      this.value = value !== null ? value : ''
      this.realInitialValue = this.value
    },
  },
}
</script>
