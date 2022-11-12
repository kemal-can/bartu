<template>
  <i-card :header="$t('team.teams')" no-body :overlay="teamsAreBeingFetched">
    <template #actions>
      <i-button
        v-show="hasTeams"
        icon="Plus"
        @click="teamIsBeingCreated = true"
        size="sm"
        >{{ $t('team.add') }}</i-button
      >
    </template>
    <ul
      role="list"
      v-if="hasTeams"
      class="divide-y divide-neutral-200 dark:divide-neutral-700"
    >
      <li v-for="team in sortedTeams" :key="team.id">
        <a
          href="#"
          @click.prevent="
            teamContentIsVisible[team.id] = !teamContentIsVisible[team.id]
          "
          class="group block hover:bg-neutral-50 dark:hover:bg-neutral-800/60"
        >
          <div class="flex items-center px-4 py-4 sm:px-6">
            <div
              class="min-w-0 flex-1 sm:flex sm:items-center sm:justify-between"
            >
              <div class="truncate">
                <div class="flex items-center text-sm">
                  <p
                    class="truncate font-medium text-primary-600 dark:text-primary-100"
                  >
                    {{ team.name }}
                  </p>
                  <a
                    href="#"
                    class="link ml-2 text-sm md:hidden md:group-hover:block"
                    @click.prevent.stop="prepareEdit(team)"
                    v-t="'app.edit'"
                  />
                  <a
                    href="#"
                    class="ml-2 text-sm text-danger-500 hover:text-danger-600 md:hidden md:group-hover:block"
                    @click.prevent.stop="destroy(team.id)"
                    v-t="'app.delete'"
                  />
                </div>
                <div class="mt-2 flex">
                  <div
                    class="flex items-center text-sm text-neutral-500 dark:text-neutral-400"
                  >
                    <icon
                      icon="Calendar"
                      class="mr-1.5 h-5 w-5 flex-shrink-0 text-neutral-400 dark:text-neutral-300"
                    />
                    <p>
                      {{ $t('app.created_at') }}
                      {{ ' ' }}
                      <time :datetime="team.created_at">{{
                        localizedDateTime(team.created_at)
                      }}</time>
                    </p>
                  </div>
                </div>
              </div>
              <div class="mt-4 flex-shrink-0 sm:mt-0 sm:ml-5">
                <div class="flex -space-x-1 overflow-hidden">
                  <i-avatar
                    v-for="member in team.members"
                    v-i-tooltip="member.name"
                    :key="member.email"
                    :alt="member.name"
                    size="xs"
                    :src="member.avatar_url"
                    class="ring-2 ring-white dark:ring-neutral-900"
                  />
                </div>
              </div>
            </div>
            <div class="ml-5 flex-shrink-0">
              <icon icon="ChevronRight" class="h-5 w-5 text-neutral-400" />
            </div>
          </div>
        </a>
        <div v-show="teamContentIsVisible[team.id]" class="px-4 py-4 sm:px-6">
          <p
            class="mb-3 text-sm font-medium text-neutral-800 dark:text-neutral-200"
            v-t="'team.members'"
          />

          <div
            class="mb-1 flex items-center space-x-1.5 last:mb-0"
            v-for="member in team.members"
            :key="'info-' + member.email"
          >
            <i-avatar :alt="member.name" size="xs" :src="member.avatar_url" />
            <p
              class="text-sm font-medium text-neutral-700 dark:text-neutral-300"
              v-text="member.name"
            />
          </div>

          <p
            class="mb-3 mt-6 text-sm font-medium text-neutral-800 dark:text-neutral-200"
            v-t="'team.description'"
            v-show="team.description"
          />
          <p
            v-text="team.description"
            class="text-sm text-neutral-700 dark:text-neutral-300"
          />
        </div>
      </li>
    </ul>
    <i-card-body v-else-if="!teamsAreBeingFetched">
      <i-empty-state
        @click="teamIsBeingCreated = true"
        title="No teams"
        button-text="Add Team"
        description="Get started by creating a new team."
      />
    </i-card-body>
  </i-card>
  <i-modal
    :title="$t('team.create')"
    form
    @hidden="teamIsBeingCreated = false"
    @submit="createNewTeam"
    @shown="() => $refs.nameInputCreate.focus()"
    :visible="teamIsBeingCreated"
    :ok-title="$t('app.create')"
    :ok-disabled="formCreate.busy"
  >
    <i-form-group for="nameInputCreate" :label="$t('team.name')" required>
      <i-form-input
        id="nameInputCreate"
        ref="nameInputCreate"
        @keydown="formCreate.errors.clear('name')"
        v-model="formCreate.name"
      />
      <form-error :form="formCreate" field="name" />
    </i-form-group>
    <i-form-group for="membersInputCreate" :label="$t('team.members')">
      <i-custom-select
        :options="users"
        id="membersInputCreate"
        label="name"
        @update:modelValue="formCreate.errors.clear('members')"
        multiple
        v-model="formCreate.members"
        :reduce="option => option.id"
      />
    </i-form-group>

    <i-form-group for="descriptionInputCreate" :label="$t('team.description')">
      <i-form-textarea
        @keydown="formCreate.errors.clear('description')"
        id="descriptionInputCreate"
        v-model="formCreate.description"
      />
      <form-error :form="formCreate" field="description" />
    </i-form-group>
  </i-modal>
  <i-modal
    form
    @hidden=";(teamIsBeingEdited = null), formUpdate.reset()"
    @submit="updateTeam"
    :visible="teamIsBeingEdited !== null"
    :ok-title="$t('app.save')"
    :ok-disabled="formUpdate.busy"
    :title="$t('team.edit')"
  >
    <i-form-group for="nameInputEdit" :label="$t('team.name')" required>
      <i-form-input
        id="nameInputEdit"
        ref="nameInputEdit"
        @keydown="formUpdate.errors.clear('name')"
        v-model="formUpdate.name"
      />
      <form-error :form="formUpdate" field="name" />
    </i-form-group>
    <i-form-group for="membersInputEdit" :label="$t('team.members')">
      <i-custom-select
        :options="users"
        id="membersInputEdit"
        label="name"
        @update:modelValue="formUpdate.errors.clear('members')"
        multiple
        v-model="formUpdate.members"
        :reduce="option => option.id"
      />
    </i-form-group>

    <i-form-group for="descriptionInputEdit" :label="$t('team.description')">
      <i-form-textarea
        @keydown="formUpdate.errors.clear('description')"
        id="descriptionInputEdit"
        v-model="formUpdate.description"
      />
      <form-error :form="formUpdate" field="description" />
    </i-form-group>
  </i-modal>
