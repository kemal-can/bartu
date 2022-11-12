/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
const state = {
  outcomes: [],
}

const mutations = {
  SET_OUTCOMES(state, outcomes) {
    state.outcomes = outcomes
  },
}

export default {
  namespaced: true,
  state,
  mutations,
}
