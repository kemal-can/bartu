<template>
  <related-field-resource
    resource-name="activity-types"
    @created="refreshStoreTypes"
    @updated="refreshStoreTypes"
    @deleted="refreshStoreTypes"
    :with-cancel="false"
  >
    <template #header>
      <i-card-heading>{{ $t('activity.type.types') }}</i-card-heading>
    </template>
  </related-field-resource>
</template>
<script>
import RelatedFieldResource from '@/components/SimpleResourceCRUD'
export default {
  name: 'activity-types-index',
  components: { RelatedFieldResource },
  methods: {
    /**
     * Refresh the activity types that are in storage
     *
     * @return {Void}
     */
    refreshStoreTypes() {
      Innoclapps.request()
        .get('/activity-types', {
          params: {
            per_page: 100,
          },
        })
        .then(({ data }) =>
          this.$store.commit('activities/SET_TYPES', data.data)
        )
    },
  },
}
</script>
