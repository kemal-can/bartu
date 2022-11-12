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
          <div class="lg:flex lg:items-center lg:justify-between">
            <div class="lg:mr-5 lg:flex lg:items-center lg:space-x-5">
              <div class="flex shrink-0 self-start lg:block">
                <i-avatar
                  size="lg"
                  :src="record.avatar_url"
                  :title="record.name"
                  class="mx-auto"
                />
              </div>
              <div class="text-center lg:mt-0 lg:text-left">
                <p
                  class="text-xl font-bold text-neutral-900 dark:text-white lg:text-2xl"
                >
                  <i-popover
                    v-if="record.authorizations.update"
                    @show="
                      ;(editFirstName = record.first_name),
                        (editLastName = record.last_name)
                    "
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
                          :label="$t('fields.contacts.first_name')"
                          label-for="editFirstName"
                        >
                          <i-form-input
                            v-model="editFirstName"
                            id="editFirstName"
                            @keydown.enter="updateFullName"
                            @keydown="updateForm.errors.clear('first_name')"
                          />
                          <form-error field="first_name" :form="updateForm" />
                        </i-form-group>
                        <i-form-group
                          :label="$t('fields.contacts.last_name')"
                          label-for="editLastName"
                        >
                          <i-form-input
                            v-model="editLastName"
                            id="editLastName"
                            @keydown.enter="updateFullName"
                            @keydown="updateForm.errors.clear('last_name')"
                          />
                          <form-error field="last_name" :form="updateForm" />
                        </i-form-group>

                        <div
                          class="-mx-6 -mb-5 mt-4 flex justify-end space-x-1 bg-neutral-100 px-6 py-3 dark:bg-neutral-900"
                        >
                          <i-button
                            size="sm"
                            variant="white"
                            :disabled="contactBeingUpdated"
                            @click="() => $refs.namePopover.hide()"
                            >{{ $t('app.cancel') }}</i-button
                          >
                          <i-button
                            size="sm"
                            variant="primary"
                            :loading="contactBeingUpdated"
                            :disabled="contactBeingUpdated"
                            @click="updateFullName"
                            >{{ $t('app.save') }}</i-button
                          >
                        </div>
                      </div>
                    </template>
                  </i-popover>

                  <span v-else v-text="record.display_name"></span>
                </p>
                <p
                  class="text-sm font-medium text-neutral-600 dark:text-neutral-300"
                >
                  <span
                    v-if="record.job_title && isAssociateToOneCompany"
                    v-text="
                      $t('contact.works_at', {
                        job_title: record.job_title,
                        company: record.companies[0].name,
                      })
                    "
                  ></span>
                  <span
                    v-else-if="record.job_title"
                    v-text="record.job_title"
                  ></span>
                </p>
              </div>
            </div>
            <div
              class="mt-5 mr-3 shrink-0 text-center lg:ml-auto lg:mt-0"
              v-show="record.authorizations.update"
            >
              <i-button
                variant="success"
                icon="Plus"
                :to="{ name: 'createDealViaContact' }"
                >{{ $t('deal.add') }}</i-button
              >
            </div>

            <div
              class="mt-5 flex shrink-0 items-center justify-center space-x-3 lg:mt-0"
            >
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
                    v-i-tooltip="$t('fields.contacts.user.name')"
                  >
                    <i-avatar size="xs" class="mr-1.5" :src="item.avatar_url" />
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
                :contact="record"
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
import Actions from '@/components/Actions/Actions'
import RecordTabs from '@/components/RecordTabs'
import ManageViewSections from '@/components/ManageViewSections'

import TimelineTab from '@/views/Timeline/RecordTab'
import ActivityTab from '@/views/Activity/RecordTab'
import EmailsTab from '@/views/Emails/RecordTab'
import CallsTab from '@/views/Calls/RecordTab'
import NotesTab from '@/views/Notes/RecordTab'

import DetailsSection from './Templates/ViewSections/Details'
import DealsSection from './Templates/ViewSections/Deals'
import CompaniesSection from './Templates/ViewSections/Companies'
import MediaSection from './Templates/ViewSections/Media'

import HandlesResourceView from '@/mixins/HandlesResourceView'
import Form from '@/components/Form/Form'

export default {
  mixins: [HandlesResourceView],
  components: {
    Actions,
    RecordTabs,
    ManageViewSections,

    TimelineTab,
    ActivityTab,
    EmailsTab,
    CallsTab,
    NotesTab,

    DetailsSection,
    DealsSection,
    CompaniesSection,
    MediaSection,
  },

  props: {
    resourceName: { required: true, type: String },
  },

  data() {
    return {
      id: this.$route.params.id,
      template: Innoclapps.config.resources.contacts.frontend.view,
      editFirstName: null,
      editLastName: null,
      contactBeingUpdated: false,
      sidebarBeingManaged: false,
      updateForm: new Form(),
    }
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
     * Checks whether the contact is associated with one company only
     *
     * @return {Boolean}
     */
    isAssociateToOneCompany() {
      return this.record.companies.length == 1
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
        this.$router.push({ name: 'contact-index' })
      }
    },

    /**
     * Update the current contact
     *
     * @param  {Object} payload
     *
     * @return {Promise}
     */
    async update(payload = {}) {
      this.contactBeingUpdated = true

      let contact = await this.updateForm
        .reset()
        .clear()
        .set(payload)
        .put(`/contacts/${this.record.id}`)
        .finally(() => (this.contactBeingUpdated = false))

      this.setRecordInStore(contact)
      this.updateFieldsValues(contact)

      return contact
    },

    /**
     * Update the contact full name
     */
    updateFullName() {
      this.update({
        first_name: this.editFirstName,
        last_name: this.editLastName,
      }).then(() => this.$refs.namePopover.hide())
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
    if (to.name === 'view-contact') {
      // Reset the page title when the route is updated
      // e.q. create deal then back to this route
      this.setPageTitle(this.record.display_name)
    }
  },
}
</script>
