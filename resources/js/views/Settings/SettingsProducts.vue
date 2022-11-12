<template>
  <form @submit.prevent="submit" @keydown="form.onKeydown($event)">
    <i-card :header="$t('product.products')" :overlay="!componentReady">
      <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
        <div class="sm:col-span-2">
          <i-form-group
            :label="$t('product.tax_label')"
            label-for="tax_label"
            required
          >
            <i-form-input
              v-model="form.tax_label"
              id="tax_label"
            ></i-form-input>
          </i-form-group>
        </div>

        <div class="sm:col-span-2">
          <i-form-group :label="$t('product.tax_rate')" label-for="tax_rate">
            <i-form-numeric-input
              :placeholder="$t('product.tax_percent')"
              :precision="3"
              :minus="true"
              v-model="form.tax_rate"
            >
            </i-form-numeric-input>
          </i-form-group>
        </div>
      </div>

      <i-form-group
        class="mt-3 space-y-1"
        :label="$t('product.settings.default_tax_type')"
        label-for="tax_type"
      >
        <i-form-radio
          v-for="taxType in taxTypes"
          :label="$t('billable.tax_types.' + taxType)"
          :id="taxType"
          v-model="form.tax_type"
          :value="taxType"
          :key="taxType"
          name="tax_type"
        />
      </i-form-group>
      <i-form-group
        :label="$t('product.settings.default_discount_type')"
        label-for="tax_type"
        class="mt-3 space-y-1"
      >
        <i-form-radio
          :label="discountType.label"
          v-for="discountType in discountTypes"
          :id="discountType.value"
          v-model="form.discount_type"
          :value="discountType.value"
          :key="discountType.value"
          name="discount_type"
        />
      </i-form-group>
      <template #footer>
        <i-button type="submit" :disabled="form.busy">{{
          $t('app.save')
        }}</i-button>
      </template>
    </i-card>
  </form>
</template>
<script>
import HandleSettingsForm from './HandleSettingsForm'
export default {
  mixins: [HandleSettingsForm],
  data: () => ({
    taxTypes: Innoclapps.config.taxes.types,
    discountTypes: [
      { label: Innoclapps.config.currency.iso_code, value: 'fixed' },
      { label: '%', value: 'percent' },
    ],
  }),
}
</script>
