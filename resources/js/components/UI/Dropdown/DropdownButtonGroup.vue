<template>
  <span
    :class="[
      'relative z-0 inline-flex shadow-sm',
      size != 'sm' ? 'rounded-md' : 'rounded',
    ]"
  >
    <i-button
      :variant="variant"
      :size="size"
      :type="type"
      :icon="icon"
      :icon-class="iconClass"
      :to="to"
      :rounded="false"
      @click="$emit('click', $event)"
      :disabled="disabled"
      :loading="loading"
      :class="[
        '-mr-px focus:z-10',
        size != 'sm' ? 'rounded-l-md' : 'rounded-l',
      ]"
    >
      <slot name="button-content">
        {{ text }}
      </slot>
    </i-button>

    <i-dropdown :placement="placement">
      <template #toggle>
        <i-button
          :variant="variant"
          :disabled="disabled"
          :size="size"
          :rounded="false"
          :class="['focus:z-10', size != 'sm' ? 'rounded-r-md' : 'rounded-r']"
        >
          <slot name="dropdown-toggle-content">
            <icon
              icon="ChevronDown"
              :class="size === 'sm' ? 'h-4 w-4' : 'h-5 w-5'"
            />
          </slot>
        </i-button>
      </template>
      <slot></slot>
    </i-dropdown>
  </span>
</template>
<script>
export default {
  name: 'IDropdownButtonGroup',
  emits: ['click'],
  props: {
    text: String,
    to: [Object, String],
    placement: { type: String, default: 'bottom-end' },
    size: { type: String, default: 'md' },
    disabled: { type: Boolean, default: false },
    loading: { type: Boolean, default: false },
    variant: { type: String, default: 'primary' },
    icon: { type: String },
    type: { type: String, default: 'button' },
    iconClass: [String, Array, Object],
  },
}
</script>
