<template>
  <div class="table-responsive">
    <div
      class="overflow-auto border border-neutral-200 dark:border-neutral-800 sm:rounded-md"
    >
      <table
        class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700"
      >
        <thead class="bg-neutral-50 dark:bg-neutral-800">
          <tr>
            <th
              class="bg-neutral-50 py-2 pl-4 pr-2 text-xs font-semibold uppercase tracking-wider text-neutral-600 dark:bg-neutral-800 dark:text-neutral-200"
              v-t="'product.table_heading'"
            ></th>
            <th
              class="bg-neutral-50 p-2 text-right text-xs font-semibold uppercase tracking-wider text-neutral-600 dark:bg-neutral-800 dark:text-neutral-200"
              v-t="'product.qty'"
            ></th>
            <th
              class="bg-neutral-50 p-2 text-right text-xs font-semibold uppercase tracking-wider text-neutral-600 dark:bg-neutral-800 dark:text-neutral-200"
              v-t="'product.unit_price'"
            ></th>
            <th
              class="bg-neutral-50 p-2 text-right text-xs font-semibold uppercase tracking-wider text-neutral-600 dark:bg-neutral-800 dark:text-neutral-200"
              v-show="hasTax"
              v-t="'product.tax'"
            ></th>
            <th
              class="bg-neutral-50 p-2 text-right text-xs font-semibold uppercase tracking-wider text-neutral-600 dark:bg-neutral-800 dark:text-neutral-200"
              v-t="'product.discount'"
            ></th>
            <th
              class="bg-neutral-50 p-2 text-right text-xs font-semibold uppercase tracking-wider text-neutral-600 dark:bg-neutral-800 dark:text-neutral-200"
              v-t="'product.amount'"
            ></th>
            <th></th>
          </tr>
        </thead>
        <draggable
          v-model="form.products"
          :tag="'tbody'"
          class="divide-y divide-neutral-200 bg-white dark:divide-neutral-700 dark:bg-neutral-800"
          :item-key="(item, index) => index"
          handle=".draggable-handle"
          v-bind="draggableOptions"
          @end="updateProductsOrder"
        >
          <template #item="{ element, index }">
            <form-table-product :form="form" :index="index">
              <td
                class="bg-white p-2 text-right align-top text-sm font-medium text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100"
              >
                <div class="flex items-center justify-between space-x-2">
                  <div class="mt-2">
                    <i-minimal-dropdown type="horizontal">
                      <i-dropdown-item
                        v-show="
                          (productIdxNoteBeingAdded === null ||
                            productIdxNoteBeingAdded !== index) &&
                          !form.products[index].note
                        "
                        @click="addNote(index)"
                        >{{ $t('app.add_note') }}</i-dropdown-item
                      >
                      <i-dropdown-item @click="removeProduct(index)">{{
                        $t('app.remove')
                      }}</i-dropdown-item>
                    </i-minimal-dropdown>
                  </div>
                  <!-- Disabled for now, as there are 2 <tr> when not exists and cannot be sorted (multiple elements) -->
                  <!--    <i-button-icon
                    icon="Selector"
                    class="draggable-handle cursor-move mt-2"
                  /> -->
                </div>
              </td>
              <template #after>
                <tr
                  v-if="
                    productIdxNoteBeingAdded === index ||
                    element.note ||
                    form.products[index].note
                  "
                >
                  <td :colspan="hasTax ? 7 : 6">
                    <div
                      class="relative z-auto -mt-2 mb-1 rounded-sm bg-white p-2 dark:bg-neutral-800"
                    >
                      <i-form-label
                        :for="'product-note' + index"
                        class="mb-1"
                        >{{ $t('app.note_is_private') }}</i-form-label
                      >
                      <i-form-textarea
                        rows="2"
                        :id="'product-note' + index"
                        :ref="'note-' + index"
                        class="border border-warning-300 bg-warning-100 dark:bg-warning-200 dark:text-neutral-800"
                        :bordered="false"
                        v-model="form.products[index].note"
                      />
                    </div>
                  </td>
                </tr>
              </template>
            </form-table-product>
          </template>
        </draggable>
      </table>
    </div>
  </div>
  <a
    class="link mt-3 inline-block text-sm font-medium"
    href="#"
    @click.prevent="insertAnotherLine()"
    >+ {{ $t('app.insert_another_line') }}</a
  >
  <div>
    <total-section
      :tax-type="form.tax_type"
      :total="total"
      :total-discount="totalDiscount"
      :sub-total="subTotal"
      :taxes="appliedTaxes"
    />
  </div>
