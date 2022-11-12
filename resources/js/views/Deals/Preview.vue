<template>
  <div>
    <div
      class="relative mb-5 overflow-hidden rounded-md border border-primary-300 py-3 px-4 md:px-5"
      v-if="previewReady"
    >
      <div class="flex flex-col items-center md:flex-row">
        <deal-stage-popover
          class="relative z-20 shrink-0 text-neutral-800 hover:text-neutral-600 dark:text-neutral-200 dark:hover:text-neutral-400 md:mr-3"
          :deal="record"
          is-floating
        />
        <p
          class="relative z-20 block text-sm text-neutral-500 md:hidden"
          v-text="beenInStageText"
        />
        <deal-status-change
          class="relative z-20 my-2 md:my-0 md:ml-auto md:mr-32"
          :deal="record"
          is-floating
        />
      </div>
      <card-header-grid-background
        class="-top-full -right-2 hidden h-44 w-full md:block"
      />
      <p
        class="relative z-20 -mt-1.5 hidden text-center text-sm text-neutral-500 dark:text-neutral-300 md:block md:text-left"
        v-text="beenInStageText"
      ></p>
    </div>
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

    <deal-contact-card
      class="mb-3 sm:mb-5"
      v-show="previewReady"
      @dissociated="removeResourceRecordHasManyRelationship($event, 'contacts')"
      :deal="record"
    />

    <deal-company-card
      class="mb-3 sm:mb-5"
      v-show="previewReady"
      @dissociated="
        removeResourceRecordHasManyRelationship($event, 'companies')
      "
      :deal="record"
    />

    <media-card
      class="mb-3 sm:mb-5"
      :record="record"
      v-show="previewReady"
      @uploaded="addResourceRecordMedia"
      @deleted="removeResourceRecordMedia"
      :is-floating="true"
      resource-name="deals"
    />
  </div>
</template>
<script>
import DealStatusChange from '@/views/Deals/StatusChange'
import DealStagePopover from '@/views/Deals/DealStagePopover'
import MediaCard from '@/components/Media/ResourceRecordMediaCard'
import InteractsWithResource from '@/mixins/InteractsWithResource'
import DealCompanyCard from '@/views/Deals/DealCompanyCard'
import DealContactCard from '@/views/Deals/DealContactCard'
import FieldsCollapseButton from '@/components/Fields/ButtonCollapse'
import CardHeaderGridBackground from '@/components/Cards/HeaderGridBackground'
export default {
  mixins: [InteractsWithResource],
  inject: ['setDescription'],
  components: {
    DealStatusChange,
    DealStagePopover,
    DealCompanyCard,
    DealContactCard,
    MediaCard,
    FieldsCollapseButton,
    CardHeaderGridBackground,
  },
  props: ['record', 'form', 'fields', 'updateFieldsFunction', 'previewReady'],
  watch: {
    previewReady: {
      handler: function (newVal, oldVal) {
        if (newVal) {
          this.setDescription(
            `${this.$t('app.created_at')} ${this.localizedDateTime(
              this.record.created_at
            )}`
          )
        }
      },
      immediate: true,
    },
  },
  computed: {
    beenInStageText() {
      const duration = moment.duration({
        seconds: this.record.time_in_stages[this.record.stage.id],
      })

      return this.$t('deal.been_in_stage_time', {
        time: duration.humanize(),
      })
    },
  },
}
</script>
