<template>
  <div>
    <i-form-radio
      v-for="taxType in formattedTaxTypes"
      :key="taxType.value"
      :id="taxType.value"
      :value="taxType.value"
      :model-value="modelValue"
      name="tax_type"
      @change="$emit('update:modelValue', $event)"
      :label="taxType.text"
    />
  </div>
</template>
<script>
export default {
  emits: ['update:modelValue'],
  props: ['modelValue'],
  data: () => ({
    taxTypes: Innoclapps.config.taxes.types,
  }),
  computed: {
    /**
     * Formatted tax types for the radio field
     *
     * @return {Array}
     */
    formattedTaxTypes() {
      return this.taxTypes.map(type => {
        return {
          value: type,
          text: this.$t('billable.tax_types.' + type),
        }
      })
    },
  },
}
</script>