</template>
<script>
import { mapState } from 'vuex'
import Form from '@/components/Form/Form'
import findIndex from 'lodash/findIndex'
import sortBy from 'lodash/sortBy'
export default {
  data: () => ({
    teams: [],
    teamsAreBeingFetched: false,
    teamIsBeingCreated: false,
    teamIsBeingEdited: null,
    teamContentIsVisible: {},
    formCreate: new Form({
      name: null,
      description: null,
      members: [],
    }),
    formUpdate: new Form({
      name: null,
      description: null,
      members: [],
    }),
  }),
  computed: {
    ...mapState({
      users: state => state.users.collection,
    }),
    sortedTeams() {
      return sortBy(this.teams, 'name')
    },
    hasTeams() {
      return this.teams.length > 0
    },
  },
  methods: {
    /**
     * Create new team
     */
    createNewTeam() {
      this.formCreate.post('/teams').then(team => {
        this.teams.push(team)
        this.teamIsBeingCreated = false
        this.formCreate.reset()
        this.$store.commit('table/RESET_SETTINGS')
      })
    },

    /**
     * Update the team being edited
     */
    updateTeam() {
      this.formUpdate.put(`/teams/${this.teamIsBeingEdited}`).then(team => {
        this.teams[findIndex(this.teams, ['id', team.id])] = team
        this.teamIsBeingEdited = null
        this.formUpdate.reset()
        this.$store.commit('table/RESET_SETTINGS')
      })
    },

    /**
     * Delete team from storage
     */
    async destroy(id) {
      await this.$dialog.confirm()
      Innoclapps.request()
        .delete(`/teams/${id}`)
        .then(() => {
          this.teams.splice(findIndex(this.teams, ['id', id]), 1)
          this.$store.commit('table/RESET_SETTINGS')
        })
    },

    /**
     * Prepare team for edit
     */
    prepareEdit(team) {
      this.teamIsBeingEdited = team.id
      this.formUpdate.fill('name', team.name)
      this.formUpdate.fill(
        'members',
        team.members.map(member => member.id)
      )
      this.formUpdate.fill('description', team.description)
    },

    /**
     * Prepare the components
     */
    prepareComponent() {
      this.teamsAreBeingFetched = true
      Innoclapps.request()
        .get('/teams')
        .then(({ data }) => (this.teams = data))
        .finally(() => (this.teamsAreBeingFetched = false))
    },
  },
  created() {
    this.prepareComponent()
  },
}
</script>
