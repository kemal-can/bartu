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
  types: [],
  endpoint: '/activities',
}

const mutations = {
  ...ResourceMutations,

  /**
   * Set the available activity types
   *
   * @param {Object} state
   * @param {Array} types
   */
  SET_TYPES(state, types) {
    state.types = types
  },
}

const actions = {
  ...ResourceCrud,
}

export default {
  namespaced: true,
  state,
  mutations,
  actions,
}
