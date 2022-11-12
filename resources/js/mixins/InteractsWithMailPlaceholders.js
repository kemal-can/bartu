/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
import { mapState } from 'vuex'
export default {
  computed: {
    ...mapState({
      placeholders: state => state.fields.placeholders,
    }),
  },
  methods: {
    /**
     * Parse the resource placeholders
     * @param {resources} Array
     * @param {String}  content
     *
     * @return {Promise}
     */
    async parsePlaceholders(resources, content) {
      if (!content) {
        return content
      }

      let { data } = await Innoclapps.request().post('/placeholders', {
        resources: resources,
        content: content,
      })

      return data
    },
  },
  created() {
    this.$store.dispatch('fields/fetchPlaceholders')
  },
}
