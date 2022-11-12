<template>
  <div>
    <div class="mt-4 mb-2 grid grid-cols-12 gap-2">
      <div
        class="col-span-8 text-sm text-neutral-600 dark:text-neutral-100 sm:col-span-5 sm:col-start-5 sm:text-right"
      >
        {{ $t('billable.sub_total') }}

        <p
          v-show="hasDiscount"
          class="italic text-neutral-500 dark:text-neutral-300"
        >
          ({{
            $t('billable.includes_discount', {
              amount: formatMoney(totalDiscount),
            })
          }})
        </p>
      </div>
      <div
        class="col-span-4 text-right text-sm text-neutral-600 dark:text-neutral-100 sm:col-span-3"
        v-text="formatMoney(subTotal)"
      ></div>
    </div>
    <div
      class="mb-2 grid grid-cols-12 gap-2"
      v-show="hasTax"
      v-for="tax in taxes"
      :key="tax.key"
    >
      <div
        class="col-span-8 text-sm text-neutral-600 dark:text-neutral-100 sm:col-span-5 sm:col-start-5 sm:text-right"
      >
        {{ tax.label }} ({{ tax.rate }}%)
      </div>
      <div
        class="col-span-4 text-right text-sm text-neutral-600 dark:text-neutral-100 sm:col-span-3"
      >
        <span
          ><span v-show="isTaxInclusive"
            >{{ $t('billable.tax_amount_is_inclusive') }}
          </span>
          {{ formatMoney(tax.total) }}</span
        >
      </div>
    </div>

    <div class="grid grid-cols-12 gap-2">
      <div
        class="col-span-8 text-sm font-semibold text-neutral-900 dark:text-neutral-100 sm:col-span-5 sm:col-start-5 sm:text-right"
        v-t="'billable.total'"
      ></div>
      <div
        class="col-span-4 text-right text-sm font-medium text-neutral-900 dark:text-neutral-100 sm:col-span-3"
        v-text="formatMoney(total)"
      ></div>
    </div>
  </div>
</template>
<script>
import { formatMoney } from '@/utils'
export default {
  props: {
    taxType: { required: true },
    total: { required: true },
    totalDiscount: { required: true },
    subTotal: { required: true },
    taxes: { required: true, default: () => [] },
  },
  computed: {
    /**
     * Indicates whether there is discount applied on the billable
     *
     * @return {Boolean}
     */
    hasDiscount() {
      return this.totalDiscount > 0
    },

    /**
     * Indicates whether the billable has tax
     *
     * @return {Boolean}
     */
    hasTax() {
      return this.taxType !== 'no_tax'
    },

    /**
     * Indicates whether the billable is tax inclusive
     *
     * @return {Boolean}
     */
    isTaxInclusive() {
      return this.taxType === 'inclusive'
    },
  },
  methods: {
    formatMoney,
  },
}
</script>
