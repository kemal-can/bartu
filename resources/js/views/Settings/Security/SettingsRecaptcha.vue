<template>
  <form
    @submit.prevent="saveReCaptcha"
    @input="form.errors.clear($event.target.name)"
  >
    <i-card
      :header="$t('settings.recaptcha.recaptcha')"
      :overlay="!componentReady"
    >
      <template #actions>
        <a
          href="https://www.google.com/recaptcha/admin"
          class="link inline-flex items-center"
          target="_blank"
          rel="noopener noreferrer"
          >v2 <icon icon="ExternalLink" class="ml-1 h-4 w-4"
        /></a>
      </template>
      <div class="lg:flex lg:space-x-4">
        <i-form-group
          class="w-full"
          :label="$t('settings.recaptcha.site_key')"
          label-for="recaptcha_site_key"
        >
          <i-form-input
            v-model="form.recaptcha_site_key"
            id="recaptcha_site_key"
          />
        </i-form-group>
        <i-form-group
          class="w-full"
          :label="$t('settings.recaptcha.secret_key')"
          label-for="recaptcha_secret_key"
        >
          <i-form-input
            v-model="form.recaptcha_secret_key"
            id="recaptcha_secret_key"
          />
        </i-form-group>
      </div>

      <i-form-group
        :description="$t('settings.recaptcha.ignored_ips_info')"
        :label="$t('settings.recaptcha.ignored_ips')"
        label-for="recaptcha_ignored_ips"
      >
        <i-form-textarea
          v-model="form.recaptcha_ignored_ips"
          id="recaptcha_ignored_ips"
        />
      </i-form-group>

      <template #footer>
        <i-button type="submit" :disabled="form.busy">
          {{ $t('app.save') }}
        </i-button>
      </template>
    </i-card>
  </form>
</template>
<script>
import HandleSettingsForm from '@/views/Settings/HandleSettingsForm'
export default {
  mixins: [HandleSettingsForm],
  methods: {
    /**
     * Save the reCaptcha settings
     *
     * @return {Void}
     */
    saveReCaptcha() {
      this.submit(form => {
        // Update the configuration for components that are using Innoclapps.config.reCaptcha.configured
        // e.q. on web forms spam protection option
        Innoclapps.config.reCaptcha.configured =
          form.recaptcha_secret_key != '' && form.recaptcha_site_key != ''
      })
    },
  },
}
</script>
