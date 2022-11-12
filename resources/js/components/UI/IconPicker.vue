<template>
  <div class="flex flex-wrap">
    <div :key="icon[valueField]" v-for="icon in icons">
      <i-button
        :disabled="disabled"
        :class="[
          'mr-1.5 mb-1',
          {
            'bg-neutral-300 dark:bg-neutral-600 sm:bg-neutral-200':
              selected === icon[valueField],
          },
        ]"
        :size="size"
        @click="pickIcon(icon[valueField])"
        :icon="icon.icon"
        variant="white"
        v-i-tooltip="icon.tooltip"
      />
    </div>
  </div>
</template>
<script>
import isObject from 'lodash/isObject'

export default {
  emits: ['update:modelValue', 'change'],
  props: {
    modelValue: {},
    label: String,
    disabled: Boolean,
    size: { type: String, default: 'md' },
    valueField: { default: 'icon' },
    icons: {
      type: Array,
      default: function () {
        return [
          {
            icon: 'Mail',
            tooltip: null,
          },
          {
            icon: 'PencilAlt',
            tooltip: null,
          },
          {
            icon: 'OfficeBuilding',
            tooltip: null,
          },
          {
            icon: 'Phone',
            tooltip: null,
          },
          {
            icon: 'Calendar',
            tooltip: null,
          },
          {
            icon: 'Collection',
            tooltip: null,
          },
          {
            icon: 'Bell',
            tooltip: null,
          },
          {
            icon: 'AtSymbol',
            tooltip: null,
          },
          {
            icon: 'Briefcase',
            tooltip: null,
          },
          {
            icon: 'Chat',
            tooltip: null,
          },
          {
            icon: 'CheckCircle',
            tooltip: null,
          },
          {
            icon: 'BookOpen',
            tooltip: null,
          },
          {
            icon: 'Camera',
            tooltip: null,
          },
          {
            icon: 'Truck',
            tooltip: null,
          },
          {
            icon: 'Folder',
            tooltip: null,
          },
          {
            icon: 'DeviceMobile',
            tooltip: null,
          },
          {
            icon: 'Users',
            tooltip: null,
          },
          {
            icon: 'ChatAlt',
            tooltip: null,
          },
          {
            icon: 'Clock',
            tooltip: null,
          },
          {
            icon: 'ShieldExclamation',
            tooltip: null,
          },
        ]
      },
    },
  },
  watch: {
    modelValue: function (newVal) {
      if (isObject(newVal)) {
        this.selected = newVal[this.valueField]
      } else {
        this.selected = newVal
      }

      this.$emit('update:modelValue', this.selected)
      this.$emit('change', this.selected)
    },
  },
  data() {
    return {
      selected: null,
    }
  },
  methods: {
    pickIcon(icon) {
      this.selected = icon
      this.$emit('update:modelValue', icon)
      this.$emit('change', icon)
    },
  },
}
</script>
