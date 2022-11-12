<template>
  <div>
    <i-overlay :show="preparingComponent">
      <i-modal
        size="sm"
        id="requiresFieldsModal"
        :title="$t('form.fields_action_required')"
        hide-footer
      >
        <p
          class="text-neutral-900 dark:text-neutral-100"
          v-t="'form.required_fields_needed'"
        />
      </i-modal>
      <i-modal
        size="sm"
        id="nonOptionalFieldRequiredModal"
        :cancel-title="$t('app.cancel')"
        :ok-title="$t('app.continue')"
        :ok-disabled="
          hasContactEmailAddressField &&
          !acceptsRequiredFields.email &&
          hasContactPhoneField &&
          !acceptsRequiredFields.phones
        "
        @ok="acceptRequiredFields"
        :title="$t('form.fields_action_required')"
      >
        <p
          class="mb-3 text-neutral-800 dark:text-neutral-100"
          v-t="'form.must_requires_fields'"
        />
        <i-form-checkbox
          id="require-contact-email"
          v-show="!contactEmailFieldIsRequired && hasContactEmailAddressField"
          v-model:checked="acceptsRequiredFields.email"
          name="require-contact-email"
          :label="$t('fields.contacts.email')"
        />
        <i-form-checkbox
          id="require-contact-phone"
          class="mt-2"
          v-show="!contactPhoneFieldIsRequired && hasContactPhoneField"
          v-model:checked="acceptsRequiredFields.phones"
          name="require-contact-phone"
          :label="$t('fields.contacts.phone')"
        />
      </i-modal>
      <form
        @submit.prevent="beforeUpdateChecks"
        @keydown="form.onKeydown($event)"
        novalidate="true"
      >
        <i-card no-body actions-class="w-full sm:w-auto">
          <template #header>
            <div class="flex items-center">
              <router-link :to="{ name: 'web-forms-index' }">
                <icon
                  icon="ChevronLeft"
                  class="h-5 w-5 text-neutral-500 hover:text-neutral-800 dark:text-neutral-300 dark:hover:text-neutral-400"
                />
              </router-link>
              <div class="ml-3 w-full">
                <div
                  class="border-b border-transparent focus-within:border-primary-500"
                >
                  <input
                    type="text"
                    name="name"
                    id="name"
                    class="block w-full border-0 border-b border-transparent bg-neutral-50 text-sm font-medium focus:border-primary-500 focus:ring-0 dark:bg-neutral-700 dark:text-white"
                    v-model="form.title"
                  />
                </div>

                <form-error class="text-sm" :form="form" field="title" />
              </div>
            </div>
          </template>
          <template #actions>
            <div
              class="mt-5 flex w-full items-center justify-end space-x-2 sm:mt-0 sm:w-auto"
            >
              <div class="flex">
                <i-action-message
                  v-show="form.recentlySuccessful"
                  :message="$t('app.saved')"
                  class="mr-2"
                />
                <i-form-toggle
                  v-model="form.status"
                  value="active"
                  class="mr-2 border-r border-neutral-200 pr-4 dark:border-neutral-700"
                  unchecked-value="inactive"
                  :disabled="addingNewSection || form.busy"
                  :label="$t('form.active')"
                />
              </div>
              <a
                :href="form.public_url"
                target="_blank"
                rel="noopener noreferrer"
                :class="{ disabled: form.busy }"
                class="btn-white btn-sm btn rounded"
                v-t="'app.preview'"
              >
              </a>
              <i-button
                size="sm"
                :loading="form.busy"
                @click="beforeUpdateChecks"
                :disabled="form.busy || addingNewSection"
              >
                {{ $t('app.save') }}
              </i-button>
            </div>
          </template>
          <div class="form-wrapper overflow-auto">
            <div class="m-auto max-w-full">
              <i-tabs
                centered
                nav-wrapper-class="sticky top-0 z-10 bg-white dark:bg-neutral-900"
              >
                <i-tab :title="$t('form.editor')">
                  <div
                    class="m-auto max-w-sm"
                    v-for="(section, index) in form.sections"
                    :key="index + section.type + section.attribute"
                  >
                    <component
                      :form="form"
                      :is="section.type"
                      :companies-fields="companiesFields"
                      :contacts-fields="contactsFields"
                      :deals-fields="dealsFields"
                      :index="index"
                      :available-resources="availableResources"
                      @remove-section-requested="removeSection(index)"
                      @update-section-requested="
                        updateSectionRequestedEvent(index, $event)
                      "
                      @create-section-requested="createSection(index, $event)"
                      :section="section"
                    />
                    <div
                      class="group relative flex flex-col items-center justify-center"
                      v-if="totalSections - 1 != index"
                    >
                      <div v-show="!addingNewSection" class="absolute">
                        <i-button
                          size="sm"
                          class="block transition-opacity delay-75 md:opacity-0 md:group-hover:opacity-100"
                          @click="newSection(index)"
                          icon="Plus"
                          variant="secondary"
                        />
                      </div>
                      <svg height="56" width="360">
                        <line
                          x1="180"
                          y1="0"
                          x2="180"
                          y2="56"
                          class="stroke-current stroke-1 text-neutral-900 dark:text-neutral-700"
                        />
                        Sorry, your browser does not support inline SVG.
                      </svg>
                    </div>
                  </div>
                </i-tab>
                <i-tab :title="$t('form.submit_options')" tag="i-card-body">
                  <h5
                    class="mb-3 text-lg font-semibold text-neutral-700 dark:text-neutral-100"
                    v-t="'form.success_page.success_page'"
                  />
                  <i-form-group
                    :label="$t('form.success_page.success_page_info')"
                  >
                    <i-form-radio
                      class="mt-2"
                      :label="$t('form.success_page.thank_you_message')"
                      id="submitMessage"
                      value="message"
                      name="submit-action"
                      v-model="form.submit_data.action"
                    />

                    <i-form-radio
                      class="mt-2"
                      :label="$t('form.success_page.redirect')"
                      id="submitRedirect"
                      value="redirect"
                      name="submit-action"
                      v-model="form.submit_data.action"
                    />

                    <form-error :form="form" field="submit_data.action" />
                  </i-form-group>
                  <div class="mb-3">
                    <div v-show="form.submit_data.action === 'message'">
                      <i-form-group
                        :label="$t('form.success_page.title')"
                        label-for="success_title"
                        required
                      >
                        <i-form-input
                          v-model="form.submit_data.success_title"
                          :placeholder="
                            $t('form.success_page.title_placeholder')
                          "
                        />
                        <form-error
                          :form="form"
                          field="submit_data.success_title"
                        />
                      </i-form-group>
                      <i-form-group :label="$t('form.success_page.message')">
                        <editor
                          :with-image="false"
                          v-model="form.submit_data.success_message"
                        />
                      </i-form-group>
                    </div>
                    <div v-show="form.submit_data.action === 'redirect'">
                      <i-form-group
                        :label="$t('form.success_page.redirect_url')"
                        label-for="success_redirect_url"
                        required
                      >
                        <i-form-input
                          type="url"
                          :placeholder="
                            $t('form.success_page.redirect_url_placeholder')
                          "
                          v-model="form.submit_data.success_redirect_url"
                        />
                        <form-error
                          :form="form"
                          field="submit_data.success_redirect_url"
                        />
                      </i-form-group>
                    </div>
                  </div>

                  <h5
                    class="mb-3 mt-8 text-lg font-semibold text-neutral-700 dark:text-neutral-100"
                    v-t="'form.saving_preferences.saving_preferences'"
                  />
                  <i-form-group
                    label-for="title_prefix"
                    :label="$t('form.saving_preferences.deal_title_prefix')"
                    :description="
                      $t('form.saving_preferences.deal_title_prefix_info')
                    "
                    optional
                  >
                    <i-form-input
                      v-model="form.title_prefix"
                      id="title_prefix"
                    />
                  </i-form-group>
                  <i-form-group
                    label-for="pipeline_id"
                    :label="$t('fields.deals.pipeline.name')"
                    required
                  >
                    <i-custom-select
                      :options="pipelines"
                      label="name"
                      input-id="pipeline_id"
                      :clearable="false"
                      @update:modelValue="stage = $event.stages[0]"
                      v-model="pipeline"
                    />
                    <form-error :form="form" field="submit_data.pipeline_id" />
                  </i-form-group>
                  <i-form-group
                    label-for="stage_id"
                    required
                    :label="$t('fields.deals.stage.name')"
                  >
                    <i-custom-select
                      :options="pipeline ? pipeline.stages : []"
                      label="name"
                      :clearable="false"
                      input-id="stage_id"
                      v-model="stage"
                    />
                    <form-error :form="form" field="submit_data.stage_id" />
                  </i-form-group>
                  <i-form-group
                    label-for="user_id"
                    :label="$t('fields.deals.user.name')"
                    required
                  >
                    <i-custom-select
                      :options="users"
                      :clearable="false"
                      label="name"
                      :reduce="user => user.id"
                      input-id="user_id"
                      v-model="form.user_id"
                    />
                    <form-error :form="form" field="user_id" />
                  </i-form-group>
                  <i-form-group
                    label-for="notifications"
                    :label="$t('form.notifications')"
                  >
                    <div
                      class="mb-3 flex rounded-md shadow-sm"
                      v-for="(email, index) in form.notifications"
                      :key="index"
                    >
                      <div
                        class="relative flex grow items-stretch focus-within:z-10"
                      >
                        <i-form-input
                          :rounded="false"
                          class="rounded-l-md"
                          type="email"
                          :placeholder="
                            $t('form.notification_email_placeholder')
                          "
                          v-model="form.notifications[index]"
                        />
                        <form-error
                          :form="form"
                          :field="'notifications.' + index"
                        />
                      </div>
                      <i-button
                        type="button"
                        icon="X"
                        variant="white"
                        :rounded="false"
                        @click="removeNotification(index)"
                        class="relative -ml-px rounded-r-md"
                      />
                    </div>

                    <a
                      href="#"
                      class="link"
                      @click.prevent="addNewNotification"
                      v-show="
                        !emptyNotificationsEmails || totalNotifications === 0
                      "
                    >
                      {{ $t('form.new_notification') }}</a
                    >
                  </i-form-group>
                </i-tab>
                <i-tab :title="$t('form.style.style')" tag="i-card-body">
                  <h5
                    class="mb-3 text-lg font-semibold text-neutral-700 dark:text-neutral-100"
                    v-t="'form.style.style'"
                  />
                  <i-form-group :label="$t('form.style.primary_color')">
                    <i-color-swatches
                      :allow-remove="false"
                      v-model="form.styles.primary_color"
                      :swatches="swatches"
                    />
                    <form-error :form="form" field="styles.primary_color" />
                  </i-form-group>
                  <i-form-group :label="$t('form.style.background_color')">
                    <i-color-swatches
                      :allow-remove="false"
                      v-model="form.styles.background_color"
                      :swatches="swatches"
                    />
                    <form-error :form="form" field="styles.background_color" />
                  </i-form-group>
                  <i-form-group
                    class="mt-3 w-full sm:w-1/2"
                    :label="$t('app.locale')"
                    label-for="locale"
                    required
                  >
                    <i-custom-select
                      input-id="locale"
                      v-model="form.locale"
                      :clearable="false"
                      :options="locales"
                    >
                    </i-custom-select>
                    <form-error :form="form" field="locale" />
                  </i-form-group>
                </i-tab>
                <i-tab
                  :title="$t('form.sections.embed.embed')"
                  tag="i-card-body"
                >
                  <web-form-embed :form="form" />
                </i-tab>
              </i-tabs>
            </div>
          </div>
        </i-card>
      </form>
    </i-overlay>
  </div>
