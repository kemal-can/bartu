<template>
  <div>
    <p
      class="inline-flex items-center text-sm font-medium text-neutral-800 dark:text-white"
      v-show="hasComments"
      v-bind="$attrs"
    >
      <span class="mr-3 h-5 w-5">
        <icon
          icon="ChatAlt"
          v-show="!requestInProgress"
          class="h-5 w-5 text-current"
        />
        <i-spinner
          class="mt-px h-4 w-4 text-current"
          v-if="requestInProgress"
        />
      </span>

      <a href="#" @click="toggle" class="inline-flex items-center">
        <span>
          {{ $t('comment.total', { total: countComputed }) }}
        </span>
        <icon
          :icon="commentsAreVisible ? 'ChevronDown' : 'ChevronRight'"
          class="ml-3 h-4 w-4"
        />
      </a>
    </p>

    <div
      v-show="commentsAreVisible && commentsAreLoaded"
      :class="['mt-3', listWrapperClass]"
    >
      <comments
        v-if="commentsAreLoaded"
        :comments="commentsComputed"
        :commentable-type="commentableType"
        :commentable-id="commentableId"
        :via-resource="viaResource"
        :auto-focus-if-required="true"
        @deleted="handleCommentDeletedEvent"
      />
    </div>
  </div>
</template>
<script>
import Commentable from './Commentable'
import Comments from '@/views/Comments/Index'
import findIndex from 'lodash/findIndex'
import debounce from 'lodash/debounce'
import { mapState } from 'vuex'
export default {
  emits: ['update:count'],
  inheritAttrs: false,
  mixins: [Commentable],
  components: { Comments },
  props: {
    count: Number,
    listWrapperClass: [Array, Object, String],
  },
  watch: {
    /**
     * Emit the update count event when new comments are added/deleted
     * For in case any parent component interested to update it's data
     */
    countComputed: function (newVal, oldVal) {
      this.$emit('update:count', newVal)
    },

    /**
     * Add debounce when the component is embedded in timeline and regular view
     * Will fire twice, but we need to load the comments once as they are stored in the store
     */
    commentsAreVisible: debounce(function (newVal) {
      if (newVal) {
        // When the comments visibility is toggled on after a comment is added
        // We don't need to make a request to load them all as we already know
        // that there were zero comments and new one was created
        if (
          this.count === 0 &&
          this.commentsComputed.length === 1 &&
          this.commentsComputed[0].was_recently_created === true
        ) {
          this.$store.commit('comments/SET_LOADED', {
            commentableId: this.commentableId,
            commentableType: this.commentableType,
            value: true,
          })
          return
        }

        !this.commentsAreLoaded && this.loadComments()
      }
    }, 250),
  },

  computed: {
    ...mapState({
      requestInProgress: state => state.comments.requestInProgress,
    }),
    /**
     * Indicates whether the comments are visible
     *
     * @return {Boolean}
     */
    commentsAreVisible() {
      return this.$store.getters['comments/areVisibleFor'](
        this.commentableId,
        this.commentableType
      )
    },

    /**
     * Indicates whether the comments are loaded
     *
     * @return {Boolean}
     */
    commentsAreLoaded() {
      return this.$store.getters['comments/areLoadedFor'](
        this.commentableId,
        this.commentableType
      )
    },

    /**
     * Get the count of the comments
     *
     * @return {Number}
     */
    countComputed() {
      if (!this.commentsAreLoaded) {
        return this.count || 0
      }

      return this.commentsComputed.length
    },

    /**
     * Check whether there are comments
     *
     * @return {Boolean}
     */
    hasComments() {
      return this.countComputed > 0
    },

    /**
     * Get the comments for the record
     *
     * @return {Array}
     */
    commentsComputed() {
      if (this.viaResource) {
        const relRecords =
          this.$store.state[this.viaResource].record[this.commentableType]

        return (
          relRecords[findIndex(relRecords, ['id', this.commentableId])]
            .comments || []
        )
      }

      return this.$store.state[this.viaResource].record.comments || []
    },
  },
  methods: {
    /**
     * Load the resource comments
     */
    async loadComments() {
      let comments = await this.$store.dispatch('comments/getAll', {
        resourceName: this.commentableType,
        resourceId: this.commentableId,
      })

      this.$store.commit('comments/SET_LOADED', {
        commentableId: this.commentableId,
        commentableType: this.commentableType,
        value: true,
      })

      if (this.viaResource) {
        this.$store.commit(
          this.viaResource + '/UPDATE_RECORD_HAS_MANY_RELATIONSHIP',
          {
            relation: this.commentableType,
            id: this.commentableId,
            item: { comments },
          }
        )
        return
      }

      this.$store.commit(this.commentableType + '/SET_RECORD', {
        comments,
      })
    },

    /**
     * Toggle the comments visibility
     */
    toggle() {
      this.updateVisibilty(!this.commentsAreVisible)
    },

    /**
     * Handle the comment deleted event
     *
     * @param  {Object} comment
     *
     * @return {Promise}
     */
    async handleCommentDeletedEvent(comment) {
      await this.$nextTick()

      if (this.countComputed === 0) {
        this.updateVisibilty(false)
      }
    },

    /**
     * Update the comments visibility indicator
     *
     * @param  {Boolean} value
     *
     * @return {Void}
     */
    updateVisibilty(value) {
      this.$store.commit('comments/SET_VISIBILITY', {
        commentableId: this.commentableId,
        commentableType: this.commentableType,
        visible: value,
      })
    },
  },
  beforeUnmount() {
    this.$store.commit('comments/SET_LOADED', {
      commentableId: this.commentableId,
      commentableType: this.commentableType,
      value: false,
    })
  },
}
</script>
