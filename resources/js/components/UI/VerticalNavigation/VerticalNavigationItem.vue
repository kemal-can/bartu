<template>
  <div>
    <a
      :href="href"
      @click.prevent="navigate"
      :class="[
        isActive
          ? 'active bg-neutral-50 text-primary-600 dark:bg-neutral-600 dark:text-primary-300'
          : 'text-neutral-900 hover:bg-neutral-50 hover:text-neutral-900 dark:text-neutral-200 dark:hover:bg-neutral-600 dark:hover:text-neutral-200',
        'group flex items-center rounded-md px-3 py-2 text-sm font-medium',
        linkClass,
      ]"
    >
      <slot name="icon">
        <icon
          v-if="icon"
          :class="[
            isActive
              ? 'text-primary-500 dark:text-primary-300'
              : 'text-neutral-400 group-hover:text-neutral-500 dark:text-neutral-300 dark:group-hover:text-neutral-200',
            '-ml-1 mr-3 h-6 w-6 shrink-0',
            iconClass,
          ]"
          :icon="icon"
        />
      </slot>
      <slot name="title" :title="title">
        <span class="truncate">
          {{ title }}
        </span>
      </slot>
      <icon
        v-if="!fixed && hasChildrens"
        icon="ChevronDown"
        :class="[
          isActive
            ? 'text-neutral-500'
            : 'text-neutral-400 group-hover:text-neutral-500',
          'ml-auto mr-1 h-6 w-6 shrink-0',
        ]"
      />
    </a>

    <div :id="itemId" ref="children" v-show="collapseVisible" class="mt-1 ml-5">
      <slot></slot>
    </div>
  </div>
</template>
<script>
import { randomString } from '@/utils'
import startsWith from 'lodash/startsWith'
export default {
  name: 'vertical-navigation-item',
  data: () => ({
    itemId: randomString(6),
    collapseVisible: false,
  }),
  props: {
    to: null,
    href: { type: String, default: '#' },
    fixed: { type: Boolean, default: false },
    linkClass: [Array, Object, String],
    title: String,
    icon: String,
    iconClass: [Array, Object, String],
  },
  watch: {
    $route: function (newVal, oldVal) {
      this.$nextTick(() => {
        if (!this.hasActiveChildren() && !this.fixed) {
          this.collapseVisible = false
        }
      })
    },
  },
  computed: {
    /**
     * Get the menu child items
     *
     * @return {Array|null}
     */
    childItems() {
      if (!this.$slots.default) {
        return []
      }

      return this.$slots.default()
    },

    /**
     * Indicates whether the item has children for dropdown
     *
     * @return {Boolean}
     */
    hasChildrens() {
      return (
        this.childItems &&
        this.childItems.length > 0 &&
        // We will check if the props prop is set on the first child
        // If yes, then it's real item, otherwise probably is in for loop
        this.childItems[0].props
      )
    },

    /**
     * Get the resolved route
     *
     * @return {Object}
     */
    resolvedRoute() {
      return this.$router.resolve(this.to)
    },

    /**
     * Indicates whether the item menu is active
     *
     * @return {Boolean}
     */
    isActive() {
      if (!this.to) {
        return this.collapseVisible && !this.fixed
      }

      return (
        this.$route.path == this.resolvedRoute.path ||
        startsWith(this.$route.path, this.resolvedRoute.path)
      )
    },
  },
  methods: {
    /**
     * Check whether the menu item has active children items
     *
     * @return {Boolean}
     */
    hasActiveChildren() {
      if (!this.$refs.children) {
        return false
      }

      return this.$refs.children.querySelectorAll('.active').length > 0
    },

    /**
     * Navigate to the menu item
     *
     * @return {Void}
     */
    navigate() {
      if (this.to) {
        if (this.$route.path != this.resolvedRoute.path) {
          this.$router.push(this.to)
        }
        return
      }

      if (!this.fixed) {
        this.collapseVisible = !this.collapseVisible
      }
    },
  },

  /**
   * Handle component created event
   *
   * We will update the collaseVisible data in created lifecycle to prevent
   * blinking the collapsible when the item has not child items
   *
   * @return {Void}
   */
  created() {
    this.collapseVisible = this.fixed === true
  },

  /**
   * Handle the item mounted lifecycle
   *
   * @return {Void}
   */
  mounted() {
    // Set the initial collapseVisible in case of direct access
    if (this.hasActiveChildren()) {
      this.collapseVisible = true
    }
  },
}
</script>
