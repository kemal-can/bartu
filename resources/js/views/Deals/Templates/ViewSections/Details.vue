<template>
  <div
    class="sticky top-2 z-10 mt-5 block overflow-hidden rounded-md bg-white px-6 py-4 shadow dark:bg-neutral-700 md:hidden"
    v-show="componentReady && (fieldsAreDirty || recordForm.recentlySuccessful)"
  >
    <card-header-grid-background
      class="-top-16 z-0 h-44 w-full"
      style="transform: skewY(-11deg)"
    />
    <div class="flex items-center">
      <i-button
        size="sm"
        @click="update"
        class="relative z-10 px-5"
        :loading="recordForm.busy"
        :disabled="recordForm.busy || $gate.denies('update', record)"
      >
        {{ $t('app.save') }}
      </i-button>
      <i-action-message
        :message="$t('app.saved')"
        class="relative z-10 ml-2"
        v-show="recordForm.recentlySuccessful"
      />
    </div>
  </div>
  <form @submit.prevent="update" novalidate="true" class="pb-1">
    <i-overlay
      :show="!componentReady"
      :class="{
        'rounded-lg bg-white p-6 shadow dark:bg-neutral-900': !componentReady,
      }"
    >
      <i-alert
        :show="recordForm.errors.any()"
        variant="warning"
        class="mb-2 border border-warning-200"
      >
        {{ $t('app.form_validation_failed') }}
      </i-alert>

      <fields-generator
        v-if="componentReady"
        :class="[
          'overflow-y-auto rounded-t-lg bg-white p-6 shadow dark:bg-neutral-900',
          { 'h-56': !fieldsHeight },
        ]"
        resizeable
        :resource-name="resourceName"
        :fields="fields"
        :form="recordForm"
        :view="fieldsViewName"
      >
        <template #after="{ fields }">
          <div
            class="relative flex space-x-2 rounded-b-lg bg-neutral-50 px-6 py-3 shadow dark:bg-neutral-700"
          >
            <i-button
              v-if="$gate.isSuperAdmin()"
              size="sm"
              v-i-tooltip="$t('fields.manage')"
              variant="white"
              :to="{
                name: 'resource-fields',
                params: { resourceName: resourceName },
                query: { view: fieldsViewName },
              }"
              icon="Cog"
            />
            <fields-collapse-button :fields="fields" />
            <div class="flex items-center">
              <div class="hidden md:block">
                <i-button
                  type="submit"
                  size="sm"
                  v-show="fieldsAreDirty"
                  :loading="recordForm.busy"
                  :disabled="recordForm.busy || $gate.denies('update', record)"
                >
                  {{ $t('app.save') }}
                </i-button>
              </div>
              <i-action-message
                :message="$t('app.saved')"
                class="ml-2 hidden md:block"
                v-show="recordForm.recentlySuccessful"
              />
            </div>
          </div>
        </template>
      </fields-generator>
    </i-overlay>
  </form>
</template>
<script>
import FieldsCollapseButton from '@/components/Fields/ButtonCollapse'
import HandlesResourceUpdate from '@/mixins/HandlesResourceUpdate'
import CardHeaderGridBackground from '@/components/Cards/HeaderGridBackground'
export default {
  inheritAttrs: false,
  mixins: [HandlesResourceUpdate],
  components: {
    FieldsCollapseButton,
    CardHeaderGridBackground,
  },
  data: () => ({
    fieldsViewName: Innoclapps.config.fields.views.detail,
    fieldsHeight: Innoclapps.config.fields.height.deal,
  }),
  props: {
    deal: { required: true, type: Object },
    resourceName: { type: String, default: 'deals' },
  },
  computed: {
    /**
     * The current record
     *
     * @return {Object}
     */
    record() {
      return this.deal
    },
  },
  methods: {
    /**
     * Boot the update for the record
     *
     * @return {Void}
     */
    bootRecordUpdate(config) {
      this.updateConfig = Object.assign({}, this.updateConfig, config)
      this.initRecord()
    },

    /**
     * Init record
     *
     * @return {Void}
     */
    initRecord() {
      this.getResourceUpdateFields().then(fields => {
        this.prepareComponent(fields, this.deal)
      })
    },
  },
  beforeMount() {
    this.bootRecordUpdate({
      resource: this.resourceName,
      id: this.deal.id,
    })
  },
}
</script>
