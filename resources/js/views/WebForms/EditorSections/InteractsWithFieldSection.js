/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
import find from 'lodash/find'
import map from 'lodash/map'
import orderBy from 'lodash/orderBy'
import { randomString } from '@/utils'

export default {
  props: {
    companiesFields: {
      required: true,
    },
    contactsFields: {
      required: true,
    },
    dealsFields: {
      required: true,
    },
    availableResources: {
      required: true,
    },
  },
  data: () => ({
    field: null,
    fieldLabel: null,
    resourceName: null,
    isRequired: false,
  }),
  computed: {
    /**
     * Indicates whether the selected field must be required
     *
     * @return {Boolean}
     */
    fieldMustBeRequired() {
      return this.field && this.field.isRequired && this.field.primary
    },

    /**
     * Get the available select fields
     *
     * @return {Array}
     */
    availableFields() {
      return orderBy(
        map(this[this.resourceName + 'Fields'], field => {
          field.disabled = this.isFieldAlreadySelected(field)

          return field
        }),
        ['disabled', 'order'],
        ['desc', 'asc']
      )
    },
  },
  methods: {
    /**
     * Generate new field request attribute
     *
     * @return {String}
     */
    generateRequestAttribute() {
      return randomString(25)
    },

    /**
     * Handle the field changed event
     *
     * @param  {Object|null} field
     *
     * @return {Void}
     */
    handleFieldChanged(field) {
      if (field) {
        this.fieldLabel = field.label
        this.isRequired = field.isRequired
      } else {
        this.fieldLabel = ''
        this.isRequired = false
      }
    },

    /**
     * Check whether the given field is already selected for the resource
     *
     * @param  {Object}  field
     *
     * @return {Boolean}
     */
    isFieldAlreadySelected(field) {
      return !find(this.form.sections, {
        attribute: field.attribute,
        resourceName: this.resourceName,
      })
    },
  },
}
