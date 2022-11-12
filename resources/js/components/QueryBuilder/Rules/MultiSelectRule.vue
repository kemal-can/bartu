<script>
import SelectRule from './SelectRule'
import isEqual from 'lodash/isEqual'
import map from 'lodash/map'
import { isValueEmpty } from '@/utils'
export default {
  extends: SelectRule,
  methods: {
    /**
     * Initialize component
     *
     * @param  {array} options
     *
     * @return {Void}
     */
    initializeComponent(options) {
      this.setOptions(options)

      if (!isValueEmpty(this.query.value)) {
        this.setInitialValue()
      }
    },

    /**
     * Handle change for select to update the value
     *
     * @param  {mixed} value
     *
     * @return {Void}
     */
    handleChange(value) {
      if (isValueEmpty(value)) {
        this.updateValue([])
      } else {
        this.updateValue(map(value, this.rule.valueKey))
      }
    },

    /**
     * Set the select initial internal value
     */
    setInitialValue() {
      if (isValueEmpty(this.query.value)) {
        this.updateValue([])
      } else {
        this.updateValue(this.query.value)
      }
    },

    /**
     * Set the select value from the given query builder value
     *
     * @param  {Mixed} value
     *
     * @return {Void}
     */
    setSelectValue(value) {
      if (isValueEmpty(value)) {
        this.selectValue = []
        return
      }

      value =
        this.options.filter(
          option => value.indexOf(option[this.rule.valueKey]) > -1
        ) || []

      if (!isEqual(value, this.selectValue)) {
        this.selectValue = value
      }
    },
  },
}
</script>
