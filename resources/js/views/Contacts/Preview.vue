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

    <contact-company-card
      class="mb-3 sm:mb-5"
      v-show="previewReady"
      @dissociated="
        removeResourceRecordHasManyRelationship($event, 'companies')
      "
      :contact="record"
    />

    <contact-deal-card
      class="mb-3 sm:mb-5"
      v-show="previewReady"
      @dissociated="removeResourceRecordHasManyRelationship($event, 'deals')"
      :contact="record"
    />

    <media-card
      class="mb-3 sm:mb-5"
      :record="record"
      v-show="previewReady"
      @uploaded="addResourceRecordMedia"
      @deleted="removeResourceRecordMedia"
      :is-floating="true"
      resource-name="contacts"
    />
  </div>
</template>
<script>
import InteractsWithResource from '@/mixins/InteractsWithResource'
import MediaCard from '@/components/Media/ResourceRecordMediaCard'
import ContactCompanyCard from '@/views/Contacts/ContactCompanyCard'
import ContactDealCard from '@/views/Contacts/ContactDealCard'
import FieldsCollapseButton from '@/components/Fields/ButtonCollapse'
export default {
  mixins: [InteractsWithResource],
  components: {
    ContactCompanyCard,
    ContactDealCard,
    MediaCard,
    FieldsCollapseButton,
  },
  props: ['record', 'form', 'fields', 'updateFieldsFunction', 'previewReady'],
}
</script>
