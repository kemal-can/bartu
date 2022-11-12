<template>
  <div class="space-y-3">
    <pipeline-index></pipeline-index>

    <i-card no-body :header="$t('deal.deals')" :overlay="!componentReady">
      <ul class="divide-y divide-neutral-200 dark:divide-neutral-700">
        <li class="px-4 py-4 sm:px-6">
          <div
            class="space-y-3 space-x-0 md:flex md:items-center md:justify-between md:space-y-0 lg:space-x-3"
          >
            <div>
              <h5
                class="font-medium leading-relaxed text-neutral-700 dark:text-neutral-100"
                v-t="'deal.settings.allow_lost_reason_enter'"
              />
              <p
                class="text-sm text-neutral-600 dark:text-neutral-300"
                v-t="'deal.settings.allow_lost_reason_enter_info'"
              />
            </div>
            <div>
              <i-form-toggle
                :value="true"
                :unchecked-value="false"
                @change="submit"
                v-model="form.allow_lost_reason_enter"
              />
            </div>
          </div>
        </li>
      </ul>
    </i-card>

    <lost-reasons-index></lost-reasons-index>
  </div>
</template>
<script>
import PipelineIndex from '@/views/Deals/Pipelines/Index'
import LostReasonsIndex from '@/views/Deals/LostReasonsIndex'
import HandleSettingsForm from './HandleSettingsForm'
export default {
  mixins: [HandleSettingsForm],
  components: { PipelineIndex, LostReasonsIndex },
}
</script>
