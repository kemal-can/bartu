<template>
  <i-layout full :overlay="loading">
    <template #actions>
      <navbar-separator class="hidden lg:block" />

      <div class="inline-flex items-center">
        <i-button-group class="mr-5">
          <i-button
            size="sm"
            class="relative focus:z-10"
            :to="{ name: 'activity-index' }"
            v-i-tooltip="$t('app.list_view')"
            variant="white"
            icon="ViewList"
            icon-class="w-4 h-4 text-neutral-500 dark:text-neutral-400"
          />
          <i-button
            size="sm"
            class="relative bg-neutral-100 focus:z-10"
            :to="{ name: 'activity-calendar' }"
            v-i-tooltip="$t('calendar.calendar')"
            variant="white"
            icon="Calendar"
            icon-class="w-4 h-4 text-neutral-700 dark:text-neutral-100"
          />
        </i-button-group>

        <i-button
          @click="eventBeingCreated = true"
          size="sm"
          icon="Plus"
          variant="primary"
          >{{ $t('activity.create') }}</i-button
        >
      </div>
    </template>

    <div class="flex flex-wrap items-center px-3 py-4 sm:px-5">
      <div class="grow">
        <div class="flex w-full flex-wrap items-center">
          <div
            class="order-1 mb-2 flex w-full items-center sm:order-first sm:mb-0 sm:w-auto"
          >
            <a
              href="#"
              v-show="query.activity_type_id"
              @click.prevent="query.activity_type_id = null"
              class="link mr-3 border-r-2 border-neutral-200 pr-3 dark:border-neutral-600"
            >
              {{ $t('app.all') }}
            </a>
            <i-icon-picker
              class="min-w-max"
              :icons="formattedTypes"
              value-field="id"
              size="sm"
              v-model="query.activity_type_id"
            />
          </div>
          <div
            class="mb-2 dark:border-neutral-600 sm:ml-3 sm:mb-0 sm:border-l sm:border-neutral-200 sm:pl-3"
          >
            <dropdown-select
              :items="users"
              label-key="name"
              v-if="$gate.userCan('view all activities')"
              value-key="id"
              @change="query.user_id = $event.id"
              v-model="user"
            />
          </div>
        </div>
      </div>
      <div class="flex w-full justify-between sm:w-auto sm:justify-end">
        <i-button-group class="mr-3 inline">
          <i-button
            @click="$refs.calendar.getApi().prev()"
            class="relative !rounded-l-md px-2 focus:z-10"
            :size="false"
            icon="ChevronLeft"
            icon-class="h-4 w-4"
            v-i-tooltip.left="
              $t('calendar.fullcalendar.locale.buttonText.prev')
            "
            variant="white"
          />
          <i-button
            @click="$refs.calendar.getApi().today()"
            class="relative focus:z-10"
            variant="white"
            size="sm"
          >
            {{ $t('calendar.fullcalendar.locale.buttonText.today') }}
          </i-button>
          <i-button
            class="relative !rounded-r-md px-2 focus:z-10"
            :size="false"
            icon-class="h-4 w-4"
            @click="$refs.calendar.getApi().next()"
            v-i-tooltip.right="
              $t('calendar.fullcalendar.locale.buttonText.next')
            "
            variant="white"
            icon="ChevronRight"
          />
        </i-button-group>
        <i-dropdown :text="activeViewText" auto-size="min" size="sm">
          <i-dropdown-item
            @click="changeView('timeGridWeek')"
            v-show="activeView !== 'timeGridWeek'"
          >
            {{ $t('calendar.fullcalendar.locale.buttonText.week') }}
          </i-dropdown-item>
          <i-dropdown-item
            @click="changeView('dayGridMonth')"
            v-show="activeView !== 'dayGridMonth'"
          >
            {{ $t('calendar.fullcalendar.locale.buttonText.month') }}
          </i-dropdown-item>
          <i-dropdown-item
            @click="changeView('timeGridDay')"
            v-show="activeView !== 'timeGridDay'"
          >
            {{ $t('calendar.fullcalendar.locale.buttonText.day') }}
          </i-dropdown-item>
        </i-dropdown>
      </div>
    </div>
    <div class="fc-wrapper">
      <full-calendar
        v-if="calendarOptions.initialView"
        class="h-screen"
        :options="calendarOptions"
        ref="calendar"
      />

      <activity-edit
        v-if="activityId"
        :resource-name="resourceName"
        :id="activityId"
        :on-hidden="viewModalHidden"
        :on-action-executed="handleViewActionExecuted"
      />
      <activity-create
        :visible="eventBeingCreated"
        :due-date="createDueDate"
        :end-date="createEndDate"
        @created="onActivityCreatedEventHandler"
        @hidden="onActivityCreateModalHidden"
      />
    </div>
  </i-layout>
