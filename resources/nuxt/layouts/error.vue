<template>
  <v-container fill-height style="height: calc(100vh - 58px);">
    <v-layout align-center>
      <v-flex text-xs-center>
        <h1 class="display-2 primary--text">
          Error code {{ error.statusCode }}
        </h1>
        <p>{{ error.message }}</p>
        <v-btn to="/" outlined color="primary">
          ホーム
        </v-btn>
      </v-flex>
    </v-layout>
  </v-container>
</template>
<script>
export default {
  props: {
    error: {
      type: Object,
      default() {
        return {}
      }
    }
  }
}
</script>
