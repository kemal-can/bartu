<template>
  <i-card
    class="group"
    :class="{
      'border border-primary-400': editing,
      'border border-transparent transition duration-75 hover:border-primary-400 dark:border dark:border-neutral-700':
        !editing,
    }"
  >
    <template #header>
      <p
        class="font-semibold text-neutral-800 dark:text-neutral-200"
        v-t="'form.sections.submit.button'"
      />
    </template>
    <template #actions>
      <i-button-icon
        icon="PencilAlt"
        class="block md:hidden md:group-hover:block"
        icon-class="h-4 w-4"
        v-show="!editing"
        @click="setEditingMode"
      />
    </template>
    <div
      v-show="!editing"
      class="text-sm text-neutral-900 dark:text-neutral-300"
    >
      <p v-text="section.text"></p>
    </div>
    <div v-if="editing">
      <i-form-group
        :label="$t('form.sections.submit.button_text')"
        label-for="text"
      >
        <i-form-input id="text" v-model="text" />
      </i-form-group>
      <i-form-group v-show="reCaptchaConfigured">
        <i-form-checkbox
          v-model:checked="spamProtected"
          name="spam_protected"
          id="spam_protected"
          :label="$t('form.sections.submit.spam_protected')"
        />
      </i-form-group>
      <i-form-group>
        <i-form-checkbox
          v-model:checked="privacyPolicyAcceptIsRequired"
          name="require_privacy_policy"
          id="require_privacy_policy"
          :label="$t('form.sections.submit.require_privacy_policy')"
        />
      </i-form-group>
      <i-form-group
        v-show="privacyPolicyAcceptIsRequired"
        :label="$t('form.sections.submit.privacy_policy_url')"
        label-for="privacy_policy_url"
      >
        <i-form-input v-model="privacyPolicyUrl" id="privacy_policy_url" />
      </i-form-group>
      <div class="space-x-2 text-right">
        <i-button size="sm" @click="editing = false" variant="white">
          {{ $t('app.cancel') }}
        </i-button>
        <i-button size="sm" @click="saveSection" variant="secondary">
          {{ $t('app.save') }}
        </i-button>
      </div>
    </div>
  </i-card>
</template>
<script>
import Section from './Section'
export default {
  mixins: [Section],
  data: () => ({
    text: null,
    spamProtected: false,
    privacyPolicyAcceptIsRequired: false,
    privacyPolicyUrl: Innoclapps.config.privacyPolicyUrl,
    reCaptchaConfigured: Innoclapps.config.reCaptcha.configured,
  }),
  methods: {
    /**
     * Save the section information
     *
     * @return {Void}
     */
    saveSection() {
      this.updateSection({
        text: this.text,
        spamProtected: this.spamProtected,
        privacyPolicyAcceptIsRequired: this.privacyPolicyAcceptIsRequired,
        privacyPolicyUrl: this.privacyPolicyUrl,
      })

      this.editing = false
    },

    /**
     * Invoke editing mode
     */
    setEditingMode() {
      this.text = this.section.text
      this.spamProtected = this.section.spamProtected
      this.privacyPolicyAcceptIsRequired =
        this.section.privacyPolicyAcceptIsRequired
      this.privacyPolicyUrl = this.section.privacyPolicyUrl
      this.editing = true
    },
  },
}
</script>
