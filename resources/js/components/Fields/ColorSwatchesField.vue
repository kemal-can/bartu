<template>
  <form-field-group :field="field" :form="form" :field-id="fieldId">
    <div>
      <i-color-swatches
        :swatches="swatches"
        :allow-remove="!field.isRequired"
        v-model="value"
      />
    </div>
  </form-field-group>
</template>
<script>
import FormField from '@/components/Form/FormField'
export default {
  mixins: [FormField],
  data: () => ({
    swatches: Innoclapps.config.favourite_colors,
  }),
}
</script>
