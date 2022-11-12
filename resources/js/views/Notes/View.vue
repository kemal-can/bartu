<template>
  <i-card
    :class="['note-' + note.id]"
    footer-class="inline-flex flex-col w-full"
    v-show="!noteBeingEdited"
    v-bind="$attrs"
  >
    <template #header>
      <div class="flex space-x-1.5">
        <a
          href="#"
          v-if="hasTextToCollapse"
          @click="collapsed = !collapsed"
          class="mr-2 mt-0.5 text-neutral-500 dark:text-neutral-300"
          ><icon
            :icon="collapsed ? 'ChevronRight' : 'ChevronDown'"
            class="h-4 w-4"
        /></a>

        <i-avatar size="xs" :src="note.user.avatar_url" />
        <div class="flex grow items-center" v-once>
          <i18n-t
            scope="global"
            keypath="note.info_created"
            tag="span"
            class="text-sm text-neutral-700 dark:text-white"
          >
            <template #user>
              <span class="font-medium">
                {{ note.user.name }}
              </span>
            </template>
            <template #date>
              <span
                class="font-medium"
                v-text="localizedDateTime(note.created_at)"
              />
            </template>
          </i18n-t>
        </div>
        <i-minimal-dropdown
          v-if="note.authorizations.update && note.authorizations.delete"
          class="ml-2 self-start md:ml-5"
        >
          <i-dropdown-item
            @click="toggleEdit"
            v-show="note.authorizations.update"
            >{{ $t('app.edit') }}</i-dropdown-item
          >
          <i-dropdown-item
            @click="destroy(note.id)"
            v-show="note.authorizations.delete"
            >{{ $t('app.delete') }}</i-dropdown-item
          >
        </i-minimal-dropdown>
      </div>
    </template>

    <text-collapse
      v-if="collapsable"
      :text="note.body"
      :length="250"
      v-model:collapsed="collapsed"
      @hasTextToCollapse="hasTextToCollapse = $event"
      @dblclick="toggleEdit"
      class="wysiwyg-text"
    />

    <div
      v-else
      @dblclick="toggleEdit"
      class="wysiwyg-text"
      v-html="note.body"
    />

    <comments-collapse
      class="mt-6"
      :via-resource="resourceName"
      :commentable-id="note.id"
      commentable-type="notes"
      :count="note.comments_count"
      @update:count="
        $store.commit(resourceName + '/UPDATE_RECORD_HAS_MANY_RELATIONSHIP', {
          relation: 'notes',
          id: note.id,
          item: { comments_count: $event },
        })
      "
    />

    <template #footer>
      <add-comment
        class="self-end"
        @created="updateCommentsVisibility(true)"
        :via-resource="resourceName"
        :commentable-id="note.id"
        commentable-type="notes"
      />
    </template>
  </i-card>
  <edit-note
    v-if="noteBeingEdited"
    @cancelled="noteBeingEdited = false"
    @updated="noteBeingEdited = false"
    :resource-name="resourceName"
    :note="note"
  />
</template>
<script>
import EditNote from './Edit'
import TextCollapse from '@/components/TextCollapse'
import InteractsWithResource from '@/mixins/InteractsWithResource'
import CommentsCollapse from '@/views/Comments/CommentsCollapse'
import AddComment from '@/views/Comments/AddComment'
export default {
  inheritAttrs: false,
  mixins: [InteractsWithResource],
  components: {
    EditNote,
    CommentsCollapse,
    AddComment,
    TextCollapse,
  },
  props: {
    note: { required: true, type: Object },
    collapsable: Boolean,
  },
  data: () => ({
    noteBeingEdited: false,
    collapsed: true,
    hasTextToCollapse: false,
  }),
  methods: {
    /**
     * Update the collapsed value indicator
     *
     * @param  {Boolean} value
     *
     * @return {VOid}
     */
    updateCommentsVisibility(value) {
      this.$store.commit('comments/SET_VISIBILITY', {
        commentableId: this.note.id,
        commentableType: 'notes',
        visible: value,
      })
    },

    /**
     * Delete note from storage
     *
     * @param  {Number} id
     *
     * @return {Void}
     */
    destroy(id) {
      this.$dialog.confirm().then(() => {
        Innoclapps.request()
          .delete(`/notes/${id}`)
          .then(() => {
            this.removeResourceRecordHasManyRelationship(id, 'notes')
            this.decrementResourceRecordCount('notes_count')
            Innoclapps.success(this.$t('note.deleted'))
          })
      })
    },

    /**
     * Toggle edit
     *
     * @param  {Object} e
     *
     * @return {Void}
     */
    toggleEdit(e) {
      // The double click to edit should not work while in edit mode
      if (e.type == 'dblclick' && this.noteBeingEdited) return
      // For double click event
      if (!this.note.authorizations.update) return

      this.noteBeingEdited = !this.noteBeingEdited
    },
  },
}
</script>
