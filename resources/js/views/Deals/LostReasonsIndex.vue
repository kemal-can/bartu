<template>
  <related-field-resource
    resource-name="lost-reasons"
    @created="refreshStoreTypes"
    @updated="refreshStoreTypes"
    @deleted="refreshStoreTypes"
    :with-cancel="false"
  >
    <template #header>
      <i-card-heading>{{
        $t('deal.lost_reasons.lost_reasons')
      }}</i-card-heading>
    </template>
  </related-field-resource>
</template>
<script>
import RelatedFieldResource from '@/components/SimpleResourceCRUD'
export default {
  name: 'lost-reasons-index',
  components: { RelatedFieldResource },
  methods: {
    /**
     * Refresh the lost reasons that are in storage
     *
     * @return {Void}
     */
    refreshStoreTypes() {
      Innoclapps.request()
        .get('/lost-reasons', {
          params: {
            per_page: 100,
          },
        })
        .then(({ data }) =>
          this.$store.commit('deals/SET_LOST_REASONS', data.data)
        )
    },
  },
}
</script>
