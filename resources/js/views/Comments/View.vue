<template>
  <div>
    <!-- The :id="'comment-'+comment.id" is used to auto focus the comment in index.vue -->
    <div
      :class="[
        'comment rounded-md bg-white dark:bg-neutral-700',
        {
          'border border-neutral-300 py-2.5 px-4 shadow-sm dark:border-neutral-600':
            !commentBeingEdited,
        },
      ]"
      :id="'comment-' + comment.id"
    >
      <div class="flex flex-wrap" v-show="!commentBeingEdited">
        <div class="grow">
          <i-avatar size="xs" class="mr-1" :src="comment.creator.avatar_url" />
          <i18n-t
            scope="global"
            :keypath="'comment.user_left_comment'"
            tag="span"
            class="text-sm text-neutral-800 dark:text-white"
          >
            <template #user>
              <b v-text="comment.creator.name"></b>
            </template>
          </i18n-t>
        </div>
        <div class="mt-1 text-sm text-neutral-500 dark:text-neutral-300">
          {{ localizedDateTime(comment.created_at) }}
        </div>
      </div>
      <div
        class="wysiwyg-text mt-3"
        v-show="!commentBeingEdited"
        v-html="comment.body"
      />
      <edit-comment
        :comment="comment"
        class="mt-3"
        :commentable-type="commentableType"
        :commentable-id="commentableId"
        :via-resource="viaResource"
        @cancelled="commentBeingEdited = false"
        @updated="commentBeingEdited = false"
        v-if="commentBeingEdited"
      />
    </div>
    <div class="flex justify-end space-x-2 py-2 text-sm">
      <a
        href="#"
        class="link"
        v-if="comment.created_by !== currentUser.id && !commentBeingEdited"
        v-t="'comment.reply'"
        @click.prevent="replyToComment"
      />
      <a
        href="#"
        class="link"
        v-show="comment.authorizations.update && !commentBeingEdited"
        v-t="'app.edit'"
        @click.prevent="commentBeingEdited = true"
      />
      <a
        href="#"
        class="text-danger-500 hover:text-danger-700"
        v-show="comment.authorizations.delete && !commentBeingEdited"
        v-t="'app.delete'"
        @click.prevent="destroy(comment.id)"
      />
    </div>
  </div>
</template>
<script>
import EditComment from './Edit.vue'
import Commentable from './Commentable'
export default {
  emits: ['deleted'],
  mixins: [Commentable],
  components: { EditComment },
  props: {
    comment: {
      required: true,
      type: Object,
    },
  },
  data: () => ({
    commentBeingEdited: false,
  }),
  methods: {
    /**
     * Initialize a reply to the current comment
     *
     * @return {Void}
     */
    replyToComment() {
      this.$store.commit('comments/SET_ADD_COMMENT_VISIBILITY', {
        commentableId: this.commentableId,
        commentableType: this.commentableType,
        value: true,
      })

      this.$nextTick(() => {
        const $addCommentWrapper = document.getElementById(
          'add-comment-' + this.commentableType + '-' + this.commentableId
        )

        $addCommentWrapper.scrollIntoView({
          behavior: 'smooth',
          block: 'center',
          inline: 'nearest',
        })

        // Add timeout untill editor is initialized
        setTimeout(() => {
          tinymce.activeEditor.setContent('')
          tinymce.activeEditor.concordCommands.insertMentionUser(
            this.comment.creator.id,
            this.comment.creator.name
          )
        }, 500)
      })
    },

    /**
     * Remove the current comment from store when displayed via resource
     *
     * @return {Void}
     */
    removeCommentFromStoreWhenViaResource() {
      this.$store.commit(
        this.viaResource + '/REMOVE_RECORD_HAS_MANY_SUB_RELATION',
        {
          relation: this.commentableType,
          relation_id: this.commentableId,
          sub_relation: 'comments',
          sub_relation_id: this.comment.id,
        }
      )
    },

    /**
     * Remove the current comment from store
     *
     * @return {Void}
     */
    removeCommentFromStore() {
      this.$store.commit(
        this.commentableType + '/REMOVE_RECORD_HAS_MANY_RELATIONSHIP',
        {
          relation: 'comments',
          id: this.comment.id,
        }
      )
    },

    /**
     * Delete the given comment
     *
     * @param  {Number} id
     *
     * @return {Void}
     */
    async destroy(id) {
      await this.$dialog.confirm()

      Innoclapps.request()
        .delete(`/comments/${this.comment.id}`)
        .then(() => {
          this.$emit('deleted', this.comment)

          this.viaResource
            ? this.removeCommentFromStoreWhenViaResource()
            : this.removeCommentFromStore()
        })
    },
  },
}
</script>
