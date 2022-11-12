<template>
  <div
    :class="['p-4', colors[variant].bg, { 'rounded-md': rounded }]"
    v-show="computedShow"
  >
    <div :class="['flex', wrapperClass]">
      <div class="shrink-0">
        <icon
          :icon="icon || iconMap[variant]"
          :class="colors[variant].icon"
          class="h-5 w-5"
        />
      </div>
      <div class="ml-3">
        <h3
          class="text-sm font-medium"
          :class="colors[variant].heading"
          v-show="heading"
        >
          {{ heading }}
        </h3>
        <div
          class="text-sm"
          :class="[colors[variant].text, { 'mt-2': heading }]"
        >
          <slot></slot>
        </div>
      </div>
      <div class="ml-auto pl-3" v-if="dismissible">
        <div class="-mx-1.5 -my-1.5">
          <button
            type="button"
            @click="dismiss"
            class="inline-flex p-1.5 text-neutral-500 hover:opacity-50 focus:outline-none"
          >
            <icon icon="X" class="h-5 w-5" />
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
export default {
  emits: ['dismissed'],
  name: 'IAlert',
  props: {
    heading: String,
    show: { type: Boolean, default: true },
    dismissible: { type: Boolean, default: false },
    rounded: { type: Boolean, default: true },
    icon: String,
    wrapperClass: [Array, Object, String],
    variant: {
      default: 'info',
      type: String,
      validator(value) {
        return ['success', 'info', 'warning', 'danger'].includes(value)
      },
    },
  },
  data: () => ({
    dismissed: false,
    iconMap: {
      warning: 'Exclamation',
      danger: 'XCircle',
      success: 'CheckCircle',
      info: 'InformationCircle',
    },
    colors: {
      warning: {
        bg: 'bg-warning-50',
        text: 'text-warning-700',
        heading: 'text-warning-800',
        icon: 'text-warning-400',
      },
      danger: {
        bg: 'bg-danger-50',
        text: 'text-danger-700',
        heading: 'text-danger-800',
        icon: 'text-danger-400',
      },
      success: {
        bg: 'bg-success-50',
        text: 'text-success-700',
        heading: 'text-success-800',
        icon: 'text-success-400',
      },
      info: {
        bg: 'bg-info-50',
        text: 'text-info-700',
        heading: 'text-info-800',
        icon: 'text-info-400',
      },
    },
  }),
  computed: {
    computedShow() {
      if (this.dismissed) {
        return false
      }

      return this.show
    },
  },
  methods: {
    dismiss() {
      this.dismissed = true
      this.$emit('dismissed')
    },
  },
}
</script>
