/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
import FormFieldGroup from '@/components/Form/FormFieldGroup'
import get from 'lodash/get'
import isObject from 'lodash/isObject'
import isEqual from 'lodash/isEqual'
import cloneDeep from 'lodash/cloneDeep'
import { isValueEmpty } from '@/utils'
export default {
  components: { FormFieldGroup },

  data: () => ({
    value: '',
    clearErrorOnChange: true,
    realInitialValue: null,
  }),

  props: {
    viaResource: String,
    viaResourceId: Number,

    field: {
      required: true,
      type: Object,
    },

    view: {
      required: true,
      type: String,
      validator: function (value) {
        return (
          [
            ...Object.keys(Innoclapps.config.fields.views),
            ...['internal'],
          ].indexOf(value) !== -1
        )
      },
    },

    isFloating: {
      type: Boolean,
      default: false,
    },

    form: {
      required: true,
      type: Object,
    },
  },

  watch: {
    /**
     * Update field value param
     *
     * Can be used e.q. to watch for changes or create computed properties on parent components
     */
    value: {
      handler: function (newVal, oldVal) {
        // VueJS triggers the watcher when an object is not changed too
        // @link https://github.com/vuejs/vue/issues/2164
        if (
          isObject(newVal) &&
          isObject(oldVal) &&
          JSON.stringify(newVal) === JSON.stringify(oldVal)
        ) {
          return
        }

        this.field.currentValue = newVal

        if (this.clearErrorOnChange && this.form.errors) {
          this.form.errors.clear(this.field.attribute)
        }

        if (this.field.emitChangeEvent) {
          Innoclapps.$emit(this.field.emitChangeEvent, newVal)
        }
      },
      deep: true,
    },
  },
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

    /**
     * Determine if the field is in readonly mode
     */
    isReadonly() {
      return this.field.readonly || get(this.field, 'attributes.readonly')
    },

    /**
     * Determine the field id
     *
     * @return {String}
     */
    fieldId() {
      return (
        (this.field.id || this.field.attribute) +
        (this.isFloating ? '-floating' : '')
      )
    },
  },
  methods: {
    /**
     * Initialize the field data
     * @return {Void}
     */
    initialize() {
      this.setInitialValue()

      // If not already set in parent component in "created" lifecycle
      if (!this.realInitialValue) {
        this.realInitialValue = cloneDeep(this.value)
      }

      this.form.set(this.field.attribute, this.value)
    },

    /*
     * Set the initial value for the field
     */
    setInitialValue() {
      this.value = !(
        this.field.value === undefined || this.field.value === null
      )
        ? this.field.value
        : ''
    },

    /**
     * Provide a function that fills a passed form object with the
     * field's internal value attribute
     */
    fill(form) {
      form.fill(this.field.attribute, this.value)
    },

    /**
     * Update the field's internal value
     */
    handleChange(value) {
      this.value = value
      this.realInitialValue = value
    },
  },

  /**
   * Before mount bind the necessary functions
   * @return {Void}
   */
  beforeMount() {
    // Add a default fill and handleChange methods for the field
    this.field.fill = this.fill

    // For form reset and to set initial value
    this.field.handleChange = this.handleChange

    // Provide dirty check function
    this.field.isDirty = () => {
      return this.isDirty
    }

    this.field.currentValue = null
  },

  /**
   * Handle the field unmmounted lifecycle hook
   */
  unmounted() {
    // Allows to garbage collect the fields?
    this.field.isDirty = null
    this.field.currentValue = null
    this.field.handleChange = null
    this.field.fill = null
  },

  /**
   * On mounted, initialize the field
   * @return {Void}
   */
  mounted() {
    this.initialize()
  },
}
