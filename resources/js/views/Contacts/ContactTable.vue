<template>
  <resource-table
    v-if="initialize"
    @loaded="$emit('loaded', $event)"
    resource-name="contacts"
    :table-id="tableId"
    :empty-state="{
      to: { name: 'create-contact' },
      title: 'No contacts',
      buttonText: 'Create Contact',
      description: 'Get started by creating a new contact.',
    }"
  >
    <template #display_name="{ row, formatted }">
      <div class="flex w-full justify-between">
        <router-link
          class="link grow"
          :to="{ name: 'view-contact', params: { id: row.id } }"
          >{{ formatted }}</router-link
        >
        <div class="ml-2">
          <i-minimal-dropdown>
            <i-dropdown-item
              @click="
                activityBeingCreatedRow = {
                  ...row,
                  ...{ name: row.display_name },
                }
              "
              :text="$t('activity.create')"
            />
            <i-dropdown-item
              @click="preview(row.id)"
              :text="$t('app.preview')"
            />

            <i-dropdown-item
              v-if="row.authorizations.delete"
              @click="remove(row.id)"
              :text="$t('app.delete')"
            />
          </i-minimal-dropdown>
        </div>
      </div>
    </template>
  </resource-table>

  <create-activity
    :visible="activityBeingCreatedRow !== null"
    :hide-on-created="true"
    :deals="[activityBeingCreatedRow]"
    @created="reload"
    @hidden="activityBeingCreatedRow = null"
  />
  <preview-modal />
</template>
<script>
import ResourceTable from '@/components/Table'
import CreateActivity from '@/views/Activity/CreateSimple'
import { mapActions } from 'vuex'

export default {
  emits: ['deleted', 'loaded'],
  components: { ResourceTable, CreateActivity },
  props: {
    initialize: { default: true, type: Boolean },
  },
  data: () => ({
    tableId: 'contacts',
    activityBeingCreatedRow: null,
  }),
  methods: {
    ...mapActions('contacts', ['destroy', 'preview']),

    /**
     * Reload the table
     */
    reload() {
      this.$iTable.reload(this.tableId)
    },

    /**
     * Customize the table (refs usage)
     */
    customize() {
      this.$store.commit('table/SET_CUSTOMIZE_VISIBILTY', {
        id: this.tableId,
        value: true,
      })
    },

    /**
     * Remove contact from storage
     *
     * @param  {Number} id
     *
     * @return {Void}
     */
    remove(id) {
      this.destroy(id).then(() => {
        this.$emit('deleted', id)
        this.reload()
        Innoclapps.success(this.$t('resource.deleted'))
      })
    },
  },
}
</script>
