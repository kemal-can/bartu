<template>
  <a
    href="#"
    v-bind="$attrs"
    v-show="!commentBeingCreated"
    @click.prevent="updateVisible(true)"
    class="link inline-flex items-center text-sm"
  >
    <icon icon="Plus" class="mr-1 h-4 w-4" /> {{ $t('comment.add') }}
  </a>
  <create-comment
    v-if="commentBeingCreated"
    :commentable-type="commentableType"
    :commentable-id="commentableId"
    :via-resource="viaResource"
    @created="handleCommentCreated"
    @cancelled="updateVisible(false)"
  />
</template>
<script>
import Commentable from './Commentable'
import CreateComment from '@/views/Comments/Create'
export default {
  inheritAttrs: false,
  emits: ['created'],
  mixins: [Commentable],
  components: { CreateComment },
  computed: {
    /**
     * Indicates whether a comment is being created
     *
     * @return {Boolean}
     */
    commentBeingCreated() {
      return this.$store.getters['comments/isCommentBeingCreatedFor'](
        this.commentableId,
        this.commentableType
      )
    },
  },
  methods: {
    /**
     * Update the comment being created value
     *
     * @param  {Boolean} value
     *
     * @return {Void}
     */
    updateVisible(value) {
      this.$store.commit('comments/SET_ADD_COMMENT_VISIBILITY', {
        commentableId: this.commentableId,
        commentableType: this.commentableType,
        value: value,
      })
    },

    /**
     * Handle comment created event
     *
     * @param  {Object} comment
     *
     * @return {Void}
     */
    handleCommentCreated(comment) {
      this.$emit('created', comment)
    },
  },
}
</script>
