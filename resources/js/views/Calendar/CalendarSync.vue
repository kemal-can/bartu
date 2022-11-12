<template>
  <i-layout>
    <div class="mx-auto max-w-5xl">
      <i-card :overlay="!componentReady">
        <template #header>
          <i-card-heading class="flex items-center">
            {{ $t('calendar.calendar_sync') }}
            <i-badge
              variant="success"
              class="ml-2"
              v-if="
                calendar &&
                !calendar.is_sync_disabled &&
                !calendar.is_sync_stopped
              "
            >
              <icon icon="Refresh" class="mr-1 h-3 w-3" />
              {{
                calendar.is_synchronizing_via_webhook ? 'Webhook' : 'Polling'
              }}</i-badge
            >
            <i-badge variant="info" class="ml-2">Beta</i-badge>
          </i-card-heading>
        </template>
        <div
          v-if="
            (!calendar || (calendar && calendar.is_sync_disabled)) &&
            !accountConnectionInProgress
          "
        >
          <connect-account />
          <i-button
            variant="secondary"
            v-i-modal="'calendarConnectNewAccount'"
            :class="{ 'mr-1': hasAccounts }"
            >{{ $t('app.oauth.add') }}</i-button
          >
          <span
            v-show="hasAccounts"
            class="mt-3 block text-neutral-800 dark:text-neutral-300 sm:mt-0 sm:inline"
            v-t="'app.oauth.or_choose_existing'"
          ></span>
          <div v-if="hasAccounts && !accountConnectionInProgress" class="mt-4">
            <account-row
              class="mb-3"
              :account="account"
              v-for="account in accounts"
              :key="account.id"
            >
              <i-button
                size="sm"
                variant="secondary"
                class="ml-2"
                :disabled="account.requires_auth"
                @click="connect(account)"
                >{{ $t('app.oauth.connect') }}</i-button
              >
            </account-row>
          </div>
        </div>
        <div
          v-if="
            accountConnectionInProgress ||
            (calendar && !calendar.is_sync_disabled)
          "
        >
          <account-row
            class="mb-3"
            v-if="calendar && !calendar.is_sync_disabled && calendar.account"
            :account="calendar.account"
          >
            <i-button
              size="sm"
              variant="danger"
              class="ml-2"
              @click="stopSync"
              >{{
                calendar.is_sync_stopped
                  ? $t('app.cancel')
                  : $t('app.oauth.stop_syncing')
              }}</i-button
            >
            <template #after-name v-if="calendar.sync_state_comment">
              <span class="text-sm text-danger-500">
                {{ calendar.sync_state_comment }}
              </span>
            </template>
          </account-row>
          <div class="mb-3">
            <p
              class="mb-6 text-neutral-800 dark:text-neutral-100"
              v-if="!calendar || (calendar && calendar.is_sync_disabled)"
            >
              {{
                $t('calendar.account_being_connected', {
                  email: accountConnectionInProgress.email,
                })
              }}
            </p>
            <div class="grid grid-cols-12 gap-1 lg:gap-6">
              <div
                class="col-span-12 lg:col-span-3 lg:flex lg:items-center lg:justify-end"
              >
                <p
                  class="font-medium text-neutral-800 dark:text-neutral-100 lg:text-sm"
                  v-t="'calendar.select_calendar'"
                ></p>
              </div>
              <div class="col-span-12 lg:col-span-4">
                <i-custom-select
                  :options="calendars"
                  :model-value="selectedCalendar"
                  label="title"
                  :disabled="oAuthAccountRequiresAuthentication"
                  :placeholder="
                    oAuthAccountCalendarsFetchRequestInProgress
                      ? $t('app.loading')
                      : ''
                  "
                  @option:selected="form.calendar_id = $event.id"
                  :clearable="false"
                />
                <form-error :form="form" field="calendar_id" />
              </div>
            </div>
          </div>

          <div class="mb-3">
            <div class="grid grid-cols-12 gap-1 lg:gap-6">
              <div
                class="col-span-12 lg:col-span-3 lg:flex lg:items-center lg:justify-end"
              >
                <p
                  class="font-medium text-neutral-800 dark:text-neutral-100 lg:text-sm"
                  v-t="'calendar.save_events_as'"
                ></p>
              </div>
              <div class="col-span-12 lg:col-span-4">
                <i-custom-select
                  :options="types"
                  :model-value="selectedActivityTypeValue"
                  label="name"
                  :clearable="false"
                  @option:selected="form.activity_type_id = $event.id"
                />
                <form-error :form="form" field="activity_type_id" />
              </div>
            </div>
          </div>
          <div class="grid grid-cols-12 gap-1 lg:gap-6">
            <div class="col-span-12 lg:col-span-3 lg:text-right">
              <p
                class="font-medium text-neutral-800 dark:text-neutral-100 lg:text-sm"
                v-t="'calendar.sync_activity_types'"
              ></p>
            </div>
            <div class="col-span-12 lg:col-span-4">
              <i-form-checkbox
                v-model:checked="form.activity_types"
                :value="type.id"
                v-for="type in types"
                :key="type.id"
                :label="type.name"
                name="activity_types"
              />
              <form-error :form="form" field="activity_types" />
            </div>
          </div>
        </div>
        <template
          #footer
          v-if="
            accountConnectionInProgress ||
            (calendar && !calendar.is_sync_disabled)
          "
        >
          <div>
            <div class="flex flex-col lg:flex-row lg:items-center">
              <div class="mb-2 grow lg:mb-0">
                <span
                  v-if="startSyncFromText"
                  class="text-sm text-neutral-800 dark:text-neutral-100"
                >
                  <icon
                    icon="Exclamation"
                    class="mr-1 -mt-1 inline-flex h-5 w-5"
                  />
                  {{ startSyncFromText }}
                </span>
              </div>
              <div class="space-x-2">
                <i-button
                  @click="accountConnectionInProgress = null"
                  v-if="
                    !calendar ||
                    (calendar && calendar.is_sync_disabled) ||
                    calendar.is_sync_stopped
                  "
                  :disabled="form.busy"
                  >{{ $t('app.cancel') }}</i-button
                >
                <i-button
                  variant="secondary"
                  v-show="!calendar || (calendar && !calendar.is_sync_stopped)"
                  :disabled="form.busy"
                  @click="save"
                  >{{
                    !calendar ||
                    (calendar && calendar.is_sync_disabled) ||
                    calendar.is_sync_stopped
                      ? $t('app.oauth.start_syncing')
                      : $t('app.save')
                  }}</i-button
                >
                <i-button
                  variant="secondary"
                  v-show="calendar && calendar.is_sync_stopped"
                  :disabled="form.busy || oAuthAccountRequiresAuthentication"
                  @click="save"
                  >{{ $t('calendar.reconfigure') }}</i-button
                >
              </div>
            </div>
          </div>
        </template>
      </i-card>
    </div>
  </i-layout>
