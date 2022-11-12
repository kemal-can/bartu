<template>
  <i-card :overlay="!componentReady">
    <template #header>
      <div class="flex items-center">
        <icon
          :icon="
            numbers.length === 0 ||
            selectedNumberHasNoVoiceCapabilities ||
            !isSecure
              ? 'XCircleSolid'
              : 'CheckCircleSolid'
          "
          :class="[
            'mr-1 h-5 w-5',
            numbers.length === 0 ||
            selectedNumberHasNoVoiceCapabilities ||
            !isSecure
              ? 'text-danger-500'
              : 'text-success-600',
          ]"
          v-if="
            isConfigured && componentReady && !numbersRetrievalRequestInProgress
          "
        />

        <i-card-heading>Twilio</i-card-heading>
        <i-steps-circle class="pointer-events-none ml-4">
          <i-step-circle :status="!showNumberConfig ? 'current' : 'complete'" />
          <i-step-circle :status="form.twilio_number ? 'complete' : ''" />
          <i-step-circle
            :status="isConfigured && form.twilio_number ? 'complete' : ''"
            is-last
          />
        </i-steps-circle>
      </div>
    </template>
    <template #actions>
      <i-button
        variant="danger"
        size="sm"
        @click="disconnect"
        v-show="isConfigured"
        >{{ $t('settings.integrations.twilio.disconnect') }}</i-button
      >
    </template>
    <div class="lg:flex lg:space-y-4">
      <div class="w-full">
        <i-alert class="mb-10" v-show="showAppUrlWarning" variant="warning">
          Your Twilio application URL does match your installation URL.
          <div class="mt-4">
            <div class="-mx-2 -my-1.5 flex">
              <button
                type="button"
                @click="updateTwiMLAppURL"
                class="rounded-md bg-warning-50 px-2 py-1.5 text-sm font-medium text-warning-800 hover:bg-warning-100 focus:outline-none focus:ring-2 focus:ring-warning-600 focus:ring-offset-2 focus:ring-offset-warning-50"
              >
                Update URL
              </button>
            </div>
          </div>
        </i-alert>

        <i-alert class="mb-10" :show="!isSecure" variant="warning">
          Application must be served over HTTPS URL in order to use the Twilio
          integration.
        </i-alert>

        <div class="grid grid-cols-12 gap-2 lg:gap-4">
          <div class="col-span-12 lg:col-span-6">
            <i-form-group>
              <template #label>
                <div class="flex">
                  <div class="grow">
                    <i-form-label for="twilio_account_sid">
                      Account SID
                    </i-form-label>
                  </div>
                  <div>
                    <small>
                      <a
                        href="https://www.twilio.com/console"
                        class="link mb-1 inline-flex items-center"
                        target="_blank"
                        rel="noopener noreferrer"
                      >
                        https://www.twilio.com/console
                        <icon icon="ExternalLink" class="ml-1 h-4 w-4" />
                      </a>
                    </small>
                  </div>
                </div>
              </template>
              <i-form-input
                v-model="form.twilio_account_sid"
                id="twilio_account_sid"
                autocomplete="off"
              />
            </i-form-group>
          </div>
          <div class="col-span-12 lg:col-span-6">
            <i-form-group>
              <template #label>
                <div class="flex">
                  <div class="grow">
                    <i-form-label for="twilio_auth_token"
                      >Auth Token</i-form-label
                    >
                  </div>
                  <div>
                    <small>
                      <a
                        href="https://www.twilio.com/console"
                        class="link mb-1 inline-flex items-center"
                        target="_blank"
                        rel="noopener noreferrer"
                      >
                        https://www.twilio.com/console
                        <icon icon="ExternalLink" class="ml-1 h-4 w-4" />
                      </a>
                    </small>
                  </div>
                </div>
              </template>
              <i-form-input
                type="password"
                v-model="form.twilio_auth_token"
                id="twilio_auth_token"
                autocomplete="off"
              />
            </i-form-group>
          </div>
        </div>

        <div
          class="mt-2 border-t border-neutral-200 pt-5 dark:border-neutral-600"
          :class="{
            'pointer-events-none opacity-50 blur-sm': !showNumberConfig,
          }"
        >
          <i-form-label :label="$t('settings.integrations.twilio.number')" />

          <i-alert
            :show="selectedNumberHasNoVoiceCapabilities"
            class="my-3"
            variant="danger"
          >
            This phone number does not have enabled voice capabilities.
          </i-alert>
          <div class="mt-1 flex rounded-md shadow-sm">
            <div class="relative flex grow items-stretch focus-within:z-10">
              <div
                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
              >
                <icon icon="Phone" class="h-5 w-5 text-neutral-400" />
              </div>
              <i-form-select
                :rounded="false"
                :disabled="!numbers.length"
                class="rounded-l-md pl-10"
                v-model="form.twilio_number"
              >
                <option value=""></option>
                <option
                  :value="number.phoneNumber"
                  v-for="number in numbers"
                  :key="number.phoneNumber"
                >
                  {{ number.phoneNumber }}
                </option>
              </i-form-select>
            </div>
            <i-button
              class="relative -ml-px rounded-r-md"
              variant="white"
              :rounded="false"
              :loading="numbersRetrievalRequestInProgress"
              :disabled="numbersRetrievalRequestInProgress"
              @click="retrieveNumbers"
            >
              Retrieve Numbers
            </i-button>
          </div>
        </div>

        <div
          class="mt-5 border-t border-neutral-200 pt-5 dark:border-neutral-600"
          :class="{
            'pointer-events-none opacity-50 blur-sm': !showAppConfig,
          }"
        >
          <i-form-label :label="$t('settings.integrations.twilio.app')" />
          <div class="mt-1 flex rounded-md shadow-sm">
            <div class="relative flex grow items-stretch focus-within:z-10">
              <i-form-input
                v-model="form.twilio_app_sid"
                :disabled="true"
                :rounded="false"
                class="rounded-l-md"
              />
            </div>
            <i-button
              :rounded="false"
              :class="['relative', { 'rounded-r-md': !hasAppSid }]"
              :disabled="hasAppSid || selectedNumberHasNoVoiceCapabilities"
              @click="createTwiMLApp"
            >
              {{ $t('settings.integrations.twilio.create_app') }}
            </i-button>
            <i-button
              variant="danger"
              :rounded="false"
              class="relative rounded-r-md"
              icon="Trash"
              v-if="hasAppSid"
              @click="deleteTwiMLApp"
            />
          </div>
        </div>
      </div>
    </div>

    <template #footer v-if="isConfigured">
      <i-button
        class="mb-2"
        @click="save"
        :disabled="selectedNumberHasNoVoiceCapabilities"
        >{{ $t('app.save') }}</i-button
      >
    </template>
  </i-card>
