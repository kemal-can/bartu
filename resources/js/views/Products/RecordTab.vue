<template>
  <record-tab
    :title="$t('product.products')"
    @activated-first-time="loadProducts"
    section-id="products"
    :badge="totalProducts"
    badge-variant="neutral"
    :classes="{ 'opacity-70': !hasProducts }"
    icon="CurrencyDollar"
  >
    <div class="mb-4 block" v-if="!hasProducts">
      <div
        class="rounded-md border border-neutral-200 bg-neutral-50 px-6 py-5 shadow-sm dark:border-neutral-900 dark:bg-neutral-900 sm:flex sm:items-start sm:justify-between"
      >
        <div class="sm:flex sm:items-center">
          <span
            class="hidden rounded border border-neutral-200 bg-neutral-100 px-3 py-1.5 dark:border-neutral-600 dark:bg-neutral-700/60 sm:inline-flex sm:self-start"
          >
            <icon
              icon="CurrencyDollar"
              class="h-5 w-5 text-neutral-700 dark:text-neutral-200"
            />
          </span>

          <div class="sm:ml-4">
            <div
              class="text-sm font-medium text-neutral-900 dark:text-neutral-100"
              v-t="'product.deal_info'"
            />
          </div>
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-6 sm:flex-shrink-0">
          <i-button
            @click="manageProducts = true"
            :disabled="!resourceRecord.authorizations.update"
            size="sm"
          >
            {{ $t('product.manage') }}
          </i-button>
        </div>
      </div>
    </div>

    <div v-else class="mb-8 text-right">
      <i-button
        @click="manageProducts = true"
        :disabled="!resourceRecord.authorizations.update"
        size="sm"
      >
        {{ $t('product.manage') }}
      </i-button>
    </div>

    <products-table :billable="resourceRecord.billable" />

    <form-table-modal
      @hidden="manageProducts = false"
      :billable="resourceRecord.billable"
      :resource-name="resourceName"
      :resource-id="resourceRecord.id"
      @saved="handleBillableModelSavedEvent"
      :visible="manageProducts"
    />
  </record-tab>
</template>
<script>
import Recordable from '@/components/RecordTabs/Recordable'
import RecordTab from '@/components/RecordTabs/RecordTab'
import FormTableModal from '@/components/Billable/FormProductsModal'
import ProductsTable from '@/components/Billable/ProductsTable'
export default {
  components: {
    RecordTab,
    FormTableModal,
    ProductsTable,
  },
  mixins: [Recordable],
  data: () => ({
    manageProducts: false,
  }),
  computed: {
    /**
     * Resource total products
     *
     * @return {Number}
     */
    totalProducts() {
      if (!this.resourceRecord || !this.resourceRecord.billable) {
        return 0
      }

      return this.resourceRecord.billable.products.length
    },

    /**
     * Indicates whether the resource has products
     *
     * @return {Boolean}
     */
    hasProducts() {
      return this.totalProducts > 0
    },
  },
  methods: {
    /**
     * Show the products dialog manually (refs usage)
     *
     * @return {Void}
     */
    showProductsDialog() {
      this.manageProducts = true
      this.loadProducts()
    },

    /**
     * Load the products in store
     *
     * @return {Void}
     */
    loadProducts() {
      this.$store.dispatch('products/fetchActive')
    },

    /**
     * Handle the billable saved event
     *
     * @param  {Object} billable
     *
     * @return {Void}
     */
    handleBillableModelSavedEvent(billable) {
      this.$store.commit(this.resourceName + '/SET_RECORD', {
        billable: billable,
      })
    },
  },
}
</script>
