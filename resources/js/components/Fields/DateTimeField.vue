<template>
  <form-field-group :field="field" :form="form" :field-id="fieldId">
    <date-picker
      v-model="value"
      :required="field.isRequired"
      :id="fieldId"
      :name="field.attribute"
      :disabled="isReadonly"
      v-bind="field.attributes"
      mode="dateTime"
    />
  </form-field-group>
</template>
<script>
import FormField from '@/components/Form/FormField'
import { isValueEmpty } from '@/utils'
import isObject from 'lodash/isObject'

export default {
  mixins: [FormField],
  computed: {
    /**
     * Check whether the field is dirty
     *
     * @return {Boolean}
     */
    isDirty() {
      const areDatesEqual = (d1, d2) => {
        if (isValueEmpty(d1) && isValueEmpty(d2)) {
          return true
        } else if (
          (isValueEmpty(d1) && !isValueEmpty(d2)) ||
          (isValueEmpty(d2) && !isValueEmpty(d1))
        ) {
          return false
        }

        return moment.utc(d1).isSame(d2)
      }

      // Range
      if (isObject(this.realInitialValue) && isObject(this.value)) {
        const keys = Object.keys(this.value)

        return keys.some(
          key => !areDatesEqual(this.value[key], this.realInitialValue[key])
        )
      }

      return !areDatesEqual(this.value, this.realInitialValue)
    },
  },
}
</script>
