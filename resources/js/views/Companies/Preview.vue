<template>
  <div>
    <fields-generator
      :fields="fields"
      :form="form"
      view="update"
      :is-floating="true"
    >
      <template #after="{ fields }">
        <fields-collapse-button
          :fields="fields"
          v-if="previewReady"
          class="mb-3"
        />
      </template>
    </fields-generator>

    <company-contact-card
      class="mb-3 sm:mb-5"
      v-show="previewReady"
      @dissociated="removeResourceRecordHasManyRelationship($event, 'contacts')"
      :company="record"
    />

    <company-deal-card
      class="mb-3 sm:mb-5"
      v-show="previewReady"
      @dissociated="removeResourceRecordHasManyRelationship($event, 'deals')"
      :company="record"
    />

    <media-card
      class="mb-3 sm:mb-5"
      :record="record"
      v-show="previewReady"
      @uploaded="addResourceRecordMedia"
      @deleted="removeResourceRecordMedia"
      :is-floating="true"
      resource-name="companies"
    />
  </div>
</template>
<script>
import MediaCard from '@/components/Media/ResourceRecordMediaCard'
import InteractsWithResource from '@/mixins/InteractsWithResource'
import CompanyDealCard from '@/views/Companies/CompanyDealCard'
import CompanyContactCard from '@/views/Companies/CompanyContactCard'
import FieldsCollapseButton from '@/components/Fields/ButtonCollapse'
export default {
  mixins: [InteractsWithResource],
  components: {
    CompanyDealCard,
    CompanyContactCard,
    MediaCard,
    FieldsCollapseButton,
  },
  props: ['record', 'form', 'fields', 'updateFieldsFunction', 'previewReady'],
}
</script>
