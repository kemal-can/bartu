<template>
  <i-slideover
    @hidden="goBack"
    :visible="true"
    :ok-title="$t('app.save')"
    :ok-disabled="form.busy"
    form
    @submit="update"
    @keydown="form.onKeydown($event)"
    :title="title"
    static-backdrop
  >
    <form-fields-placeholder v-if="!componentReady" />
    <role-form-fields v-else :form="form" />
  </i-slideover>
</template>
<script>
import RoleFormFields from '@/views/Roles/RoleFormFields'
import Form from '@/components/Form/Form'

export default {
  components: { RoleFormFields },
  data: () => ({
    form: {},
    componentReady: false,
  }),
  computed: {
    /**
     * Get the modal title
     *
     * @return {String}
     */
    title() {
      return this.$t('role.edit') + ' ' + this.form.name
    },
  },
  methods: {
    /**
     * Update role
     *
     * @return {Void}
     */
    update() {
      this.$store
        .dispatch('roles/update', {
          form: this.form,
          id: this.$route.params.id,
        })
        .then(role => {
          Innoclapps.success(this.$t('role.updated'))
          this.goBack()
        })
    },

    /**
     * Prepare component for edit
     *
     * @param  {Object} role
     *
     * @return {Void}
     */
    prepareComponent(role) {
      this.form = new Form(role)
      this.form.permissions = role.permissions.map(
        permission => permission.name
      )
      this.$nextTick(() => (this.componentReady = true))
    },

    /**
     * Retrieve role from storage and set data
     *
     * @param  {Number} id
     *
     * @return {Void}
     */
    fetchAndPrepareComponent(id) {
      this.$store.dispatch('roles/get', id).then(role => {
        this.prepareComponent(role)
      })
    },
  },
  created() {
    let role = this.$store.getters['roles/getById'](this.$route.params.id)
    // The initial roles are not yet loaded
    // The role are not loaded e.q. on the AppComposer for security reasons
    // Now in this case, the user is able to view/edit roles and
    // we could load the roles
    // However, after the first load, they will stay loaded and the
    // statement below will pass
    if (!role) {
      this.fetchAndPrepareComponent(this.$route.params.id)
    } else {
      this.prepareComponent(role)
    }
  },
}
</script>
