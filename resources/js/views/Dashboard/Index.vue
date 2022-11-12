<template>
  <i-layout>
    <template #actions>
      <navbar-separator class="hidden lg:block" />
      <div class="flex items-center space-x-3 lg:space-x-6">
        <i-minimal-dropdown type="horizontal" placement="bottom-end">
          <div class="py-1">
            <i-dropdown-item
              @click="redirectToEdit(dashboard)"
              icon="PencilAlt"
            >
              {{ $t('dashboard.edit_current') }}
            </i-dropdown-item>
            <i-dropdown-item v-i-modal="'newDashboard'" icon="Plus">
              {{ $t('dashboard.new_dashboard') }}
            </i-dropdown-item>
            <i-dropdown-item @click="destroy(dashboard)" icon="Trash">
              {{ $t('dashboard.delete_current') }}
            </i-dropdown-item>
          </div>
        </i-minimal-dropdown>

        <dropdown-select
          :model-value="dashboard"
          :items="userDashboards"
          label-key="name"
          placement="bottom-end"
          class="min-w-0 max-w-full sm:max-w-xs"
          value-key="id"
          @change="setActiveDashboard"
        >
          <template v-slot="{ label }">
            <i-button
              variant="white"
              :class="[
                'w-full',
                { 'pointer-events-none blur': !componentReady },
              ]"
            >
              <span class="truncate">
                <!-- "Dashboard" text allow the blur to be more visible while loading -->
                {{ componentReady ? label : 'Dashboard' }}
              </span>
              <icon icon="ChevronDown" class="-mr-1 ml-2 h-5 w-5"></icon>
            </i-button>
          </template>
          <template #label="{ label, item }">
            <icon v-if="item.is_default" class="mr-1 h-5 w-5" icon="Star" />
            {{ label }}
          </template>
        </dropdown-select>
      </div>
    </template>

    <div
      v-if="!componentReady"
      class="before:box-inherit after:box-inherit columns-1 gap-x-6 lg:columns-2"
    >
      <card-placeholder pulse class="mb-5" />
      <card-placeholder pulse class="mb-5" size="lg" />
      <card-placeholder pulse class="mb-5" />
      <card-placeholder pulse class="mb-5" />
      <card-placeholder pulse class="mb-5" size="lg" />
      <card-placeholder pulse class="mb-5" size="lg" />
      <card-placeholder pulse class="mb-5" />
      <card-placeholder pulse class="mb-5" />
    </div>

    <draggable
      v-model="mutableCards"
      handle=".dashboard-drag-handle"
      class="before:box-inherit after:box-inherit columns-1 gap-x-6 lg:columns-2"
      item-key="uriKey"
      v-bind="scrollableDraggableOptions"
      @change="saveOrder"
    >
      <template #item="{ element }">
        <div class="mb-5 break-inside-avoid">
          <div class="relative">
            <div class="dashboard-drag-handle absolute top-5 left-2 z-10 block">
              <i-button-icon
                icon="Selector"
                class="cursor-move"
                icon-class="w-4 h-4"
              />
            </div>
            <component :is="element.component" :card="element" />
          </div>
        </div>
      </template>
    </draggable>
    <i-modal
      id="newDashboard"
      size="sm"
      :cancel-title="$t('app.cancel')"
      :ok-title="$t('app.create')"
      form
      @submit="store"
      :ok-disabled="form.busy"
      @keydown="form.onKeydown($event)"
      :title="$t('dashboard.create')"
      @shown="() => $refs.inputName.focus()"
    >
      <i-form-group label-for="name" :label="$t('dashboard.name')" required>
        <i-form-input id="name" ref="inputName" v-model="form.name" />
        <form-error :form="form" field="name" />
      </i-form-group>
    </i-modal>
  </i-layout>
