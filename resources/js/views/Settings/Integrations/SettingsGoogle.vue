<template>
  <form
    @submit.prevent="submitGoogle"
    @input="form.errors.clear($event.target.name)"
  >
    <i-card :overlay="!componentReady">
      <template #header>
        <div class="flex items-center">
          <icon
            icon="CheckCircleSolid"
            class="mr-1 h-5 w-5 text-success-600"
            v-if="isConfigured && componentReady"
          />
          <i-card-heading>Google</i-card-heading>
        </div>
      </template>
      <template #actions>
        <a
          href="https://console.developers.google.com/"
          class="link inline-flex items-center text-sm"
          target="_blank"
          rel="noopener noreferrer"
        >
          Console <icon icon="ExternalLink" class="ml-1 h-4 w-4" />
        </a>
      </template>
      <div
        class="mb-6 flex justify-between rounded-md border border-neutral-200 bg-neutral-50 px-3 py-2"
      >
        <div class="text-sm">
          <span class="mr-2 font-medium text-neutral-700">Redirect Url:</span>
          <span class="select-all text-neutral-600" v-text="redirectUri"></span>
        </div>
        <copy-button class="ml-3" :text="redirectUri" />
      </div>
      <div class="sm:flex sm:space-x-4">
        <i-form-group
          label="Client ID"
          label-for="google_client_id"
          class="w-full"
        >
          <i-form-input
            v-model="form.google_client_id"
            autocomplete="off"
            id="google_client_id"
            name="google_client_id"
          />
        </i-form-group>
        <i-form-group
          label="Client Secret"
          label-for="google_client_secret"
          class="w-full"
        >
          <i-form-input
            type="password"
            autocomplete="off"
            v-model="form.google_client_secret"
            id="google_client_secret"
            name="google_client_secret"
          />
        </i-form-group>
      </div>

      <template #footer>
        <i-button type="submit" :disabled="form.busy" variant="primary">{{
          $t('app.save')
        }}</i-button>
      </template>
    </i-card>
  </form>
</template>
<script>
import HandleSettingsForm from '@/views/Settings/HandleSettingsForm'
export default {
  mixins: [HandleSettingsForm],
  data: () => ({
    redirectUri: Innoclapps.config.url + '/google/callback',
  }),
  computed: {
    isConfigured() {
      return (
        this.originalSettings.google_client_secret &&
        this.originalSettings.google_client_id
      )
    },
  },
  methods: {
    submitGoogle() {
      this.submit(form => window.location.reload())
    },
  },
}
</script>
