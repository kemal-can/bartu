<template>
  <i-button
    variant="white"
    size="sm"
    v-if="hasCollapsedFields"
    @click="handleClickEvent"
    v-text="collapseText"
  ></i-button>
</template>
<script>
export default {
  props: {
    fields: { required: true, type: Array },
  },
  data: () => ({
    collapsed: true,
  }),
  computed: {
    /**
     * Get only the collapsible fields
     *
     * @return {Array}
     */
    collapsibleFields() {
      return this.fields.filter(field => field.collapsed)
    },

    /**
     * Indicates whether there are collapsed fields
     *
     * @return {Boolean}
     */
    hasCollapsedFields() {
      return this.collapsibleFields.length > 0
    },

    /**
     * Collapse text
     *
     * @return {String}
     */
    collapseText() {
      return this.$t(`fields.${this.collapsed ? 'more' : 'less'}`)
    },
  },

  methods: {
    handleClickEvent() {
      this.collapsed = !this.collapsed
      this.toggleFields()
    },
    toggleFields() {
      this.collapsibleFields.forEach(
        field => (field.displayNone = this.collapsed)
      )
    },
  },
  mounted() {
    this.toggleFields()
  },
}
</script>
