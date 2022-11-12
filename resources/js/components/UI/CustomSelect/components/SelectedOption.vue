<template>
  <span
    :key="key"
    :class="[
      'dark:text-white',
      multiple
        ? 'mr-2 inline-flex rounded-md bg-neutral-100 px-2 dark:bg-neutral-500'
        : '',
      multiple ? (disabled ? 'text-neutral-800' : '') : '',
      disabled ? 'text-neutral-500' : '',
      searching ? 'opacity-60' : '',
    ]"
  >
    <slot name="option" v-bind="slotProps" :optionLabel="label"></slot>
    <deselect-button
      v-if="multiple"
      :deselect="deselect"
      :disabled="disabled === null ? false : disabled"
      :label="label"
      :option="option"
    />
  </span>
</template>
<script>
import DeselectButton from './DeselectButton'
export default {
  components: { DeselectButton },
  props: [
    'option',
    'getOptionLabel',
    'getOptionKey',
    'normalizeOptionForSlot',
    'multiple',
    'searching',
    'disabled',
    'deselect',
  ],
  computed: {
    label() {
      return this.getOptionLabel(this.option)
    },
    key() {
      return this.getOptionKey(this.option)
    },
    slotProps() {
      return this.normalizeOptionForSlot(this.option)
    },
  },
}
</script>
