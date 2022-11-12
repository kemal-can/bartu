<template>
  <i-slideover
    @hidden="goBack()"
    :visible="true"
    static-backdrop
    form
    @submit="update"
    :ok-disabled="
      form.busy || (fieldsConfigured && !product.authorizations.update)
    "
    :ok-loading="form.busy"
    :ok-title="$t('app.save')"
    :title="$t('product.edit')"
  >
    <form-fields-placeholder v-if="!fieldsConfigured" />
    <fields-generator :fields="fields" view="update" :form="form" />
  </i-slideover>
</template>
<script>
import InteractsWithResourceFields from '@/mixins/InteractsWithResourceFields'
import Form from '@/components/Form/Form'

export default {
  emits: ['updated'],
  mixins: [InteractsWithResourceFields],
  data: () => ({
    form: new Form(),
    product: {},
  }),
  methods: {
    /**
     * Update the product in storage
     *
     * @return {Void}
     */
    update() {
      this.$store
        .dispatch('products/update', {
          form: this.fillFormFields(this.form),
          id: this.$route.params.id,
        })
        .then(user => {
          this.$emit('updated')
          Innoclapps.success(this.$t('product.updated'))
          this.goBack()
        })
    },

    /**
     * Prepare component
     *
     * @return {Void}
     */
    prepareComponent() {
      Promise.all([
        this.$store.dispatch('products/get', this.$route.params.id),

        this.$store.dispatch('fields/getForResource', {
          resourceName: 'products',
          view: Innoclapps.config.fields.views.update,
          resourceId: this.$route.params.id,
        }),
      ]).then(values => {
        this.setFieldsForUpdate(values[1], values[0])
        this.product = values[0]
      })
    },
  },
  created() {
    this.prepareComponent()
  },
}
</script>
