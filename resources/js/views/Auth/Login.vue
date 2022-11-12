<template>
  <form @submit.prevent="submit" class="space-y-6" method="POST">
    <i-form-group :label="$t('auth.login')" label-for="email">
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

    <i-form-group :label="$t('auth.password')" label-for="password">
      <i-form-input
        type="password"
        v-model="form.password"
        id="password"
        ref="password"
        name="password"
        required
        autocomplete="current-password"
      />
      <form-error field="password" :form="form" />
    </i-form-group>

    <i-form-group v-if="reCaptcha.validate">
      <vue-recaptcha
        :sitekey="reCaptcha.siteKey"
        @verify="reCaptchaVerified"
        ref="reCaptcha"
      />
      <form-error field="g-recaptcha-response" :form="form" />
    </i-form-group>

    <div class="flex items-center justify-between">
      <div class="flex items-center">
        <i-form-checkbox
          id="remember"
          name="remember"
          v-model="form.remember"
          :label="$t('auth.remember_me')"
        />
      </div>

      <div class="text-sm" v-if="!disablePasswordForgot">
        <a
          :href="installationUrl + '/password/reset'"
          class="link"
          v-t="'auth.forgot_password'"
        />
      </div>
    </div>

    <div>
      <i-button
        type="submit"
        block
        @click="login"
        :disabled="submitButtonIsDisabled"
        :loading="requestInProgress"
      >
        {{ $t('auth.login') }}
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
    disablePasswordForgot: Innoclapps.config.options.disable_password_forgot,
    form: new Form({
      email: null,
      password: null,
      remember: null,
      'g-recaptcha-response': null,
    }),
    installationUrl: Innoclapps.config.url,
    requestInProgress: false,
  }),
  computed: {
    /**
     * Indicates whether the submit button is disabled
     */
    submitButtonIsDisabled() {
      return this.requestInProgress
    },
  },
  methods: {
    /**
     * Login the user
     *
     * @return {Void}
     */
    async login() {
      this.requestInProgress = true
      this.$refs.password.blur()

      await Innoclapps.request().get(
        this.installationUrl + '/sanctum/csrf-cookie'
      )

      this.form
        .post(this.installationUrl + '/login')
        .then(data => (window.location.href = data.redirect_path))
        .finally(() => this.$refs.reCaptcha && this.$refs.reCaptcha.reset())
        .catch(() => (this.requestInProgress = false))
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
