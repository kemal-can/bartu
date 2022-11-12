<template>
  <form @submit.prevent="submit" class="space-y-6" method="POST">
    <i-alert variant="success" :show="successMessage !== null">{{
      successMessage
    }}</i-alert>
    <i-form-group :label="$t('auth.email_address')" label-for="email">
      <i-form-input
        type="email"
        id="email"
        name="email"
        v-model="form.email"
        autocomplete="email"
        autofocus
        required
      />
      <form-error field="email" :form="form" />
    </i-form-group>

    <i-form-group v-if="reCaptcha.validate">
      <vue-recaptcha
        :sitekey="reCaptcha.siteKey"
        @verify="reCaptchaVerified"
        ref="reCaptcha"
      />
      <form-error field="g-recaptcha-response" :form="form" />
    </i-form-group>

    <div>
      <i-button
        type="submit"
        block
        @click="sendPasswordResetEmail"
        :disabled="requestInProgress || !Boolean(form.email)"
        :loading="requestInProgress"
      >
        {{ $t('passwords.send_password_reset_link') }}
      </i-button>
    </div>
  </form>
</template>
<script>
import { VueRecaptcha } from 'vue-recaptcha'
import Form from '@/components/Form/Form'

export default {
  components: { VueRecaptcha },
  data: () => ({
    reCaptcha: Innoclapps.config.reCaptcha,
    form: new Form({
      email: null,
      'g-recaptcha-response': null,
    }),
    installationUrl: Innoclapps.config.url,
    requestInProgress: false,
    successMessage: null,
  }),
  methods: {
    /**
     * Send password reset email
     *
     * @return {Void}
     */
    async sendPasswordResetEmail() {
      this.requestInProgress = true
      this.successMessage = null

      await Innoclapps.request().get(
        this.installationUrl + '/sanctum/csrf-cookie'
      )

      this.form
        .post(this.installationUrl + '/password/email')
        .then(data => {
          this.successMessage = data.message
          this.form.reset()
        })
        .finally(() => {
          this.requestInProgress = false
          this.$refs.reCaptcha && this.$refs.reCaptcha.reset()
        })
    },

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
