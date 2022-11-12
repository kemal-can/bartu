<template>
  <record-tab
    @activated-first-time="loadData"
    :title="$t('note.notes')"
    :section-id="associateable"
    badge-variant="neutral"
    :badge="resourceRecord.notes_count"
    :classes="{ 'opacity-70': !hasNotes }"
    icon="PencilAlt"
  >
    <create-note
      v-show="dataLoadedFirstTime || hasNotes"
      :resource-name="resourceName"
      :show-introduction-section="!hasNotes"
    />

    <div class="my-3">
      <input-search
        v-model="search"
        v-show="hasNotes || search"
        @input="performSearch($event, associateable)"
      />
    </div>

    <card-placeholder v-if="!dataLoadedFirstTime && !hasNotes" pulse />

    <notes :notes="notes" :resource-name="resourceName" />

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
import Notes from './Index'
import CreateNote from './Create'
import Recordable from '@/components/RecordTabs/Recordable'
import RecordTab from '@/components/RecordTabs/RecordTab'
import orderBy from 'lodash/orderBy'
import CardPlaceholder from '@/components/Loaders/CardPlaceholder'

export default {
  mixins: [Recordable],
  components: {
    Notes,
    CreateNote,
    RecordTab,
    CardPlaceholder,
  },
  data: () => ({
    associateable: 'notes',
  }),
  computed: {
    /**
     * Get the record notes from the resource store
     *
     * @return {Array}
     */
    notes() {
      return orderBy(
        this.searchResults || this.resourceRecord.notes,
        'created_at',
        'desc'
      )
    },

    /**
     * Check whether the record has notes
     *
     * @return {Boolean}
     */
    hasNotes() {
      return this.notes.length > 0
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
