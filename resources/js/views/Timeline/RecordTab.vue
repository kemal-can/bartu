<template>
  <record-tab
    @activated-first-time="loadData"
    :title="$t('timeline.timeline')"
    section-id="timeline"
    icon="MenuAlt2"
  >
    <div class="mt-4 flex items-center sm:ml-1 sm:mt-1">
      <p
        class="mr-2 -mt-0.5 font-medium text-neutral-700 dark:text-neutral-300"
        v-t="'filters.filter_by'"
      />
      <dropdown-select
        :items="filters"
        value-key="id"
        label-key="name"
        @change="loadData"
        v-model="filter"
      />
    </div>
    <div class="pt-6">
      <div class="flow-root">
        <ul role="list" class="sm:-mb-8">
          <li
            v-for="(entry, index) in timeline"
            :key="'timeline-' + entry.timeline_component + '-' + entry.id"
          >
            <div class="relative sm:pb-8">
              <span
                v-if="index !== timeline.length - 1"
                class="absolute top-5 left-5 -ml-px hidden h-full w-0.5 bg-neutral-200 dark:bg-neutral-600 sm:block"
                aria-hidden="true"
              />
              <div class="relative flex items-start sm:space-x-3">
                <component
                  :is="'timeline-' + entry.timeline_component"
                  :log="entry"
                  :resource-name="resourceName"
                  :resource-record="resourceRecord"
                />
              </div>
            </div>
            <div
              class="block sm:hidden"
              aria-hidden="true"
              v-if="index !== timeline.length - 1"
            >
              <div class="py-5">
                <div
                  class="border-t border-neutral-200 dark:border-neutral-600"
                />
              </div>
            </div>
          </li>
        </ul>
        <infinity-loader
          @handle="infiniteHandler($event, 'changelog')"
          :scroll-element="scrollElement"
          ref="infinity"
        />
      </div>
    </div>
  </record-tab>
</template>
<script>
import TimelineCreated from './TimelineCreated'
import TimelineUpdated from './TimelineUpdated'
import TimelineNote from './TimelineNote'
import TimelineCall from './TimelineCall'
import TimelineActivity from './TimelineActivity'
import TimelineAttached from './TimelineAttached'
import TimelineDetached from './TimelineDetached'
import TimelineGeneric from './TimelineGeneric'
import TimelineDeleted from './TimelineDeleted'
import TimelineRestored from './TimelineRestored'
import TimelineEmail from './TimelineEmail'
import TimelineWebFormSubmission from './TimelineWebFormSubmission'
import Recordable from '@/components/RecordTabs/Recordable'
import RecordTab from '@/components/RecordTabs/RecordTab'
import orderBy from 'lodash/orderBy'
import findIndex from 'lodash/findIndex'

export default {
  mixins: [Recordable],
  components: {
    TimelineRestored,
    TimelineDeleted,
    TimelineCreated,
    TimelineUpdated,
    TimelineNote,
    TimelineCall,
    TimelineActivity,
    TimelineAttached,
    TimelineDetached,
    TimelineGeneric,
    TimelineEmail,
    TimelineWebFormSubmission,
    RecordTab,
  },
  props: {
    resources: {
      type: Array,
      default() {
        return ['notes', 'calls', 'activities', 'emails']
      },
    },
  },
  data() {
    return {
      filter: {
        id: null,
        name: this.$t('app.all'),
      },
      filters: [
        {
          id: null,
          name: this.$t('app.all'),
        },
        {
          id: 'changelog',
          name: this.$t('app.changelog'),
        },
        {
          id: 'activities',
          name: this.$t('activity.activities'),
        },
        {
          id: 'emails',
          name: this.$t('mail.emails'),
        },
        {
          id: 'calls',
          name: this.$t('call.calls'),
        },
        {
          id: 'notes',
          name: this.$t('note.notes'),
        },
      ].filter(
        filter =>
          this.resources.indexOf(filter.id) > -1 ||
          filter.id === null ||
          filter.id === 'changelog'
      ),
    }
  },
  computed: {
    changelog() {
      // The changelog is returned too from the record request
      // these are the general changelog related to the model
      // in this case, when the record is updated the new changelog
      // are able to be reflected and shown in the tab
      return !this.filter.id || this.filter.id === 'changelog'
        ? this.resourceRecord.changelog || []
        : []
    },

    /**
     * Get the resource notes
     *
     * @return {Array}
     */
    notes() {
      return !this.filter.id || this.filter.id === 'notes'
        ? this.resourceRecord.notes || []
        : []
    },

    /**
     * Get the resource calls
     *
     * @return {Array}
     */
    calls() {
      return !this.filter.id || this.filter.id === 'calls'
        ? this.resourceRecord.calls || []
        : []
    },

    /**
     * Get the resource emails
     *
     * @return {Array}
     */
    emails() {
      return !this.filter.id || this.filter.id === 'emails'
        ? this.resourceRecord.emails || []
        : []
    },

    /**
     * Get the resource activities
     *
     * @return {Array}
     */
    activities() {
      return !this.filter.id || this.filter.id === 'activities'
        ? this.resourceRecord.activities || []
        : []
    },

    /**
     * All activities merged and sorted properly
     *
     * @return {Array}
     */
    timeline() {
      return orderBy(
        [
          ...this.changelog,
          ...this.notes,
          ...this.calls,
          ...this.emails,
          ...this.activities,
        ],
        ['is_pinned', 'pinned_date', log => new Date(log.created_at)],
        ['desc', 'desc', 'desc']
      )
    },
  },
  methods: {
    /**
     * Handle the infinity load response
     *
     * @param  {Object} data
     *
     * @return {Void}
     */
    handleInfinityResult(data) {
      data.data.forEach(entry => {
        let associateable = entry.timeline_relation

        let existsInStore =
          findIndex(this.resourceRecord[associateable], [
            'id',
            Number(entry.id),
          ]) !== -1

        if (!existsInStore) {
          this.addResourceRecordHasManyRelationship(entry, associateable)

          return
        }

        this.updateResourceRecordHasManyRelationship(entry, associateable)
      })
    },

    /**
     * Make the request for data
     *
     * @param  {string} associateable
     * @param  {int} page

     * @return {Void}
     */
    makeRequestForData(associateable, page) {
      return Innoclapps.request().get(`${this.resourceRecord.path}/timeline`, {
        params: {
          page: page,
          q: this.search,
          per_page: this.perPage,
          resources: this.resources,
        },
      })
    },
  },
  mounted() {
    this.$nextTick(() => this.loadData())
  },
}
</script>
