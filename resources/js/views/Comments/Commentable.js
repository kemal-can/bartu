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
  props: {
    commentableType: {
      required: true,
      type: String,
    },
    commentableId: {
      required: true,
      type: Number,
    },
    viaResource: {
      type: String,
    },
  },
  computed: {
    /**
     * The resource record the comments are being added
     *
     * @return {Object}
     */
    resourceRecord() {
      if (!this.viaResource) {
        return {}
      }

      return this.$store.state[this.viaResource].record
    },
  },
}
