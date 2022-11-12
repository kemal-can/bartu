<template>
  <i-slideover
    @hidden="goBack"
    :title="$t('product.create')"
    :visible="true"
    static-backdrop
    form
    @submit="store"
  >
    <form-fields-placeholder v-if="!fieldsConfigured" />

    <focus-able-fields-generator
      :form="form"
      :fields="fields"
      view="create"
      :is-floating="true"
    />
    <template #modal-ok>
      <i-dropdown-button-group
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
    </template>

    <teleport to="#after-name-field" v-if="trashedProduct !== null">
      <i-alert dismissible>
        {{ $t('product.exists_in_trash_by_name') }}

        <div class="mt-4">
          <div class="-mx-2 -my-1.5 flex">
            <i-button-minimal variant="info" @click="restoreTrashed">
              {{ $t('app.soft_deletes.restore') }}
            </i-button-minimal>
          </div>
        </div>
      </i-alert>
    </teleport>
  </i-slideover>
</template>
<script>
import InteractsWithResourceFields from '@/mixins/InteractsWithResourceFields'
import Form from '@/components/Form/Form'
import debounce from 'lodash/debounce'

export default {
  emits: ['created'],
  mixins: [InteractsWithResourceFields],
  data: () => ({
    form: new Form(),
    nameField: {},
    trashedProduct: null,
  }),
  watch: {
    'nameField.currentValue': debounce(function (newVal, oldVal) {
      if (!newVal) {
        this.trashedProduct = null
        return
      }

      Innoclapps.request()
        .get(`/trashed/products/search`, {
          params: {
            q: newVal,
            search_fields: 'name:=',
          },
        })
        .then(({ data: products }) => {
          this.trashedProduct = products.length > 0 ? products[0] : null
        })
    }, 500),
  },
  methods: {
    /**
     * Store product in storage
     *
     * @return {Void}
     */
    store() {
      this.request().then(product => this.goBack())
    },

    /**
     * Store product in storage and add another
     *
     * @return {Void}
     */
    storeAndAddAnother() {
      this.request().then(product => this.resetFormFields(this.form))
    },

    /**
     * Store product in storage and go to list view
     *
     * @return {Void}
     */
    storeAndGoToList() {
      this.request().then(product => this.$router.push('/products'))
    },

    /**
     * Perform request
     *
     * @return {Promise}
     */
    async request() {
      let product = await this.$store
        .dispatch('products/store', this.fillFormFields(this.form))
        .catch(e => {
          if (e.response.status === 422) {
            Innoclapps.error(this.$t('app.form_validation_failed'), 3000)
          }
          return Promise.reject(e)
        })

      this.$emit('created', product)

      Innoclapps.success(this.$t('product.created'))

      return product
    },

    /**
     * Restore the found trashed product by name
     *
     * @return {Void}
     */
    restoreTrashed() {
      Innoclapps.request()
        .post('/trashed/products/' + this.trashedProduct.id)
        .then(() => {
          this.$router.replace({
            name: 'view-product',
            params: { id: this.trashedProduct.id },
          })
        })
    },

    /**
     * Prepare the component
     *
     * @return {Void}
     */
    prepareComponent() {
      this.$store
        .dispatch('fields/getForResource', {
          resourceName: Innoclapps.config.fields.groups.products,
          view: Innoclapps.config.fields.views.create,
        })
        .then(fields => {
          this.setFields(fields)
          this.nameField = this.fields.find('name')
        })
    },
  },
  created() {
    this.prepareComponent()
  },
}
</script>
