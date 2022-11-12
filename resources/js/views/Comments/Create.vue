<template>
  <div :id="'add-comment-' + commentableType + '-' + commentableId">
    <editor
      :placeholder="$t('comment.add_placeholder')"
      v-model="form.body"
      :with-mention="true"
      @init="() => $refs.editor.focus()"
      @input="form.errors.clear('body')"
      ref="editor"
    />
    <form-error :form="form" field="body" />
    <div class="mt-2 flex justify-end space-x-2">
      <i-button variant="white" @click="$emit('cancelled')" size="sm">{{
        $t('app.cancel')
      }}</i-button>
      <i-button
        variant="secondary"
        @click="store"
        size="sm"
        :disabled="form.busy"
        >{{ $t('app.save') }}</i-button
      >
    </div>
  </div>
</template>
<script>
import Editor from '@/components/Editor'
import Commentable from './Commentable'
import Form from '@/components/Form/Form'

export default {
  mixins: [Commentable],
  emits: ['created', 'cancelled'],
  components: { Editor },
  data: () => ({
    form: new Form({
      body: '',
    }),
  }),
  methods: {
    /**
     * Add new comment in store when displayed via resource
     *
     * @return {Void}
     */
    addCommentInStoreWhenViaResource(comment) {
      this.$store.commit(
        this.viaResource + '/ADD_RECORD_HAS_MANY_SUB_RELATION',
        {
          relation: this.commentableType,
          relation_id: this.commentableId,
          sub_relation: 'comments',
          item: comment,
        }
      )
    },

    /**
     * Add new comment from in store
     *
     * @return {Void}
     */
    addCommentInStore(comment) {
      this.$store.commit(
        this.commentableType + '/ADD_RECORD_HAS_MANY_RELATIONSHIP',
        {
          relation: 'comments',
          item: comment,
        }
      )
    },

    /**
     * Action executed when a comment is created
     *
     * @param  {Object} comment
     *
     * @return {Void}
     */
    handleCommentCreated(comment) {
      this.form.reset()

      this.viaResource
        ? this.addCommentInStoreWhenViaResource(comment)
        : this.addCommentInStore(comment)

      this.$emit('created', comment)
      Innoclapps.success(this.$t('comment.created'))
    },

    /**
     * Add new comment
     *
     * @return {Void}
     */
    store() {
      if (this.viaResource) {
        this.form.withQueryString({
          via_resource: this.viaResource,
          via_resource_id: this.resourceRecord.id,
        })
      }

      this.form
        .post(`${this.commentableType}/${this.commentableId}/comments`)
        .then(comment => this.handleCommentCreated(comment))
    },
  },
}
</script>
