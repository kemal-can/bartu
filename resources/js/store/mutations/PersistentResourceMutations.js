/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
import findIndex from 'lodash/findIndex'

export default {
  /**
   * Set the resource records
   *
   * @param {Object} state
   * @param {Array} collection
   */
  SET(state, collection) {
    state.collection = collection

    state.dataFetched = true
  },

  /**
   * Reset the resource records
   *
   * @param {Object} state
   */
  RESET(state) {
    state.collection = []

    state.dataFetched = false
  },

  /**
   * Add record to resource collection
   *
   * @param {Object} state
   * @param {Object} item
   */
  ADD(state, item) {
    state.collection.push(item)
  },

  /**
   * Update resource record
   *
   * @param {Object} state
   * @param {Object} payload
   */
  UPDATE(state, payload) {
    const index = findIndex(state.collection, ['id', Number(payload.id)])

    if (index !== -1) {
      state.collection[index] = payload.item
    }
  },

  /**
   * Remove resource record
   *
   * @param {Object} state
   * @param {Number} id
   */
  REMOVE(state, id) {
    const index = findIndex(state.collection, ['id', Number(id)])

    if (index != -1) {
      state.collection.splice(index, 1)
    }
  },
}
