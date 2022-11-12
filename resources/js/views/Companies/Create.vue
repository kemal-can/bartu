<template>
  <i-slideover
    @shown="$emit('shown')"
    @hidden="goBack"
    :title="modalTitle"
    :visible="true"
    static-backdrop
    form
    @submit="store"
  >
    <form-fields-placeholder v-if="!fieldsConfigured" />

    <div
      class="mb-4 rounded-md border border-success-400 px-4 py-3"
      v-if="viaResource && fieldsConfigured"
    >
      <fields-generator
        :form="associateForm"
        view="create"
        :fields="associateField"
      />
    </div>

    <div v-show="!hasSelectedExistingCompany">
      <focus-able-fields-generator
        :form="form"
        view="create"
        :is-floating="true"
        :fields="fields"
      />
    </div>

    <template #modal-ok>
      <div v-show="!hasSelectedExistingCompany">
        <i-dropdown-button-group
          placement="top-end"
          :disabled="form.busy"
          :loading="form.busy"
          :text="$t('app.create')"
          type="submit"
        >
          <i-dropdown-item
            @click="storeAndAddAnother"
            :text="$t('app.create_and_add_another')"
          />
          <i-dropdown-item
            @click="storeAndGoToList"
            :text="$t('app.create_and_go_to_list')"
          />
        </i-dropdown-button-group>
      </div>
      <i-button
        v-show="hasSelectedExistingCompany"
        variant="primary"
        @click="associate"
        >{{ $t('app.associate') }}</i-button
      >
    </template>
    <teleport to="#after-email-field" v-if="trashedCompanyByEmail !== null">
      <i-alert dismissible>
        {{ $t('company.exists_in_trash_by_email') }}

        <div class="mt-4">
          <div class="-mx-2 -my-1.5 flex">
            <i-button-minimal
              variant="info"
              @click="restoreTrashed(trashedCompanyByEmail.id)"
            >
              {{ $t('app.soft_deletes.restore') }}
            </i-button-minimal>
          </div>
        </div>
      </i-alert>
    </teleport>
    <teleport to="#after-name-field" v-if="trashedCompanyByName !== null">
      <i-alert dismissible>
        {{ $t('company.exists_in_trash_by_name') }}

        <div class="mt-4">
          <div class="-mx-2 -my-1.5 flex">
            <i-button-minimal
              variant="info"
              @click="restoreTrashed(trashedCompanyByName.id)"
            >
              {{ $t('app.soft_deletes.restore') }}
            </i-button-minimal>
          </div>
        </div>
      </i-alert>
    </teleport>
  </i-slideover>
</template>
<script>
import FieldsCollection from '@/services/FieldsCollection'
import InteractsWithResourceFields from '@/mixins/InteractsWithResourceFields'
import debounce from 'lodash/debounce'
import Form from '@/components/Form/Form'

