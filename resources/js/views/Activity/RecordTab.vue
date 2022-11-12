<template>
  <record-tab
    :badge="incompleteActivitiesCount"
    badge-variant="danger"
    @activated-first-time="loadData"
    :section-id="associateable"
    :title="$t('activity.activities')"
    :classes="{ 'opacity-70': !hasActivities }"
    icon="Calendar"
  >
    <create-activity
      v-show="dataLoadedFirstTime || hasActivities"
      :resource-name="resourceName"
      :show-introduction-section="!hasActivities"
    />

    <div class="my-3">
      <input-search
        v-model="search"
        v-show="hasActivities || search"
        @input="performSearch($event, associateable)"
      />
    </div>

    <div class="sm:block">
      <div
        class="border-b border-neutral-200 dark:border-neutral-600"
        v-show="hasActivities"
      >
        <div class="flex items-center justify-center">
          <nav
            ref="nav"
            class="overlow-y-hidden -mb-px flex grow snap-x snap-mandatory overflow-x-auto sm:grow-0 sm:space-x-4 lg:space-x-6"
          >
            <a
              v-for="filter in filters"
              :key="filter.id"
              @click.prevent="activateFilter(filter)"
              href="#"
              :class="[
                activeFilter === filter.id
                  ? 'border-neutral-700 text-neutral-700 dark:border-neutral-400 dark:text-neutral-200'
                  : 'border-transparent text-neutral-500 hover:border-neutral-300 hover:text-neutral-700 dark:text-neutral-100 dark:hover:border-neutral-500 dark:hover:text-neutral-300',
                'group inline-flex min-w-full shrink-0 snap-start snap-always items-center justify-center whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium sm:min-w-0',
              ]"
            >
              {{ filter.title }} <span class="ml-2">({{ filter.total }})</span>
            </a>
          </nav>
        </div>
      </div>
    </div>

    <div class="py-2 sm:py-4">
      <p
        class="mt-6 flex items-center justify-center font-medium text-neutral-800 dark:text-neutral-200"
        v-if="
          activeFilterInstance.total === 0 &&
          dataLoadedFirstTime &&
          !(isPerformingSearch && !hasSearchResults)
        "
      >
        <icon :icon="activeFilterInstance.empty.icon" class="mr-2 h-5 w-5" />{{
          activeFilterInstance.empty.text
        }}
      </p>

      <card-placeholder v-if="!dataLoadedFirstTime && !hasActivities" pulse />

      <activities
        :activities="activeFilterInstance.data"
        :resource-name="resourceName"
      />
    </div>

    <div
      class="mt-6 text-center text-neutral-800 dark:text-neutral-200"
      v-show="isPerformingSearch && !hasSearchResults"
      v-t="'app.no_search_results'"
    />

    <infinity-loader
      @handle="infiniteHandler($event, associateable)"
      :scroll-element="scrollElement"
      ref="infinity"
    />
  </record-tab>
</template>
<script>
import Activities from './RelatedRecordActivities'
import CreateActivity from './RelatedRecordCreate'
import Recordable from '@/components/RecordTabs/Recordable'
import RecordTab from '@/components/RecordTabs/RecordTab'
import orderBy from 'lodash/orderBy'
import filter from 'lodash/filter'
import CardPlaceholder from '@/components/Loaders/CardPlaceholder'

