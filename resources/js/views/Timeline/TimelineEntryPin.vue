<template>
  <a
    href="#"
    @click.prevent="pin"
    class="text-xs text-neutral-800 hover:text-neutral-500 dark:text-neutral-200 dark:hover:text-neutral-400"
    v-t="'timeline.pin'"
    v-if="!timelineable.is_pinned"
  />
  <a
    v-else
    href="#"
    class="text-xs text-neutral-800 hover:text-neutral-500 dark:text-neutral-200 dark:hover:text-neutral-400"
    @click.prevent="unpin"
    v-t="'timeline.unpin'"
  />
</template>
<script>
import InteractsWithResource from '@/mixins/InteractsWithResource'

export default {
  mixins: [InteractsWithResource],
  props: {
    timelineable: {
      type: Object,
      required: true,
    },
  },
  methods: {
    /**
     * Pin the timelineable
     *
     * @return {Void}
     */
    pin() {
      Innoclapps.request()
        .post('timeline/pin', {
          subject_id: Number(this.resourceRecord.id),
          subject_type: this.resourceRecord.timeline_subject_key,
          timelineable_id: Number(this.timelineable.id),
          timelineable_type: this.timelineable.timeline_key,
        })
        .then(() => {
          this.updateResourceRecordHasManyRelationship(
            {
              id: this.timelineable.id,
              is_pinned: true,
              pinned_date: this.appMoment().toISOString(), // toISOString allowing consistency with the back-end dates
            },
            this.timelineable.timeline_relation
          )
        })
    },

    /**
     * Unpin the timelineable
     *
     * @return {Void}
     */
    unpin() {
      Innoclapps.request()
        .post('timeline/unpin', {
          subject_id: Number(this.resourceRecord.id),
          subject_type: this.resourceRecord.timeline_subject_key,
          timelineable_id: Number(this.timelineable.id),
          timelineable_type: this.timelineable.timeline_key,
        })
        .then(() => {
          this.updateResourceRecordHasManyRelationship(
            {
              id: this.timelineable.id,
              is_pinned: false,
              pinned_date: null,
            },
            this.timelineable.timeline_relation
          )
        })
    },
  },
}
</script>
