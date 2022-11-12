<template>
  <div
    :class="{
      'rounded-lg border border-neutral-100 bg-white shadow dark:border-neutral-700 dark:bg-neutral-900':
        card,
    }"
  >
    <div :class="{ 'px-4 py-5 sm:px-6': card }">
      <slot name="heading">
        <h2 class="font-medium text-neutral-800 dark:text-white">
          {{ $t('app.attachments') }}
          <span
            v-show="total > 0"
            class="text-sm font-normal text-neutral-400"
            v-text="'(' + total + ')'"
          />
        </h2>
      </slot>
      <div :class="wrapperClass" v-if="show">
        <media-items-list
          :items="localMedia"
          :authorize-delete="$gate.allows('update', record)"
          @delete-requested="destroy"
        />
        <p
          v-show="!hasMedia"
          class="text-sm text-neutral-500 dark:text-neutral-300"
          v-t="'app.no_attachments'"
        />
        <div class="mt-3">
          <media-upload
            @file-uploaded="uploaded"
            :input-id="
              'media-' +
              resourceName +
              '-' +
              record.id +
              (isFloating ? '-floating' : '')
            "
            :action-url="`${$store.state.apiURL}/${resourceName}/${record.id}/media`"
          />
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import MediaUpload from './MediaUpload'
import MediaItemsList from './MediaItemsList'
import orderBy from 'lodash/orderBy'

export default {
  emits: ['deleted', 'uploaded'],
  components: {
    MediaUpload,
    MediaItemsList,
  },
  props: {
    show: { default: true, type: Boolean },
    record: { required: true, type: Object },
    resourceName: { type: String, required: true },
    isFloating: { type: Boolean, required: false },
    automaticUpload: { default: true, type: Boolean },
    card: { default: true, type: Boolean },
    wrapperClass: [String, Array, Object],
  },
  computed: {
    /**
     * Get the component media ordered by created_at
     *
     * @return {Array}
     */
    localMedia() {
      return orderBy(this.record.media, media => new Date(media.created_at), [
        'desc',
      ])
    },

    /**
     * Get the count of the record media
     */
    total() {
      // Perhaps not yet loaded?
      if (!this.record || (this.record && !this.record.media)) {
        return 0
      }

      return this.record.media.length
    },

    /**
     * Indicates whether the record has attachments uploaded
     *
     * @return {Boolean}
     */
    hasMedia() {
      return this.total > 0
    },
  },
  methods: {
    /**
     * Handle the uploaded event
     *
     * @param  {Obejct} media
     *
     * @return {Void}
     */
    uploaded(media) {
      this.$emit('uploaded', media)
    },

    /**
     * Remove media from resource
     *
     * @param  {Object} media
     *
     * @return {Void}
     */
    destroy(media) {
      this.$dialog.confirm().then(() => {
        Innoclapps.request()
          .delete(`/${this.resourceName}/${this.record.id}/media/${media.id}`)
          .then(({ data }) => this.$emit('deleted', media))
      })
    },
  },
}
</script>
