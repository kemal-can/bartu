/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
import InteractsWithResourceFields from '@/mixins/InteractsWithResourceFields'
import Form from '@/components/Form/Form'
import { singularize } from '@/utils'

export default {
  mixins: [InteractsWithResourceFields],
  data: () => ({
    fieldsViewName: Innoclapps.config.fields.views.update,
    recordForm: new Form(),
    updateConfig: {
      resource: null,
      id: null,

      /**
       * Callback for before update record
       *
       * @type {Function}
       */
      beforeUpdateRecord: null,

      /**
       * Callback for after fields configured
       *
       * @type {Function}
       */
      afterFieldsConfigured: null,

      /**
       * Callback for before the record is set in store
       *
       * @type {Function}
       */
      beforeSetRecord: null,
    },
  }),
  computed: {
    /**
     * The current record
     *
     * @return {Object}
     */
    record() {
      return this.$store.state[this.updateConfig.resource].record || {}
    },

    /**
     * The resource name in singular
     *
     * @return {String}
     */
    resourceSingular() {
      if (this.updateConfig.resource) {
        return singularize(this.updateConfig.resource)
      }
    },

    /**
     * Get record Vuex action
     *
     * @return {String}
     */
    getAction() {
      return `${this.updateConfig.resource}/get`
    },

    /**
     * Get the update Vuex action for the record
     *
     * @return {String}
     */
    updateAction() {
      return `${this.updateConfig.resource}/update`
    },

    /**
     * Check whether the component is ready
     *
     * Checks if the record is fetched
     *
     * If fetched, then the data is ready
     *
     * @return {Boolean}
     */
    componentReady() {
      return Object.keys(this.record).length > 0 && this.fieldsConfigured
    },
  },
  methods: {
    /**
     * Fetch record from database via store
     *
     * @return {Promise}
     */
    fetchRecord() {
      return this.dispatchFetchAction()
    },

    /**
     * Dispatch the fetch action
     *
     * @return {Promise}
     */
    dispatchFetchAction() {
      return this.$store.dispatch(this.getAction, this.updateConfig.id)
    },

    /**
     * Update record field
     *
     * @param  {String} attribute
     * @param  {Mixed} value
     *
     * @return {Void}
     */
    updateField(attribute, value) {
      this.fields.find(attribute).handleChange(value)
      this.update()
    },

    /**
     * Update record fields
     *
     * @param  {Object} data
     *
     * @return {Void}
     */
    updateFields(data) {
      Object.keys(data).forEach(attribute =>
        this.fields.find(attribute).handleChange(data[attribute])
      )

      this.$nextTick(() => this.update())
    },

    /**
     * Update record via store
     *
     * @return {Void}
     */
    async update(e) {
      // Is modal, do not close the modal when a form is saved
      // as it may cause issue when the modal is route and no
      // events will be invoked as the modal will be closed
      if (e && e.target.classList.contains('modal')) {
        e.preventDefault()
      }

      if (this.updateConfig.beforeUpdateRecord) {
        this.updateConfig.beforeUpdateRecord(this.recordForm)
      }

      let record = await this.dispatchUpdateAction()

      Innoclapps.success(this.$t('resource.updated'))
      this.setRecord(record)

      // Update fields values  as well, e.q. some fields uses the ID's
      // e.q. on MorphMany fields in this case, the field will need to ID in case of a create
      this.setFieldsForUpdate(this.fields, record)
      Innoclapps.$emit(`${this.updateConfig.resource}-record-updated`, record)
    },

    /**
     * Dispatch the update action
     *
     * @returns {Promise}
     */
    dispatchUpdateAction() {
      return this.$store.dispatch(this.updateAction, {
        form: this.fillFormFields(this.recordForm),
        id: this.updateConfig.id,
      })
    },

    /**
     * Get the record fields
     *
     * @return {Promise}
     */
    getResourceUpdateFields() {
      return this.$store.dispatch('fields/getForResource', {
        resourceName: this.updateConfig.resource,
        view: this.fieldsViewName,
        resourceId: this.updateConfig.id,
      })
    },

    /**
     * Init record
     *
     * Get fields and record from database
     *
     * @param {Function|null} callback
     *
     * @return {Void}
     */
    initRecord(callback) {
      Promise.all([this.getResourceUpdateFields(), this.fetchRecord()]).then(
        values => {
          this.prepareComponent(values[0], values[1])
          if (callback) {
            callback(values[1])
          }
        }
      )
    },

    /**
     * Set the record data
     *
     * @param {mixed} record
     */
    setRecord(record) {
      if (this.updateConfig.beforeSetRecord) {
        this.updateConfig.beforeSetRecord(record)
      }

      this.setRecordInStore(record)
    },

    /**
     * Set the record in store
     *
     * @param {Object} record
     */
    setRecordInStore(record) {
      this.$store.commit(`${this.updateConfig.resource}/SET_RECORD`, record)
    },

    /**
     * Prepare the component data
     *
     * @param  {Array} fields
     * @param  {Object} record
     *
     * @return {Void}
     */
    prepareComponent(fields, record) {
      this.setRecord(record)

      this.setFieldsForUpdate(fields, record)

      if (this.updateConfig.afterFieldsConfigured) {
        this.$nextTick(this.updateConfig.afterFieldsConfigured)
      }
    },

    /**
     * Reset the record in store
     *
     * @return {Void}
     */
    resetRecord() {
      this.$store.commit(`${this.updateConfig.resource}/RESET_RECORD`)
    },

    /**
     * Boot the update for the record
     *
     * @return {Void}
     */
    bootRecordUpdate(config) {
      this.updateConfig = Object.assign({}, this.updateConfig, config)
      // If we don't reset the record before booting  it may cause issues with componentReady computed
      // The computed is checking the record keys from the store, in case of previous record set in the store
      this.resetRecord()
      this.initRecord()
    },
  },
}
