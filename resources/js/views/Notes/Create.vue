<template>
  <div class="mb-2 sm:mb-4">
    <i-card v-if="noteBeingCreated">
      <editor
        :placeholder="$t('note.write')"
        v-model="form.body"
        :with-mention="true"
        @init="() => $refs.editor.focus()"
        @input="form.errors.clear('body')"
        ref="editor"
      />
      <form-error :form="form" field="body" />
      <template #footer>
        <div class="flex flex-col sm:flex-row sm:items-center">
          <div class="grow">
            <create-follow-up-task :form="form" />
          </div>
          <div class="mt-2 space-y-2 sm:mt-0 sm:space-y-0 sm:space-x-2">
            <i-button
              class="w-full sm:w-auto"
              variant="white"
              size="sm"
              @click="noteBeingCreated = false"
              >{{ $t('app.cancel') }}</i-button
            >
            <i-button
              class="w-full sm:w-auto"
              @click="store"
              size="sm"
              :disabled="form.busy"
              >{{ $t('note.add') }}</i-button
            >
          </div>
        </div>
      </template>
    </i-card>

    <div
      class="mb-4 block"
      v-show="!noteBeingCreated"
      v-if="showIntroductionSection"
    >
      <div
        class="rounded-md border border-neutral-200 bg-neutral-50 px-6 py-5 shadow-sm dark:border-neutral-900 dark:bg-neutral-900 sm:flex sm:items-start sm:justify-between"
      >
        <div class="sm:flex sm:items-center">
          <span
            class="hidden rounded border border-neutral-200 bg-neutral-100 px-3 py-1.5 dark:border-neutral-600 dark:bg-neutral-700/60 sm:inline-flex sm:self-start"
          >
            <icon
              icon="PencilAlt"
              class="h-5 w-5 text-neutral-700 dark:text-neutral-200"
            />
          </span>

          <div class="sm:ml-4">
            <div
              class="text-sm font-medium text-neutral-900 dark:text-neutral-100"
              v-t="'note.info'"
            />
          </div>
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-6 sm:flex-shrink-0">
          <i-button @click="showCreateForm" size="sm" icon="Plus">
            {{ $t('note.add') }}
          </i-button>
        </div>
      </div>
    </div>

    <div
      v-show="!noteBeingCreated && !showIntroductionSection"
      class="mb-8 text-right"
    >
      <i-button @click="showCreateForm" size="sm" icon="Plus">
        {{ $t('note.add') }}
      </i-button>
    </div>
  </div>
</template>
<script>
import Editor from '@/components/Editor'
import CreateFollowUpTask from '@/views/Activity/CreateFollowUpTask'
import InteractsWithResource from '@/mixins/InteractsWithResource'
import Form from '@/components/Form/Form'

export default {
  mixins: [InteractsWithResource],
  components: {
    Editor,
    CreateFollowUpTask,
  },
  props: {
    showIntroductionSection: {
      default: true,
      type: Boolean,
    },
  },
  data: () => ({
    noteBeingCreated: false,
    form: new Form({
      body: '',
      with_task: false,
      task_date: null,
    }),
  }),
  methods: {
    /**
     * On create clicked event
     *
     * @return {Void}
     */
    showCreateForm() {
      this.noteBeingCreated = true
    },

    /**
     * Action executed when a note is created
     *
     * @param  {Object} note
     *
     * @return {Void}
     */
    handleNoteCreated(note) {
      this.form.reset()

      if (note.createdActivity) {
        this.addResourceRecordHasManyRelationship(
          note.createdActivity,
          'activities'
        )
        this.incrementResourceRecordCount(
          'incomplete_activities_for_user_count'
        )
        delete note.createdActivity
      }

      this.addResourceRecordHasManyRelationship(note, 'notes')
      this.incrementResourceRecordCount('notes_count')
      Innoclapps.success(this.$t('note.created'))
    },

    /**
     * Create a note
     *
     * @return {Void}
     */
    store() {
      this.form.set(this.resourceName, [this.resourceRecord.id])

      this.form.withQueryString({
        via_resource: this.resourceName,
        via_resource_id: this.resourceRecord.id,
      })

      this.form.post('/notes').then(note => this.handleNoteCreated(note))
    },
  },
}
</script>
