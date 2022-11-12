<template>
  <a
    :href="href || '#'"
    @click="handleClickEvent"
    :class="[
      'group block px-4 py-2 text-sm',
      { 'group flex items-center': icon },
      { 'justify-between': icon && prependIcon },
      active
        ? 'bg-primary-100 text-primary-700 hover:bg-primary-200 hover:text-primary-800'
        : 'text-neutral-700 hover:bg-neutral-50 hover:text-neutral-900 dark:text-neutral-200 dark:hover:bg-neutral-600 dark:hover:text-neutral-100',
      disabled ? 'pointer-events-none opacity-50' : null,
    ]"
    :tabindex="disabled ? '-1' : null"
    :aria-disabled="disabled ? 'true' : null"
  >
    <icon
      v-if="icon && !prependIcon"
      :icon="icon"
      :class="[
        'mr-2 h-5 w-5 shrink-0',
        !active
          ? 'text-neutral-500 group-hover:text-neutral-600 dark:text-neutral-300 dark:group-hover:text-neutral-100'
          : '',
      ]"
    />

    <slot>{{ text }}</slot>

    <icon
      v-if="icon && prependIcon"
      :icon="icon"
      :class="[
        'ml-2 h-5 w-5 shrink-0',
        !active
          ? 'text-neutral-500 group-hover:text-neutral-600 dark:text-neutral-300 dark:group-hover:text-neutral-100'
          : '',
      ]"
    />
  </a>
</template>
<script>
///	The <i-dropdown-item> is typically used to create a navigation link inside your menu. Use either the href prop or the to prop (for router link support) to generate the appropriate navigation link. If neither href nor to are provided, a standard <a> link will be generated with an href of # (with an event handler that will prevent scroll to top behaviour by preventing the default link action).
export default {
  emits: ['click'],
  // PopperMethods
  inject: ['hide'],
  props: {
    active: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    icon: String,
    prependIcon: Boolean,
    href: String,
    text: String,
    to: [Object, String],
  },
  methods: {
    handleClickEvent(e) {
      // Is it needed?
      if (this.disabled) {
        return
      }

      if ((!this.to && !this.href) || this.to) {
        e.preventDefault()
      }

      if (this.to) {
        this.$router.push(this.to)
      }

      this.$emit('click', e)

      if (!this.href) {
        this.hide()
      }
    },
  },
}
</script>
