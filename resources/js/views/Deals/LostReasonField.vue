<template>
  <i-custom-select
    v-if="(!manualLostReason && lostReasons.length > 0) || !allowCustom"
    :options="lostReasons"
    :input-id="manualLostReason ? `${attribute}-hidden` : attribute"
    @update:modelValue="$emit('update:modelValue', $event ? $event.name : null)"
    label="name"
  />

  <div v-show="manualLostReason">
    <i-form-textarea
      :modelValue="modelValue"
      :id="!manualLostReason ? `${attribute}-hidden` : attribute"
      @update:modelValue="$emit('update:modelValue', $event)"
      rows="2"
    />
  </div>

  <i-form-text
    v-if="lostReasons.length > 0 && allowCustom"
    class="inline-flex items-center space-x-1"
  >
    <a
      href="#"
      tabindex="-1"
      @click.prevent="manualLostReason = !manualLostReason"
      v-t="
        `deal.lost_reasons.${
          manualLostReason
            ? 'choose_lost_reason'
            : 'choose_lost_reason_or_enter'
        }`
      " />
    <a
      href="#"
      class="link"
      @click.prevent="manualLostReason = !manualLostReason"
    >
      <svg
        xmlns="http://www.w3.org/2000/svg"
        class="h-5 w-5"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
        stroke-width="2"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          d="M13 7l5 5m0 0l-5 5m5-5H6"
        />
      </svg> </a
  ></i-form-text>
</template>
<script>
export default {
  emits: ['update:modelValue'],
  props: {
    modelValue: String,
    allowCustom: {
      type: Boolean,
      default() {
        return Innoclapps.config.options.allow_lost_reason_enter
      },
    },
    attribute: { default: 'lost_reason', type: String },
  },
  data: () => ({
    manualLostReason: false,
  }),
  computed: {
    lostReasons() {
      return this.$store.state.deals.lostReasons
    },
  },
  created() {
    if (this.lostReasons.length === 0 && this.allowCustom) {
      this.manualLostReason = true
    }
  },
  mounted() {
    this.$nextTick(() => {
      if (this.modelValue) {
        this.manualLostReason = true
      }
    })
  },
}
</script>