</template>
<script>
import { mapState } from 'vuex'
import AccountRow from '@/views/OAuth/AccountRow'
import ConnectAccount from './CalendarSyncConnectAccount'
import Form from '@/components/Form/Form'
import orderBy from 'lodash/orderBy'
import filter from 'lodash/filter'

export default {
  components: {
    AccountRow,
    ConnectAccount,
  },
  data: () => ({
    componentReady: false,
    oAuthAccountCalendarsFetchRequestInProgress: false,
    accountConnectionInProgress: null,
    calendar: null,
    accounts: [],
    calendars: [],
    form: {},
  }),
  computed: {
    ...mapState({
      types: state => state.activities.types,
    }),

    /**
     * Indicates whether there are  oAuth accounts
     *
     * @return {Boolean}
     */
    hasAccounts() {
      return this.accounts.length > 0
    },

    /**
     * Selected activity type value
     *
     * @return {Object}
     */
    selectedActivityTypeValue() {
      return this.types.find(type => type.id == this.form.activity_type_id)
    },

    /**
     * Currently selected calendar object
     *
     * @return {Object}
     */
    selectedCalendar() {
      return this.calendars.find(
        calendar => calendar.id == this.form.calendar_id
      )
    },

    /**
     * Latest oauth account created
     *
     * @return {Object}
     */
    latestOAuthAccount() {
      return orderBy(this.accounts, account => new Date(account.created_at), [
        'desc',
      ])[0]
    },

    /**
     * Indicates whether the connected calendar oAuth account requires authentication
     *
     * @return {Boolean}
     */
    oAuthAccountRequiresAuthentication() {
      if (!this.calendar || !this.calendar.account) {
        return false
      }

      return this.calendar.account.requires_auth
    },

    startSyncFromText() {
      // No connection nor calendar, do nothing
      if (
        (!this.accountConnectionInProgress && !this.calendar) ||
        (this.calendar && this.calendar.is_sync_stopped)
      ) {
        return
      }

      // If the calendar is not yet created, this means that we don't have any
      // sync history and just will show that only future events will be synced for the selected calendar
      if (!this.calendar) {
        return this.$t('calendar.only_future_events_will_be_synced')
      }

      // Let's try to find if the current selected calendar was previously configured
      // as calendar to sync, if found, the initial start_sync_from will be used to actual start syncing the events
      // in case if there were previously synced events and then the user changed the calendar and now want to get back again // on this calendar, we need to sync the previously synced events as well
      const previouslyUsedCalendar = filter(this.calendar.previously_used, [
        'calendar_id',
        this.form.calendar_id,
      ])

      // User does not want to sync and he is just looking at the configuration screen
      // for a configured account to sync, in this case, we will just show from what date the events are synced
      if (
        this.calendar.calendar_id === this.form.calendar_id &&
        !this.accountConnectionInProgress
      ) {
        return this.$t('calendar.events_being_synced_from', {
          date: this.localizedDateTime(this.calendar.start_sync_from),
        })
      }

      // Finally, we will check if there is account connection in progress or the actual form
      // calendar id is not equal with the currrent calendar id that the user selected
      if (
        this.accountConnectionInProgress ||
        this.calendar.calendar_id !== this.form.calendar_id
      ) {
        // If history found, use the start_sync_from date from the history
        if (previouslyUsedCalendar.length > 0) {
          return this.$t('calendar.events_will_sync_from', {
            date: this.localizedDateTime(
              previouslyUsedCalendar[0].start_sync_from
            ),
          })
        } else if (this.calendar.calendar_id === this.form.calendar_id) {
          // Otherwise the user has selected a calendar that was first time selected and now we will just use
          // the start_sync_from date from the first time when the calendar was connected
          return this.$t('calendar.events_will_sync_from', {
            date: this.localizedDateTime(this.calendar.start_sync_from),
          })
        } else {
          return this.$t('calendar.only_future_events_will_be_synced')
        }
      }
    },
  },
  methods: {
    /**
     * Set the initial form for the sync calendar data
     */
    setInitialForm() {
      this.form = new Form({
        access_token_id: null,
        activity_type_id: Innoclapps.config.activities.default_activity_type_id,
        activity_types: this.types.map(type => type.id),
        calendar_id: null,
      })
    },

    /**
     * Start account sync connection
     *
     * @param  {Object} account]
     *
     * @return {Void}
     */
    connect(account) {
      this.accountConnectionInProgress = account
      this.form.fill('access_token_id', account.id)
      this.retrieveOAuthAccountCalendars(account.id).then(calendars =>
        this.form.set('calendar_id', calendars[0].id)
      )
    },

    /**
     * Save the sync data
     *
     * @return {Void}
     */
    save() {
      this.form.post('/calendar/account').then(calendar => {
        this.calendar = calendar
        this.accountConnectionInProgress = null
      })
    },

    /**
     * Stop syncing
     *
     * @return {Void}
     */
    stopSync() {
      Innoclapps.request()
        .delete('/calendar/account')
        .then(({ data }) => {
          this.calendar = data
          this.accountConnectionInProgress = null
          this.setInitialForm()
        })
    },

    /**
     * Retrieve the given oauth account available calendar
     *
     * @param  {Number} id
     *
     * @return {Promise}
     */
    async retrieveOAuthAccountCalendars(id) {
      this.oAuthAccountCalendarsFetchRequestInProgress = true

      let { data } = await Innoclapps.request().get('/calendars/' + id)

      this.calendars = data
      this.oAuthAccountCalendarsFetchRequestInProgress = false

      return data
    },

    /**
     * Fill the form from the given calendar
     *
     * @param  {Object} calendar
     *
     * @return {Void}
     */
    fillForm(calendar) {
      this.form.set('activity_type_id', calendar.activity_type_id)
      // Perhaps account deleted?
      this.form.set(
        'access_token_id',
        calendar.account ? calendar.account.id : null
      )
      this.form.set('activity_types', calendar.activity_types)
    },
  },
  created() {
    this.setInitialForm()

    Promise.all([
      Innoclapps.request().get('oauth/accounts'),
      Innoclapps.request().get('calendar/account'),
    ]).then(values => {
      this.accounts = values[0].data
      this.calendar = values[1].data

      if (this.calendar) {
        this.fillForm(this.calendar)
      }

      if (this.$route.query.viaOAuth) {
        this.connect(this.latestOAuthAccount)
      } else if (
        this.calendar.account &&
        !this.oAuthAccountRequiresAuthentication
      ) {
        // perhaps deleted or requires auth?
        this.retrieveOAuthAccountCalendars(this.calendar.account.id).then(
          calendars => {
            this.form.set('calendar_id', this.calendar.calendar_id)
          }
        )
      }

      this.componentReady = true
    })
  },
}
</script>
