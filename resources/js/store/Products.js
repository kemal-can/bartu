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
  active: [],
  endpoint: '/products',
}

const mutations = {
  ...ResourceMutations,

  /**
   * Set the active products state
   *
   * @param {Object} state
   * @param {Array} products
   */
  SET_ACTIVE_PRODUCTS(state, products) {
    state.active = products
  },
}

const actions = {
  ...ResourceCrud,

  /**
   * Retrieve the active products
   *
   * @param  {Function} options.commit
   * @param  {Object} options.state
   *
   * @return {Void}
   */
  fetchActive({ commit, state }) {
    Innoclapps.request()
      .get('/products/search?q=1&search_fields=is_active:=')
      .then(({ data }) => {
        commit('SET_ACTIVE_PRODUCTS', data)
      })
  },
}

export default {
  namespaced: true,
  state,
  mutations,
  actions,
}
