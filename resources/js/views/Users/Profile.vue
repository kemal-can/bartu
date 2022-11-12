<template>
  <i-layout>
    <div class="m-auto max-w-7xl">
      <div class="md:grid md:grid-cols-3 md:gap-6">
        <div class="md:col-span-1">
          <h3
            class="text-lg font-medium leading-6 text-neutral-900 dark:text-white"
            v-t="'app.avatar'"
          />
          <p
            class="mt-1 text-sm text-neutral-600 dark:text-neutral-300"
            v-t="'profile.avatar_info'"
          />
        </div>
        <div class="mt-5 md:col-span-2 md:mt-0">
          <i-card>
            <crops-and-uploads-image
              name="avatar"
              :upload-url="`${$store.state.apiURL}/users/${currentUser.id}/avatar`"
              :image="currentUser.uploaded_avatar_url"
              :cropper-options="{ aspectRatio: 1 / 1 }"
              :choose-text="
                currentUser.uploaded_avatar_url
                  ? $t('app.change')
                  : $t('app.upload_avatar')
              "
              @cleared="clearAvatar"
              @success="avatarUploaded"
            />
          </i-card>
        </div>
      </div>

      <div class="hidden sm:block" aria-hidden="true">
        <div class="py-5">
          <div class="border-t border-neutral-200 dark:border-neutral-600" />
        </div>
      </div>

      <div class="mt-10 sm:mt-0">
        <div class="md:grid md:grid-cols-3 md:gap-6">
          <div class="md:col-span-1">
            <h3
              class="text-lg font-medium leading-6 text-neutral-900 dark:text-white"
              v-t="'profile.profile'"
            />
            <p
              class="mt-1 text-sm text-neutral-600 dark:text-neutral-300"
              v-t="'profile.profile_info'"
            />
          </div>
          <div class="mt-5 md:col-span-2 md:mt-0">
            <form @submit.prevent="update" @keydown="form.onKeydown($event)">
              <i-card>
                <i-form-group :label="$t('user.name')" label-for="name">
                  <i-form-input v-model="form.name" id="name" name="name" />
                  <form-error :form="form" field="name" />
                </i-form-group>
                <i-form-group :label="$t('user.email')" label-for="email">
                  <i-form-input
                    v-model="form.email"
                    id="email"
                    name="email"
                    type="email"
                  >
                  </i-form-input>
                  <form-error :form="form" field="email" />
                </i-form-group>
                <i-form-group
                  :label="$t('mail.signature')"
                  label-for="mail_signature"
                  :description="$t('mail.signature_info')"
                >
                  <editor v-model="form.mail_signature" />
                  <form-error :form="form" field="mail_signature" />
                </i-form-group>
                <template #footer>
                  <div class="text-right">
                    <i-button @click="update" :disabled="form.busy">{{
                      $t('profile.update')
                    }}</i-button>
                  </div>
                </template>
              </i-card>
            </form>
          </div>
        </div>
      </div>

      <div class="hidden sm:block" aria-hidden="true">
        <div class="py-5">
          <div class="border-t border-neutral-200 dark:border-neutral-600" />
        </div>
      </div>

      <div class="mt-10 sm:mt-0">
        <div class="md:grid md:grid-cols-3 md:gap-6">
          <div class="md:col-span-1">
            <h3
              class="text-lg font-medium leading-6 text-neutral-900 dark:text-white"
              v-t="'user.localization'"
            />
            <p
              class="mt-1 text-sm text-neutral-600 dark:text-neutral-300"
              v-t="'profile.localization_info'"
            />
          </div>
          <div class="mt-5 md:col-span-2 md:mt-0">
            <i-card>
              <localization-fields :form="form" />
              <template #footer>
                <div class="text-right">
                  <i-button @click="update" :disabled="form.busy">{{
                    $t('app.save')
                  }}</i-button>
                </div>
              </template>
            </i-card>
          </div>
        </div>
      </div>

      <div class="hidden sm:block" aria-hidden="true">
        <div class="py-5">
          <div class="border-t border-neutral-200 dark:border-neutral-600" />
        </div>
      </div>

      <div class="mt-10 sm:mt-0">
        <div class="md:grid md:grid-cols-3 md:gap-6">
          <div class="md:col-span-1">
            <h3
              class="text-lg font-medium leading-6 text-neutral-900 dark:text-white"
              v-t="'notifications.notifications'"
            />
            <p
              class="mt-1 text-sm text-neutral-600 dark:text-neutral-300"
              v-t="'profile.notifications_info'"
            />
          </div>
          <div class="mt-5 md:col-span-2 md:mt-0">
            <i-card no-body>
              <notification-settings :form="form" class="-mt-px" />
              <template #footer>
                <div class="text-right">
                  <i-button
                    @click="update"
                    :disabled="form.busy"
                    v-t="'app.save'"
                  ></i-button>
                </div>
              </template>
            </i-card>
          </div>
        </div>
      </div>

      <div class="hidden sm:block" aria-hidden="true">
        <div class="py-5">
          <div class="border-t border-neutral-200 dark:border-neutral-600" />
        </div>
      </div>

      <div class="mt-10 sm:mt-0">
        <div class="md:grid md:grid-cols-3 md:gap-6">
          <div class="md:col-span-1">
            <h3
              class="text-lg font-medium leading-6 text-neutral-900 dark:text-white"
              v-t="'auth.password'"
            />
            <p
              class="mt-1 text-sm text-neutral-600 dark:text-neutral-300"
              v-t="'profile.password_info'"
            />
          </div>
          <div class="mt-5 md:col-span-2 md:mt-0">
            <form
              @submit.prevent="updatePassword"
              @keydown="formPassword.onKeydown($event)"
            >
              <i-card>
                <i-form-group
                  :label="$t('auth.current_password')"
                  label-for="old_password"
                >
                  <i-form-input
                    v-model="formPassword.old_password"
                    id="old_password"
                    name="old_password"
                    type="password"
                    autocomplete="current-password"
                  >
                  </i-form-input>
                  <form-error :form="formPassword" field="old_password" />
                </i-form-group>
                <i-form-group>
                  <template #label>
                    <div class="flex">
                      <i-form-label
                        class="mb-1 grow"
                        for="password"
                        :label="$t('auth.new_password')"
                      />

                      <a
                        class="link text-sm"
                        href="#"
                        v-i-toggle="'generate-password'"
                        v-t="'app.password_generator.heading'"
                      ></a>
                    </div>
                  </template>

                  <i-form-input
                    v-model="formPassword.password"
                    id="password"
                    name="password"
                    type="password"
                    autocomplete="new-password"
                  >
                  </i-form-input>
                  <form-error :form="formPassword" field="password" />
                </i-form-group>
                <i-form-group
                  :label="$t('auth.confirm_password')"
                  label-for="password_confirmation"
                >
                  <i-form-input
                    v-model="formPassword.password_confirmation"
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    autocomplete="new-password"
                  >
                  </i-form-input>
                  <form-error
                    :form="formPassword"
                    field="password_confirmation"
                  />
                </i-form-group>
                <div id="generate-password" style="display: none">
                  <password-generator />
                </div>
                <template #footer>
                  <div class="text-right">
                    <i-button type="submit" :disabled="formPassword.busy">{{
                      $t('auth.change_password')
                    }}</i-button>
                  </div>
                </template>
              </i-card>
            </form>
          </div>
        </div>
      </div>
    </div>
  </i-layout>
