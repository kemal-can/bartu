/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
import { randomString } from '@/utils'

export default {
  emits: ['hidden', 'shown', 'ok', 'update:visible', 'submit', 'keydown'],
  props: {
    form: { type: Boolean },
    visible: { type: Boolean, default: false }, // v-model
    title: String,
    description: String,
    busy: Boolean,

    id: {
      type: String,
      default() {
        return randomString(15)
      },
    },

    okTitle: { type: String, default: 'Ok' },
    okDisabled: { type: Boolean, default: false },
    okLoading: { type: Boolean, default: false },
    okVariant: { type: String, default: 'primary' },
    okSize: String,

    cancelTitle: { type: String, default: 'Cancel' },
    cancelVariant: { type: String, default: 'white' },
    cancelDisabled: { type: Boolean, default: false },
    cancelSize: String,

    hideFooter: Boolean,
    hideHeader: Boolean,
    hideHeaderClose: Boolean,
    initialFocus: { type: Object, default: null },
    staticBackdrop: Boolean, // prevent dialog close on esc and backdrop click
  },
  data: () => ({
    localVisible: false,
  }),
  computed: {
    computedOkDisabled() {
      return this.busy || this.okDisabled
    },
    computedCancelDisabled() {
      return this.busy || this.cancelDisabled
    },
  },
  methods: {
    dialogClosedEvent() {
      if (!this.staticBackdrop) {
        this.hide()
      }
    },
    show() {
      this.localVisible = true
      this.$emit('update:visible', true)
      this.$nextTick(() => this.$emit('shown'))
    },
    hide() {
      this.localVisible = false
      this.$emit('update:visible', false)
      this.$nextTick(() => this.$emit('hidden'))
    },
    handleOkClick(e) {
      this.$emit('ok', e)
    },
    globalHide(id) {
      if (id === this.id) {
        this.hide()
      }
    },
    globalShow(id) {
      if (id === this.id) {
        this.show()
      }
    },
  },
  watch: {
    visible: {
      handler: function (newVal) {
        newVal ? this.show() : this.hide()
      },
    },
  },
  mounted() {
    Innoclapps.$on('modal-hide', this.globalHide)
    Innoclapps.$on('modal-show', this.globalShow)

    if (this.visible) {
      this.show()
    }
  },
  unmounted() {
    Innoclapps.$off('modal-hide', this.globalHide)
    Innoclapps.$off('modal-show', this.globalShow)
  },
}
