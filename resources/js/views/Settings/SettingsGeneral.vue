<template>
  <form @submit.prevent="saveGeneralSettings" @keydown="form.onKeydown($event)">
    <!-- General settings -->
    <i-card
      :header="$t('settings.general')"
      class="mb-3"
      :overlay="!componentReady"
    >
      <p
        class="mb-3 text-sm font-medium text-neutral-700 dark:text-neutral-200"
      >
        Dark logo
      </p>
      <crops-and-uploads-image
        name="logo_dark"
        :upload-url="$store.state.apiURL + '/logo/dark'"
        :image="currentDarkLogo"
        :show-delete="Boolean(form.logo_dark)"
        :cropper-options="{ aspectRatio: null }"
        :choose-text="
          !currentDarkLogo ? $t('settings.choose_logo') : $t('app.change')
        "
        @cleared="deleteLogo('dark')"
        @success="refreshPage"
      >
        <template #image="{ src }">
          <img :src="src" class="h-8 w-auto" />
        </template>
      </crops-and-uploads-image>

      <hr
        class="-mx-7 my-4 border-t border-neutral-200 dark:border-neutral-700"
      />
      <p
        class="mb-3 text-sm font-medium text-neutral-700 dark:text-neutral-200"
      >
        Light logo
      </p>
      <crops-and-uploads-image
        name="logo_light"
        :show-delete="Boolean(form.logo_light)"
        :upload-url="$store.state.apiURL + '/logo/light'"
        :image="currentLightLogo"
        :cropper-options="{ aspectRatio: null }"
        :choose-text="
          !currentLightLogo ? $t('settings.choose_logo') : $t('app.change')
        "
        @cleared="deleteLogo('light')"
        @success="refreshPage"
      >
        <template #image="{ src }">
          <img :src="src" class="h-8 w-auto" />
        </template>
      </crops-and-uploads-image>

      <hr
        class="-mx-7 my-4 border-t border-neutral-200 dark:border-neutral-700"
      />

      <i-form-group
        :label="$t('app.currency')"
        label-for="currency"
        class="w-auto xl:w-1/3"
      >
        <i-custom-select
          input-id="currency"
          v-model="form.currency"
          :clearable="false"
          :options="currencies"
        >
        </i-custom-select>
        <form-error :form="form" field="currency" />
      </i-form-group>
      <i-form-group
        :label="$t('settings.system_email')"
        label-for="system_email_account_id"
      >
        <div class="w-auto xl:w-1/3">
          <i-custom-select
            input-id="system_email_account_id"
            :placeholder="
              !systemEmailAccountIsVisibleToCurrentUser &&
              systemEmailAccountIsConfiguredFromOtherUser
                ? $t('settings.system_email_configured')
                : ''
            "
            :model-value="systemEmailAccount"
            :disabled="
              !systemEmailAccountIsVisibleToCurrentUser &&
              systemEmailAccountIsConfiguredFromOtherUser
            "
            :options="emailAccounts"
            label="email"
            @option:selected="form.system_email_account_id = $event.id"
            @cleared="form.system_email_account_id = null"
          />
        </div>
        <i-form-text v-text="$t('settings.system_email_info')" />
        <form-error :form="form" field="system_email_account_id" />
      </i-form-group>

      <i-form-group
        :label="$t('app.allowed_extensions')"
        :description="$t('app.allowed_extensions_info')"
      >
        <i-form-textarea
          rows="2"
          v-model="form.allowed_extensions"
          id="allowed_extensions"
        ></i-form-textarea>
        <form-error :form="form" field="allowed_extensions" />
      </i-form-group>

      <hr
        class="-mx-7 my-4 border-t border-neutral-200 dark:border-neutral-700"
      />

      <ul class="divide-y divide-neutral-200 dark:divide-neutral-700">
        <li class="py-4">
          <div
            class="space-y-3 space-x-0 md:flex md:items-center md:justify-between md:space-y-0 lg:space-x-3"
          >
            <div>
              <h5
                class="font-medium leading-relaxed text-neutral-700 dark:text-neutral-100"
                v-t="'settings.phones.require_calling_prefix'"
              />
              <p
                class="break-words text-sm text-neutral-600 dark:text-neutral-300"
                v-t="'settings.phones.require_calling_prefix_info'"
              />
            </div>
            <div>
              <i-form-toggle
                :value="true"
                :unchecked-value="false"
                v-model="form.require_calling_prefix_on_phones"
              />
            </div>
          </div>
        </li>
      </ul>

      <hr
        class="-mx-7 my-4 border-t border-neutral-200 dark:border-neutral-700"
      />

      <div class="my-4 block">
        <i-alert class="mb-5">
          {{ $t('settings.update_user_account_info') }}
        </i-alert>
        <localization-fields
          class="w-auto xl:w-1/3"
          :exclude="['timezone', 'locale']"
          :form="form"
        />
      </div>
      <template #footer>
        <i-button type="submit" :disabled="form.busy">{{
          $t('app.save')
        }}</i-button>
      </template>
    </i-card>

    <!-- Company information -->
    <i-card
      :header="$t('settings.company_information')"
      class="mb-3"
      :overlay="!componentReady"
    >
      <i-form-group
        class="w-auto xl:w-1/3"
        :label="$t('app.company.name')"
        label-for="company_name"
      >
        <i-form-input
          v-model="form.company_name"
          id="company_name"
        ></i-form-input>
      </i-form-group>

      <i-form-group
        class="w-auto xl:w-1/3"
        :label="$t('app.company.country')"
        label-for="company_country_id"
      >
        <i-custom-select
          v-model="country"
          :options="countries"
          label="name"
          @option:selected="form.company_country_id = $event.id"
          @cleared="form.company_country_id = null"
          input-id="company_country_id"
        ></i-custom-select>
      </i-form-group>

      <template #footer>
        <i-button type="submit" :disabled="form.busy">
          {{ $t('app.save') }}
        </i-button>
      </template>
    </i-card>
    <i-card :header="$t('app.privacy_policy')" :overlay="!componentReady">
      <editor v-model="form.privacy_policy" />
      <i-form-text
        tabindex="-1"
        v-t="{
          path: 'settings.privacy_policy_info',
          args: { url: privacyPolicyUrl },
        }"
      />
      <template #footer>
        <i-button type="submit" :disabled="form.busy">{{
          $t('app.save')
        }}</i-button>
      </template>
    </i-card>
  </form>
