<template>
  <i-modal
    size="sm"
    id="calendarConnectNewAccount"
    hide-footer
    :title="$t('app.oauth.connect_new_account')"
  >
    <div class="py-4">
      <p
        class="mb-5 text-center text-neutral-800 dark:text-neutral-200"
        v-t="'calendar.choose_oauth_account'"
      ></p>
      <div class="flex justify-center space-x-2">
        <div
          class="flex cursor-pointer flex-col items-center space-y-1 rounded-lg border border-neutral-200 px-5 py-3 shadow-sm hover:bg-neutral-100 dark:border-neutral-500 dark:hover:bg-neutral-800"
          @click="connectOAuthAccount('google')"
        >
          <i-popover
            :disabled="isGoogleApiConfigured"
            ref="googlePopover"
            placement="top"
          >
            <google-icon />

            <template #popper>
              <p class="text-sm">
                Google application project not configured, you must
                <a
                  href="/settings/integrations/google"
                  target="_blank"
                  rel="noopener noreferrer"
                  >configure</a
                >
                your Google application project in order to connect to sync
                Google calendar.
              </p>
            </template>
          </i-popover>
          <span
            class="text-sm font-medium text-neutral-600 dark:text-neutral-300"
            >Google Calendar</span
          >
        </div>
        <div
          class="flex cursor-pointer flex-col items-center space-y-1 rounded-lg border border-neutral-200 px-5 py-3 shadow-sm hover:bg-neutral-100 dark:border-neutral-500 dark:hover:bg-neutral-800"
          @click="connectOAuthAccount('microsoft')"
        >
          <i-popover
            :disabled="isMicrosoftGraphConfigured"
            placement="top"
            ref="microsoftPopover"
          >
            <outlook-icon />
            <template #popper>
              <p class="text-sm">
                Microsoft application not configured, you must
                <a
                  href="/settings/integrations/microsoft"
                  target="_blank"
                  rel="noopener noreferrer"
                  >configure</a
                >
                your Microsoft application in order to sync Outlook calendar.
              </p>
            </template>
          </i-popover>
          <span
            class="text-sm font-medium text-neutral-600 dark:text-neutral-300"
            >Outlook Calendar</span
          >
        </div>
      </div>
    </div>
  </i-modal>
</template>
<script>
import OutlookIcon from '@/components/Icons/OutlookIcon'
import GoogleIcon from '@/components/Icons/GoogleIcon'
export default {
  components: {
    OutlookIcon,
    GoogleIcon,
  },
  methods: {
    /**
     * Connect the given oauth provider
     *
     * @param  {String} provider
     *
     * @return {Void}
     */
    connectOAuthAccount(provider) {
      if (provider === 'google' && !this.isGoogleApiConfigured) {
        this.$refs.googlePopover.show()
        return
      } else if (provider === 'microsoft' && !this.isMicrosoftGraphConfigured) {
        this.$refs.microsoftPopover.show()
        return
      }

      window.location.href = `${Innoclapps.config.url}/calendar/sync/${provider}/connect`
    },
  },
}
</script>
