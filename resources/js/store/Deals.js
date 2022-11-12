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
import ResourceCrud from '@/store/actions/ResourceCrud'

const state = {
  record: {},
  lostReasons: [],
  endpoint: '/deals',
}

const mutations = {
  ...ResourceMutations,

  /**
   * Set the available lost reasons
   *
   * @param {Object} state
   * @param {Array} types
   */
  SET_LOST_REASONS(state, types) {
    state.lostReasons = types
  },
}

const actions = {
  ...ResourceCrud,

  /**
   * Initiate deal preview
   *
   * @param  {Function} options.commit
   * @param  {Number} id
   *
   * @return {Void}
   */
  preview({ commit }, id) {
    commit(
      'recordPreview/SET_PREVIEW_RESOURCE',
      {
        resourceName: 'deals',
        resourceId: id,
      },
      { root: true }
    )
  },

  /**
   * Deal updated helper function handler
   *
   * @param  {Function} options.commit
   * @param  {Object} options.rootState
   * @param  {Object} options.deal
   * @param  {Boolean} options.isFloating
   *
   * @return {Void}
   */
  updateRecordWhenViewing({ commit, rootState }, { deal, isFloating }) {
    if (!isFloating) {
      commit('SET_RECORD', deal)
    } else {
      commit('recordPreview/SET_RECORD', deal, { root: true })

      // When previewing the same record in profile view
      // update the main store to reflect the updates as well
      if (
        rootState.recordPreview.record.id == deal.id &&
        rootState.recordPreview.viaResource === 'deals'
      ) {
        commit('SET_RECORD', deal)
      }
    }

    // E.q. table refresh, activities reload
    Innoclapps.$emit('deals-record-updated', deal)
  },
}

export default {
  namespaced: true,
  state,
  actions,
  mutations,
}