</template>
<script>
import FormTableProduct from '@/components/Billable/FormTableProductRow'
import TotalSection from '@/components/Billable/TotalSection'
import accounting from 'accounting-js'
import unionBy from 'lodash/unionBy'
import filter from 'lodash/filter'
import sortBy from 'lodash/sortBy'
import {
  totalProductAmountWithDiscount,
  totalProductDiscountAmount,
  totalTaxInAmount,
  blankProduct,
} from './utils'

import draggable from 'vuedraggable'
import ProvidesDraggableOptions from '@/mixins/ProvidesDraggableOptions'
export default {
  mixins: [ProvidesDraggableOptions],
  components: {
    FormTableProduct,
    TotalSection,
    draggable,
  },
  props: {
    form: { required: true, type: Object },
  },
  data: () => ({
    precision: Innoclapps.config.currency.precision,
    productIdxNoteBeingAdded: null,
  }),
  computed: {
    /**
     * Indicates whether the billable has tax
     *
     * @return {Boolean}
     */
    hasTax() {
      return this.form.tax_type !== 'no_tax'
    },

    /**
     * Check whether the billable is tax inclusive
     *
     * @return {Boolean}
     */
    isTaxInclusive() {
      return this.form.tax_type === 'inclusive'
    },

    /**
     * Total
     *
     * @return {Number}
     */
    total() {
      let total =
        parseFloat(this.subTotal) +
        parseFloat(!this.isTaxInclusive ? this.totalTax : 0)

      return parseFloat(accounting.toFixed(total, this.precision))
    },

    /**
     * Total discount
     *
     * @return {Number}
     */
    totalDiscount() {
      return parseFloat(
        accounting.toFixed(
          this.form.products.reduce((total, product) => {
            return (
              total + totalProductDiscountAmount(product, this.isTaxInclusive)
            )
          }, 0),
          this.precision
        )
      )
    },

    /**
     * Subtotal
     *
     * @return {Number}
     */
    subTotal() {
      return parseFloat(
        accounting.toFixed(
          this.form.products.reduce((total, product) => {
            return (
              total +
              totalProductAmountWithDiscount(product, this.isTaxInclusive)
            )
          }, 0),
          this.precision
        )
      )
    },

    /**
     * Get the unique applied taxes
     *
     * @return {Array}
     */
    appliedTaxes() {
      if (this.form.tax_type === 'no_tax') {
        return []
      }

      return sortBy(
        unionBy(this.form.products, product => {
          // Track uniqueness by tax label and tax rate
          return product.tax_label + product.tax_rate
        }),
        'tax_rate'
      )
        .filter(tax => tax.tax_rate > 0)
        .reduce((groups, tax) => {
          groups.push({
            key: tax.tax_label + tax.tax_rate,
            rate: tax.tax_rate,
            label: tax.tax_label,
            // We will get all products that are using the current tax in the loop
            total: filter(this.form.products, {
              tax_label: tax.tax_label,
              tax_rate: tax.tax_rate,
            })
              // Calculate the total tax based on the product
              .reduce((total, product) => {
                total += totalTaxInAmount(
                  totalProductAmountWithDiscount(product, this.isTaxInclusive),
                  product.tax_rate,
                  this.isTaxInclusive
                )
                return total
              }, 0),
          })
          return groups
        }, [])
    },

    /**
     * Total tax calculated
     *
     * @return {Number}
     */
    totalTax() {
      return parseFloat(
        accounting.toFixed(
          this.appliedTaxes.reduce((total, tax) => {
            return (
              total + parseFloat(accounting.toFixed(tax.total, this.precision))
            )
          }, 0),
          this.precision
        )
      )
    },
  },
  methods: {
    /**
     * Add note to a product
     *
     * @param {Void} index
     */
    addNote(index) {
      this.productIdxNoteBeingAdded = index
      this.$nextTick(() => this.$refs['note-' + index].focus())
    },

    /**
     * Queue product for removal
     *
     * @param  {Number} index
     *
     * @return {Void}
     */
    removeProduct(index) {
      let product = this.form.products[index]

      if (product.id) {
        this.form.removed_products.push(product.id)
      }

      // Clear errors in case there was error previously for the index
      // If we don't clear the errors the product that is below will be
      // shown as error after the given index is deleted
      // e.q. add 2 products, cause error on first, delete first
      if (this.form.errors.has('products.' + index + '.name')) {
        this.form.errors.clear('products.' + index + '.name')
      }

      this.form.products.splice(index, 1)
    },

    /**
     * Add new line
     *
     * @return {Void}
     */
    insertAnotherLine() {
      this.form.products.push(blankProduct())
      this.updateProductsOrder()
    },

    /**
     * Update the products display order
     *
     * @return {Void}
     */
    updateProductsOrder() {
      this.form.products.forEach(
        (product, index) =>
          (this.form.products[index].display_order = index + 1)
      )
    },
  },
}
</script>
