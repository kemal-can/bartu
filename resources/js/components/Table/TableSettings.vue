<template>
  <div class="mb-2 flex w-full items-center space-x-2 md:mb-0 md:w-auto">
    <i-button
      variant="white"
      v-if="config.customizeable && withCustomizeButton"
      @click="() => $refs.settings.showModal()"
      icon="Cog"
    />
  </div>

  <!-- Settings Modal -->
  <customize-table-settings
    :config="config"
    ref="settings"
    :table-id="tableId"
    :url-path="urlPath"
  />
</template>
<script>
import CustomizeTableSettings from '@/components/Table/TableSettingsCustomization'
import ProvidesSettingsData from './ProvidesSettingsData'
export default {
  mixins: [ProvidesSettingsData],
  components: {
    CustomizeTableSettings,
  },
  props: {
    withCustomizeButton: {
      type: Boolean,
      default: false,
    },
    resourceName: {
      type: String,
      required: true,
    },
  },
}
</script>
