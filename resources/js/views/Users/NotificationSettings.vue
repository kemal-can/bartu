<template>
  <i-table v-bind="$attrs">
    <thead>
      <tr>
        <th
          class="text-left"
          width="6%"
          scope="col"
          v-i-tooltip="$t('notifications.channels.' + channel)"
          v-for="channel in allAvailableChannels"
          :key="'heading-' + channel"
        >
          <icon class="h-5 w-5" :icon="iconMaps[channel]" />
        </th>
        <th
          class="text-left"
          width="auto"
          v-text="$t('notifications.notification')"
        ></th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="notification in notifications" :key="notification.key">
        <td v-for="channel in allAvailableChannels" :key="channel">
          <i-form-checkbox
            v-if="notification.channels.indexOf(channel) > -1"
            :id="channel + '-' + notification.key"
            v-model:checked="form.notifications[notification.key][channel]"
          />
          <icon class="h-5 w-5" icon="X" v-else />
        </td>
        <td>
          <p class="text-neutral-700 dark:text-neutral-300">
            {{ notification.name }}
          </p>
          <p
            v-show="notification.description"
            class="mt-1 text-sm text-neutral-500 dark:text-neutral-300"
          >
            {{ notification.description }}
          </p>
        </td>
      </tr>
    </tbody>
  </i-table>
</template>
<script>
import map from 'lodash/map'
import uniq from 'lodash/uniq'
import flatten from 'lodash/flatten'

export default {
  inheritAttrs: false,
  props: {
    form: {
      required: true,
      type: Object,
    },
  },
  data: () => ({
    notifications: Innoclapps.config.notifications_information,
    iconMaps: {
      mail: 'Mail',
      database: 'Bell',
    },
  }),
  computed: {
    /**
     * Get all available channels from all notifications
     *
     * @return {Array}
     */
    allAvailableChannels() {
      return uniq(flatten(map(this.notifications, 'channels')))
    },
  },
}
</script>
