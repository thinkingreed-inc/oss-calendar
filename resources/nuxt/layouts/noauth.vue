<template>
  <v-app id="app">
    <noauth-header />
    <v-content>
      <nuxt />
    </v-content>
    <common-footer />
    <message-snackbar />
  </v-app>
</template>

<script>
import NoauthHeader from '~/components/layouts/noauth/Header'
import CommonFooter from '~/components/layouts/common/Footer'
import MessageSnackbar from '~/components/MessageSnackbar'

export default {
  components: {
    MessageSnackbar,
    NoauthHeader,
    CommonFooter
  }
}
</script>
