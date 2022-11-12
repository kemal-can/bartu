/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
import Innoclapps from '@/innoclapps'
import registerComponents from '@/components'
import registerFields from '@/fields'
import { registerDirectives } from '@/directives'
import Gate from '@/gate'
import i18n from '@/i18n'
import Broadcast from '@/services/Broadcast'
import ApplicationMixins from '@/mixins/Application'
import DatesMixin from '@/mixins/HandlesDates'
import store from '@/store'
import VoIP from '@/services/VoIP'

require('./element-prototypes')
require('./plugins')

/**
 * Boot the application config
 *
 * @param  {Object} Vue
 *
 * @return {Void}
 */
function bootApplicationConfig(Vue) {
  // Set dashboard menu items in store
  store.commit('SET_MENU', config.menu || [])

  // Set available application users in store
  store.commit('users/SET', config.users || [])

  // Below we will set common used resources data, as multiple component may be rendering
  // the same data and making dozens of requests just to retrieve the data is not convenient
  store.commit('calls/SET_OUTCOMES', config.calls ? config.calls.outcomes : [])

  store.commit(
    'activities/SET_TYPES',
    config.activities ? config.activities.types : []
  )

  store.commit(
    'activities/SET_TYPES',
    config.activities ? config.activities.types : []
  )

  store.commit('pipelines/SET', config.deals ? config.deals.pipelines : [])

  store.commit(
    'deals/SET_LOST_REASONS',
    config.deals ? config.deals.lost_reasons : []
  )
}

/**
 * Subscribe to the private channel for user notifications
 *
 * @param {Number} userId
 *
 * @return {Void}
 */
function subscribeForUserNotifications(userId, app) {
  window.Echo.private('App.Models.User.' + userId).notification(
    notification => {
      app.$emit('notification-broadcasted', notification.id)
    }
  )
}

/**
 * Listen when email accounts sync is finished
 *
 * @param  {Object} app
 *
 * @return {Void}
 */
function listenForEmailAccountSync(app) {
  window.Echo.private('inbox').listen('EmailAccountsSyncFinished', e => {
    app.$emit('email-accounts-sync-finished', e)
    app
      .request()
      .get('mail/accounts/unread')
      .then(({ data }) =>
        store.dispatch('emailAccounts/updateUnreadCountUI', data)
      )
  })
}

/**
 * Before each route callback function
 */
function beforeEachRoute(to, from, next, app) {
  // Close sidebar on route change when on mobile
  if (store.state.sidebarOpen) {
    store.commit('SET_SIDEBAR_OPEN', false)
  }

  // Check if it's a gate route, if yes, perform check before each route
  const gateRoute = to.matched.find(match => match.meta.gate)

  if (gateRoute && typeof gateRoute.meta.gate === 'string') {
    if (app.config.globalProperties.$gate.userCant(gateRoute.meta.gate)) {
      next({ path: '/403' })
    }
  }

  // Let's try to set page title now, as the user is allowed to access the route
  if (to.meta.title) {
    store.commit('SET_PAGE_TITLE', to.meta.title)
  } else if (store.state.pageTitle && !to.meta.title) {
    // Reset title if now there is no title but previously title was set
    store.commit('SET_PAGE_TITLE', '')
  }

  next()
}
;(function () {
  this.CreateApplication = function (config) {
    const app = new Innoclapps(config)

    app.booting((Vue, router) => {
      // [Vue warn]: injected property "activeTab" is a ref and will be auto-unwrapped and no longer needs `.value` in the next minor release. To opt-in to the new behavior now, set `app.config.unwrapInjectedRef = true` (this config is temporary and will not be needed in the future.)

      // It should be safe to remove the config below in Vue v3.3?
      Vue.config.unwrapInjectedRef = true

      router.beforeEach((to, from, next) =>
        beforeEachRoute(to, from, next, Vue)
      )

      Vue.use(i18n.instance)
      Vue.use(store)

      Vue.mixin(ApplicationMixins)
      Vue.mixin(DatesMixin)

      store.commit('SET_SETTINGS', config.options)
      store.commit('SET_API_URL', config.apiURL)
      store.commit('SET_URL', config.url)
    })

    app.booting(bootApplicationConfig)

    if (config.hasOwnProperty('voip') && config.voip.client) {
      app.booting(Vue => {
        const VoIPInstance = new VoIP(config.voip.client)
        Vue.config.globalProperties.$voip = VoIPInstance
        Vue.component('call-component', VoIPInstance.callComponent)
      })
    }

    const broadcaster = new Broadcast(config.broadcasting)
    app.broadcaster = broadcaster

    if (broadcaster.hasDriver() && config.user_id) {
      app.booting(Vue => listenForEmailAccountSync(app))
      app.booting(Vue => subscribeForUserNotifications(config.user_id, app))
    }

    app.booting(Vue => {
      Vue.config.globalProperties.$gate = new Gate(
        store.getters['users/current']
      )

      Vue.config.globalProperties.$iModal = {
        hide(id) {
          app.$emit('modal-hide', id)
        },
        show(id) {
          app.$emit('modal-show', id)
        },
      }

      Vue.config.globalProperties.$iTable = {
        reload(tableId) {
          app.$emit('reload-resource-table', tableId)
        },
      }

      registerDirectives(Vue)
      registerComponents(Vue)
      registerFields(Vue)
    })

    return app
  }
}.call(window))
