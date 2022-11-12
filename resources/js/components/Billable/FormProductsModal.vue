<template>
  <i-modal
    :visible="visible"
    size="xxl"
    @ok="save"
    id="productsModal"
    static-backdrop
    :cancel-title="$t('app.cancel')"
    :ok-title="$t('app.save')"
    :ok-disabled="form.busy"
    @hidden="$emit('hidden')"
    @shown="handleShownEvent"
    :title="$t('product.add_to_deal')"
  >
    <tax-types
      v-model="form.tax_type"
      class="mb-4 flex flex-col space-y-1 sm:flex-row sm:space-x-2 sm:space-y-0"
    />
    <form-table-products ref="products" :form="form" />
  </i-modal>
</template>
<script>
import FormTableProducts from '@/components/Billable/FormTableProducts'
import TaxTypes from '@/components/Billable/TaxTypes'
import Form from '@/components/Form/Form'

export default {
  emits: ['saved', 'hidden'],
  components: {
    TaxTypes,
    FormTableProducts,
  },
  props: {
    billable: { type: Object },
    visible: { default: false, type: Boolean },
    resourceName: { required: true, type: String },
    resourceId: { required: true, type: Number },
  },
  data: () => ({
    form: new Form({
      products: [],
      removed_products: [],
    }),
  }),
  methods: {
    /**
     * Save the billable
     *
     * @return {Void}
     */
    save() {
      this.form
        .post(`${this.resourceName}/${this.resourceId}/billable`)
        .then(billable => {
          this.$emit('saved', billable)
          this.$iModal.hide('productsModal')
        })
    },

    /**
     * Handle the modal shown event
     *
     * @return {Void}
     */
    handleShownEvent() {
      if (this.billable) {
        this.form.set('tax_type', this.billable.tax_type)
        this.form.set('products', this.cleanObject(this.billable.products))
      } else {
        this.form.set('tax_type', 'exclusive')
        this.form.set('products', [])
      }

      if (this.form.products.length === 0) {
        this.$nextTick(this.$refs.products.insertAnotherLine())
      }
    },
  },
}
</script>
