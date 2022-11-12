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

const state = {
  collection: [],
  dataFetched: false,
  endpoint: '/forms',
}

const mutations = {
  ...PersistentResourceMutations,
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
