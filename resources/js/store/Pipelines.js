/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
import PersistentResourceCrud from '@/store/actions/PersistentResourceCrud'
import PersistentResourceMutations from '@/store/mutations/PersistentResourceMutations'
import PersistentResourceGetters from '@/store/getters/PersistentResourceGetters'
import orderBy from 'lodash/orderBy'

const state = {
  collection: [],
  dataFetched: false,
  endpoint: '/pipelines',
}

const mutations = {
  ...PersistentResourceMutations,

  /**
   * Set the resource records
   *
   * @param {Object} state
   * @param {Array} collection
   */
  SET(state, collection) {
    state.collection = orderBy(
      collection,
      ['user_display_order', 'is_primary', 'name'],
      ['asc', 'desc', 'asc']
    )
    state.dataFetched = true
  },
}

const getters = {
  ...PersistentResourceGetters,
}

const actions = {
  ...PersistentResourceCrud,
}

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations,
}
