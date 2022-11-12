<template>
  <div class="table-responsive shadow sm:rounded-md">
    <div class="border-b border-neutral-200 dark:border-neutral-700">
      <table
        class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-600"
      >
        <thead class="bg-neutral-50 dark:bg-neutral-800">
          <th
            class="whitespace-nowrap bg-neutral-50 p-3 text-left text-xs font-semibold uppercase tracking-wider text-neutral-600 dark:bg-neutral-900 dark:text-neutral-200"
            v-t="'product.table_heading'"
          />
          <th
            class="whitespace-nowrap bg-neutral-50 px-2 py-3 text-right text-xs font-semibold uppercase tracking-wider text-neutral-600 dark:bg-neutral-900 dark:text-neutral-200"
            v-t="'product.qty'"
          />
          <th
            class="whitespace-nowrap bg-neutral-50 px-2 py-3 text-right text-xs font-semibold uppercase tracking-wider text-neutral-600 dark:bg-neutral-900 dark:text-neutral-200"
            v-t="'product.unit_price'"
          />
          <th
            class="whitespace-nowrap bg-neutral-50 px-2 py-3 text-right text-xs font-semibold uppercase tracking-wider text-neutral-600 dark:bg-neutral-900 dark:text-neutral-200"
            v-show="billable.tax_type !== 'no_tax'"
            v-t="'product.tax'"
          />
          <th
            class="whitespace-nowrap bg-neutral-50 px-2 py-3 text-right text-xs font-semibold uppercase tracking-wider text-neutral-600 dark:bg-neutral-900 dark:text-neutral-200"
            v-show="billable.has_discount"
            v-t="'product.discount'"
          />
          <th
            class="whitespace-nowrap bg-neutral-50 p-3 text-right text-xs font-semibold uppercase tracking-wider text-neutral-600 dark:bg-neutral-900 dark:text-neutral-200"
            v-t="'product.amount'"
          />
        </thead>
        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-600">
          <tr v-for="product in billable.products" :key="product.id">
            <td
              class="w-80 whitespace-nowrap bg-white p-3 text-left align-top text-sm text-neutral-900 dark:bg-neutral-700 dark:text-neutral-100"
            >
              <div class="flex">
                <span class="mr-2 mt-px" v-if="product.note">
                  <i-popover :triggers="['hover', 'focus']" placement="top">
                    <a class="mr-2">
                      <icon
                        icon="Annotation"
                        class="h-4 w-4 text-neutral-500 hover:text-neutral-700 dark:text-white dark:hover:text-neutral-300"
                      />
                    </a>
                    <template #popper>
                      <p
                        class="whitespace-pre-line text-sm text-neutral-700 dark:text-neutral-200"
                        v-text="product.note"
                      ></p>
                    </template>
                  </i-popover>
                </span>
                <div>
                  <p class="font-medium text-neutral-800 dark:text-neutral-100">
                    {{ (product.sku ? product.sku + ': ' : '') + product.name }}
                  </p>
                  <div
                    class="mt-1 whitespace-pre-line text-neutral-600 dark:text-neutral-300"
                    v-show="product.description"
                    v-text="product.description"
                  ></div>
                </div>
              </div>
            </td>
            <td
              class="whitespace-nowrap bg-white p-2 text-right align-top text-sm text-neutral-900 dark:bg-neutral-700 dark:text-neutral-100"
            >
              {{ product.qty }} {{ product.unit || '' }}
            </td>
            <td
              class="whitespace-nowrap bg-white p-2 text-right align-top text-sm text-neutral-900 dark:bg-neutral-700 dark:text-neutral-100"
            >
              {{ formatMoney(product.unit_price) }}
            </td>
            <td
              v-show="billable.tax_type !== 'no_tax'"
              class="whitespace-nowrap bg-white p-2 text-right align-top text-sm text-neutral-900 dark:bg-neutral-700 dark:text-neutral-100"
            >
              {{ product.tax_label }} ({{ product.tax_rate }}%)
            </td>
            <td
              class="whitespace-nowrap bg-white p-2 text-right align-top text-sm text-neutral-900 dark:bg-neutral-700 dark:text-neutral-100"
              v-show="billable.has_discount"
            >
              <span v-show="product.discount_type === 'fixed'">
                {{ formatMoney(product.discount_total) }}
              </span>
              <span v-show="product.discount_type === 'percent'">
                {{ product.discount_total }}%
              </span>
            </td>
            <td
              class="whitespace-nowrap bg-white p-3 text-right align-top text-sm text-neutral-900 dark:bg-neutral-700 dark:text-neutral-100"
            >
              {{ formatMoney(product.amount) }}
            </td>
          </tr>

          <tr v-show="!hasProducts">
            <td
              :colspan="billable.tax_type === 'no_tax' ? 4 : 5"
              class="bg-white p-3 text-center text-sm text-neutral-900 dark:bg-neutral-700 dark:text-neutral-100"
              v-t="'product.resource_has_no_products'"
            />
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <total-section
    v-show="hasProducts"
    :tax-type="billable.tax_type"
    :total="billable.total"
    :total-discount="billable.total_discount"
    :sub-total="billable.sub_total"
    :taxes="billable.applied_taxes"
  />
</template>
<script>
import TotalSection from '@/components/Billable/TotalSection'
import { formatMoney } from '@/utils'
export default {
  components: { TotalSection },
  props: {
    billable: { required: true, type: Object },
  },
  computed: {
    hasProducts() {
      return this.billable.products.length > 0
    },
  },
  methods: {
    formatMoney,
  },
}
</script>
