<template>
  <div>
    <i-form-group
      :label="$t('app.timezone')"
      label-for="timezone"
      v-if="withTimezoneField"
    >
      <app-timezone-field v-model="form.timezone" />
      <form-error :form="form" field="timezone" />
    </i-form-group>
    <i-form-group
      v-if="withLocaleField"
      :label="$t('app.locale')"
      label-for="locale"
    >
      <i-custom-select
        input-id="locale"
        v-model="form.locale"
        :clearable="false"
        :options="locales"
      >
      </i-custom-select>
      <form-error :form="form" field="locale" />
    </i-form-group>
    <i-form-group :label="$t('settings.date_format')" label-for="date_format">
      <date-format-field v-model="form.date_format" />
      <form-error :form="form" field="date_format" />
    </i-form-group>
    <i-form-group :label="$t('settings.time_format')" label-for="time_format">
      <time-format-field v-model="form.time_format" />
      <form-error :form="form" field="time_format" />
    </i-form-group>
    <i-form-group
      :label="$t('settings.first_day_of_week')"
      label-for="first_day_of_week"
    >
      <!-- http://chartsbin.com/view/41671 -->
      <week-days-select v-model="form.first_day_of_week" :only="[1, 6, 0]" />
      <form-error :form="form" field="first_day_of_week" />
    </i-form-group>
  </div>
</template>
<script>
import AppTimezoneField from '@/components/TimezoneField'
import DateFormatField from '@/components/DateFormatField'
import TimeFormatField from '@/components/TimeFormatField'
import WeekDaysSelect from '@/components/WeekDaysSelect'
import { mapGetters } from 'vuex'
export default {
  components: {
    AppTimezoneField,
    DateFormatField,
    TimeFormatField,
    WeekDaysSelect,
  },
  props: {
    form: {
      required: true,
      type: Object,
    },
    exclude: {
      type: Array,
      default: () => [],
    },
  },
  computed: {
    ...mapGetters({
      locales: 'locales',
    }),
    withTimezoneField() {
      return this.exclude.indexOf('timezone') === -1
    },
    withLocaleField() {
      return this.exclude.indexOf('locale') === -1
    },
  },
}
</script>
