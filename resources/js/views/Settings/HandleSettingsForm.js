/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
import Form from '@/components/Form/Form'

export default {
  data: () => ({
    componentReady: false,
    originalSettings: {},
    form: {},
  }),
  methods: {
    /**
     * Set the form settings
     */
    fetchAndSetSettings() {
      Innoclapps.request()
        .get('/settings')
        .then(({ data }) => {
          this.form = new Form(data)
          this.originalSettings = data
          this.componentReady = true
        })
    },

    /**
     * Save the settings
     *
     * @param  {Function} callback
     *
     * @return {Void}
     */
    saveSettings(callback) {
      this.form.post('settings').then(settings => {
        Innoclapps.success(this.$t('settings.updated'))

        if (typeof callback === 'function') {
          callback(this.form, settings)
        }

        this.form.keys().forEach(key => {
          if (Innoclapps.config.options.hasOwnProperty(key)) {
            Innoclapps.config.options[key] = this.form[key]
          }
        })
      })
    },

    /**
     * Save the settings form
     *
     * @param  {Function} callback
     *
     * @return {Promise}
     */
    async submit(callback) {
      // Wait till v-model update e.q. on checkboxes like in company field @change="submit"
      await this.$nextTick()

      this.saveSettings(callback)
    },
  },
  created() {
    this.fetchAndSetSettings()
  },
}
