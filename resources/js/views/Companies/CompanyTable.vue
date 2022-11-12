<template>
  <resource-table
    v-if="initialize"
    @loaded="$emit('loaded', $event)"
    resource-name="companies"
    :table-id="tableId"
    :empty-state="{
      to: { name: 'create-company' },
      title: 'No companies',
      buttonText: 'Create Company',
      description: 'Get started by creating a new company.',
    }"
  >
    <template #domain="{ row, formatted }">
      <a
        :href="'http://' + formatted"
        target="blank"
        v-show="formatted"
        class="link flex items-center"
        >{{ formatted }} <icon icon="ExternalLink" class="ml-1 h-4 w-4"
      /></a>
    </template>
    <template #name="{ row, formatted }">
      <div class="flex w-full justify-between">
        <router-link
          class="link grow"
          :to="{ name: 'view-company', params: { id: row.id } }"
          >{{ formatted }}</router-link
        >
        <div class="ml-2">
          <i-minimal-dropdown>
            <i-dropdown-item
              @click="activityBeingCreatedRow = row"
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
    tableId: 'companies',
    activityBeingCreatedRow: null,
  }),
  methods: {
    ...mapActions('companies', ['destroy', 'preview']),

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
     * Remove company from storage
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
