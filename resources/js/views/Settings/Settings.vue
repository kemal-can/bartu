<template>
  <i-layout>
    <div class="mx-auto max-w-7xl">
      <div class="lg:grid lg:grid-cols-12 lg:gap-x-5">
        <aside class="lg:col-span-3">
          <settings-menu></settings-menu>
        </aside>
        <div ref="settingsView" class="sm:hidden"></div>
        <div class="lg:col-span-9">
          <router-view></router-view>
        </div>
      </div>
    </div>
  </i-layout>
</template>
<script>
import each from 'lodash/each'
import SettingsMenu from './SettingsMenu'
export default {
  components: { SettingsMenu },
  beforeRouteUpdate(to, from, next) {
    // Scroll on top when some child route from the settings is loaded
    // Helps the user to see the top if navigated too down as well on mobile as
    // the menu is on the top of the page
    if (to.matched && to.matched[0] && to.matched[0].name === 'settings') {
      document.getElementById('main').scrollTo({
        top: this.$refs.settingsView.getBoundingClientRect().top,
        behavior: 'smooth',
      })
    }
    next()
  },
}
</script>
