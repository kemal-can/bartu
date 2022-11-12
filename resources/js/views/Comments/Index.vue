<template>
  <div ref="comments">
    <p
      class="text-center text-sm text-neutral-800 dark:text-neutral-400"
      v-if="!hasComments"
      v-t="'comment.no_comments'"
    />
    <comment
      class="mb-3"
      v-for="comment in comments"
      :key="comment.id"
      :commentable-type="commentableType"
      :commentable-id="commentableId"
      :via-resource="viaResource"
      :comment="comment"
      @deleted="$emit('deleted', $event)"
    />
  </div>
</template>
<script>
import Comment from './View'
import Commentable from './Commentable'
export default {
  emits: ['deleted'],
  mixins: [Commentable],
  components: { Comment },
  props: { autoFocusIfRequired: Boolean, comments: Array },
  data: () => ({
    bgTimeoutClear: null,
  }),
  computed: {
    hasComments() {
      return this.comments.length > 0
    },
  },
  methods: {
    /**
     * Focus to the comment if needed
     *
     * @return {Void}
     */
    focusIfRequired() {
      if (!this.$route.query.comment_id || !this.autoFocusIfRequired) {
        return
      }

      this.$nextTick(() => {
        const $comment = this.$refs.comments.querySelector(
          '#comment-' + this.$route.query.comment_id
        )

        if ($comment) {
          $comment.scrollIntoView({
            behavior: 'auto',
            block: 'center',
            inline: 'nearest',
          })

          // Add background color so it indicates that is focused
          $comment.classList.add('bg-info-50')

          this.bgTimeoutClear = setTimeout(
            () => $comment.classList.remove('bg-info-50'),
            10000
          )
        }
      })
    },
  },
  mounted() {
    this.focusIfRequired()
  },
  beforeUnmount() {
    this.bgTimeoutClear && clearTimeout(this.bgTimeoutClear)
  },
}
</script>
