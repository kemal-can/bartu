<template>
  <i-form-error v-text="errorMessage" v-if="hasError"></i-form-error>
</template>
<script>
export default {
  props: {
    field: { required: true, type: String },
    form: { required: true, type: Object },
  },
  computed: {
    /**
     * Get the first error message for the field
     *
     * @return {null|String}
     */
    errorMessage() {
      return this.form.errors.get(this.field)
    },

    /**
     * Indicates whether the field has error
     *
     * @return {Boolean}
     */
    hasError() {
      // hasForm && is checking if form errors exists, perhaps is ASYNC request and the form is not yet created
      return this.hasForm && this.form.errors.has(this.field)
    },

    /**
     * Indicates whether there is form class provided
     *
     * @return {Boolean}
     */
    hasForm() {
      return !!this.form.errors
    },
  },
}
</script>
