<script>
const focusAbleInputs = [
  'date',
  'datetime-local',
  'email',
  'file',
  'month',
  'number',
  'password',
  'range',
  'search',
  'tel',
  'text',
  'time',
  'url',
  'week',
]
import FieldsGenerator from '@/components/Fields/FieldsGenerator'
export default {
  name: 'focus-able-fields-generator',
  extends: FieldsGenerator,
  props: {
    preventFocus: { type: Boolean, default: false },
  },
  data: () => ({
    clearTimeout: null,
  }),
  methods: {
    /**
     * Execute action on fields mounted
     *
     * @return {Void}
     */
    fieldsMounted() {
      if (this.preventFocus) {
        return
      }

      this.focusFirst()
    },

    /**
     * Focus the first available focusable field
     *
     * @return {Void}
     */
    focusFirst() {
      const input = this.$refs.wrapper.querySelector(
        '.field-col:first-child input'
      )

      if (input && focusAbleInputs.indexOf(input.getAttribute('type')) > -1) {
        input.focus()
      }
    },
  },
  mounted() {
    this.clearTimeout = setTimeout(() => {
      this.fieldsMounted()
    }, 600)
  },
  beforeUnmount() {
    this.clearTimeout && clearTimeout(this.clearTimeout)
  },
}
</script>
