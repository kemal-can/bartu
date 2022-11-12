<template>
  <i-card
    :class="['call-' + call.id]"
    footer-class="inline-flex flex-col w-full"
    v-show="!callBeingEdited"
    v-bind="$attrs"
  >
    <template #header>
      <div class="flex items-center">
        <a
          href="#"
          v-if="hasTextToCollapse"
          @click="collapsed = !collapsed"
          class="mr-2 mt-0.5 shrink-0 self-start text-neutral-500 dark:text-neutral-300 md:mt-0 md:self-auto"
          ><icon
            :icon="collapsed ? 'ChevronRight' : 'ChevronDown'"
            class="h-4 w-4"
        /></a>

        <i-avatar
          size="xs"
          class="mr-1.5 shrink-0 self-start md:self-auto"
          :src="call.user.avatar_url"
        />

        <div
          class="flex grow flex-col space-y-1 md:flex-row md:items-center md:space-x-3 md:space-y-0"
        >
          <i18n-t
            scope="global"
            keypath="call.info_created"
            tag="span"
            class="grow text-sm text-neutral-700 dark:text-white"
          >
            <template #user>
              <span class="font-medium">
                {{ call.user.name }}
              </span>
            </template>
            <template #date>
              <span class="font-medium" v-text="localizedDateTime(call.date)" />
            </template>
          </i18n-t>
          <text-background
            :color="call.outcome.swatch_color"
            class="inline-flex shrink-0 items-center self-start rounded-md py-0.5 dark:!text-white sm:rounded-full"
          >
            <dropdown-select
              v-if="call.authorizations.update"
              :items="outcomes"
              :model-value="call.outcome"
              @change="updateCall({ call_outcome_id: $event.id })"
              label-key="name"
              value-key="id"
            >
              <template v-slot="{ label }">
                <button
                  type="button"
                  class="flex w-full items-center justify-between rounded px-2.5 text-sm leading-5 focus:outline-none"
                >
                  {{ label }}
                  <icon icon="ChevronDown" class="ml-1 h-4 w-4" />
                </button>
              </template>
            </dropdown-select>
            <span v-else class="px-1 text-sm" v-text="call.outcome.name" />
          </text-background>
        </div>
        <div
          class="ml-2 mt-px inline-flex self-start md:ml-5"
          v-if="call.authorizations.update && call.authorizations.delete"
        >
          <i-minimal-dropdown class="mt-1 md:mt-0.5">
            <i-dropdown-item
              @click="toggleEdit"
              v-show="call.authorizations.update"
              >{{ $t('app.edit') }}</i-dropdown-item
            >
            <i-dropdown-item
              @click="destroy(call.id)"
              v-show="call.authorizations.delete"
              >{{ $t('app.delete') }}</i-dropdown-item
            >
          </i-minimal-dropdown>
        </div>
      </div>
    </template>

    <text-collapse
      v-if="collapsable"
      :text="call.body"
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
      v-html="call.body"
    />

    <comments-collapse
      class="mt-6"
      :via-resource="resourceName"
      :commentable-id="call.id"
      commentable-type="calls"
      :count="call.comments_count"
      @update:count="
        $store.commit(resourceName + '/UPDATE_RECORD_HAS_MANY_RELATIONSHIP', {
          relation: 'calls',
          id: call.id,
          item: { comments_count: $event },
        })
      "
    />

    <template #footer>
      <add-comment
        class="self-end"
        @created="updateCommentsVisibility(true)"
        :via-resource="resourceName"
        :commentable-id="call.id"
        commentable-type="calls"
      />
    </template>
  </i-card>
  <edit-call
    v-if="callBeingEdited"
    @cancelled="callBeingEdited = false"
    @updated="callBeingEdited = false"
    :resource-name="resourceName"
    :call="call"
  />
</template>
<script>
import EditCall from '@/views/Calls/Edit'
import TextCollapse from '@/components/TextCollapse'
import InteractsWithResource from '@/mixins/InteractsWithResource'
import CommentsCollapse from '@/views/Comments/CommentsCollapse'
import AddComment from '@/views/Comments/AddComment'
import TextBackground from '@/components/TextBackground'
import { mapState } from 'vuex'
export default {
  inheritAttrs: false,
  mixins: [InteractsWithResource],
  components: {
    EditCall,
    TextCollapse,
    CommentsCollapse,
    AddComment,
    TextBackground,
  },
  props: {
    call: { required: true, type: Object },
    collapsable: Boolean,
  },
  data: () => ({
    callBeingEdited: false,
    collapsed: true,
    hasTextToCollapse: false,
  }),
  computed: {
    ...mapState({
      outcomes: state => state.calls.outcomes,
    }),
  },
  methods: {
    /**
     * Update the current call
     *
     * @param  {Object} payload
     *
     * @return {Void}
     */
    updateCall(payload = {}) {
      Innoclapps.request()
        .put(`/calls/${this.call.id}`, {
          call_outcome_id: this.call.call_outcome_id,
          date: this.call.date,
          body: this.call.body,
          via_resource: this.resourceName,
          via_resource_id: this.resourceRecord.id,
          ...payload,
        })
        .then(({ data }) =>
          this.updateResourceRecordHasManyRelationship(data, 'calls')
        )
    },

    /**
     * Update the collapsed value indicator
     *
     * @param  {Boolean} value
     *
     * @return {VOid}
     */
    updateCommentsVisibility(value) {
      this.$store.commit('comments/SET_VISIBILITY', {
        commentableId: this.call.id,
        commentableType: 'calls',
        visible: value,
      })
    },

    /**
     * Delete call from storage
     *
     * @param  {Number} id
     *
     * @return {Void}
     */
    destroy(id) {
      this.$dialog.confirm().then(() => {
        Innoclapps.request()
          .delete(`/calls/${id}`)
          .then(() => {
            this.removeResourceRecordHasManyRelationship(id, 'calls')
            this.decrementResourceRecordCount('calls_count')
            Innoclapps.success(this.$t('call.deleted'))
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
      if (e.type == 'dblclick' && this.callBeingEdited) return
      // For double click event
      if (!this.call.authorizations.update) return

      this.callBeingEdited = !this.callBeingEdited
    },
  },
}
</script>
