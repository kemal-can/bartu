/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
import get from 'lodash/get'

export default {
  methods: {
    /**
     * Go back in history
     *
     * @return {Void}
     */
    goBack() {
      this.$router.go(-1)
    },

    /**
     * Get setting from store
     *
     * @param  {String} name
     *
     * @return {mixed}
     */
    setting(name) {
      return get(this.$store.state.settings, name)
    },

    /**
     * Set application page title
     *
     * @param {String} title
     */
    setPageTitle(title) {
      this.$store.commit('SET_PAGE_TITLE', title)
    },

    /**
     * Reset the store state
     *
     * @return {Void}
     */
    resetStoreState() {
      this.$store.commit('table/RESET_SETTINGS')
      this.$store.commit('fields/RESET')
    },

    /**
     * Clean object of given object
     *
     * @param  {Object} object
     *
     * @return {Object}
     */
    cleanObject(object) {
      return JSON.parse(JSON.stringify(object))
    },
  },
  computed: {
    /**
     * Get the current logged in user
     *
     * @return {Object}
     */
    currentUser() {
      return this.$store.getters['users/current']
    },

    /**
     * Indicates whether there is VoIP client configured
     *
     * @return {Boolean}
     */
    hasVoIPClient() {
      return Innoclapps.config.voip.client !== null
    },

    /**
     * Checks whether a Microsoft application is configured
     * The function uses the Innoclapps.config because it will check
     * whether Microsoft application credentials are configured in .env file
     * or via settings
     *
     * @return {Boolean}
     */
    isMicrosoftGraphConfigured() {
      return Boolean(Innoclapps.config.microsoft.client_id)
    },

    /**
     * Checks whether a Google project is configured
     * The function uses the Innoclapps.config because it will check
     * whether Google application credentials are configured in .env file
     * or via settings
     *
     * @return {Boolean}
     */
    isGoogleApiConfigured() {
      return Boolean(Innoclapps.config.google.client_id)
    },
  },
}
