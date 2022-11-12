<template>
  <SwitchGroup>
    <div class="flex items-center">
      <SwitchLabel
        class="mr-4 text-sm text-neutral-800 dark:text-neutral-100"
        v-if="label"
        >{{ label }}</SwitchLabel
      >
      <Switch
        v-model="enabled"
        :class="[
          enabled ? 'bg-primary-600' : 'bg-neutral-200 dark:bg-neutral-500',
          disabled ? 'pointer-events-none opacity-60' : '',
          'relative inline-flex h-5 w-10 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2',
        ]"
      >
        <!-- Circle -->
        <span
          aria-hidden="true"
          :class="[
            enabled
              ? 'translate-x-5 dark:bg-neutral-300'
              : 'translate-x-0 dark:bg-neutral-400',
            'pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
          ]"
        />
      </Switch>
    </div>
  </SwitchGroup>
</template>
<script>
import { Switch, SwitchGroup, SwitchLabel } from '@headlessui/vue'
export default {
  emits: ['update:modelValue', 'change'],
  components: {
    Switch,
    SwitchLabel,
    SwitchGroup,
  },
  props: {
    label: String,
    modelValue: {},
    disabled: {
      type: Boolean,
      default: false,
    },
    value: {
      default: true,
    },
    uncheckedValue: {
      default: false,
    },
    disabled: Boolean,
  },
  data: () => ({
    enabled: false,
  }),
  watch: {
    enabled: function (enabled) {
      const value = enabled === true ? this.value : this.uncheckedValue
      if (value != this.modelValue) {
        this.$emit('update:modelValue', value)
        this.$emit('change', value)
      }
    },
    modelValue: {
      immediate: true,
      handler: function (modelValue) {
        this.enabled = modelValue == this.value
      },
    },
  },
}
</script>