</template>
<script>
import ProvidesDraggableOptions from '@/mixins/ProvidesDraggableOptions'
import draggable from 'vuedraggable'
import find from 'lodash/find'
import get from 'lodash/get'
import findIndex from 'lodash/findIndex'
import Editor from '@/components/Editor'
import IntroductionSection from './EditorSections/IntroductionSection'
import SubmitButtonSection from './EditorSections/SubmitButtonSection'
import FileSection from './EditorSections/FileSection'
import FieldSection from './EditorSections/FieldSection'
import NewSection from './EditorSections/NewSection'
import MessageSection from './EditorSections/MessageSection'
import WebFormEmbed from './WebFormEmbed'
import Form from '@/components/Form/Form'
import { mapGetters, mapState } from 'vuex'

export default {
  mixins: [ProvidesDraggableOptions],
  components: {
    draggable,
    Editor,
    FieldSection,
    IntroductionSection,
    SubmitButtonSection,
    FileSection,
    NewSection,
    MessageSection,
    WebFormEmbed,
  },
  data: () => ({
    form: {
      notifications: [],
      sections: [],
      styles: [],
      submit_data: [],
    },
    acceptsRequiredFields: {
      email: true,
      phones: true,
    },
    contactsFields: [],
    companiesFields: [],
    dealsFields: [],
    pipeline: null,
    preparingComponent: false,
    stage: null,
    swatches: Innoclapps.config.favourite_colors,
    addingNewSection: false,
  }),
  computed: {
    /**
     * Available form resources
     *
     * @return {Array}
     */
    availableResources() {
      return [
        {
          id: 'contacts',
          label: this.$t('contact.contact'),
        },
        {
          id: 'companies',
          label: this.$t('company.company'),
        },
        {
          id: 'deals',
          label: this.$t('deal.deal'),
        },
      ]
    },
    ...mapGetters({
      locales: 'locales',
    }),
    ...mapState({
      users: state => state.users.collection,
      pipelines: state => state.pipelines.collection,
    }),

    /**
     * Total notifications
     *
     * @return {Number}
     */
    totalNotifications() {
      return this.form.notifications.length
    },

    /**
     * Indicates whether there are empty notifications emails
     *
     * @return {Boolean}
     */
    emptyNotificationsEmails() {
      return (
        this.form.notifications.filter(email => email === '' || email === null)
          .length > 0
      )
    },

    /**
     * Get the total number of sections in the form
     *
     * @return {Numebr}
     */
    totalSections() {
      return this.form.sections.length
    },

    /**
     * Check whether the contact email field is required
     *
     * @return {Boolean}
     */
    contactEmailFieldIsRequired() {
      if (!this.hasContactEmailAddressField) {
        return false
      }

      return (
        find(this.form.sections, {
          resourceName: 'contacts',
          attribute: 'email',
        }).isRequired === true
      )
    },

    /**
     * Check whether the contact phone field is required
     *
     * @return {Boolean}
     */
    contactPhoneFieldIsRequired() {
      if (!this.hasContactPhoneField) {
        return false
      }

      return (
        find(this.form.sections, {
          resourceName: 'contacts',
          attribute: 'phones',
        }).isRequired === true
      )
    },

    /**
     * Check whether there is contact email field addded
     *
     * @return {Boolean}
     */
    hasContactEmailAddressField() {
      return (
        find(this.form.sections, {
          resourceName: 'contacts',
          attribute: 'email',
        }) !== undefined
      )
    },

    /**
     * Check whether there is contact phone field added
     *
     * @return {Boolean}
     */
    hasContactPhoneField() {
      return (
        find(this.form.sections, {
          resourceName: 'contacts',
          attribute: 'phones',
        }) !== undefined
      )
    },

    /**
     * Indicates whether the form requires the required fields
     *
     * @return {Boolean}
     */
    requiresFields() {
      return !this.hasContactEmailAddressField && !this.hasContactPhoneField
    },

    /**
     * Indicates whether the form requires optional fields to be marked as optional
     *
     * @return {Boolean}
     */
    requiresNonOptionalFields() {
      return (
        !this.contactEmailFieldIsRequired && !this.contactPhoneFieldIsRequired
      )
    },
  },
  methods: {
    /**
     * Handle the request to update the section
     *
     * @param  {Number} index
     * @param  {Object} data
     *
     * @return {Void}
     */
    updateSectionRequestedEvent(index, data) {
      this.updateSection(index, data, false)

      if (this.requiresFields || this.requiresNonOptionalFields) {
        this.beforeUpdateChecks()
      } else {
        this.update()
      }
    },

    /**
     * Create new section
     *
     * @param  {Number} index
     *
     * @return {Void}
     */
    newSection(index) {
      this.addingNewSection = true

      this.form.sections.splice(index + 1, 0, {
        type: 'new-section',
        label: this.$t('form.sections.new'),
      })
    },

    /**
     * Remove the given section
     *
     * @param  {Number} index
     *
     * @return {Promise}
     */
    async removeSection(index) {
      if (this.form.sections[index].type === 'new-section') {
        this.addingNewSection = false
        this.form.sections.splice(index, 1)
      } else {
        await this.$dialog.confirm()
        this.form.sections.splice(index, 1)
        this.updateSilentlyIfPossible()
      }
    },

    /**
     * Update the section data
     *
     * @param  {Number} index
     * @param  {Object} data
     * @param {Boolean} forceUpdate Whether for try to update the form
     * @return {Void}
     */
    updateSection(index, data, forceUpdate = true) {
      this.form.sections[index] = Object.assign(
        {},
        this.form.sections[index],
        data
      )

      if (forceUpdate) {
        this.update(true)
      }
    },

    /**
     * Create new section
     *
     * @param  {Number} fromIndex
     * @param  {Object} data
     *
     * @return {Void}
     */
    createSection(fromIndex, data) {
      this.form.sections.splice(fromIndex + 1, 0, data)
      this.updateSilentlyIfPossible()
    },

    /**
     * Update the form if possible
     *
     * The function will check if the required fields criteria is met
     * If yes, will silently perform update, used when user is creating, updating and removed section
     * So the form is automatically saved with click on SAVE on the section button
     *
     * @return {Void}
     */
    updateSilentlyIfPossible() {
      if (!this.requiresFields && !this.requiresNonOptionalFields) {
        this.update(true)
      }
    },

    /**
     * Set the default sections if needed
     */
    setDefaultSectionsIfNeeded() {
      if (this.totalSections === 0) {
        this.form.sections.push({
          type: 'introduction-section',
          message: '',
          title: '',
        })

        this.form.sections.push({
          type: 'submit-button-section',
          text: this.$t('form.sections.submit.default_text'),
        })
      }
    },

    /**
     * Remove notification by given index
     *
     * @param  {Number} index
     *
     * @return {Void}
     */
    removeNotification(index) {
      this.form.notifications.splice(index, 1)
    },

    /**
     * Add new empty email notification option
     */
    addNewNotification() {
      this.form.notifications.push('')

      if (this.form.notifications.length === 1) {
        this.form.notifications[0] = this.currentUser.email
      }
    },

    /**
     * Perform before update checks
     *
     * @return {Void}
     */
    beforeUpdateChecks() {
      if (this.requiresFields) {
        this.$iModal.show('requiresFieldsModal')
        return
      } else if (this.requiresNonOptionalFields) {
        this.$iModal.show('nonOptionalFieldRequiredModal')
        return
      }

      this.update()
    },

    /**
     * Accept the asked required fields
     *
     * @return {Void}
     */
    acceptRequiredFields() {
      if (
        this.hasContactEmailAddressField &&
        this.acceptsRequiredFields.email
      ) {
        this.updateSection(
          findIndex(this.form.sections, {
            resourceName: 'contacts',
            attribute: 'email',
          }),
          {
            isRequired: true,
          },
          false
        )
      }

      if (this.hasContactPhoneField && this.acceptsRequiredFields.phones) {
        this.updateSection(
          findIndex(this.form.sections, {
            resourceName: 'contacts',
            attribute: 'phones',
          }),
          {
            isRequired: true,
          },
          false
        )
      }

      this.update()
      this.$iModal.hide('nonOptionalFieldRequiredModal')
    },

    /**
     * Upate web form
     *
     * @return {Void}
     */
    update(silent = false) {
      this.form.submit_data.pipeline_id = this.pipeline
        ? this.pipeline.id
        : null
      this.form.submit_data.stage_id = this.stage ? this.stage.id : null

      this.$store
        .dispatch('webForms/update', {
          form: this.form,
          id: this.$route.params.id,
        })
        .then(webForm => {
          if (!silent) {
            Innoclapps.success(this.$t('form.updated'))
          }
        })
    },

    /**
     * Check whether the given field is reaonly
     *
     * @param  {Object}  field
     *
     * @return {Boolean}
     */
    isReadOnly(field) {
      return field.readonly || get(field, 'attributes.readonly')
    },

    /**
     * Filter fields for the form
     *
     * @param  {Array} fields
     * @param  {Array} excludedAttributes
     *
     * @return {Array}
     */
    filterFields(fields, excludedAttributes) {
      return fields.filter(
        field =>
          field.showOnCreation === true &&
          (excludedAttributes.indexOf(field.attribute) === -1 ||
            this.isReadOnly(field))
      )
    },

    /**
     * Get the resources fields
     *
     * @return {Promise}
     */
    async getResourcesFields() {
      let { data } = await Innoclapps.request().get(
        '/fields/settings/bulk/create?intent=create',
        {
          params: {
            groups: ['contacts', 'companies', 'deals'],
          },
        }
      )

      this.contactsFields = this.filterFields(data.contacts, [
        'user_id',
        'source_id',
      ])

      this.dealsFields = this.filterFields(data.deals, [
        'user_id',
        'pipeline_id',
        'stage_id',
      ])

      this.companiesFields = this.filterFields(data.companies, [
        'user_id',
        'parent_company_id',
        'source_id',
      ])
    },
    /**
     * Prepare the web form for edit
     *
     * @return {Void}
     */
    prepareComponent() {
      // We will get the fields from settings as these
      // are the fields the user is allowed to interact and use them in forms
      this.preparingComponent = true
      this.getResourcesFields().finally(() => {
        this.$store
          .dispatch('webForms/get', this.$route.params.id)
          .then(webForm => {
            this.setPageTitle(webForm.title)
            this.form = new Form(this.cleanObject(webForm))

            this.pipeline = this.pipelines.filter(
              pipeline => pipeline.id == webForm.submit_data.pipeline_id
            )[0]

            this.stage = this.pipeline.stages.filter(
              stage => stage.id == webForm.submit_data.stage_id
            )[0]

            this.setDefaultSectionsIfNeeded()
            this.preparingComponent = false
          })
      })
    },
  },
  created() {
    this.prepareComponent()
  },
}
</script>
<style>
@media (min-width: 640px) {
  .form-wrapper {
    height: calc(100vh - (var(--navbar-height) + 220px));
  }
}
</style>
