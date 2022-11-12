<template>
  <related-field-resource
    resource-name="call-outcomes"
    @created="refreshStoreOutcomes"
    @updated="refreshStoreOutcomes"
    @deleted="refreshStoreOutcomes"
    :with-cancel="false"
  >
    <template #header>
      <i-card-heading>{{ $t('call.outcome.outcomes') }}</i-card-heading>
    </template>
  </related-field-resource>
</template>
<script>
import RelatedFieldResource from '@/components/SimpleResourceCRUD'
export default {
  name: 'call-outcomes-index',
  components: { RelatedFieldResource },
  methods: {
    /**
     * Refresh the call outcomes that are in storage
     *
     * @return {Void}
     */
    refreshStoreOutcomes() {
      Innoclapps.request()
        .get('/call-outcomes', {
          params: {
            per_page: 100,
          },
        })
        .then(({ data }) => this.$store.commit('calls/SET_OUTCOMES', data.data))
    },
  },
}
</script>