</template>
<script>
import filter from 'lodash/filter'
import find from 'lodash/find'
import sortBy from 'lodash/sortBy'
import orderBy from 'lodash/orderBy'
import draggable from 'vuedraggable'
import ProvidesDraggableOptions from '@/mixins/ProvidesDraggableOptions'
import { CancelToken } from '@/services/HTTP'
import Form from '@/components/Form/Form'
import { useCards } from '@/components/Cards/Composables/useCards'
import CardPlaceholder from '@/components/Loaders/CardPlaceholder'
export default {
  name: 'dashboard',
  mixins: [ProvidesDraggableOptions],

  components: { draggable, CardPlaceholder },
  setup() {
    const { applyUserConfig } = useCards()

    return { applyUserConfig }
  },
  data: () => ({
    form: new Form({
      name: '',
    }),
    mutableCards: [],
    dashboard: {},
    cards: [],
    cancelToken: null,
    componentReady: false,
  }),
  computed: {
    /**
     * The cards that are intended to be shown on the dashboard
     *
     * @return {Array}
     */
    cardsWithConfigApplied() {
      return sortBy(
        filter(
          this.applyUserConfig(this.cards, this.dashboard),
          card => card.enabled
        ),
        ['order', 'asc']
      )
    },

    /**
     * The default dashboard that will be shown here
     *
     * @return {Object}
     */
    default() {
      let dashboard = find(this.userDashboards, ['is_default', true])

      return dashboard || this.userDashboards[0]
    },

    /**
     * User available dashboard
     *
     * @return {Array}
     */
    userDashboards() {
      return (
        orderBy(
          this.currentUser.dashboards,
          ['is_default', 'name'],
          ['desc', 'asc']
        ) || []
      )
    },
  },
  methods: {
    /**
     * Redirect to edit dashboard
     *
     * @param  {Object} dashboard
     *
     * @return {Void}
     */
    redirectToEdit(dashboard) {
      this.$router.push({
        name: 'edit-dashboard',
        params: {
          id: dashboard.id,
        },
      })
    },

    /**
     * Create new dashboard
     *
     * @return {Void}
     */
    store() {
      this.form.post('/dashboards').then(dashboard => {
        Innoclapps.success(this.$t('dashboard.created'))
        this.$store.commit('users/ADD_DASHBOARD', dashboard)
        this.redirectToEdit(dashboard)
      })
    },

    /**
     * Set the active dashboard
     *
     * @param {Object} dashboard
     */
    setActiveDashboard(dashboard) {
      this.dashboard = dashboard
      this.$nextTick(() => (this.mutableCards = this.cardsWithConfigApplied))
    },

    /**
     * Save the cards order
     *
     * @return {Void}
     */
    saveOrder() {
      let payload = this.mutableCards.map((card, key) => {
        return {
          key: card.uriKey,
          order: key + 1,
        }
      })

      Innoclapps.request()
        .put('/dashboards/' + this.dashboard.id, {
          cards: payload,
        })
        .then(({ data }) => {
          this.setActiveDashboard(data)
          this.$store.commit('users/UPDATE_DASHBOARD', data)
        })
    },

    /**
     * Fetch the user dashboard
     *
     * @return {Void}
     */
    async fetch() {
      let response = await Innoclapps.request().get('/cards', {
        cancelToken: new CancelToken(token => (this.cancelToken = token)),
      })

      this.cards = response.data
      this.componentReady = true
    },

    /**
     * Delete dashboard
     *
     * @param  {Object} dashboard
     *
     * @return {Void}
     */
    destroy(dashboard) {
      this.$dialog.confirm().then(() => {
        Innoclapps.request()
          .delete('/dashboards/' + dashboard.id)
          .then(response => {
            Innoclapps.success(this.$t('dashboard.deleted'))
            this.$store.commit('users/REMOVE_DASHBOARD', dashboard.id)
            this.setActiveDashboard(this.default)
          })
      })
    },
  },
  mounted() {
    this.fetch().then(() => {
      this.$nextTick(() => {
        this.setActiveDashboard(this.default)
      })
    })
  },
  beforeUnmount() {
    this.cancelToken && this.cancelToken()
  },
}
</script>
