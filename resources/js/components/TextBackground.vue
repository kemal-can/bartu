<template>
  <span :style="style">
    <slot>{{ text }}</slot>
  </span>
</template>
<script>
import { getContrast, shadeColor } from '@/utils'
import hexRgb from 'hex-rgb'
export default {
  props: {
    text: String,
    color: String,
    // When false uses black white color contrast with original background
    shade: { type: Boolean, default: true },
    // Only when shade is true
    bgOpacity: { type: [String, Number], default: 0.2 },
    colorShade: { type: [String, Number], default: -50 },
  },
  computed: {
    /**
     * Get the color styling
     *
     * @return {Object|null}
     */
    style() {
      if (!this.color) {
        return null
      }

      if (!this.shade) {
        return {
          background: this.color,
          color: getContrast(this.color),
        }
      }

      const rgbObject = hexRgb(this.color)
      let rgbBackgroud = `rgba(${rgbObject.red}, ${rgbObject.green}, ${rgbObject.blue}, ${this.bgOpacity})`

      return {
        background: rgbBackgroud,
        color: shadeColor(this.color, this.colorShade),
      }
    },
  },
}
</script>
