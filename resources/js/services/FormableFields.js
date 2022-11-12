/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
class FormFields {
  /**
   * Initialize new FormableFields instance
   *
   * @param  {Array} fields
   *
   * @return {Void}
   */
  constructor(fields) {
    this.collection = fields
  }

  /**
   * Set field values
   *
   * E.q. used when the record is changed to populate the fields
   * with the new values via the field handleChange method
   *
   * @param  {Object} record
   *
   * @return {this}
   */
  setValues(record) {
    this.forEach(field =>
      field.handleChange(this.extractValueFromRecord(field, record))
    )

    return this
  }

  /**
   * Populate field values by object|record
   *
   * @param  {Object}   record
   *
   * @return {this}
   */
  populate(record) {
    this.forEach(
      field => (field.value = this.extractValueFromRecord(field, record))
    )

    return this
  }

  /**
   * Check whether the fields are dirty
   *
   * @return {Boolean}
   */
  dirty() {
    const noDirty = arr => arr.every(f => f.isDirty && f.isDirty() === false)

    return noDirty(this.collection) === false
  }

  /**
   * Fill field values by form
   *
   * @param  {Form} form
   *
   * @return {Form}
   */
  fill(form) {
    this.forEach(field => field.fill(form))

    return form
  }

  /**
   * @private
   *
   * Get the field value from the record
   *
   * @param  {Object} field
   * @return {mixed}
   */
  extractValueFromRecord(field, record) {
    if (field.belongsToRelation) {
      return record[field.belongsToRelation]
    } else if (field.morphManyRelationship) {
      return record[field.morphManyRelationship]
    } else {
      // Perhaps heading field, it has no attribute
      if (field.attribute) {
        return record[field.attribute]
      }
    }
  }
}

export default FormFields
