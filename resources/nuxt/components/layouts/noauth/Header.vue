<template>
  <v-app-bar absolute fixed flat app color="#ffffff">
    <v-toolbar-title>
      <router-link to="/"
        ><img src="~/assets/img/logo.png" alt="OSS-Calendar" class="icon_logo"
      /></router-link>
    </v-toolbar-title>
  </v-app-bar>
</template>

<script>
export default {
  data() {
    return {
      menu: false
    }
  },
  methods: {
    logout() {
      this.$auth.logout()
    },
    onMyPasswordClick() {
      this.$emit('onMyPasswordClick')
    },
    onMyEmaildClick() {
      this.$emit('onMyEmailClick')
    }
  }
}
</script>
