<template>
  <i-dropdown placement="bottom-end" :full="false" ref="dropdown">
    <template #toggle>
      <i-button
        variant="white"
        :rounded="false"
        :size="false"
        class="relative rounded-full p-1"
        @click="markAllRead()"
      >
        <icon icon="Bell" class="h-6 w-6" />
        <i-badge
          variant="primary"
          size="circle"
          class="absolute -top-2 -right-2"
          v-if="hasUnread"
          v-text="totalUnread"
        />
      </i-button>
    </template>

    <div
      :class="[
        'flex items-center px-4 py-3 sm:p-4',
        { 'border-b border-neutral-200 dark:border-neutral-700': total > 0 },
      ]"
    >
      <div
        :class="[
          'grow text-neutral-700 dark:text-white',
          { 'font-medium': total > 0, 'sm:text-sm': total === 0 },
        ]"
      >
        {{ headerLabel }}
      </div>
      <router-link
        :to="{ name: 'profile', hash: '#notifications' }"
        @click="() => $refs.dropdown.hide()"
        v-i-tooltip="$t('settings.settings')"
        class="link ml-2"
      >
        <icon icon="Cog" class="h-5 w-5" />
      </router-link>
    </div>

    <div
      class="max-h-96 divide-y divide-neutral-200 overflow-y-auto dark:divide-neutral-700"
    >
      <i-dropdown-item
        v-for="notification in notifications"
        :key="notification.id"
        :to="notification.data.path"
      >
        <p class="truncate text-neutral-800 dark:text-neutral-100">
          {{ localize(notification) }}
        </p>
        <span class="text-xs text-neutral-500 dark:text-neutral-300">
          {{ localizedDateTime(notification.created_at) }}
        </span>
      </i-dropdown-item>
    </div>
    <div
      class="flex items-center justify-end border-t border-neutral-200 bg-neutral-50 px-4 py-2 text-sm dark:border-neutral-600 dark:bg-neutral-700"
      v-show="total > 0"
    >
      <router-link
        :to="{ name: 'notifications' }"
        @click="() => $refs.dropdown.hide()"
        class="link"
        v-t="'app.see_all'"
      ></router-link>
    </div>
  </i-dropdown>
</template>
<script>
import { mapGetters, mapActions } from 'vuex'

export default {
  computed: {
    ...mapGetters({
      total: 'users/totalNotifications',
      hasUnread: 'users/hasUnreadNotifications',
      localize: 'users/localizeNotification',
    }),

    /**
     * Current user latest notifications
     *
     * @return {Array}
     */
    notifications() {
      return this.currentUser.notifications.latest
    },

    /**
     * Current user total unread notifications
     *
     * @return {Number}
     */
    totalUnread() {
      return this.currentUser.notifications.unread_count
    },

    /**
     * Get the dropdown header label
     *
     * @return {String}
     */
    headerLabel() {
      if (this.total > 0) {
        return this.$t('notifications.notifications')
      }

      return this.$t('notifications.no_notifications')
    },
  },
  methods: {
    ...mapActions({
      markAllRead: 'users/markAllNotificationsAsRead',
    }),

    /**
     * Handle new notification broadcasted
     *
     * @param  {String} id
     *
     * @return {Void}
     */
    handleNewNotification(id) {
      Innoclapps.request()
        .get(`/notifications/${id}`)
        .then(({ data }) => this.$store.commit('users/NEW_NOTIFICATION', data))
    },
  },
  created() {
    Innoclapps.$on('notification-broadcasted', this.handleNewNotification)
  },
  unmounted() {
    // For all cases destroy the broadcasted events
    // However, this should not trigger as the NavBarNotifications is static
    Innoclapps.$off('notification-broadcasted', this.handleNewNotification)
  },
}
</script>
