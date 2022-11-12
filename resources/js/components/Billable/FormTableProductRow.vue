<template>
  <tr v-bind="$attrs">
    <td
      width="30%"
      class="bg-white p-2 align-top text-sm font-medium text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100"
    >
      <div class="w-60 sm:w-auto">
        <i-custom-select
          @option:selected="handleProductChangeEvent"
          @cleared="handleProductChangeEvent(null)"
          :create-option-provider="createNewProductViaSelect"
          :option-label-provider="provideProductSelectFieldOptionLabel"
          :placeholder="$t('product.choose_or_enter')"
          label="name"
          :taggable="true"
          :filterable="true"
          v-model="selectedProduct"
          :options="products"
        >
        </i-custom-select>
        <form-error :field="'products.' + index + '.name'" :form="form" />
        <i-form-textarea
          v-show="selectedProduct"
          v-model="form.products[index].description"
          class="mt-1"
          ref="description"
          :name="'products' + '.' + index + '.description'"
          :placeholder="
            $t('product.description') + ' ' + '(' + $t('app.optional') + ')'
          "
          :rows="3"
        />
      </div>
    </td>
    <td
      class="bg-white p-2 align-top text-sm font-medium text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100"
    >
      <div class="w-32 sm:w-auto">
        <i-form-numeric-input
          class="text-right"
          size="sm"
          decimal-separator="."
          :precision="2"
          ref="quantity"
          :empty-value="1"
          :placeholder="$t('product.quantity')"
          pattern=".*"
          v-model="form.products[index].qty"
        >
        </i-form-numeric-input>
        <i-form-input
          size="sm"
          :placeholder="$t('product.unit')"
          class="mt-1 text-right"
          v-model="form.products[index].unit"
        />
      </div>
    </td>
    <td
      class="bg-white p-2 align-top text-sm font-medium text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100"
    >
      <div class="w-40 sm:w-auto">
        <i-form-numeric-input
          class="text-right"
          size="sm"
          :placeholder="$t('product.unit_price')"
          :minus="true"
          v-model="form.products[index].unit_price"
        />
      </div>
    </td>
    <td
      v-show="form.tax_type !== 'no_tax'"
      class="bg-white p-2 align-top text-sm font-medium text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100"
    >
      <div class="w-44 sm:w-auto">
        <div class="flex rounded-md shadow-sm">
          <div class="relative flex grow items-stretch focus-within:z-10">
            <i-form-numeric-input
              :placeholder="$t('product.tax_percent')"
              :precision="3"
              size="sm"
              :rounded="false"
              class="rounded-l-md"
              :minus="true"
              :max="100"
              v-model="form.products[index].tax_rate"
            />
          </div>
          <div
            class="-ml-px flex items-center border border-neutral-300 bg-white px-1.5 dark:border-neutral-500 dark:bg-neutral-700"
            v-text="'%'"
          />
          <i-popover placement="auto">
            <i-button
              :rounded="false"
              size="sm"
              variant="white"
              class="-ml-px rounded-r-md"
            >
              {{ form.products[index].tax_label }}
            </i-button>
            <template #popper>
              <i-form-input
                v-model="form.products[index].tax_label"
              ></i-form-input>
            </template>
          </i-popover>
        </div>
      </div>
    </td>
    <td
      class="bg-white p-2 align-top text-sm font-medium text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100"
    >
      <div class="relative w-44 rounded-md shadow-sm sm:w-auto">
        <i-form-numeric-input
          class="pr-12"
          size="sm"
          :placeholder="$t('product.discount_amount')"
          v-if="form.products[index].discount_type == 'fixed'"
          v-model="form.products[index].discount_total"
        />
        <i-form-numeric-input
          class="pr-12"
          :placeholder="$t('product.discount_percent')"
          :max="100"
          size="sm"
          v-else
          :precision="2"
          v-model="form.products[index].discount_total"
        />

        <div class="absolute inset-y-0 right-0 flex items-center">
          <i-form-select
            v-model="form.products[index].discount_type"
            :bordered="false"
            size="sm"
            class="bg-transparent pr-4 dark:bg-transparent"
          >
            <option
              v-for="dType in discountTypes"
              :key="dType.value"
              :value="dType.value"
            >
              {{ dType.label }}
            </option>
          </i-form-select>
        </div>
      </div>
    </td>
    <td
      class="bg-white py-4 px-2 text-right align-top text-sm font-medium text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100"
    >
      {{ formatMoney(amountBeforeTaxWithDiscountApplied) }}
    </td>
    <slot></slot>
  </tr>
  <slot name="after"></slot>
</template>
<script>
import { totalProductAmountWithDiscount, blankProduct } from './utils'
import { formatMoney } from '@/utils'

export default {
  inheritAttrs: false,
  props: {
    form: { required: true, type: Object },
    index: { required: true, type: Number },
  },
  data: () => ({
    currency: Innoclapps.config.currency,
    selectedProduct: null,
    discountTypes: [
      { label: Innoclapps.config.currency.iso_code, value: 'fixed' },
      { label: '%', value: 'percent' },
    ],
  }),
  computed: {
    /**
     * Get the amount before any tax calculations and with discount applied
     * for the last amount column
     *
     * @return {Number}
     */
    amountBeforeTaxWithDiscountApplied() {
      return totalProductAmountWithDiscount(
        this.form.products[this.index],
        this.form.tax_type === 'inclusive'
      )
    },

    /**
     * Available products
     *
     * @return {Array}
     */
    products() {
      return this.$store.state.products.active
    },
  },
  methods: {
    /**
     * Create new product for select
     *
     * @param  {String} newOption
     *
     * @return {Object}
     */
    createNewProductViaSelect(newOption) {
      return blankProduct({
        name: newOption,
      })
    },

    /**
     * Provide the select field option label
     *
     * @param  {Object} option
     *
     * @return {String}
     */
    provideProductSelectFieldOptionLabel(option) {
      if (option.sku) {
        // Allow sku in label to be searchable as well
        return option.sku + ': ' + option.name
      }

      return option.name
    },

    /**
     * Handle the product change event
     *
     * @param  {Object|null} product
     *
     * @return {Void}
     */
    handleProductChangeEvent(product) {
      if (product) {
        this.form.errors.clear('products.' + this.index + '.name')

        const billableProduct = {
          product_id: product.id,
          name: product.name,
          description: product.description,
          unit_price: product.unit_price || 0,
          unit: product.unit,
          tax_rate: product.tax_rate || 0,
          tax_label: product.tax_label,
        }

        // Perhaps new product?
        if (!billableProduct.id) {
          // We will try to find an existing product from the product list
          // based on the name user entered, users may enter names but don't realize
          // that the product already exists, in this case, we will help the user
          // to pre-use the product and prevent creating this product in storage on server side
          Innoclapps.request()
            .get('/products/search', {
              params: {
                q: product.name,
                search_fields: 'name',
              },
            })
            .then(({ data }) => {
              if (data.length === 0) {
                Innoclapps.info(
                  this.$t('product.will_be_added_as_new', {
                    name: product.name,
                  })
                )
              } else {
                billableProduct.product_id = data[0].id
              }
            })
        }

        this.form.products[this.index] = Object.assign(
          this.form.products[this.index],
          billableProduct
        )
      } else {
        this.form.products[this.index].name = null
        this.form.products[this.index].product_id = null
      }
    },
    formatMoney,
  },
  mounted() {
    this.selectedProduct = this.form.products[this.index]
  },
}
</script>
