<template>
  <media-card
    :record="deal"
    :resource-name="resourceName"
    @deleted="removeResourceRecordMedia"
    @uploaded="addResourceRecordMedia"
  />
</template>
<script>
import MediaCard from '@/components/Media/ResourceRecordMediaCard'
import InteractsWithResource from '@/mixins/InteractsWithResource'
export default {
  inheritAttrs: false,
  emits: ['refetch'],
  mixins: [InteractsWithResource],
  components: {
    MediaCard,
  },
  props: {
    deal: { required: true, type: Object },
    resourceName: { type: String, default: 'deals' },
  },
}
</script>
