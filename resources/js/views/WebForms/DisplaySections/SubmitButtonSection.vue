<template>
  <div>
    <i-form-group v-if="validateWithReCaptcha">
      <vue-recaptcha
        :sitekey="reCaptcha.siteKey"
        @verify="reCaptchaVerified"
        ref="reCaptcha"
      />
      <form-error field="g-recaptcha-response" :form="form" />
    </i-form-group>
    <div class="mb-3 flex" v-if="section.privacyPolicyAcceptIsRequired">
      <i-form-checkbox
        v-model:checked="privacyPolicyAccepted"
        id="acceptPrivacyPolicy"
        @change="form.fill('_privacy-policy', $event)"
      />
      <div>
        <i18n-t
          scope="global"
          :keypath="'app.agree_to_privacy_policy'"
          class="inline-block w-full"
        >
          <template #privacyPolicyLink>
            <a
              :href="section.privacyPolicyUrl"
              class="link"
              v-t="'app.privacy_policy'"
            ></a>
          </template>
        </i18n-t>
        <div>
          <form-error field="_privacy-policy" :form="form" />
        </div>
      </div>
    </div>
    <i-button
      type="submit"
      id="submitButton"
      size="lg"
      :disabled="form.busy"
      :loading="form.busy"
      block
      >{{ section.text }}</i-button
    >
  </div>
</template>
<script>
import Section from './Section'
import { VueRecaptcha } from 'vue-recaptcha'
export default {
  components: { VueRecaptcha },
  mixins: [Section],
  data: () => ({
    reCaptcha: Innoclapps.config.reCaptcha,
    privacyPolicyAccepted: false,
  }),
  computed: {
    /**
     * Indicates whether the form should be validated with reCaptcha
     *
     * @return {Boolean}
     */
    validateWithReCaptcha() {
      if (!this.section.spamProtected) {
        return false
      }

      return this.reCaptcha.validate && this.reCaptcha.configured
    },
  },
  methods: {
    /**
     * Handle reCaptcha verified event
     *
     * @param  {String} response
     *
     * @return {Void}
     */
    reCaptchaVerified(response) {
      this.form.fill('g-recaptcha-response', response)
    },
  },
}
</script>
<style>
#submitButton {
  color: var(--primary-contrast);
}
</style>
