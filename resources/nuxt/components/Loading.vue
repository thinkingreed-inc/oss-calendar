<template>
  <v-dialog
    v-if="loading"
    v-model="loading"
    persistent
    max-width="400px"
    class="loading"
  >
    <v-progress-circular
      indeterminate
      size="120"
      width="5"
      color="#ffffff"
    ></v-progress-circular>
  </v-dialog>
</template>

<script>
export default {
  data() {
    return {
      loading: false
    }
  },
  methods: {
    start(fullcalendarFlag) {
      // fullcalendar から 意図的に呼ばれた場合、ロード画面制御
      console.log('LOADING...........', fullcalendarFlag, this.loading)
      if (fullcalendarFlag === true) {
        this.loading = true
      }
    },
    finish(fullcalendarFlag) {
      // fullcalendar から 意図的に呼ばれた場合、ロード画面制御
      if (fullcalendarFlag === true) {
        this.loading = false
      }
    }
  }
}
</script>
