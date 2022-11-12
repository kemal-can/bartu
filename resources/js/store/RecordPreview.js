/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
import ResourceMutations from '@/store/mutations/ResourceMutations'
import store from '@/store'

const state = {
  record: {},
  viaResource: null,
  resourceName: null,
  resourceId: null,
}

const mutations = {
  ...ResourceMutations,
  /**
   * Set the preview resource data
   *
   * @param {Object} state
   * @param {Object} data
   */
  SET_PREVIEW_RESOURCE(state, data) {
    state.resourceName = data.resourceName
    state.resourceId = data.resourceId
  },

  /**
   * Set the via resource parameter
   *
   * @param {Object} state
   * @param {String|Null} resourceName
   */
  SET_VIA_RESOURCE(state, resourceName) {
    state.viaResource = resourceName
  },

  /**
   * Reset the record previe
   *
   * @param {Object} state
   */
  RESET_PREVIEW(state) {
    state.resourceName = null
    state.resourceId = null
    state.viaResource = null
    state.record = {}
  },

  /**
   * Push record relationship
   *
   * @param {Object} state
   * @param {Object} data
   */
  ADD_RECORD_HAS_MANY_RELATIONSHIP(state, data) {
    ResourceMutations.ADD_RECORD_HAS_MANY_RELATIONSHIP(state, data)
    if (state.viaResource) {
      store.commit(
        state.viaResource + '/ADD_RECORD_HAS_MANY_RELATIONSHIP',
        data
      )
    }
  },

  /**
   * Update record relationship
   *
   * @param {Object} state
   * @param {Object} data
   */
  UPDATE_RECORD_HAS_MANY_RELATIONSHIP(state, data) {
    ResourceMutations.UPDATE_RECORD_HAS_MANY_RELATIONSHIP(state, data)
    if (state.viaResource) {
      store.commit(
        state.viaResource + '/UPDATE_RECORD_HAS_MANY_RELATIONSHIP',
        data
      )
    }
  },

  /**
   * Remove record relationship
   *
   * @param {Object} state
   * @param {Object} data
   */
  REMOVE_RECORD_HAS_MANY_RELATIONSHIP(state, data) {
    ResourceMutations.REMOVE_RECORD_HAS_MANY_RELATIONSHIP(state, data)
    if (state.viaResource) {
      store.commit(
        state.viaResource + '/REMOVE_RECORD_HAS_MANY_RELATIONSHIP',
        data
      )
    }
  },

  /**
   * Add record relation sub relationship
   *
   * @param {Object} state
   * @param {Object} data
   */
  ADD_RECORD_HAS_MANY_SUB_RELATION(state, data) {
    ResourceMutations.ADD_RECORD_HAS_MANY_SUB_RELATION(state, data)
    if (state.viaResource) {
      store.commit(
        state.viaResource + '/ADD_RECORD_HAS_MANY_SUB_RELATION',
        data
      )
    }
  },

  /**
   * Remove record relation sub relationship
   *
   * @param {Object} state
   * @param {Object} data
   */
  REMOVE_RECORD_HAS_MANY_SUB_RELATION(state, data) {
    ResourceMutations.REMOVE_RECORD_HAS_MANY_SUB_RELATION(state, data)
    if (state.viaResource) {
      store.commit(
        state.viaResource + '/REMOVE_RECORD_HAS_MANY_SUB_RELATION',
        data
      )
    }
  },
}

export default {
  namespaced: true,
  state,
  mutations,
}
