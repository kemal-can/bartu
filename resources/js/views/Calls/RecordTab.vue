<template>
  <record-tab
    @activated-first-time="loadData"
    :title="$t('call.calls')"
    :section-id="associateable"
    badge-variant="neutral"
    :badge="resourceRecord.calls_count"
    :classes="{ 'opacity-70': !hasCalls }"
    icon="Phone"
  >
    <create-call
      v-show="dataLoadedFirstTime || hasCalls"
      :resource-name="resourceName"
      :show-introduction-section="!hasCalls"
    />

    <div class="my-3">
      <input-search
        v-model="search"
        v-show="hasCalls || search"
        @input="performSearch($event, associateable)"
      />
    </div>

    <card-placeholder v-if="!dataLoadedFirstTime && !hasCalls" pulse />

    <calls :calls="calls" :resource-name="resourceName" />

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
import Calls from './Index'
import CreateCall from './Create'
import Recordable from '@/components/RecordTabs/Recordable'
import RecordTab from '@/components/RecordTabs/RecordTab'
import orderBy from 'lodash/orderBy'
import CardPlaceholder from '@/components/Loaders/CardPlaceholder'

export default {
  mixins: [Recordable],
  components: {
    Calls,
    CreateCall,
    RecordTab,
    CardPlaceholder,
  },
  data: () => ({
    associateable: 'calls',
  }),
  computed: {
    /**
     * Get the record calls from the resource store
     *
     * @return {Array}
     */
    calls() {
      return orderBy(
        this.searchResults || this.resourceRecord.calls,
        'date',
        'desc'
      )
    },

    /**
     * Check whether the record has calls
     *
     * @return {Boolean}
     */
    hasCalls() {
      return this.calls.length > 0
    },
  },
  mounted() {
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
}
</script>