export default {
  mixins: [Recordable],
  components: {
    Activities,
    CreateActivity,
    RecordTab,
    CardPlaceholder,
  },
  data: () => ({
    associateable: 'activities',
    activeFilter: 'all',
    // Because of the filters badges totals, if the user has more then 15 activities, they won't be accurate
    perPage: 100,
  }),
  computed: {
    /**
     * Get the current active filter Object
     *
     * @return {Object}
     */
    activeFilterInstance() {
      return this.filters.find(filter => filter.id === this.activeFilter)
    },

    /**
     * Get the available filters
     *
     * @return {Array}
     */
    filters() {
      return [
        {
          id: 'all',
          title: this.$t('activity.filters.all'),
          data: this.activities,
          total: this.activities.length,
          empty: { text: this.$t('app.all_caught_up'), icon: 'Check' },
        },
        {
          id: 'today',
          title: this.$t('activity.filters.today'),
          data: this.todaysActivities,
          total: this.todaysActivities.length,
          empty: { text: this.$t('app.all_caught_up'), icon: 'Check' },
        },
        {
          id: 'tomorrow',
          title: this.$t('activity.filters.tomorrow'),
          data: this.tomorrowActivities,
          total: this.tomorrowActivities.length,
          empty: { text: this.$t('app.all_caught_up'), icon: 'Check' },
        },
        {
          id: 'this_week',
          title: this.$t('activity.filters.this_week'),
          data: this.thisWeekActivities,
          total: this.thisWeekActivities.length,
          empty: { text: this.$t('app.all_caught_up'), icon: 'Check' },
        },
        {
          id: 'next_week',
          title: this.$t('activity.filters.next_week'),
          data: this.nextWeekActivities,
          total: this.nextWeekActivities.length,
          empty: { text: this.$t('app.all_caught_up'), icon: 'Check' },
        },
        {
          id: 'done',
          title: this.$t('activity.filters.done'),
          data: this.completedActivities,
          total: this.completedActivities.length,
          empty: {
            text: this.$t('activity.filters.done_empty_state'),
            icon: 'CheckCircle',
          },
        },
      ]
    },

    /**
     * Get the today activities
     *
     * @return {Array}
     */
    todaysActivities() {
      return filter(this.incompleteActivities, d =>
        this.createDueDateMoment(d.due_date).isSame(
          this.appMoment().clone().tz(this.userTimezone),
          'day'
        )
      )
    },

    /**
     * Get the tomorrow activities
     *
     * @return {Array}
     */
    tomorrowActivities() {
      return filter(this.incompleteActivities, d => {
        return this.createDueDateMoment(d.due_date).isSame(
          this.appMoment().clone().tz(this.userTimezone).add(1, 'day'),
          'day'
        )
      })
    },

    /**
     * Get the this week activities
     *
     * @return {Array}
     */
    thisWeekActivities() {
      return filter(this.incompleteActivities, d =>
        this.createDueDateMoment(d.due_date)
          .isoWeekday(Number(this.currentUser.first_day_of_week))
          .isSame(
            this.appMoment()
              .clone()
              .tz(this.userTimezone)
              .isoWeekday(Number(this.currentUser.first_day_of_week)),
            'week'
          )
      )
    },

    /**
     * Get the next week activities
     *
     * @return {Array}
     */
    nextWeekActivities() {
      return filter(this.incompleteActivities, d =>
        this.createDueDateMoment(d.due_date)
          .isoWeekday(Number(this.currentUser.first_day_of_week))
          .isSame(
            this.appMoment()
              .clone()
              .tz(this.userTimezone)
              .isoWeekday(Number(this.currentUser.first_day_of_week))
              .add(1, 'week'),
            'week'
          )
      )
    },

    /**
     * Get the activities for the resource ordered by not completed on top and by due date
     *
     * @return {Array}
     */
    activities() {
      return orderBy(
        this.searchResults || this.resourceRecord.activities,
        [
          'is_completed',
          activity => this.createDueDateMoment(activity.due_date).toDate(),
        ],
        ['asc', 'asc']
      )
    },

    /**
     * Get the currently incomplete activities from the loaded activities
     *
     * @return {Array}
     */
    incompleteActivities() {
      return this.activities.filter(activity => !activity.is_completed)
    },

    /**
     * Get the currently completed activities from the loaded activities
     *
     * @return {Array}
     */
    completedActivities() {
      return this.activities.filter(activity => activity.is_completed)
    },

    /**
     * Check whether the record has activities
     *
     * @return {Boolean}
     */
    hasActivities() {
      return this.activities.length > 0
    },

    /**
     * Record incomplete activities count
     *
     * We will check if the actual resource record incomplete_activities is 0 but the actual
     * loaded activities have incomplete, in this case, we will return the value from the loaded activities
     *
     * This may happen e.q. if there is a workflows e.q. company created => create activity
     * But because the workflow is executed on app terminating, the resource record data
     * is already retrieved before termination and the incomplete_activities_for_user_count will be 0
     *
     * TODO/TIP/NOTE: This can be solved if we don't use the cache data from create and on route VIEW enter re-retrieve
     * the actual resource record.
     *
     * @return {Number}
     */
    incompleteActivitiesCount() {
      let incompleteActivitiesCount =
        this.resourceRecord.incomplete_activities_for_user_count

      if (
        incompleteActivitiesCount === 0 &&
        this.incompleteActivities.length > 0
      ) {
        return this.incompleteActivities.length
      }

      return incompleteActivitiesCount
    },
  },
  methods: {
    /**
     * Activate the given filter
     *
     * @return {Void}
     */
    activateFilter(filter) {
      this.activeFilter = filter.id
      this.loadData()
    },

    /**
     * Create Moment.js instance from the given due date Object
     *
     * @param {Object} date
     *
     * @return {Object}
     */
    createDueDateMoment(date) {
      if (!date.time) {
        return this.appMoment(date.date)
      }

      return this.appMoment(date.date + ' ' + date.time + ':00')
        .clone()
        .tz(this.userTimezone)
    },

    /**
     * Handle resource record updated event
     *
     * We will use this function to retieve again the first page of the activities
     * for the current resource
     *
     * The check is performed e.q. if new activities are created from workflows, it won't be fetched
     * e.q. when deal stage is updated
     *
     * @return {Void}
     */
    resourceRecordUpdated(record) {
      // When using preview modal it may not be the same resource
      if (Number(record.id) === Number(this.resourceRecord.id)) {
        this.refresh(this.associateable)
      }
    },
  },
  mounted() {
    Innoclapps.$on(
      `${this.resourceName}-record-updated`,
      this.resourceRecordUpdated
    )

    if (
      this.$route.query.resourceId &&
      this.$route.query.section === this.associateable
    ) {
      // Wait till the data is loaded for the first time and the
      // elements are added to the document so we can have a proper scroll
      const unwatcher = this.$watch('dataLoadedFirstTime', () => {
        this.focusToAssociateableElement(
          this.associateable,
          this.$route.query.resourceId
        ).then(() => {
          this.$route.query.comment_id &&
            this.$store.commit('comments/SET_VISIBILITY', {
              commentableId: this.$route.query.resourceId,
              commentableType: this.associateable,
              visible: true,
            })
        })
        unwatcher()
      })
    }
  },
  unmounted() {
    Innoclapps.$off(
      `${this.resourceName}-record-updated`,
      this.resourceRecordUpdated
    )
  },
}
</script>
