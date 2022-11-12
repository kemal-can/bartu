<template>
  <i-layout>
    <div class="mx-auto max-w-5xl">
      <i-card no-body :header="$t('notifications.notifications')">
        <template #actions>
          <i-button
            variant="white"
            :loading="requestInProgress"
            size="sm"
            :disabled="!hasUnreadNotifications"
            v-show="total > 0"
            @click="markAllRead"
            >{{ $t('notifications.mark_all_as_read') }}</i-button
          >
        </template>
        <ul class="divide-y divide-neutral-200 dark:divide-neutral-700">
          <li
            v-for="(notification, index) in notifications"
            :key="notification.id"
          >
            <a
              href="#"
              @click.prevent="$router.push(notification.data.path)"
              class="block hover:bg-neutral-50 dark:hover:bg-neutral-700/60"
            >
              <div class="flex items-center px-4 py-4 sm:px-6">
                <div
                  class="min-w-0 flex-1 sm:flex sm:items-center sm:justify-between"
                >
                  <div class="truncate">
                    <p
                      class="truncate text-sm font-medium text-neutral-800 dark:text-neutral-100"
                    >
                      {{ localize(notification) }}
                    </p>
                    <p
                      class="mt-2 text-sm text-neutral-500 dark:text-neutral-300"
                    >
                      {{ localizedDateTime(notification.created_at) }}
                    </p>
                  </div>
                </div>
                <div class="ml-5 shrink-0">
                  <i-button-icon icon="Trash" @click.stop="destroy(index)" />
                </div>
              </div>
            </a>
          </li>
        </ul>

        <infinity-loader @handle="loadHandler" />

        <i-card-body v-show="total === 0" class="text-center">
          <icon icon="EmojiSad" class="mx-auto h-12 w-12 text-neutral-400" />
          <h3
            class="mt-2 text-sm font-medium text-neutral-800 dark:text-white"
            v-t="'notifications.no_notifications'"
          ></h3>
        </i-card-body>
        <p
          v-show="noMoreResults"
          class="p-3 text-center text-neutral-600"
          v-t="'notifications.no_more_notifications'"
        ></p>
      </i-card>
    </div>
  </i-layout>
</template>
<script>
import InfinityLoader from '@/components/InfinityLoader'
import findIndex from 'lodash/findIndex'
import { mapGetters } from 'vuex'
export default {
  name: 'notifications',
  components: { InfinityLoader },
  data: () => ({
    notifications: [],
    noMoreResults: false,
    nextPage: 2,
    requestInProgress: false,
  }),
  computed: {
    ...mapGetters({
      localize: 'users/localizeNotification',
      hasUnreadNotifications: 'users/hasUnreadNotifications',
    }),

    /**
     * Get the total number of notifications
     *
     * @return {Number}
     */
    total() {
      return this.notifications.length
    },
  },
  methods: {
    /**
     * Mark all notifications as read
     *
     * @return {Void}
     */
    markAllRead() {
      this.requestInProgress = true
      this.$store
        .dispatch('users/markAllNotificationsAsRead')
        .finally(() => (this.requestInProgress = false))
    },

    /**
     * Delete notification by given index
     *
     * @param  {Number} index
     *
     * @return {Void}
     */
    destroy(index) {
      this.$store
        .dispatch('users/destroyNotification', this.notifications[index])
        .then(notification => this.notifications.splice(index, 1))
    },

    /**
     * Add notifications
     *
     * @param {Array} notifications
     */
    addNotifications(notifications) {
      // We will check if the notification already exists
      // if not, then we will add to the array of notifications
      // In case of previously broadcasted notification, to prevent duplicate
      // as the last one will be duplicate
      notifications.forEach(notification => {
        if (findIndex(this.notifications, ['id', notification.id]) === -1) {
          this.notifications.push(notification)
        }
      })
    },

    /**
     * Load notifications
     *
     * @param  {Object} $state
     *
     * @return {Void}
     */
    async loadHandler($state) {
      let { data } = await this.load()

      this.addNotifications(data.data)

      this.$nextTick(() => {
        if (data.total === this.total) {
          this.noMoreResults = true
          $state.complete()
        }
      })

      this.nextPage += 1
      $state.loaded()
    },

    /**
     * Load more notifications request
     *
     * @return {Void}
     */
    load() {
      return Innoclapps.request().get(
        this.$store.state.users.notificationsEndpoint,
        {
          params: {
            page: this.nextPage,
          },
        }
      )
    },

    /**
     * Handle new notification broadcasted event
     *
     * @param  {Object} notification
     *
     * @return {Void}
     */
    handleNewNotificationBroadcasted(notification) {
      this.notifications.unshift(notification)
    },
  },
  created() {
    // Get the initial notifications from the current user, as it's the first page
    this.notifications = this.cleanObject(this.currentUser.notifications.latest)

    // Push new notification when new notification is broadcasted/added to update this list too
    // Useful when the user is at the all notifications route,
    // will update all notifications and the dropdown notifications too
    Innoclapps.$on(
      'new-notification-added',
      this.handleNewNotificationBroadcasted
    )
  },
  unmounted() {
    Innoclapps.$off(
      'new-notification-added',
      this.handleNewNotificationBroadcasted
    )
  },
}
</script>
