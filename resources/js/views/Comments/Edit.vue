<template>
  <div>
    <editor
      v-model="form.body"
      :with-mention="true"
      @input="form.errors.clear('body')"
      ref="editor"
    />
    <form-error :form="form" field="body" />
    <div class="mt-2 space-x-2 text-right">
      <i-button variant="white" @click="$emit('cancelled')" size="sm">{{
        $t('app.cancel')
      }}</i-button>
      <i-button
        variant="secondary"
        @click="update"
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
  emits: ['updated', 'cancelled'],
  mixins: [Commentable],
  components: { Editor },
  props: {
    comment: {
      required: true,
      type: Object,
    },
  },
  data() {
    return {
      form: new Form({
        body: this.comment.body,
      }),
    }
  },
  methods: {
    /**
     * Update comment in store when displayed via resource
     *
     * @return {Void}
     */
    updateCommentInStoreWhenViaResource(comment) {
      this.$store.commit(
        this.viaResource + '/UPDATE_RECORD_HAS_MANY_SUB_RELATION',
        {
          relation: this.commentableType,
          relation_id: this.commentableId,
          sub_relation: 'comments',
          sub_relation_id: comment.id,
          item: comment,
        }
      )
    },

    /**
     * Update comment in store
     *
     * @return {Void}
     */
    updateCommentInStore(comment) {
      this.$store.commit(
        this.commentableType + '/UPDATE_RECORD_HAS_MANY_RELATIONSHIP',
        {
          relation: 'comments',
          id: comment.id,
          item: comment,
        }
      )
    },

    /**
     * Update the current comment
     *
     * @return {Void}
     */
    update() {
      if (this.viaResource) {
        this.form.withQueryString({
          via_resource: this.viaResource,
          via_resource_id: this.resourceRecord.id,
        })
      }

      this.form.put(`/comments/${this.comment.id}`).then(comment => {
        this.viaResource
          ? this.updateCommentInStoreWhenViaResource(comment)
          : this.updateCommentInStore(comment)

        this.$emit('updated', comment)
        Innoclapps.success(this.$t('comment.updated'))
      })
    },
  },
}
</script>
