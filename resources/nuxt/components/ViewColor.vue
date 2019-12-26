<template>
  <v-card :style="{ backgroundColor: bg_color }" class="white--text">
    {{ setViewColor(colorCode) }}
  </v-card>
</template>

<script>
export default {
  props: {
    colorCode: {
      type: String,
      default: '#000000FF'
    }
  },
  data() {
    return {
      // view color 初期設定
      bg_color: '#000000FF'
    }
  },
  methods: {
    setViewColor(color) {
      // styleセット
      this.bg_color = color
      // リターンでコードを戻し、画面表示させる
      return color
    }
  }
}
</script>