export default {
  emits: ['created', 'shown'],
  mixins: [InteractsWithResourceFields],
  props: {
    viaResource: String,
  },
  data() {
    return {
      emailField: {},
      nameField: {},
      trashedCompanyByEmail: null,
      trashedCompanyByName: null,
      form: new Form(),
      associateForm: new Form({
        id: null,
      }),
      associateField: new FieldsCollection({
        asyncUrl: '/companies/search',
        attribute: 'id',
        component: 'select-field',
        helpText: this.$t('company.associate_field_info'),
        helpTextDisplay: 'text',
        label: this.$t('company.company'),
        labelKey: 'name',
        valueKey: 'id',
      }),
    }
  },
  watch: {
    'emailField.currentValue': debounce(function (newVal, oldVal) {
      if (!newVal) {
        this.trashedCompanyByEmail = null
        return
      }

      this.searchTrashedCompanies(newVal, 'email').then(
        ({ data: companies }) => {
          this.trashedCompanyByEmail =
            companies.length > 0 ? companies[0] : null
        }
      )
    }, 500),
    'nameField.currentValue': debounce(function (newVal, oldVal) {
      if (!newVal) {
        this.trashedCompanyByName = null
        return
      }
      this.searchTrashedCompanies(newVal, 'name').then(
        ({ data: companies }) => {
          this.trashedCompanyByName = companies.length > 0 ? companies[0] : null
        }
      )
    }, 500),
  },
  computed: {
    /**
     * Get the via resource record
     *
     * @return {Object}
     */
    resourceRecord() {
      return this.viaResource ? this.$store.state[this.viaResource].record : {}
    },

    /**
     * Determine the modal title
     *
     * @return {Void}
     */
    modalTitle() {
      if (!this.viaResource) {
        return this.$t('company.create')
      }

      if (!this.hasSelectedExistingCompany) {
        return this.$t('company.create_with', {
          name: this.resourceRecord.display_name,
        })
      }

      return this.$t('company.associate_with', {
        name: this.resourceRecord.display_name,
      })
    },

    /**
     * Indicates whether the user has selected existing company
     *
     * @return {Boolean}
     */
    hasSelectedExistingCompany() {
      return !!this.associateField.find('id').currentValue
    },
  },
  methods: {
    /**
     * Search trashed companies
     *
     * @param  {String} q
     * @param  {String} field
     *
     * @return {Promise}
     */
    searchTrashedCompanies(q, field) {
      return Innoclapps.request().get(`/trashed/companies/search`, {
        params: {
          q: q,
          search_fields: field + ':=',
        },
      })
    },
    /**
     * Restore the given trashed company
     *
     * @param {Number} id
     *
     * @return {Void}
     */
    restoreTrashed(id) {
      Innoclapps.request()
        .post('/trashed/companies/' + id)
        .then(() => {
          this.$router.push({
            name: 'view-company',
            params: { id: id },
          })
        })
    },

    /**
     * Associate company to resource
     *
     * @return {Void}
     */
    associate() {
      this.fillFormFields(this.associateForm, 'associateField')

      Innoclapps.request()
        .put('associations/companies/' + this.associateForm.id, {
          [this.viaResource]: [this.resourceRecord.id],
        })
        .then(({ data }) => {
          Innoclapps.success(this.$t('resource.associated'))
          this.$emit('created', data)
          this.goBack()
        })
    },

    /**
     * Store company in storage
     *
     * @return {Void}
     */
    store() {
      this.request().then(company => {
        if (this.viaResource) {
          this.goBack()
          return
        }

        this.$router.company = company
        this.$router.push({
          name: 'view-company',
          params: {
            id: company.id,
          },
        })
      })
    },

    /**
     * Store company in storage and add another
     *
     * @return {[type]}
     */
    storeAndAddAnother() {
      this.request().then(company => this.resetFormFields(this.form))
    },

    /**
     * Store company in storage and go to list view
     *
     * @return {Void}
     */
    storeAndGoToList() {
      this.request().then(company => this.$router.push('/companies'))
    },

    /**
     * Perform request
     *
     * @return {Promise}
     */
    async request() {
      let company = await this.$store
        .dispatch('companies/store', this.fillFormFields(this.form))
        .catch(e => {
          if (e.response.status === 422) {
            Innoclapps.error(this.$t('app.form_validation_failed'), 3000)
          }
          return Promise.reject(e)
        })

      Innoclapps.success(this.$t('resource.created'))

      this.$emit('created', company)

      return company
    },

    /**
     * Prepare the component
     *
     * @return {Void}
     */
    prepareComponent() {
      this.$store
        .dispatch('fields/getForResource', {
          resourceName: Innoclapps.config.fields.groups.companies,
          view: Innoclapps.config.fields.views.create,
        })
        .then(fields => {
          this.setFields(fields)
          this.emailField = this.fields.find('email')
          this.nameField = this.fields.find('name')

          if (this.viaResource) {
            this.fields.update(this.viaResource, {
              value: [this.resourceRecord],
            })
          }
        })
    },
  },
  created() {
    this.prepareComponent()
  },
  mounted() {
    this.setPageTitle(this.modalTitle)
  },
}
</script>
