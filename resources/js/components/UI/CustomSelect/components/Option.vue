<template>
  <li
    role="option"
    :id="`cs${uid}__option-${index}`"
    :aria-selected="index === typeAheadPointer ? true : null"
  >
    <i-dropdown-item
      :disabled="!isSelectable"
      @click="select(option)"
      @mouseover="isSelectable ? $emit('typeAheadPointer', index) : null"
      :active="isSelected || index === typeAheadPointer"
      :key="key"
    >
      <slot name="option" v-bind="slotProps" :optionLabel="label"></slot>
    </i-dropdown-item>
  </li>
</template>
<script>
export default {
  emits: ['typeAheadPointer'],
  props: [
    'option',
    'getOptionLabel',
    'getOptionKey',
    'index',
    'typeAheadPointer',
    'uid',
    'selectable',
    'select',
    'isOptionSelected',
    'normalizeOptionForSlot',
  ],
  computed: {
    isSelectable() {
      return this.selectable(this.option)
    },
    isSelected() {
      return this.isOptionSelected(this.option)
    },
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
