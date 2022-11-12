<template>
  <form @submit.prevent="submit" class="space-y-6" method="POST">
    <i-alert variant="success" :show="successMessage !== null">
      {{ successMessage }}
      <p class="mt-1">
        <!-- We will redirect to login as the user is already logged in and will be redirected to the HOME route -->
        <a
          :href="installationUrl + '/login'"
          class="link mt-1 font-medium"
          v-text="$t('dashboard.dashboard')"
        />
      </p>
    </i-alert>

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

    <i-form-group :label="$t('auth.password')" label-for="password">
      <i-form-input
        type="password"
        v-model="form.password"
        id="password"
        name="password"
        required
        autocomplete="new-password"
      />
      <form-error field="password" :form="form" />
    </i-form-group>

    <i-form-group
      :label="$t('auth.confirm_password')"
      label-for="password-confirm"
    >
      <i-form-input
        type="password"
        v-model="form.password_confirmation"
        id="password-confirm"
        name="password_confirmation"
        required
        autocomplete="new-password"
      />
    </i-form-group>

    <div>
      <i-button
        type="submit"
        block
        @click="resetPassword"
        :disabled="requestInProgress"
        :loading="requestInProgress"
      >
        {{ $t('passwords.reset_password') }}
      </i-button>
    </div>
  </form>
</template>
<script>
import Form from '@/components/Form/Form'

export default {
  props: {
    email: String,
    token: { required: true, type: String },
  },
  data: () => ({
    form: new Form({
      token: null,
      email: null,
      password: null,
      password_confirmation: null,
    }),
    installationUrl: Innoclapps.config.url,
    requestInProgress: false,
    successMessage: null,
  }),
  methods: {
    /**
     * Reset the user password
     *
     * @return {Void}
     */
    async resetPassword() {
      this.requestInProgress = true

      await Innoclapps.request().get(
        this.installationUrl + '/sanctum/csrf-cookie'
      )

      this.form
        .post(this.installationUrl + '/password/reset')
        .then(data => (this.successMessage = data.message))
        .finally(() => (this.requestInProgress = false))
    },
  },
  created() {
    this.form.set('email', this.email)
    this.form.fill('token', this.token)
  },
}
</script>
