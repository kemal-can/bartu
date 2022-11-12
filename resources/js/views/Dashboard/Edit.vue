<template>
  <i-layout class="dashboard-edit">
    <template #actions>
      <navbar-separator class="hidden lg:block" v-show="componentReady" />
      <i-button
        v-show="componentReady"
        type="button"
        @click="update"
        size="sm"
        :disabled="form.busy"
        >{{ $t('app.save') }}</i-button
      >
    </template>

    <div class="mx-auto max-w-7xl lg:max-w-6xl">
      <form @submit.prevent="update" @keydown="form.onKeydown($event)">
        <i-card class="mb-4" :overlay="!componentReady">
          <i-form-group label-for="name" :label="$t('dashboard.name')" required>
            <i-form-input id="name" v-model="form.name" />
            <form-error :form="form" field="name" />
          </i-form-group>
          <i-form-group v-if="canChangeDefaultState">
            <i-form-checkbox
              id="is_default"
              name="is_default"
              v-model:checked="form.is_default"
              :label="$t('dashboard.default')"
            />
            <form-error :form="form" field="is_default" />
          </i-form-group>
        </i-card>
        <div
          class="mb-6"
          v-for="card in cardsWithConfigApplied"
          :key="card.uriKey"
        >
          <i-form-checkbox
            :id="'status-' + card.uriKey"
            class="mb-2"
            v-model:checked="status[card.uriKey]"
            :label="$t('dashboard.cards.enabled')"
          />
          <p v-if="card.description" class="my-2" v-text="card.description"></p>
          <div class="pointer-events-none block h-full w-full opacity-70">
            <component :is="card.component" :card="card" />
          </div>
        </div>
      </form>
    </div>
  </i-layout>
</template>
<script>
import find from 'lodash/find'
import Form from '@/components/Form/Form'
import { useCards } from '@/components/Cards/Composables/useCards'

export default {
  setup() {
    const { applyUserConfig } = useCards()

    return { applyUserConfig }
  },
  data: () => ({
    form: new Form({
      cards: [],
      name: null,
      is_default: false,
    }),
    status: {},
    allCardsForDashboard: [],
    componentReady: false,
  }),
  computed: {
    /**
     * The cards intended for this dashboard with config applied
     *
     * @return {Array}
     */
    cardsWithConfigApplied() {
      return this.applyUserConfig(this.allCardsForDashboard, this.dashboard)
    },

    /**
     * Indicates whether the dashboad is_default field can be edited
     *
     * @return {Boolean}
     */
    canChangeDefaultState() {
      // Allow to set as default on the last dashboard which is not default
      if (this.totalDashboards === 1) {
        return true
      }

      return this.totalDashboards > 1 && this.dashboard.is_default === false
    },

    /**
     * The dashboard that is currently edited
     *
     * @return {Object}
     */
    dashboard() {
      return find(this.dashboards, ['id', Number(this.$route.params.id)]) || {}
    },

    /**
     * Available user dashboard
     *
     * @return {Array}
     */
    dashboards() {
      return this.currentUser.dashboards
    },

    /**
     * Total number of user dashboard
     *
     * @return {Number}
     */
    totalDashboards() {
      return this.dashboards.length
    },
  },
  methods: {
    /**
     * Update dashboard
     *
     * @return {Void}
     */
    update() {
      // Map the status values in the form
      this.form.set(
        'cards',
        this.cardsWithConfigApplied.map(card => ({
          key: card.uriKey,
          order: card.order,
          enabled: this.status[card.uriKey],
        }))
      )

      this.form.put('/dashboards/' + this.dashboard.id).then(dashboard => {
        Innoclapps.success(this.$t('dashboard.updated'))
        this.$store.commit('users/UPDATE_DASHBOARD', dashboard)

        this.$router.push({
          name: 'dashboard',
        })
      })
    },

    /**
     * Prepare dashboard for edit
     *
     * @return {Void}
     */
    async prepareComponent() {
      let { data: cards } = await Innoclapps.request().get('/cards')

      this.allCardsForDashboard = cards

      await this.$nextTick()

      // Set the status
      this.cardsWithConfigApplied.forEach(card => {
        this.status[card.uriKey] = card.enabled
      })

      this.setPageTitle(this.dashboard.name)

      this.form.set('name', this.dashboard.name)
      this.form.set('is_default', this.dashboard.is_default)

      this.componentReady = true
    },
  },
  created() {
    this.prepareComponent()
  },
}
</script>
<style>
.dashboard-edit .hide-when-editing {
  display: none !important;
}
</style>
