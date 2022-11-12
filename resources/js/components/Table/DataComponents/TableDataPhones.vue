<template>
  <i-modal
    :title="$t('call.add')"
    size="md"
    v-model:visible="logCallModalIsVisible"
    :ok-title="$t('call.add')"
    :ok-disabled="callBeingLogged"
    :cancel-title="$t('app.cancel')"
    @shown="logCallModalIsVisible = true"
    @hidden="logCallModalIsVisible = false"
    @ok="logCall"
  >
    <!-- re-render the fields as it's causing issue with the tinymce editor
                 on second time the editor has no proper height -->
    <div v-if="logCallModalIsVisible">
      <i-overlay :show="!fieldsConfigured">
        <fields-generator
          :form="form"
          view="create"
          :via-resource="resourceName"
          :via-resource-id="row.id"
          :fields="fields"
        />
      </i-overlay>
      <create-follow-up-task class="mt-2" :form="form" />
    </div>
  </i-modal>
  <div class="inline-block">
    <i-dropdown
      no-caret
      :text="phone.number + (index != total - 1 ? ', ' : '')"
      v-for="(phone, index) in row[column.attribute]"
      :key="index"
    >
      <template #toggle>
        <a
          class="link"
          @click.prevent=""
          v-i-tooltip="$t('fields.phones.types.' + phone.type)"
          :href="'tel:' + phone.number"
          v-text="phone.number"
        />
      </template>
      <i-dropdown-item
        v-i-tooltip="callDropdownTooltip"
        :disabled="!hasVoIPClient || !$gate.userCan('use voip')"
        @click="newCall(phone.number)"
        :text="$t('call.make')"
      />
      <copy-button
        :text="phone.number"
        icon=""
        :success-message="$t('fields.phones.copied')"
        :clipboard-options="{
          text: function (trigger) {
            return phone.number
          },
        }"
        :with-tooltip="false"
        tag="i-dropdown-item"
      >
        {{ $t('app.copy') }}
      </copy-button>
      <i-dropdown-item
        :href="'tel:' + phone.number"
        :text="$t('app.open_in_app')"
      />
    </i-dropdown>
  </div>
</template>
<script>
import TableData from './TableData'
import CreateFollowUpTask from '@/views/Activity/CreateFollowUpTask'
import InteractsWithResourceFields from '@/mixins/InteractsWithResourceFields'
import Form from '@/components/Form/Form'

export default {
  mixins: [TableData, InteractsWithResourceFields],
  components: { CreateFollowUpTask },
  data: () => ({
    form: null,
    logCallModalIsVisible: false,
    callBeingLogged: false,
  }),
  computed: {
    /**
     * Get the call dropdown tooltip
     *
     * @return {String}
     */
    callDropdownTooltip() {
      if (!this.hasVoIPClient) {
        return this.$t('app.integration_not_configured')
      } else if (this.$gate.userCant('use voip')) {
        return this.$t('call.no_voip_permissions')
      }

      return ''
    },

    /**
     * Total number of phone numbers
     *
     * @return {Number}
     */
    total() {
      return this.row[this.column.attribute].length
    },
  },
  methods: {
    /**
     * Initiate new call
     *
     * @param {String} phoneNumber
     *
     * @return {Promise}
     */
    async newCall(phoneNumber) {
      this.form = new Form({
        task_date: null,
      })

      let call = await this.$voip.makeCall(phoneNumber)

      call.on('Disconnect', () => {
        this.logCallModalIsVisible = true
      })

      this.$store
        .dispatch('fields/getForResource', {
          resourceName: Innoclapps.config.fields.groups.calls,
          view: Innoclapps.config.fields.views.create,
          viaResource: this.resourceName,
          viaResourceid: this.row.id,
        })
        .then(fields => this.setFields(fields))
    },

    /**
     * Log the call
     *
     * @return {Void}
     */
    logCall() {
      this.callBeingLogged = true
      this.form.set(this.resourceName, [this.row.id])

      this.form.withQueryString({
        via_resource: this.resourceName,
        via_resource_id: this.row.id,
      })

      this.fillFormFields(this.form)
        .post('/calls')
        .then(call => (this.logCallModalIsVisible = false))
        .finally(() => (this.callBeingLogged = false))
    },
  },
}
</script>