</template>
<script>
import CropsAndUploadsImage from '@/components/CropsAndUploadsImage'
import LocalizationFields from '@/views/Settings/LocalizationFields'
import HandleSettingsForm from './HandleSettingsForm'
import Editor from '@/components/Editor'
import find from 'lodash/find'
import map from 'lodash/map'
import { mapGetters } from 'vuex'
export default {
  mixins: [HandleSettingsForm],
  components: {
    Editor,
    CropsAndUploadsImage,
    LocalizationFields,
  },
  data: () => ({
    privacyPolicyUrl: Innoclapps.config.privacyPolicyUrl,
    currencies: [],
    countries: [],
    country: null,
  }),
  computed: {
    ...mapGetters({
      emailAccounts: 'emailAccounts/accounts',
    }),
    currentLightLogo() {
      return this.setting('logo_light')
    },
    currentDarkLogo() {
      return this.setting('logo_dark')
    },
    systemEmailAccount() {
      return find(this.emailAccounts, [
        'id',
        Number(this.form.system_email_account_id),
      ])
    },
    originalSystemEmailAccount() {
      return find(this.emailAccounts, [
        'id',
        Number(this.originalSettings.system_email_account_id),
      ])
    },
    systemEmailAccountIsVisibleToCurrentUser() {
      return (
        this.originalSettings.system_email_account_id &&
        this.originalSystemEmailAccount
      )
    },
    systemEmailAccountIsConfiguredFromOtherUser() {
      // If the account cannot be found in the accounts list, this means the account is not visible
      // to the current logged-in user
      return (
        this.originalSettings.system_email_account_id &&
        !this.originalSystemEmailAccount
      )
    },
  },

  methods: {
    /**
     * Save the general settings form
     *
     * @return {Void}
     */
    saveGeneralSettings() {
      this.submit(() => {
        if (
          this.form.require_calling_prefix_on_phones !==
          this.originalSettings.require_calling_prefix_on_phones
        ) {
          this.resetStoreState()
        }

        if (this.form.currency !== this.originalSettings.currency) {
          // Reload the page as the original currency is stored is in Innoclapps.config object
          window.location.reload()
        }
      })
    },

    /**
     * Refresh the page
     */
    refreshPage() {
      window.location.reload()
    },

    /**
     * Delete company logo from storage
     *
     * @return {Void}
     */
    deleteLogo(type) {
      const optionName = 'logo_' + type
      if (this.form[optionName]) {
        Innoclapps.request()
          .delete('/logo/' + type)
          .then(() => window.location.reload())
      }
    },

    /**
     * Fetch the available currencies that will be used in select
     *
     * @return {Void}
     */
    fetchAndSetCurrencies() {
      Innoclapps.request()
        .get('currencies')
        .then(({ data }) => {
          this.currencies = map(data, (val, code) => {
            return code
          })
        })
    },

    /**
     * Fetch the available countries that will be used in select
     *
     * @return {Void}
     */
    fetchAndSetCountries() {
      Innoclapps.request()
        .get('countries')
        .then(({ data }) => {
          this.countries = data

          if (this.form.company_country_id) {
            this.country = find(this.countries, [
              'id',
              Number(this.form.company_country_id),
            ])
          }
        })
    },
  },
  created() {
    this.$store.dispatch('emailAccounts/fetch')
    this.fetchAndSetCurrencies()
    this.fetchAndSetCountries()
  },
}
</script>
