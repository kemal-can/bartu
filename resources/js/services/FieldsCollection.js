/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
import castArray from 'lodash/castArray'
import FormAble from './FormableFields'
import findIndex from 'lodash/findIndex'
import each from 'lodash/each'

class Fields extends FormAble {
  constructor(fields) {
    super(castArray(fields))
  }

  /**
   * Find single field
   *
   * @param  {String} attribute
   *
   * @return {Object|null}
   */
  find(attribute) {
    let result

    this.every(field => {
      if (field.attribute == attribute) {
        result = field

        return false
      }

      return true
    })

    return result
  }

  /**
   * Determine if an field exists in the collection by attribute.
   *
   * @param  {[type]}  attribute [description]
   *
   * @return {Boolean}           [description]
   */
  has(attribute) {
    return Boolean(this.find(attribute))
  }

  /**
   * Update single field by attribute
   *
   * @param  {string} attribute
   * @param  {Object} data
   *
   * @return {this}
   */
  update(attribute, data) {
    let field = this.find(attribute)

    if (!field) {
      console.trace(
        'Cannot update field in collection as the field is not found. - ' +
          attribute
      )

      return this
    }

    each(data, (val, key) => (field[key] = val))

    return this
  }

  /**
   * Special every loop to loop through fields only
   *
   * @param  {Function} callback
   *
   * @return {this}
   */
  every(callback) {
    this.collection.every(field => {
      return callback(field)
    })

    return this
  }

  /**
   * Special foreach loop to loop through fields only
   *
   * @param  {Function} callback
   *
   * @return {this}
   */
  forEach(callback) {
    this.collection.forEach(field => callback(field))

    return this
  }

  /**
   * Push new field to collection
   *
   * @param  {Object} field
   *
   * @return {this}
   */
  push(field) {
    this.collection.push(field)

    return this
  }

  /**
   * Get fields keys/attributes
   *
   * @return {Array}
   */
  keys() {
    let result = []

    this.forEach(field => result.push(field.attribute))

    return result
  }

  /**
   * Remove field from the collection
   *
   * @param  {String} attribute
   *
   * @return {boolean}
   */
  remove(attribute) {
    const index = findIndex(this.collection, ['attribute', attribute])

    if (index != -1) {
      this.collection.splice(index, 1)

      return true
    }

    return false
  }

  /**
   * Get all fields
   *
   * @return {Array}
   */
  all() {
    return this.collection || []
  }
}

export default Fields
