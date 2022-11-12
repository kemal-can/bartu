<template>
  <i-slideover
    :visible="true"
    @hidden="goBack"
    @shown="modalShown"
    :ok-disabled="form.busy"
    :ok-title="$t('app.create')"
    form
    @submit="store"
    @keydown="form.onKeydown($event)"
    :title="$t('role.create')"
    static-backdrop
  >
    <role-form-fields ref="form" :form="form" :create="true" />
  </i-slideover>
</template>
<script>
import RoleFormFields from '@/views/Roles/RoleFormFields'
import Form from '@/components/Form/Form'

export default {
  components: { RoleFormFields },
  data: () => ({
    form: new Form({
      name: null,
      permissions: [],
    }),
  }),
  methods: {
    /**
     * Handle modal shown event
     *
     * @return {Void}
     */
    modalShown() {
      this.$refs.form.$refs.name.focus()
    },

    /**
     * Create new role
     *
     * @return {Void}
     */
    store() {
      this.$store.dispatch('roles/store', this.form).then(role => {
        Innoclapps.success(this.$t('role.created'))
        this.goBack()
      })
    },
  },
}
</script>
