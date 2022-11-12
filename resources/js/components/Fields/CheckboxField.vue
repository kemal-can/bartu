<template>
  <form-field-group :field="field" :form="form" :field-id="fieldId">
    <div
      :class="{
        'flex items-center space-x-2': field.inline,
        'space-y-1': !field.inline,
      }"
    >
      <i-form-checkbox
        v-for="(option, index) in options"
        :value="options[index][field.valueKey]"
        v-model:checked="value"
        :name="field.attribute"
        v-bind="field.attributes"
        :key="options[index][field.valueKey]"
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
  data: () => ({
    value: [],
  }),
  methods: {
    /*
     * Set the initial value for the field
     */
    setInitialValue() {
      this.value = this.prepareValue(this.field.value)
    },

    /**
     * Handle change
     *
     * @param  {mixed} value
     *
     * @return {Void}
     */
    handleChange(value) {
      this.value = this.prepareValue(value)
      this.realInitialValue = this.value
    },

    /**
     * Prepare the field internal value
     *
     * @param  {mixed} value
     *
     * @return {Array}
     */
    prepareValue(value) {
      return (!(value === undefined || value === null) ? value : []).map(
        value => value[this.field.valueKey]
      )
    },
  },
  created() {
    this.getOptions(this.field).then(options => this.setOptions(options))
  },
}
</script>
