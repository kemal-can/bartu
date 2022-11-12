/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
import { singularize } from '@/utils'

export default {
  data: () => ({
    viewConfig: {
      resource: null,
      id: null,
    },
  }),

  watch: {
    'record.display_name': function (newVal) {
      if (newVal) {
        this.setPageTitle(newVal)
      }
    },
  },

  computed: {
    /**
     * The resource name in singular
     *
     * @return {String}
     */
    resourceSingular() {
      return singularize(this.resourceName)
    },

    /**
     * The current record
     *
     * @return {Object}
     */
    record() {
      return this.$store.state[this.resourceName].record || {}
    },

    /**
     * Indicates whether the component is ready
     */
    componentReady() {
      return Object.keys(this.record).length > 0
    },

    /**
     * Action id
     *
     * @return {Number|Array}
     */
    actionId() {
      return this.record.id || []
    },

    /**
     * Get the record actions
     *
     * @return {Array}
     */
    actions() {
      return this.record.actions || []
    },
  },
  methods: {
    /**
     * Update the fields values
     *
     * @param  {Object} record
     *
     * @return {Void}
     */
    updateFieldsValues(record) {
      // Perhaps the sidebar section item is not enabled?
      if (!this.$refs['section-details'] || !this.$refs['section-details'][0]) {
        return
      }

      this.$refs['section-details'][0].setFieldsValues(record)
    },

    /**
     * Boot the update for the record
     *
     * @return {Void}
     */
    bootView(config) {
      this.viewConfig = Object.assign({}, this.viewConfig, config)
      // If we don't reset the record before booting  it may cause issues with componentReady computed
      // The computed is checking the record keys from the store, in case of previous record set in the store
      this.$store.commit(`${this.resourceName}/RESET_RECORD`)

      if (this.$router[this.resourceSingular]) {
        // $router.record (e.q. record is "company") is used when redirect to the route with cached record
        // Helps when creating new record but the user is not authorized to view the record
        // In this case, to stop showing 403 immediately we show the user the record but after he navigated from
        // the record view or tried to update, it will show the 403 error
        this.setRecordInStore(this.$router[this.resourceSingular])

        delete this.$router[this.resourceSingular]
      } else {
        this.fetchRecord()
      }
    },

    /**
     * Fetch the record for view
     *
     * @return {Promise}
     */
    async fetchRecord() {
      let record = await this.$store.dispatch(
        `${this.resourceName}/get`,
        this.viewConfig.id
      )

      this.setRecordInStore(record)

      return record
    },

    /**
     * Set the record in store
     *
     * @param {Object} record
     */
    setRecordInStore(record) {
      this.$store.commit(`${this.resourceName}/SET_RECORD`, record)
    },
  },
}
