<template>
  <i-dropdown v-bind="$attrs" :full="false">
    <template #toggle="{ disabled, noCaret }">
      <slot :item="modelValue" :label="toggleLabel">
        <button
          type="button"
          :disabled="disabled"
          :class="toggleClass"
          class="flex w-full items-center rounded-md px-1 text-sm text-neutral-800 hover:text-neutral-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 focus:ring-offset-neutral-300 disabled:pointer-events-none disabled:opacity-60 dark:text-neutral-200 dark:hover:text-neutral-300"
        >
          <slot name="label" :item="modelValue" :label="toggleLabel">
            {{ toggleLabel }}
          </slot>
          <icon icon="ChevronDown" v-if="!noCaret" class="ml-1 h-5 w-5" />
        </button>
      </slot>
    </template>
    <div class="overflow-y-auto py-1" :style="{ maxHeight: maxHeight }">
      <i-dropdown-item
        v-for="item in items"
        @click="handleClickEvent(item)"
        :class="
          typeof item == 'object' && item.hasOwnProperty('class') && item.class
            ? item.class
            : null
        "
        :icon="typeof item == 'object' && item.icon ? item.icon : null"
        :prepend-icon="
          typeof item == 'object' && item.hasOwnProperty('prependIcon')
            ? item.prependIcon
            : false
        "
        :disabled="
          typeof item == 'object' && item.disabled === true ? true : false
        "
        :key="typeof item == 'object' ? item[valueKey] : item"
      >
        {{ typeof item == 'object' ? item[labelKey] : item }}
      </i-dropdown-item>
    </div>
  </i-dropdown>
</template>
<script>
import find from 'lodash/find'
import isObject from 'lodash/isObject'
import isEqual from 'lodash/isEqual'
export default {
  inheritAttrs: false,
  emits: ['change', 'update:modelValue'],
  props: {
    label: String,
    toggleClass: [Array, Object, String],
    modelValue: { required: true },
    items: { type: Array, default: () => [] },
    maxHeight: { type: String, default: '500px' },
    // If values are object
    labelKey: { type: String, default: 'label' },
    valueKey: { type: String, default: 'value' },
  },
  computed: {
    /**
     * Get the label for the dropdown select
     *
     * @return {String}
     */
    toggleLabel() {
      if (this.label) {
        return this.label
      }

      if (isObject(this.modelValue)) {
        return this.modelValue[this.labelKey]
      } else if (
        typeof this.modelValue === 'string' ||
        typeof this.modelValue === 'number' ||
        this.modelValue === null
      ) {
        if (isObject(this.items[0])) {
          let item = find(this.items, [this.valueKey, this.modelValue])
          return item ? item[this.labelKey] : ''
        }

        return this.items.find(item => item == this.modelValue) || ''
      }

      return this.modelValue
    },
  },
  methods: {
    /**
     * Handle dropdown clicked event
     *
     * @param  {mixed} active
     *
     * @return {Void}
     */
    handleClickEvent(active) {
      // Updates the v-model value
      this.$emit('update:modelValue', active)

      // Custom change event
      if (!isEqual(active, this.modelValue)) {
        this.$emit('change', active)
      }
    },
  },
}
</script>
