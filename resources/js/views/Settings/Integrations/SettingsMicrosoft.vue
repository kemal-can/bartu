<template>
  <form
    @submit.prevent="submitMicrosoft"
    @input="form.errors.clear($event.target.name)"
  >
    <i-card header="Microsoft" :overlay="!componentReady">
      <template #header>
        <div class="flex items-center">
          <icon
            :icon="
              maybeClientSecretIsExpired ? 'XCircleSolid' : 'CheckCircleSolid'
            "
            :class="[
              'mr-1 h-5 w-5',
              {
                'text-success-600':
                  !maybeClientSecretIsExpired && isConfigured && componentReady,
                'text-danger-500':
                  maybeClientSecretIsExpired && isConfigured && componentReady,
              },
            ]"
            v-if="isConfigured && componentReady"
          />
          <i-card-heading>Microsoft</i-card-heading>
        </div>
      </template>
      <template #actions>
        <a
          href="https://portal.azure.com/#blade/Microsoft_AAD_RegisteredApps/ApplicationsListBlade"
          class="link inline-flex items-center text-sm"
          target="_blank"
          rel="noopener noreferrer"
        >
          App Registrations <icon icon="ExternalLink" class="ml-1 h-4 w-4" />
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
      <!-- <div
        class="mb-6 flex justify-between rounded-md border border-neutral-200 bg-neutral-50 px-3 py-2"
      >
        <div class="text-sm">
          <span class="mr-2 font-medium text-neutral-700">Logout Url:</span>
          <span class="select-all text-neutral-600" v-text="logoutUrl"></span>
        </div>
        <copy-button class="ml-3" :text="logoutUrl" />
      </div> -->
      <div class="sm:flex sm:space-x-4">
        <i-form-group
          class="w-full"
          label="App (client) ID"
          label-for="msgraph_client_id"
        >
          <i-form-input
            autocomplete="off"
            v-model="form.msgraph_client_id"
            id="msgraph_client_id"
            name="msgraph_client_id"
          />
        </i-form-group>
        <i-form-group
          class="w-full"
          label="Client Secret"
          label-for="msgraph_client_secret"
        >
          <i-form-input
            autocomplete="off"
            type="password"
            v-model="form.msgraph_client_secret"
            id="msgraph_client_secret"
            name="msgraph_client_secret"
          />
        </i-form-group>
      </div>

      <i-alert
        class="mt-4"
        v-if="
          originalSettings.msgraph_client_secret &&
          originalSettings.msgraph_client_secret_configured_at &&
          !maybeClientSecretIsExpired
        "
        variant="info"
      >
        The client secret was configured at
        {{
          localizedDate(originalSettings.msgraph_client_secret_configured_at)
        }}. If you followed the documentation and configured the client secret
        to expire in 24 months,
        <span class="font-bold">
          you must re-generate a new client secret at:
          {{ getClientSecretExpiresMoment().format('YYYY-MM-DD') }}
        </span>
        in order the integration to continue working.
        <div class="mt-4">
          <div class="-mx-2 -my-1.5 flex">
            <i-button-minimal
              variant="info"
              target="_blank"
              rel="noopener noreferrer"
              tag="a"
              :href="
                'https://portal.azure.com/#blade/Microsoft_AAD_RegisteredApps/ApplicationMenuBlade/Credentials/appId/' +
                form.msgraph_client_id +
                '/isMSAApp/true'
              "
            >
              Re-Generate
            </i-button-minimal>
          </div>
        </div>
      </i-alert>
      <i-alert class="mt-4" :show="maybeClientSecretIsExpired" variant="danger">
        The client secret is probably expired, click the button below to
        re-generate new secret if it's needed, don't forget to update the secret
        here in the integration as well.
        <div class="mt-4">
          <div class="-mx-2 -my-1.5 flex">
            <i-button-minimal
              variant="danger"
              tag="a"
              target="_blank"
              rel="noopener noreferrer"
              :href="
                'https://portal.azure.com/#blade/Microsoft_AAD_RegisteredApps/ApplicationMenuBlade/Credentials/appId/' +
                form.msgraph_client_id +
                '/isMSAApp/true'
              "
            >
              Re-Generate
            </i-button-minimal>
          </div>
        </div>
      </i-alert>
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
  data: () => ({
    redirectUri: Innoclapps.config.url + '/microsoft/callback',
    logoutUrl: Innoclapps.config.url + '/microsoft/logout',
  }),
  computed: {
    isConfigured() {
      return (
        this.originalSettings.msgraph_client_secret &&
        this.originalSettings.msgraph_client_id
      )
    },
    maybeClientSecretIsExpired() {
      if (
        !this.originalSettings.msgraph_client_secret ||
        !this.originalSettings.msgraph_client_secret_configured_at
      ) {
        return false
      }

      return this.getClientSecretExpiresMoment().isBefore(this.appMoment())
    },
  },
  methods: {
    /**
     * We can only fetch the secret expires date using the servicePrincipal endpoint
     * however, this endpoint required work account and as we cannot force all users
     * to configure work account, we will assume that they follow the docs and add the
     * token to expire in 24 months, based on the configuration date, we will track the expiration of the token
     * @see https://docs.microsoft.com/en-us/graph/api/serviceprincipal-list?view=graph-rest-1.0&tabs=http#permissions
     */
    getClientSecretExpiresMoment() {
      return (
        this.appMoment(
          this.originalSettings.msgraph_client_secret_configured_at
        )
          .add(24, 'months')
          // Subtract 1 day to avoid integration interruptions when the secret must be renewed at the same day
          .subtract(1, 'day')
      )
    },
    submitMicrosoft() {
      if (
        this.form.msgraph_client_secret &&
        this.originalSettings.msgraph_client_secret !=
          this.form.msgraph_client_secret
      ) {
        this.form.fill('msgraph_client_secret_configured_at', this.appDate())
      } else if (!this.form.msgraph_client_secret) {
        this.form.fill('msgraph_client_secret_configured_at', null)
      }
      this.$nextTick(() => this.submit(form => window.location.reload()))
    },
  },
}
</script>
