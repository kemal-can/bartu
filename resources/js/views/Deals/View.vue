<template>
  <i-layout :overlay="!componentReady">
    <div class="mx-auto max-w-7xl">
      <i-alert
        class="mb-6"
        variant="warning"
        v-if="componentReady && $gate.denies('view', record)"
      >
        {{ $t('role.view_non_authorized_after_record_create') }}
      </i-alert>

      <div
        class="overflow-hidden rounded-lg bg-white shadow dark:bg-neutral-900"
        v-if="componentReady"
      >
        <div class="bg-white px-3 py-4 dark:bg-neutral-900 sm:p-6">
          <div class="lg:flex lg:items-center lg:justify-between lg:space-x-5">
            <div class="lg:flex lg:space-x-5">
              <div class="text-center lg:mt-0 lg:text-left">
                <div
                  class="text-xl font-bold text-neutral-900 dark:text-white lg:flex lg:items-baseline lg:text-2xl"
                >
                  <i-popover
                    v-if="record.authorizations.update"
                    @show="editName = record.display_name"
                    @hide="updateForm.errors.clear()"
                    ref="namePopover"
                  >
                    <a
                      href="#"
                      class="rounded-md hover:bg-neutral-100 dark:hover:bg-neutral-700"
                      @click.prevent=""
                    >
                      {{ record.display_name }}
                    </a>

                    <template #popper>
                      <div class="w-72 max-w-full p-2">
                        <i-form-group
                          required
                          :label="$t('fields.deals.name')"
                          label-for="editDealName"
                        >
                          <i-form-input
                            v-model="editName"
                            id="editDealName"
                            @keydown.enter="updateName"
                            @keydown="updateForm.errors.clear('name')"
                          />
                          <form-error field="name" :form="updateForm" />
                        </i-form-group>

                        <div
                          class="-mx-6 -mb-5 mt-4 flex justify-end space-x-1 bg-neutral-100 px-6 py-3 dark:bg-neutral-900"
                        >
                          <i-button
                            size="sm"
                            variant="white"
                            :disabled="dealBeingUpdated"
                            @click="() => $refs.namePopover.hide()"
                            >{{ $t('app.cancel') }}</i-button
                          >
                          <i-button
                            size="sm"
                            variant="primary"
                            :loading="dealBeingUpdated"
                            :disabled="dealBeingUpdated"
                            @click="updateName"
                            >{{ $t('app.save') }}</i-button
                          >
                        </div>
                      </div>
                    </template>
                  </i-popover>

                  <span v-else v-text="record.display_name"></span>

                  <a
                    href="#"
                    v-if="record.authorizations.update"
                    @click.prevent="
                      () => $refs['tab-products'][0].showProductsDialog()
                    "
                    class="link shrink-0 text-sm font-normal sm:ml-2"
                    >{{ $tc('product.count', { count: totalProducts }) }}</a
                  >
                  <span v-else class="sm:ml-2">{{
                    $tc('product.count', { count: totalProducts })
                  }}</span>
                </div>
                <deal-stage-popover
                  :deal="record"
                  class="justify-center text-sm font-medium text-neutral-800 hover:text-neutral-600 dark:text-neutral-200 dark:hover:text-neutral-400 lg:justify-start"
                />
                <p class="text-sm text-neutral-600 dark:text-neutral-300">
                  {{ $t('app.created_at') }}
                  {{ localizedDateTime(record.created_at) }}
                </p>
              </div>
            </div>
            <div
              class="mt-3 flex shrink-0 flex-col items-center justify-center lg:mt-0 lg:flex-row lg:space-x-2"
            >
              <deal-status-change class="mr-0 lg:mr-5" :deal="record" />

              <div class="mt-3 flex shrink-0 space-x-3 lg:mt-0">
                <dropdown-select
                  :items="ownerDropdownOptions"
                  :model-value="record.user"
                  value-key="id"
                  label-key="name"
                  @change="update({ user_id: $event.id })"
                  v-if="record.authorizations.update"
                >
                  <template #label="{ item, label }">
                    <span
                      v-if="item"
                      class="inline-flex items-center"
                      v-i-tooltip="$t('fields.deals.user.name')"
                    >
                      <i-avatar
                        size="xs"
                        class="mr-1.5"
                        :src="item.avatar_url"
                      />
                      {{ label }}
                    </span>
                    <span
                      v-else
                      v-t="'app.no_owner'"
                      class="text-neutral-500 dark:text-neutral-300"
                    />
                  </template>
                </dropdown-select>
                <p
                  class="inline-flex items-center text-sm text-neutral-800 dark:text-neutral-200"
                  v-else-if="record.user"
                >
                  <i-avatar
                    size="xs"
                    class="mr-1.5"
                    :src="record.user.avatar_url"
                  />
                  {{ record.user.name }}
                </p>

                <actions
                  type="dropdown"
                  :ids="actionId"
                  :actions="actions"
                  :resource-name="resourceName"
                  @run="actionExecuted"
                />

                <i-minimal-dropdown
                  v-if="$gate.isSuperAdmin()"
                  placement="bottom-end"
                >
                  <i-dropdown-item
                    @click="sidebarBeingManaged = true"
                    :text="$t('app.record_view.manage_sidebar')"
                  />
                </i-minimal-dropdown>
              </div>
            </div>
          </div>
          <deal-mini-pipeline
            :deal="record"
            @stage-updated="handleUpdatedEvent"
            class="mt-5"
          />
        </div>
      </div>

      <div class="mt-6" v-if="componentReady">
        <div class="lg:grid lg:grid-cols-12 lg:gap-8">
          <div class="col-span-4 space-y-3">
            <div
              v-for="section in enabledSections"
              :key="'section-' + section.id"
              v-show="!sidebarBeingManaged"
            >
              <component
                :ref="'section-' + section.id"
                :is="section.component"
                :deal="record"
                @refetch="fetchRecord"
              />
            </div>
            <manage-view-sections
              :identifier="resourceSingular"
              v-model:sections="template.sections"
              v-model:show="sidebarBeingManaged"
              @saved="sidebarBeingManaged = false"
              class="-mt-3 inline"
            />
          </div>

          <div class="col-span-8 mt-4 lg:mt-0">
            <record-tabs>
              <component
                v-for="tab in template.tabs"
                :key="'tab-' + tab.id"
                :ref="'tab-' + tab.id"
                :is="tab.component"
                :resource-name="resourceName"
                scroll-element="#main"
              />
            </record-tabs>
          </div>
        </div>
      </div>
    </div>
    <!-- Company, Deal Create -->
    <router-view
      v-if="componentReady"
      @created="fetchRecord"
      :via-resource="resourceName"
    />
    <preview-modal
      :via-resource="resourceName"
      @resource-record-updated="updateFieldsValues"
    />
  </i-layout>
