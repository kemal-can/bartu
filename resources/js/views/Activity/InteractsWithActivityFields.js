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

export default {
  mixins: [InteractsWithResourceFields],
  methods: {
    /**
     * Get the activity create fields
     *
     * @param {Object} params
     *
     * @return {Array}
     */
    getActivityCreateFields(params = {}) {
      return this.$store.dispatch('fields/getForResource', {
        resourceName: Innoclapps.config.fields.groups.activities,
        view: Innoclapps.config.fields.views.create,
        ...params,
      })
    },

    /**
     * Get the activity update fields
     *
     * @param {Number} activityId
     * @param {Objects} params
     *
     * @return {Array}
     */
    getActivityUpdateFields(activityId, params = {}) {
      return this.$store.dispatch('fields/getForResource', {
        resourceName: config.fields.groups.activities,
        view: config.fields.views.update,
        resourceId: activityId,
        ...params,
      })
    },

    /**
     * Get the activity create fields when activity is shown via related resource
     *
     * @return {Array}
     */
    async getActivityCreateFieldsForResource() {
      let fields = await this.getActivityCreateFields({
        viaResource: this.resourceName,
        viaResourceid: this.resourceRecord.id,
      })

      return fields
    },

    /**
     * Get the activity update fields when activity is shown via related resource
     *
     * @param {Number} activityId
     *
     * @return {Array}
     */
    async getActivityUpdateFieldsForResource(activityId) {
      let fields = await this.getActivityUpdateFields(activityId, {
        viaResource: this.resourceName,
        viaResourceid: this.resourceRecord.id,
      })

      return fields
    },
  },
}
