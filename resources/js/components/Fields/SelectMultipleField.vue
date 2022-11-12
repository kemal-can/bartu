<script>
import SelectField from '@/components/Fields/SelectField'
import each from 'lodash/each'
import { isValueEmpty } from '@/utils'
import isEqual from 'lodash/isEqual'
export default {
  extends: SelectField,
  computed: {
    /**
     * Check whether the field is dirty
     *
     * @return {Boolean}
     */
    isDirty() {
      // Check for null and "" values
      if (isValueEmpty(this.value) && isValueEmpty(this.realInitialValue)) {
        return false
      }

      return !isEqual(this.value, this.realInitialValue)
    },
  },
  methods: {
    /**
     * Fill the form value
     *
     * @param  {Object} form
     *
     * @return {Void}
     */
    fill(form) {
      if (this.field.asObjectValue) {
        form.fill(this.field.attribute, this.value)

        return
      }

      let values = []
      each(this.value, data => values.push(data[this.field.valueKey]))

      form.fill(this.field.attribute, values)
    },
  },
  created() {
    if (!this.field.attributes) {
      this.field.attributes = {}
    }

    this.field.attributes.multiple = true
  },
}
</script>
