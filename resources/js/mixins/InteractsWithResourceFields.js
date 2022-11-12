/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
import FieldsCollection from '@/services/FieldsCollection'
import cloneDeep from 'lodash/cloneDeep'

export default {
  data: () => ({
    fields: [],
  }),
  computed: {
    /**
     * Indicates whether the main fields are configured
     *
     * @return {Boolean}
     */
    fieldsConfigured() {
      return this.fieldsAreConfigured('fields')
    },

    /**
     * Indicates whether the fields are dirty
     *
     * @return {Boolean}
     */
    fieldsAreDirty() {
      return this.fields.dirty()
    },
  },
  methods: {
    /**
     * Check whether the fields are configured
     *
     * @param  {String} fieldsKey
     *
     * @return {Boolean}
     */
    fieldsAreConfigured(fieldsKey) {
      if (Array.isArray(this[fieldsKey])) {
        return this[fieldsKey].length > 0
      }

      if (this[fieldsKey] === null) {
        return false
      }

      // Is collection
      return this[fieldsKey].all().length > 0
    },

    /**
     * Fill the form fields with the values
     *
     * @param  {Object} form
     * @param  {String} fieldsKey
     *
     * @return {Object}
     */
    fillFormFields(form, fieldsKey = 'fields') {
      return this[fieldsKey].fill(form)
    },

    /**
     * Reset the form fields
     *
     * @param  {Object} form
     * @param  {String} fieldsKey
     *
     * @return {Void}
     */
    resetFormFields(form, fieldsKey = 'fields') {
      form.reset()

      const keys = form.keys()

      this[fieldsKey].forEach(field => {
        const index = keys.indexOf(field.attribute)
        let originalValue = cloneDeep(form.originalData[keys[index]])
        field.handleChange(originalValue)
      })
    },

    /**
     * Reset the form fields
     *
     * @param  {Object} fields
     * @param  {String} fieldsKey
     *
     * @return {Void}
     */
    setFields(fields, fieldsKey = 'fields') {
      let clone = this.cleanObject(fields)

      this[fieldsKey] = new FieldsCollection(clone)
    },

    /**
     * Set the fields values
     *
     * @param {Object} record
     * @param {String} fieldsKey
     */
    setFieldsValues(record, fieldsKey = 'fields') {
      this[fieldsKey].setValues(record)
    },

    /**
     * Configure the form fields
     *
     * The function check if the initial fields are already configured, if yes, just updates the actual
     * fields values from the record
     *
     * This check is required because causing some issues if the fields are configured multiple times
     * the fill method is losen from the actual field object because the fill method is added in
     * FormField on beforeMount, also this improves memory usage e.q. it wont re-configure the fields
     *
     * @param  {Array} fields
     * @param  {Object} record
     * @param  {String} fieldsKey
     *
     * @return {Void}
     */
    setFieldsForUpdate(fields, record, fieldsKey = 'fields') {
      if (this.fieldsAreConfigured(fieldsKey)) {
        this.setFieldsValues(record, fieldsKey)
      } else {
        this.setFields(fields, fieldsKey)
        this[fieldsKey].populate(record)
      }
    },
  },
}