</template>
<script>
// https://github.com/fullcalendar/fullcalendar-vue/issues/152
import '@fullcalendar/core/vdom' // solves problem with Vite
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'
import ActivityCreate from '@/views/Activity/CreateSimple'
import ActivityEdit from '@/views/Activity/Edit'
import momentTimezonePlugin from '@fullcalendar/moment-timezone'
import { getLocale } from '@/utils'
import { useTypes } from '@/views/Activity/Composables/useTypes'

const currentLocale = getLocale()

import { mapState } from 'vuex'

export default {
  components: {
    FullCalendar,
    ActivityCreate,
    ActivityEdit,
  },
  setup() {
    const { formatTypesForIcons } = useTypes()

    return { formatTypesForIcons }
  },
  watch: {
    query: {
      handler: function (newVal) {
        this.refreshEvents()
      },
      deep: true,
    },
  },
  data() {
    return {
      resourceName: 'activities',
      createDueDate: null,
      createEndDate: null,
      activityId: null,
      loading: false,
      activeView: null,
      user: null,
      eventBeingCreated: false,
      query: {
        activity_type_id: null,
        user_id: null,
      },
      calendarOptions: {
        plugins: [
          dayGridPlugin,
          timeGridPlugin,
          interactionPlugin,
          momentTimezonePlugin,
        ],
        locales: [
          {
            code: currentLocale,
            ...lang[currentLocale].calendar.fullcalendar.locale,
          },
        ],
        locale: currentLocale,
        headerToolbar: {
          left: false,
          center: 'title',
          end: false,
        },
        dayMaxEventRows: true, // for all non-TimeGrid views
        views: {
          day: {
            dayMaxEventRows: false,
          },
        },
        eventDisplay: 'block',
        initialView: null,
        timeZone: null,
        lazyFetching: false,
        editable: true,
        droppable: true,

        scrollTime: '00:00:00', // not scroll to current time e.q. on day view

        // Remove the top left all day text as it's not suitable
        allDayContent: arg => {
          arg.text = ''

          return arg
        },

        moreLinkClick: arg => {
          this.$refs.calendar.getApi().gotoDate(arg.date)

          this.$refs.calendar.getApi().changeView('dayGridDay')
        },

        viewDidMount: arg => {
          this.activeView = arg.view.type

          // We don't remember the dayGridDay as
          // this is the more link redirect view
          if (arg.view.type !== 'dayGridDay') {
            this.rememberDefaultView(arg.view.type)
          }
        },

        eventContent: arg => {
          return this.createEventTitleDomNodes(arg)
        },

        eventClick: info => {
          this.activityId = parseInt(info.event.id)
        },

        dateClick: data => {
          this.createDueDate = {
            date: data.allDay
              ? data.dateStr
              : moment.utc(data.dateStr).format('YYYY-MM-DD'),
            time: data.allDay ? null : moment.utc(data.dateStr).format('HH:mm'),
          }

          // On end date, we will format with the user timezone as the end date
          // has not time when on dateClick click and for this reason, we must get the actual date
          // to be displayed in the create modal e.q. if user click on day view 19th April 12 AM
          // the dueDate will be shown properly but not the end date as if we format the end date
          // with UTC will 18th April e.q. 18th April 22:00 (UTC)
          this.createEndDate = {
            date: data.allDay
              ? data.dateStr
              : moment(data.dateStr).format('YYYY-MM-DD'),
            time: null,
          }

          this.eventBeingCreated = true
        },

        eventResize: resizeInfo => {
          let payload = {}
          if (resizeInfo.event.allDay) {
            payload = {
              due_date: resizeInfo.event.startStr,
              end_date: this.endDateForStorage(resizeInfo.event.endStr),
            }
          } else {
            payload = {
              due_date: this.dateToAppTimezone(
                resizeInfo.event.startStr,
                'YYYY-MM-DD'
              ),
              due_time: this.dateToAppTimezone(
                resizeInfo.event.startStr,
                'HH:mm'
              ),
              end_date: this.dateToAppTimezone(
                resizeInfo.event.endStr,
                'YYYY-MM-DD'
              ),
              end_time: this.dateToAppTimezone(
                resizeInfo.event.endStr,
                'HH:mm'
              ),
            }
          }

          this.saveActivity(payload, resizeInfo.event.id)
        },

        eventDrop: dropInfo => {
          const payload = {}
          const event = this.$refs.calendar
            .getApi()
            .getEventById(dropInfo.event.id)
          if (dropInfo.event.allDay) {
            payload.due_date = dropInfo.event.startStr

            payload.due_time = null
            payload.end_time = null
            // When dropping event from time column to all day e.q. on week view
            // there is no end date as it's the same day, for this reason, we need to update the
            // end date to be the same like the start date for the update request payload
            if (!dropInfo.event.end) {
              payload.end_date = payload.due_date
            } else {
              // Multi days event, we will remove the one day to store
              // the end date properly in database as here for the calendar they are endDate + 1 day so they are
              // displayed properly see prepareEventsForCalendar method
              payload.end_date = this.endDateForStorage(dropInfo.event.endStr)
            }

            event.setExtendedProp('isAllDay', true)
            event.setEnd(this.endDateForCalendar(payload.end_date))
          } else {
            payload.due_date = this.dateToAppTimezone(
              dropInfo.event.startStr,
              'YYYY-MM-DD'
            )
            payload.due_time = this.dateToAppTimezone(
              dropInfo.event.startStr,
              'HH:mm'
            )
            // When dropping all day event to non all day e.q. on week view from top to the timeline
            // we need to update the end date as well
            if (dropInfo.oldEvent.allDay && !dropInfo.event.allDay) {
              let endDateStr = moment(dropInfo.event.startStr)
                .add(1, 'hours')
                .format('YYYY-MM-DD HH:mm:ss')
              payload.end_date = this.dateToAppTimezone(
                endDateStr,
                'YYYY-MM-DD'
              )
              payload.end_time = this.dateToAppTimezone(endDateStr, 'HH:mm')
              event.setEnd(endDateStr)
              event.setExtendedProp('hasEndTime', true)
            } else {
              // We will check if the actual endStr is set, if not will use the due dates as due time
              // because this may happen when the activity due and end
              // date are the same, in this case, fullcalendar does not provide the endStr
              payload.end_date = dropInfo.event.endStr
                ? this.dateToAppTimezone(dropInfo.event.endStr, 'YYYY-MM-DD')
                : payload.due_date

              // Time can be modified on week and day view, on month view we will
              // only modify the time on actual activities with time
              if (
                this.activeView !== 'dayGridMonth' ||
                dropInfo.event.extendedProps.hasEndTime
              ) {
                payload.end_time = dropInfo.event.endStr
                  ? this.dateToAppTimezone(dropInfo.event.endStr, 'HH:mm')
                  : payload.due_time
                event.setExtendedProp('hasEndTime', true)
              }
            }

            event.setExtendedProp('isAllDay', false)
          }

          this.saveActivity(payload, dropInfo.event.id)
        },

        loading: isLoading => (this.loading = isLoading),

        events: (info, successCallback, failureCallback) => {
          Innoclapps.request()
            .get('calendar', {
              params: {
                resourceName: this.resourceName,
                ...this.query,
                start_date: this.dateToAppTimezone(info.start.toUTCString()),
                end_date: this.dateToAppTimezone(info.end.toUTCString()),
              },
            })
            .then(({ data }) =>
              successCallback(this.prepareEventsForCalendar(data))
            )
            .catch(error => {
              console.error(error)
              failureCallback('Error while retrieving events', error)
            })
        },
      },
    }
  },
  computed: {
    formattedTypes() {
      return this.formatTypesForIcons(this.types)
    },
    ...mapState({
      users: state => state.users.collection,
      types: state => state.activities.types,
    }),
    activeViewText() {
      switch (this.activeView) {
        case 'timeGridWeek':
          return this.$t('calendar.fullcalendar.locale.buttonText.week')
        case 'dayGridMonth':
          return this.$t('calendar.fullcalendar.locale.buttonText.month')
        case 'dayGridDay':
        case 'timeGridDay':
          return this.$t('calendar.fullcalendar.locale.buttonText.day')
      }
    },
  },
  methods: {
    /**
     * Handle the activity created event
     */
    onActivityCreatedEventHandler() {
      this.refreshEvents()
      this.eventBeingCreated = false
    },

    /**
     * Change the calendar view
     *
     * @param  {String} viewName
     *
     * @return {Void}
     */
    changeView(viewName) {
      this.$refs.calendar.getApi().changeView(viewName)
      this.activeView = viewName
      this.rememberDefaultView(viewName)
    },

    /**
     * Create end date for the calendar
     *
     * @see  prepareEventsForCalendar
     *
     * @param  {mixed} date
     * @param  {String} format
     *
     * @return {String}
     */
    endDateForCalendar(date, format = 'YYYY-MM-DD') {
      return moment(date).add('1', 'days').format(format)
    },

    /**
     * Create end date for storage
     *
     * @see  prepareEventsForCalendar
     *
     * @param  {mixed} date
     * @param  {String} format
     *
     * @return {String}
     */
    endDateForStorage(date, format = 'YYYY-MM-DD') {
      return moment(date).subtract('1', 'days').format(format)
    },

    /**
     * Save the activity in storage
     *
     * @param  {Object} payload
     * @param  {Number} id
     *
     * @return {Void}
     */
    saveActivity(payload, id) {
      Innoclapps.request().put(`/activities/${id}`, payload)
    },

    /**
     * Format the event title
     *
     * @see https://fullcalendar.io/docs/event-render-hooks
     *
     * @param  {Object} arg
     *
     * @return {Object}
     */
    createEventTitleDomNodes(arg) {
      let event = document.createElement('span')
      if (arg.event.allDay) {
        event.innerHTML = arg.event.title
      } else {
        let momentInstanceStartTime = moment(arg.event.startStr)
        let startTime = momentInstanceStartTime.format(
          moment().PHPconvertFormat(this.currentTimeFormat)
        )
        let momentInstanceEndTime

        if (
          arg.isMirror &&
          arg.isDragging &&
          arg.event.extendedProps.isAllDay
        ) {
          // Dropping from all day to non-all day
          // In this case, there is no end date, we will automatically add 1 hour to the start date
          momentInstanceEndTime = moment(arg.event.startStr).add(1, 'hours')
        } else if (
          ((arg.isMirror && arg.isResizing) ||
            (arg.isMirror && arg.isDragging) ||
            (arg.event.endStr &&
              arg.event.extendedProps.hasEndTime === true)) &&
          // This may happen when the activity due and end
          // date are the same, in this case, fullcalendar does not provide the endStr
          // attribute and the time will be shown only from the startStr
          arg.event.endStr != arg.event.startStr
        ) {
          momentInstanceEndTime = moment(arg.event.endStr)
        }

        if (momentInstanceEndTime) {
          let endTime = momentInstanceEndTime.format(
            moment().PHPconvertFormat(this.currentTimeFormat)
          )
          if (momentInstanceEndTime.date() != momentInstanceStartTime.date()) {
            startTime +=
              ' - ' + endTime + ' ' + momentInstanceEndTime.format('MMM D')
          } else {
            startTime += ' - ' + endTime
          }
        }

        event.innerHTML = startTime + ' ' + arg.event.title
      }

      return {
        domNodes: [event],
      }
    },

    /**
     * Prepare the given events for calendar
     *
     * @param  {Array} events
     *
     * @return {Array}
     */
    prepareEventsForCalendar(events) {
      return events.map(event => {
        // @see https://stackoverflow.com/questions/30323397/fullcalendar-event-shows-wrong-end-date-by-one-day
        // @see https://fullcalendar.io/docs/event-parsing
        // e.q. event with start 2021-04-01 and end date 2021-04-03 in the calendar is displayed
        // from 2021-04-01 to 2021-04-02, in this case on fetch, we will add 1 days so they are
        // displayed properly and on update, we will remove 1 day so they are saved properly
        event.extendedProps.isAllDay = event.allDay

        if (event.allDay) {
          event.end = this.endDateForCalendar(event.end)
        } else if (!/\d{4}-\d{2}-\d{2}\T?\d{2}:\d{2}:\d{2}$/.test(event.end)) {
          // no end time, is not in y-m-dTh:i:s format
          // to prevent clogging the calendar with events showing
          // over the week/day view, we will just add the start hour:minute
          // as end hour:minute + 30 minutes to be shown in one simple box
          // this can usually happen when to due and the end date are the same and there is no end time
          event.end = moment(event.end)
          const momentStart = moment(event.start)
          event.end
            .hour(momentStart.hour())
            .minute(momentStart.minute())
            .second(0)
            .add(30, 'minutes')
          event.end = event.end.format('YYYY-MM-DD\THH:mm:ss')
          event.extendedProps.hasEndTime = false
        } else {
          event.extendedProps.hasEndTime = true
        }

        // We need to set endEditable on events displayed on the month view as for some reason
        // when the calendar option {editable: true} is set the month view events are not resizable
        // note this is only applicable for all day events as non-all days events cannot be dragged
        // on month view (fullcalendar limitation)
        if (this.activeView === 'dayGridMonth') {
          event.endEditable = true
        }

        if (event.isReadOnly) {
          event.editable = false
        }

        return event
      })
    },

    /**
     * The event view modal hidden
     *
     * @return {Void}
     */
    viewModalHidden() {
      this.activityId = null
      this.refreshEvents()
      this.setPageTitle(this.$t('calendar.calendar'))
    },

    /**
     * Activity create modal hidden
     *
     * @return {Void}
     */
    onActivityCreateModalHidden() {
      this.eventBeingCreated = false
      this.createDueDate = null
      this.createEndDate = null
    },

    /**
     * Handle action executed function callback
     *
     * Because Activity/Edit.vue redirects to the index view of the resource
     * after an action is executed, we are providing custom calendar callback when action is executed
     *
     * @param  {Object} action
     *
     * @return {Void}
     */
    handleViewActionExecuted(action) {
      if (action.destroyable) {
        this.$iModal.hide('activityEdit')
      }
    },

    /**
     * Refresh events
     *
     * @return {Void}
     */
    refreshEvents() {
      this.$refs.calendar.getApi().refetchEvents()
    },

    /**
     * Remember the default view to storage
     *
     * @param  {String} view
     *
     * @return {Void}
     */
    rememberDefaultView(view) {
      localForage.setItem('default-calendar-view', view)
    },

    /**
     * Set the calendar default view
     */
    setDefaultView() {
      localForage
        .getItem('default-calendar-view')
        .then(
          (value, err) =>
            (this.calendarOptions.initialView =
              !err && value ? value : 'timeGridWeek')
        )
    },
  },
  created() {
    this.calendarOptions.timeZone = this.currentUser.timezone
    this.user = this.currentUser
    this.calendarOptions.firstDay = this.currentUser.first_day_of_week
  },
  mounted() {
    this.setDefaultView()

    localForage.setItem('activity-calendar-view-default', true)
    Innoclapps.$on('activities-record-updated', this.refreshEvents)

    if (Innoclapps.broadcaster.hasDriver()) {
      window.Echo.private(`calendar.${this.currentUser.id}`).listen(
        'CalendarSyncFinished',
        this.refreshEvents
      )
    }
  },
  unmounted() {
    Innoclapps.$off('activities-record-updated', this.refreshEvents)

    if (Innoclapps.broadcaster.hasDriver()) {
      window.Echo.private(`calendar.${this.currentUser.id}`).stopListening(
        'CalendarSyncFinished',
        this.refreshEvents
      )
    }
  },
}
</script>
<style>
.fc-theme-standard .fc-scrollgrid {
  border-left: 0 !important;
  border-right: 0 !important;
}
</style>
