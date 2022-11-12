<template>
  <i-modal
    size="sm"
    @hidden="goBack"
    @shown="() => $refs.email.focus()"
    :ok-disabled="form.busy"
    form
    @submit="invite"
    @keydown="form.onKeydown($event)"
    :ok-title="$t('user.send_invitation')"
    static-backdrop
    :visible="true"
    :title="$t('user.invite')"
  >
    <p class="text-neutral-700 dark:text-white">
      {{
        $t('user.invitation_expires_after_info', {
          total: invitationExpiresAfter,
        })
      }}
    </p>

    <div
      class="mb-4 border-b border-neutral-200 pt-4 dark:border-neutral-700"
    ></div>

    <i-form-group label-for="email" :label="$t('user.email')" required>
      <i-form-input
        v-model="form.email"
        id="email"
        name="email"
        ref="email"
        type="email"
      />
      <form-error :form="form" field="email" />
    </i-form-group>
    <i-form-group :label="$t('role.roles')" label-for="roles">
      <i-custom-select
        input-id="roles"
        :placeholder="$t('user.roles')"
        v-model="form.roles"
        :options="roles"
        label="name"
        :multiple="true"
      />
    </i-form-group>
    <i-form-group :label="$t('team.teams')" label-for="teams">
      <i-custom-select
        input-id="teams"
        :placeholder="$t('team.teams')"
        v-model="form.teams"
        :options="teams"
        label="name"
        :reduce="team => team.id"
        :multiple="true"
      />
    </i-form-group>
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
          class="text-neutral-700 dark:text-neutral-300"
          v-t="'user.as_super_admin_info'"
        />
      </div>
      <div class="ml-3">
        <i-form-toggle
          v-model="form.super_admin"
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
          class="text-neutral-700 dark:text-neutral-300"
          v-t="'user.allow_api_info'"
        />
      </div>
      <div class="ml-3">
        <i-form-toggle v-model="form.access_api" :disabled="form.super_admin" />
      </div>
    </div>
  </i-modal>
</template>
<script>
import Form from '@/components/Form/Form'
import { mapState } from 'vuex'
export default {
  data: () => ({
    invitationExpiresAfter: Innoclapps.config.invitation.expires_after,
    teams: [],
    form: new Form({
      email: null,
      access_api: true,
      super_admin: false,
      roles: [],
    }),
  }),
  computed: {
    ...mapState({
      roles: state => state.roles.collection,
    }),
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

    /**
     * Invite the user
     *
     * @return {Void}
     */
    invite() {
      this.form.roles = this.form.roles.map(role => role.name)

      this.form.post('/users/invite').then(() => {
        Innoclapps.success(this.$t('user.invited'))
        this.goBack()
      })
    },
  },
  created() {
    this.$store.dispatch('roles/fetch')
    Innoclapps.request()
      .get('/teams')
      .then(({ data }) => (this.teams = data))
  },
}
</script>
