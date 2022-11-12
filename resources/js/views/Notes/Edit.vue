<template>
  <i-card>
    <editor
      v-model="form.body"
      @input="form.errors.clear('body')"
      :with-mention="true"
    />
    <form-error :form="form" field="body" />

    <template #footer>
      <div class="flex justify-end space-x-2">
        <i-button variant="white" size="sm" @click="$emit('cancelled')">{{
          $t('app.cancel')
        }}</i-button>
        <i-button
          variant="primary"
          size="sm"
          @click="update"
          :disabled="form.busy"
          >{{ $t('app.save') }}</i-button
        >
      </div>
    </template>
  </i-card>
</template>
<script>
import Editor from '@/components/Editor'
import InteractsWithResource from '@/mixins/InteractsWithResource'
import Form from '@/components/Form/Form'

export default {
  emits: ['updated', 'cancelled'],
  mixins: [InteractsWithResource],
  components: { Editor },
  props: {
    note: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      form: new Form({
        body: this.note.body,
      }),
    }
  },
  methods: {
    /**
     * Update note in storage
     *
     * @return {Void}
     */
    update() {
      this.form.withQueryString({
        via_resource: this.resourceName,
        via_resource_id: this.resourceRecord.id,
      })

      this.form.put(`/notes/${this.note.id}`).then(note => {
        this.updateResourceRecordHasManyRelationship(note, 'notes')

        this.$emit('updated', note)

        Innoclapps.success(this.$t('note.updated'))
      })
    },
  },
}
</script>