</template>
<script>
import { mapState } from 'vuex'
import DealStatusChange from '@/views/Deals/StatusChange'
import DealStagePopover from '@/views/Deals/DealStagePopover'
import DealMiniPipeline from '@/views/Deals/Pipelines/DealMiniPipeline'
import Actions from '@/components/Actions/Actions'
import RecordTabs from '@/components/RecordTabs'
import ManageViewSections from '@/components/ManageViewSections'

import ProductsRecordTab from '@/views/Products/RecordTab'
import TimelineTab from '@/views/Timeline/RecordTab'
import ActivityTab from '@/views/Activity/RecordTab'
import EmailsTab from '@/views/Emails/RecordTab'
import CallsTab from '@/views/Calls/RecordTab'
import NotesTab from '@/views/Notes/RecordTab'

import DetailsSection from './Templates/ViewSections/Details'
import ContactsSection from './Templates/ViewSections/Contacts'
import CompaniesSection from './Templates/ViewSections/Companies'
import MediaSection from './Templates/ViewSections/Media'

import HandlesResourceView from '@/mixins/HandlesResourceView'
import Form from '@/components/Form/Form'

export default {
  mixins: [HandlesResourceView],
  components: {
    DealStagePopover,
    DealStatusChange,
    DealMiniPipeline,
    Actions,
    RecordTabs,
    ManageViewSections,

    ProductsRecordTab,
    TimelineTab,
    NotesTab,
    CallsTab,
    EmailsTab,
    ActivityTab,

    DetailsSection,
    ContactsSection,
    CompaniesSection,
    MediaSection,
  },

  props: {
    resourceName: { required: true, type: String },
  },

  data() {
    return {
      id: this.$route.params.id,
      template: Innoclapps.config.resources.deals.frontend.view,
      editName: null,
      dealBeingUpdated: false,
      sidebarBeingManaged: false,
      updateForm: new Form(),
    }
  },

  watch: {
    products: {
      handler: function (newVal, oldVal) {
        if (
          !this.$refs['section-details'] ||
          JSON.stringify(newVal) === JSON.stringify(oldVal)
        ) {
          return
        }

        const isReadOnly = newVal.length > 0

        this.$refs['section-details'][0].fields.update('amount', {
          readonly: isReadOnly,
        })

        this.$refs['section-details'][0].fields
          .find('amount')
          .handleChange(this.record.billable.total)
      },
      deep: true,
    },
  },

  computed: {
    ...mapState({
      users: state => state.users.collection,
    }),

    /**
     * Get the users for the owner dropdown
     */
    ownerDropdownOptions() {
      if (this.record.user) {
        return [
          ...this.users,
          {
            id: null,
            icon: 'X',
            prependIcon: true,
            name: this.$t('app.no_owner'),
            class: 'border-t border-neutral-200 dark:border-neutral-700',
          },
        ]
      }

      return this.users
    },

    /**
     * Get the enabled sidebar section
     */
    enabledSections() {
      return this.template.sections.filter(section => section.enabled === true)
    },

    /**
     * Get the total number of products associated with the deal
     *
     * @return {Number}
     */
    totalProducts() {
      return this.products.length
    },

    /**
     * Get the deal products
     *
     * @return {Array}
     */
    products() {
      if (!this.componentReady || !this.record.billable) {
        return []
      }

      return this.record.billable.products
    },
  },
  methods: {
    /**
     * Handle the action executed event
     *
     * @param  {Object} action
     *
     * @return {Void}
     */
    actionExecuted(action) {
      // Reload the record data on any action executed except delete
      // If we try to reload on delete will throw 404 error
      if (!action.destroyable) {
        this.fetchRecord().then(this.updateFieldsValues)
      } else {
        this.$router.push({ name: 'deal-index' })
      }
    },

    /**
     * Handle the stage updated via mini pipeline event
     *
     * We need to re-set the record with all activities and new stage data
     * However, we need to update the fields as well, because the stage won't be updated
     * on the fields after the stage is updated via the mini pipeline
     *
     * @param  {Object} record
     *
     * @return {Void}
     */
    handleUpdatedEvent(record) {
      this.setRecordInStore(record)
      this.updateFieldsValues(record)
    },

    /**
     * Update the current deal
     *
     * @param  {Object} payload
     *
     * @return {Promise}
     */
    async update(payload = {}) {
      this.dealBeingUpdated = true

      let deal = await this.updateForm
        .reset()
        .clear()
        .set(payload)
        .put(`/deals/${this.record.id}`)
        .finally(() => (this.dealBeingUpdated = false))

      this.handleUpdatedEvent(deal)

      return deal
    },

    /**
     * Update the deal name
     */
    updateName() {
      this.update({ name: this.editName }).then(() =>
        this.$refs.namePopover.hide()
      )
    },
  },

  /**
   * Handle the component before mount lifecycle hook
   */
  beforeMount() {
    this.bootView({
      id: this.id,
      resource: this.resourceName,
    })
  },

  /**
   * Handle the before route update hook
   */
  beforeRouteUpdate(to, from) {
    if (to.name === 'view-deal') {
      // Reset the page title when the route is updated
      // e.q. create deal then back to this route
      this.setPageTitle(this.record.display_name)
    }
  },
}
</script>
