/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
export default {
  data: () => ({
    options: [],
  }),
  methods: {
    /**
     * Set the options
     *
     * @param {Array} options
     * @param {Function} callback
     */
    setOptions(options, callback) {
      this.options = options

      if (callback) {
        callback(this.options)
      }
    },

    /**
     * Get option from object that may hold options or options settings
     *
     * @param  {Object} object
     *
     * @return {Promise}
     */
    async getOptions(object) {
      return object.options
    },
  },
}
