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
  endpoint: '/contacts',
}

const mutations = {
  ...ResourceMutations,
}

const actions = {
  ...ResourceCrud,
  preview({ commit }, id) {
    commit(
      'recordPreview/SET_PREVIEW_RESOURCE',
      {
        resourceName: 'contacts',
        resourceId: id,
      },
      { root: true }
    )
  },
}

export default {
  namespaced: true,
  state,
  mutations,
  actions,
}
