<template>
  <v-snackbar v-model="snackbar" :color="getColorText" top right vertical>
    {{ getMessageText }}
    <v-btn dark text @click="clearMessage">
      Close
    </v-btn>
  </v-snackbar>
</template>

<script>
import { mapGetters, mapActions } from 'vuex'

export default {
  data() {
    return {
      snackbar: false
    }
  },
  computed: {
    isShow() {
      return this.existsMessage()
    },
    getMessageText() {
      return this.getMessage()
    },
    getColorText() {
      return this.getColor()
    }
  },
  watch: {
    isShow: function() {
      // computedのisShow()を監視している
      this.snackbar = this.existsMessage()
    },
    snackbar: function() {
      // タイマでスナックバーが閉じたときにメッセージをクリアする
      if (!this.snackbar) {
        this.clearMessage()
      }
    }
  },
  methods: {
    ...mapGetters('message', ['getMessage', 'getColor', 'existsMessage']),
    ...mapActions('message', ['clearMessage'])
  }
}
</script>