</template>
<script>
import Form from '@/components/Form/Form'
import { isValueEmpty } from '@/utils'
import find from 'lodash/find'

export default {
  data: () => ({
    numbers: [],
    componentReady: false,
    numbersRetrievalRequestInProgress: false,
    showAppUrlWarning: false,
    form: {},
    isSecure: Innoclapps.config.is_secure,
  }),
  computed: {
    hasAuthToken() {
      return !isValueEmpty(this.form.twilio_auth_token)
    },
    hasAccountSid() {
      return !isValueEmpty(this.form.twilio_account_sid)
    },
    hasAppSid() {
      return !isValueEmpty(this.form.twilio_app_sid)
    },
    showNumberConfig() {
      return this.hasAuthToken && this.hasAccountSid
    },
    showAppConfig() {
      return !isValueEmpty(this.form.twilio_number)
    },
    isConfigured() {
      return this.hasAuthToken && this.hasAccountSid && this.hasAppSid
    },
    selectedNumber() {
      return find(this.numbers, ['phoneNumber', this.form.twilio_number])
    },
    selectedNumberHasNoVoiceCapabilities() {
      if (!this.selectedNumber) {
        return false
      }
      return this.selectedNumber.capabilities.voice === false
    },
  },
  methods: {
    /**
     * Submit the form
     *
     * @return {Void}
     */
    save() {
      this.form.post('settings').then(() => {
        Innoclapps.success(this.$t('settings.updated'))
        window.location.reload()
      })
    },

    /**
     * Disconnect the integartion
     *
     * @return {Void}
     */
    disconnect() {
      Innoclapps.request()
        .delete('/twilio')
        .then(() => {
          window.location.reload()
        })
    },

    /**
     * Update the associated TwiML app URL to match the installation URL
     *
     * @return {Void}
     */
    updateTwiMLAppURL() {
      Innoclapps.request()
        .put(`/twilio/app/${this.form.twilio_app_sid}`, {
          voiceUrl: Innoclapps.config.voip.endpoints.call,
        })
        .then(() => {
          window.location.reload()
        })
    },

    /**
     * Retrieve the user available incoming Twilio number
     *
     * @return {Void}
     */
    retrieveNumbers() {
      this.numbersRetrievalRequestInProgress = true
      Innoclapps.request()
        .get('/twilio/numbers', {
          params: {
            account_sid: this.form.twilio_account_sid,
            auth_token: this.form.twilio_auth_token,
          },
        })
        .then(({ data }) => {
          this.numbers = data
        })
        .finally(() => (this.numbersRetrievalRequestInProgress = false))
    },

    /**
     * Get the TwiML app associated with the integration
     *
     * @return {Object}
     */
    async getTwiMLApp() {
      let { data } = await Innoclapps.request().get(
        `/twilio/app/${this.form.twilio_app_sid}`
      )

      return data
    },

    /**
     * Create new TwiML application for the current number
     *
     * @return {Void}
     */
    createTwiMLApp() {
      Innoclapps.request()
        .post('/twilio/app', {
          number: this.form.twilio_number,
          account_sid: this.form.twilio_account_sid,
          auth_token: this.form.twilio_auth_token,
          voiceMethod: 'POST',
          voiceUrl: Innoclapps.config.voip.endpoints.call,
          friendlyName: 'bartu CRM',
        })
        .then(({ data }) => {
          this.form.twilio_app_sid = data.app_sid
        })
    },

    /**
     * Delete TwiML app associated with the integration
     *
     * @return {Void}
     */
    deleteTwiMLApp() {
      this.$dialog.confirm().then(() => {
        this.deleteTwiMLAppWithoutConfirmation()
      })
    },

    /**
     * Delete TwiML app associated with the integration without confirmation modal
     *
     * @return {Void}
     */
    deleteTwiMLAppWithoutConfirmation() {
      Innoclapps.request()
        .delete('/twilio/app/' + this.form.twilio_app_sid, {
          params: {
            account_sid: this.form.twilio_account_sid,
            auth_token: this.form.twilio_auth_token,
          },
        })
        .then(() => {
          this.form.twilio_app_sid = null
        })
    },

    /**
     * Prepare the component
     *
     * @return {Void}
     */
    prepareComponent() {
      Innoclapps.request()
        .get('/settings')
        .then(({ data }) => {
          this.form = new Form({
            twilio_account_sid: data.twilio_account_sid,
            twilio_auth_token: data.twilio_auth_token,
            twilio_app_sid: data.twilio_app_sid,
            twilio_number: data.twilio_number,
          })

          this.componentReady = true

          if (this.hasAuthToken && this.hasAccountSid) {
            this.retrieveNumbers()

            if (this.hasAppSid) {
              this.getTwiMLApp()
                .then(app => {
                  if (app.voiceUrl !== Innoclapps.config.voip.endpoints.call) {
                    this.showAppUrlWarning = true
                  }
                })
                .catch(error => {
                  // If we get 404 error when retrieving the app, this means that the app
                  // does not exists in Twilio, in this case, we will delete the app from
                  // the installation to forget the apps sid, see the TwilioAppController destroy method
                  if (error.response.data.message.indexOf('[HTTP 404]') > -1) {
                    this.deleteTwiMLAppWithoutConfirmation()
                  }
                })
            }
          }
        })
    },
  },
  created() {
    this.prepareComponent()
  },
}
</script>
