<template>
  <div>
    <i-modal
      size="sm"
      static-backdrop
      :ok-title="$t('app.create')"
      :ok-disabled="form.busy"
      id="createRecord"
      form
      @submit="store"
      @keydown="form.onKeydown($event)"
      @hidden="handleModalHiddenEvent"
      :title="$t('resource.create', { resource: singularLabel })"
    >
      <focus-able-fields-generator
        :fields="fields"
        :form="form"
        view="create"
        :is-floating="true"
      />
    </i-modal>
    <i-modal
      size="sm"
      static-backdrop
      id="updateRecord"
      :ok-title="$t('app.save')"
      @hidden="handleModalHiddenEvent"
      :ok-disabled="form.busy"
      form
      @submit="update"
      @keydown="form.onKeydown($event)"
      :title="$t('resource.edit', { resource: singularLabel })"
    >
      <fields-generator
        :fields="fields"
        :form="form"
        view="update"
        :is-floating="true"
      />
    </i-modal>
    <i-card no-body>
      <template #header>
        <i-button-minimal
          v-show="withCancel"
          variant="info"
          @click="requestCancel"
        >
          {{ $t('app.go_back') }}
        </i-button-minimal>

        <slot name="header"></slot>
      </template>
      <template #actions>
        <i-button @click="prepareCreate" icon="plus" size="sm">{{
          $t('resource.create', { resource: singularLabel })
        }}</i-button>
      </template>
      <table-simple
        :table-props="{ shadow: false, ...tableProps }"
        :table-id="resourceName"
        :request-uri="resourceName"
        ref="table"
        sort-by="name"
        :fields="columns"
      >
        <template #name="{ row }">
          <div class="flex justify-between">
            <a
              href="#"
              class="link"
              @click.prevent="prepareEdit(row.id)"
              v-text="row.name"
            ></a>
            <i-minimal-dropdown>
              <i-dropdown-item
                @click="prepareEdit(row.id)"
                :text="$t('app.edit')"
              />

              <span
                v-i-tooltip="
                  row.is_primary
                    ? $t('resource.primary_record_delete_info', {
                        resource: singularLabel,
                      })
                    : null
                "
              >
                <i-dropdown-item
                  :disabled="row.is_primary"
                  @click="destroy(row.id)"
                  :text="$t('app.delete')"
                />
              </span>
            </i-minimal-dropdown>
          </div>
        </template>
      </table-simple>
    </i-card>
  </div>
</template>
<script>
import TableSimple from '@/components/Table/Simple/TableSimple'
import InteractsWithResourceFields from '@/mixins/InteractsWithResourceFields'
import Form from '@/components/Form/Form'

export default {
  emits: ['cancel', 'updated', 'created', 'deleted'],
  mixins: [InteractsWithResourceFields],
  components: { TableSimple },
  props: {
    resourceName: {
      required: true,
      type: String,
    },
    withCancel: {
      type: Boolean,
      default: true,
    },
    tableProps: {
      type: Object,
      default() {
        return {}
      },
    },
  },
  data() {
    return {
      form: new Form({}),
      columns: [
        {
          key: 'id',
          label: this.$t('app.id'),
          sortable: true,
        },
        {
          key: 'name',
          label: this.$t('fields.label'),
          sortable: true,
        },
      ],
    }
  },
  computed: {
    /**
     * Get the resource label
     *
     * @return {String}
     */
    label() {
      return Innoclapps.config.resources[this.resourceName].label
    },

    /**
     * Get the resource singular label
     *
     * @return {String}
     */
    singularLabel() {
      return Innoclapps.config.resources[this.resourceName].singularLabel
    },
  },
  methods: {
    handleModalHiddenEvent() {
      this.resetFormFields(this.form)
      this.fields = []
    },
    /**
     * Request cancel edit
     *
     * @return {Void}
     */
    requestCancel() {
      this.$emit('cancel')
    },

    /**
     * Prepare resource record create
     *
     * @return {Void}
     */
    async prepareCreate() {
      let fields = await this.getResourceCreateFields()
      this.columns[1].key = fields[0].attribute
      this.setFields(fields)
      this.$iModal.show('createRecord')
    },

    /**
     * Get the resource create fields
     *
     * @return {Promise}
     */
    getResourceCreateFields() {
      return this.$store.dispatch('fields/getForResource', {
        resourceName: this.resourceName,
        view: Innoclapps.config.fields.views.create,
      })
    },

    /**
     * Prepare the resource record edit
     *
     * @param {Number} id
     *
     * @return {Void}
     */
    async prepareEdit(id) {
      let fields = await this.getResourceUpdateFields(id)
      let { data } = await Innoclapps.request().get(
        `${this.resourceName}/${id}`
      )

      this.columns[1].key = fields[0].attribute
      this.form.fill('id', id)
      this.setFieldsForUpdate(fields, data)
      this.$iModal.show('updateRecord')
    },

    /**
     * Get the resource update fields
     *
     * @return {Promise}
     */
    getResourceUpdateFields(resourceId) {
      return this.$store.dispatch('fields/getForResource', {
        resourceName: this.resourceName,
        view: Innoclapps.config.fields.views.update,
        resourceId: resourceId,
      })
    },

    /**
     * Store resource record in storage
     *
     * @return {Void}
     */
    store() {
      this.fillFormFields(this.form)
      this.form.post(this.resourceName).then(record => {
        this.actionExecuted('created')
        this.$iModal.hide('createRecord')
      })
    },

    /**
     * Update resource record in storage
     *
     * @return {Void}
     */
    update() {
      this.fillFormFields(this.form)
      this.form.put(`${this.resourceName}/${this.form.id}`).then(record => {
        this.actionExecuted('updated')
        this.$iModal.hide('updateRecord')
      })
    },

    /**
     * Remove resource record from storage
     *
     * @param  {Number} id
     *
     * @return {Void}
     */
    async destroy(id) {
      await this.$dialog.confirm()

      Innoclapps.request()
        .delete(`${this.resourceName}/${id}`)
        .then(data => this.actionExecuted('deleted'))
    },

    /**
     * Handle action executed
     *
     * @param  {String} action
     *
     * @return {Void}
     */
    actionExecuted(action) {
      Innoclapps.success(this.$t('resource.' + action))
      this.$refs.table.reload()
      this.resetStoreState()
      this.$emit(action)
    },
  },
}
</script>
<style scoped>
::v-deep(table thead th:first-child) {
  width: 7%;
}

::v-deep(table thead th:first-child a) {
  justify-content: center;
}

::v-deep(table tbody td:first-child) {
  width: 7%;
  text-align: center;
}
</style>