</template>
<script>
import CropsAndUploadsImage from '@/components/CropsAndUploadsImage'
import LocalizationFields from '@/views/Settings/LocalizationFields'
import PasswordGenerator from '@/components/PasswordGenerator'
import NotificationSettings from '@/views/Users/NotificationSettings'
import Editor from '@/components/Editor'
import Form from '@/components/Form/Form'
import reduce from 'lodash/reduce'

export default {
  name: 'user-profile',
  components: {
    CropsAndUploadsImage,
    LocalizationFields,
    PasswordGenerator,
    NotificationSettings,
    Editor,
  },
  data: () => ({
    form: {},
    formPassword: {},
    originalLocale: null,
  }),
  methods: {
    /**
     * Handle avatar uploaded
     *
     * @param  {Object} user
     *
     * @return {Void}
     */
    avatarUploaded(user) {
      this.$store.commit('users/UPDATE', {
        id: user.id,
        item: user,
      })

      // Update form avatar with new value
      // to prevent using the old value if the user saves the profile
      this.form.avatar = user.avatar
    },

    /**
     * Clear user avatar
     *
     * @return {Void}
     */
    clearAvatar() {
      if (!this.currentUser.avatar) {
        return
      }

      this.$store
        .dispatch('users/removeAvatar', this.currentUser.id)
        .then(data => (this.form.avatar = data.avatar))
    },

    /**
     * Update user profile
     *
     * @return {Void}
     */
    update() {
      this.$store.dispatch('users/updateProfile', this.form).then(() => {
        Innoclapps.success(this.$t('profile.updated'))

        if (this.originalLocale !== this.form.locale) {
          window.location.href = window.location.href + '?viaLocale=1'
        } else {
          this.resetStoreState()
        }
      })
    },

    /**
     * Update user password
     *
     * @return {Void}
     */
    updatePassword() {
      this.formPassword.put('/profile/password').then(() => {
        this.formPassword.reset()
        Innoclapps.success(this.$t('profile.password_updated'))
      })
    },

    /**
     * Prepare the component
     *
     * @return {Void}
     */
    prepareComponent() {
      // For some reason, when updating the locale, it's required
      // to refrsesh the page twice in order the new locale to be loaded
      if (this.$route.query.viaLocale === '1') {
        window.location.href = window.location.href.split('?')[0]
      }

      this.originalLocale = this.currentUser.locale

      this.form = new Form({
        name: this.currentUser.name,
        email: this.currentUser.email,
        mail_signature: this.currentUser.mail_signature,
        date_format: this.currentUser.date_format,
        time_format: this.currentUser.time_format,
        first_day_of_week: this.currentUser.first_day_of_week,
        timezone: this.currentUser.timezone,
        locale: this.currentUser.locale,
        notifications: reduce(
          this.cleanObject(this.currentUser.notifications.settings),
          (obj, val, key) => {
            obj[val.key] = val.availability
            return obj
          },
          {}
        ),
      })

      this.formPassword = new Form({
        old_password: null,
        password: null,
        password_confirmation: null,
      })
    },
  },
  created() {
    this.prepareComponent()
  },
}
</script>
