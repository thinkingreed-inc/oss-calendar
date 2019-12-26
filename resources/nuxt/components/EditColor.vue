<template>
  <v-layout wrap>
    <v-container grid-list-xl>
      <v-layout wrap>
        <v-card-title class="subtitle-1" style="height: 80px">
          カラー設定
        </v-card-title>
        <v-flex xs12 d-flex justify>
          <v-color-picker
            v-model="color"
            class="ma-2"
            :swatches="swatches"
            show-swatches
            hide-inputs
          ></v-color-picker>
        </v-flex>
        <v-flex md4>
          <v-text-field
            v-model="color"
            label="カラー"
            data-vv-name="color"
            readonly
            >{{ showColor }}</v-text-field
          >
        </v-flex>
      </v-layout>
    </v-container>
  </v-layout>
</template>

<script>
export default {
  data() {
    return {
      // color picker 初期設定
      type: 'hex',
      hex: '#FFFFFFFF',
      color: this.propColor,
      swatches: [
        ['#F44336', '#2196F3', '#8BC34A', '#FF5722'],
        ['#E91E63', '#03A9F4', '#CDDC39', '#795548'],
        ['#9C27B0', '#00BCD4', '#FFEB3B', '#607D8B'],
        ['#673AB7', '#009688', '#FFC107', '#9E9E9E'],
        ['#3F51B5', '#4CAF50', '#FF9800', '#000000']
      ]
    }
  },
  computed: {
    showColor() {
      if (typeof this.color === 'string') return this.color
      return JSON.stringify(
        Object.keys(this.color).reduce((color, key) => {
          color[key] = Number(this.color[key].toFixed(2))
          return color
        }, {}),
        null,
        2
      )
    }
  },
  watch: {
    color() {
      this.$emit('setSelectedColor', this.color)
    }
  },
  methods: {
    setInit(color) {
      this.color = color
    }
  }
}
</script>
