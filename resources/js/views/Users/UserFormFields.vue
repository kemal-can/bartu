<template>
  <i-tabs fill nav-class="mb-3">
    <i-tab :title="$t('user.user')">
      <i-form-group :label="$t('user.name')" label-for="name" required>
        <i-form-input
          v-model="form.name"
          id="name"
          ref="name"
          type="text"
          autocomplete="off"
        >
        </i-form-input>
        <form-error :form="form" field="name" />
      </i-form-group>
      <i-form-group :label="$t('user.email')" label-for="email" required>
        <i-form-input
          v-model="form.email"
          id="email"
          name="email"
          type="email"
          autocomplete="off"
        >
        </i-form-input>
        <form-error :form="form" field="email" />
      </i-form-group>
      <i-form-group :label="$t('role.roles')" label-for="roles">
        <i-custom-select
          input-id="roles"
          :placeholder="$t('user.roles')"
          v-model="form.roles"
          :options="rolesForSelect"
          :multiple="true"
        />
      </i-form-group>
    </i-tab>

    <i-tab :title="$t('auth.password')">
      <i-form-group
        :label="$t('auth.password')"
        label-for="password"
        :required="!isEdit"
      >
        <i-form-input
          v-model="form.password"
          id="password"
          name="password"
          type="password"
          autocomplete="new-password"
        >
        </i-form-input>
        <form-error :form="form" field="password" />
      </i-form-group>
      <i-form-group
        :label="$t('auth.confirm_password')"
        label-for="password_confirmation"
        :required="!isEdit || Boolean(form.password)"
      >
        <i-form-input
          v-model="form.password_confirmation"
          id="password_confirmation"
          name="password_confirmation"
          autocomplete="new-password"
          type="password"
        >
        </i-form-input>
        <form-error :form="form" field="password_confirmation" />
      </i-form-group>
      <password-generator />
    </i-tab>

    <i-tab :title="$t('user.localization')">
      <localization-fields :form="form" />
    </i-tab>

    <i-tab :title="$t('notifications.notifications')">
      <notification-settings
        v-if="form.notifications"
        class="overflow-hidden rounded-md border-x border-b border-neutral-200 dark:border-neutral-800"
        :form="form"
      />
    </i-tab>

    <i-tab :title="$t('app.advanced')">
      <div
        :class="[
          'flex items-center rounded-md border px-5 py-4 shadow-sm',
          form.super_admin
            ? 'border-primary-400'
            : 'border-neutral-200 dark:border-neutral-400',
        ]"
      >
        <div class="grow">
          <p class="text-neutral-900 dark:text-neutral-200">
            {{ $t('user.super_admin') }}
          </p>
          <small
            class="text-neutral-700 dark:text-neutral-400"
            v-t="'user.as_super_admin_info'"
          />
        </div>
        <div class="ms-3">
          <i-form-toggle
            v-model="form.super_admin"
            :disabled="currentUserIsSuperAdmin"
            @change="handleSuperAdminChange"
          />
        </div>
      </div>
      <div
        :class="[
          'mt-3 flex items-center rounded-md border px-5 py-4 shadow-sm',
          form.access_api
            ? 'border-primary-400'
            : 'border-neutral-200 dark:border-neutral-400',
        ]"
      >
        <div class="grow">
          <p class="text-neutral-900 dark:text-neutral-200">
            {{ $t('user.enable_api') }}
          </p>

          <small
            class="text-neutral-700 dark:text-neutral-400"
            v-t="'user.allow_api_info'"
          />
        </div>
        <div class="ms-3">
          <i-form-toggle
            v-model="form.access_api"
            :disabled="currentUserIsSuperAdmin || form.super_admin"
          />
        </div>
      </div>
    </i-tab>
  </i-tabs>
</template>
<script>
import LocalizationFields from '@/views/Settings/LocalizationFields'
import PasswordGenerator from '@/components/PasswordGenerator'
import NotificationSettings from '@/views/Users/NotificationSettings'
import { mapState } from 'vuex'
export default {
  components: {
    LocalizationFields,
    PasswordGenerator,
    NotificationSettings,
  },
  props: {
    isEdit: { type: Boolean, default: false },
    form: {
      required: true,
      type: Object,
      default: () => {},
    },
  },
  computed: {
    ...mapState({
      roles: state => state.roles.collection,
    }),

    /**
     * Get the role names for the select
     *
     * @return {Array}
     */
    rolesForSelect() {
      return this.roles.map(role => role.name)
    },

    /**
     * Check whether the current logged in user is super admin
     * Checks the actual id, as if the user can access this component,
     * means that is admin as this component is intended only for admins
     *
     * @return {Boolean}
     */
    currentUserIsSuperAdmin() {
      return this.currentUser.id === this.form.id
    },
  },
  methods: {
    /**
     * Handle the super admin toggle change
     *
     * @param  {Boolean} val
     *
     * @return {Void}
     */
    handleSuperAdminChange(val) {
      if (val) {
        this.form.access_api = true
      }
    },
  },
  created() {
    this.$store.dispatch('roles/fetch')
  },
}
</script>
