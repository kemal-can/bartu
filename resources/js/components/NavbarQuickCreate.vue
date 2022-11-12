<template>
  <i-dropdown
    ref="dropdown"
    placement="bottom-end"
    :full="false"
    @show="visible = true"
    @hide="visible = false"
  >
    <template #toggle>
      <i-button
        variant="secondary"
        :rounded="false"
        :size="false"
        icon="Plus"
        icon-class="w-6 h-6"
        class="rounded-full p-1"
      />
    </template>
    <div class="w-56">
      <div
        class="flex items-center justify-between border-b border-neutral-200 px-4 py-3 text-sm dark:border-neutral-700"
      >
        <p
          class="font-medium text-neutral-600 dark:text-neutral-100"
          v-t="'app.quick_create'"
        />
        <span
          class="rounded-md bg-neutral-700 px-1.5 text-base text-neutral-100 dark:bg-neutral-600 dark:text-neutral-200"
        >
          <span class="-mt-0.5 block">+</span>
        </span>
      </div>
      <div class="space-y-0.5">
        <i-dropdown-item
          v-for="(item, index) in quickCreateMenuItems"
          v-show="$route.path !== item.quickCreateRoute"
          :key="index"
          :icon="item.icon"
          :to="item.quickCreateRoute"
        >
          <span class="inline-flex w-full items-center justify-between">
            <span>{{ item.quickCreateName }}</span>
            <span
              class="rounded-md bg-neutral-100 px-1.5 uppercase text-neutral-500 dark:bg-neutral-700 dark:text-neutral-300"
              v-text="item.keyboardShortcutChar"
            />
          </span>
        </i-dropdown-item>
      </div>
    </div>
  </i-dropdown>
</template>
<script>
import { mapState } from 'vuex'
export default {
  data: () => ({
    visible: false,
  }),
  computed: {
    ...mapState({
      sidebarNavigation: state => state.menu,
    }),

    /**
     * Get menu items that should be shown in quick-create
     *
     * @return {Array}
     */
    quickCreateMenuItems() {
      return this.sidebarNavigation.filter(item => item.inQuickCreate)
    },

    /**
     * Get all the items with keyboard shortcut
     *
     * @return {Array}
     */
    itemsWithKeyboardShortcut() {
      return this.quickCreateMenuItems.filter(
        item => item.keyboardShortcutChar !== null
      )
    },
  },
  methods: {
    /**
     * Register the quick create keyboard shortcuts
     * NOTE: They don't need to be unbinded as this is a global component
     */
    registereKeyboardShortcuts() {
      this.itemsWithKeyboardShortcut.forEach(item => {
        Innoclapps.addShortcut(
          '+ ' + item.keyboardShortcutChar.toLowerCase(),
          () => {
            // If the dropdown is open and the user uses keyboard shortcut
            // it won't be closed as the popper component is expecting click in order to close the component
            if (this.visible) {
              this.$refs.dropdown.hide()
            }

            this.$router.push(item.quickCreateRoute)
          }
        )
      })
    },
  },
  created() {
    this.registereKeyboardShortcuts()
  },
}
</script>
