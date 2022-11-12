<template>
  <form
    @submit.prevent="submitPusher"
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
          <i-card-heading>Pusher</i-card-heading>
        </div>
      </template>
      <template #actions>
        <a
          href="https://dashboard.pusher.com"
          class="link inline-flex items-center text-sm"
          target="_blank"
          rel="noopener noreferrer"
        >
          Pusher.com <icon icon="ExternalLink" class="ml-1 h-4 w-4" />
        </a>
      </template>

      <i-alert
        :show="!isConfigured && componentReady"
        variant="info"
        class="mb-6"
      >
        Receive notifications in real time without the need to manually refresh
        the page, after synchronization, automatically updates the calendar,
        total unread emails and new emails.
      </i-alert>

      <div class="sm:flex sm:space-x-4">
        <i-form-group class="w-full" label="App ID" label-for="pusher_app_id">
          <i-form-input
            v-model="form.pusher_app_id"
            id="pusher_app_id"
          ></i-form-input>
        </i-form-group>
        <i-form-group class="w-full" label="App Key" label-for="pusher_app_key">
          <i-form-input
            v-model="form.pusher_app_key"
            id="pusher_app_key"
          ></i-form-input>
        </i-form-group>
      </div>
      <div class="sm:flex sm:space-x-4">
        <i-form-group
          class="w-full"
          label="App Secret"
          label-for="pusher_app_secret"
        >
          <i-form-input
            type="password"
            v-model="form.pusher_app_secret"
            id="pusher_app_secret"
          ></i-form-input>
        </i-form-group>
        <i-form-group class="w-full">
          <template #label>
            <div class="flex">
              <div class="grow">
                <i-form-label for="pusher_app_cluster"
                  >App Cluster</i-form-label
                >
              </div>
              <div>
                <small>
                  <a
                    href="https://pusher.com/docs/clusters"
                    class="link mb-1 inline-flex items-center"
                    target="_blank"
                    rel="noopener noreferrer"
                  >
                    https://pusher.com/docs/clusters
                    <icon icon="ExternalLink" class="ml-1 h-4 w-4" />
                  </a>
                </small>
              </div>
            </div>
          </template>
          <i-form-input
            v-model="form.pusher_app_cluster"
            id="pusher_app_cluster"
          ></i-form-input>
        </i-form-group>
      </div>

      <template #footer>
        <i-button type="submit" :disabled="form.busy">{{
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
  computed: {
    isConfigured() {
      return (
        this.originalSettings.pusher_app_id &&
        this.originalSettings.pusher_app_key &&
        this.originalSettings.pusher_app_secret
      )
    },
  },
  methods: {
    submitPusher() {
      this.submit(form => window.location.reload())
    },
  },
}
</script>
