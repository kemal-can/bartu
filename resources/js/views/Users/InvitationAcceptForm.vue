<template>
  <form @keydown="form.onKeydown($event)" @submit.prevent="accept">
    <i-tabs nav-wrapper-class="-mt-5">
      <i-tab :title="$t('profile.profile')">
        <i-form-group :label="$t('user.name')" label-for="name" required>
          <i-form-input v-model="form.name" id="name" ref="name" type="text" />
          <form-error :form="form" field="name" />
        </i-form-group>
        <i-form-group :label="$t('user.email')" label-for="email">
          <i-form-input
            v-model="form.email"
            id="email"
            name="email"
            disabled
            type="email"
          />
          <form-error :form="form" field="email" />
        </i-form-group>
      </i-tab>
      <i-tab :title="$t('auth.password')">
        <i-form-group
          :label="$t('auth.password')"
          label-for="password"
          required
        >
          <i-form-input
            v-model="form.password"
            id="password"
            name="password"
            type="password"
          />
          <form-error :form="form" field="password" />
        </i-form-group>
        <i-form-group
          :label="$t('auth.confirm_password')"
          label-for="password_confirmation"
          required
        >
          <i-form-input
            v-model="form.password_confirmation"
            id="password_confirmation"
            name="password_confirmation"
            type="password"
          />
          <form-error :form="form" field="password_confirmation" />
        </i-form-group>
      </i-tab>
      <i-tab :title="$t('user.localization')">
        <localization-fields :form="form" :exclude="['timezone']" />
      </i-tab>
      <i-button
        type="submit"
        :disabled="requestInProgress"
        :loading="requestInProgress"
      >
        {{ $t('user.accept_invitation') }}
      </i-button>
    </i-tabs>
  </form>
</template>
<script>
import LocalizationFields from '@/views/Settings/LocalizationFields'
import Form from '@/components/Form/Form'

export default {
  components: { LocalizationFields },
  props: {
    invitation: {
      type: Object,
      required: true,
    },
    dateFormat: String,
    timeFormat: String,
    firstDayOfWeek: String,
  },
  data() {
    return {
      requestInProgress: false,
      form: new Form({
        name: null,
        password: null,
        timezone: moment.tz.guess(),
        locale: 'en',
        date_format: this.dateFormat,
        time_format: this.timeFormat,
        first_day_of_week: this.firstDayOfWeek,
        password_confirmation: null,
        email: this.invitation.email,
      }),
    }
  },
  methods: {
    accept() {
      this.requestInProgress = true
      this.form
        .post(this.invitation.link)
        .then(() => (window.location.href = Innoclapps.config.url))
        .finally(() => (this.requestInProgress = false))
    },
  },
}
</script>
