/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
const qs = require('qs')
export default {
  /**
   * Fetch records from storage
   *
   * @param  {Function} options.commit
   * @param  {Object} options.state
   * @param  {Object} options
   *
   * @return {Array}
   */
  async fetch({ state }, options = {}) {
    let { data: records } = await Innoclapps.request().get(
      options.endpoint ? options.endpoint : state.endpoint,
      options
    )

    return records
  },

  /**
   * Get single record from database
   *
   * @param  {Function} options.commit
   * @param  {Object} options.state
   * @param  {Number|Object} options
   *
   * @return {Object}
   */
  async get({ state }, options) {
    const id = typeof options === 'object' ? options.id : options
    let { data: records } = await Innoclapps.request().get(
      `${state.endpoint}/${id}${
        options.queryString ? '?' + qs.stringify(options.queryString) : ''
      }`
    )

    return records
  },

  /**
   * Store a record
   *
   * @param  {Function} options.commit
   * @param  {Object} options.state
   * @param  {Object} form
   *
   * @return {Object}
   */
  async store({ state }, form) {
    let record = await form.post(state.endpoint)

    return record
  },

  /**
   * Update a record
   *
   * @param  {Function} options.commit
   * @param  {Object} options.state
   * @param  {Object} payload
   *
   * @return {Object}
   */
  async update({ state }, payload) {
    let record = await payload.form.put(
      `${state.endpoint}/${payload.id}${
        payload.queryString ? '?' + qs.stringify(payload.queryString) : ''
      }`
    )

    return record
  },

  /**
   * Delete a record
   *
   * @param  {Function} options.commit
   * @param  {Object} options.state
   * @param  {Number} id
   *
   * @return {mixed}
   */
  async destroy({ state }, id) {
    const dialog =
      await Innoclapps.app.config.globalProperties.$dialog.confirm()

    let { data } = await Innoclapps.request().delete(`${state.endpoint}/${id}`)

    return data
  },
}
