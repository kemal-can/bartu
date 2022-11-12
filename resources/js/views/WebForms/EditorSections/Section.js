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
  inheritAttrs: false,
  emits: [
    'create-section-requested',
    'remove-section-requested',
    'update-section-requested',
  ],
  props: {
    index: {
      type: Number,
    },
    form: {
      type: Object,
      required: true,
    },
    section: {
      required: true,
      type: Object,
    },
  },
  data: () => ({
    editing: false,
    editorToolbarSections: 'bold italic underline link removeformat',
  }),
  methods: {
    /**
     * Request section remove
     *
     * @return {Void}
     */
    removeSection() {
      this.$emit('remove-section-requested')
    },

    /**
     * Request section update
     *
     * @param  {Object} data
     *
     * @return {Void}
     */
    updateSection(data) {
      this.$emit('update-section-requested', data)
    },

    /**
     * Request section create
     *
     * @param  {Object} data
     *
     * @return {Void}
     */
    createSection(data) {
      this.$emit('create-section-requested', data)
    },
  },
}
