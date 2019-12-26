<template>
  <v-container fluid fill-height> </v-container>
</template>

<script>
export default {
  middleware: 'auth',
  data() {
    return {
      token: ''
    }
  },
  async created() {
    const params = this.$route.params
    this.token = params.token
    if (this.token !== undefined) {
      await this.send()
      this.$router.push('/')
    }
  },
  methods: {
    async send() {
      try {
        const data = { token: this.token }
        const res = await this.$axios.patch('/api/mypage/email/check', data)
        const message = res.data.message
        this.$store.dispatch('message/setMessage', message)
        this.$store.dispatch('message/setSuccess')
      } catch (e) {
        const status = e.response.status
        let message = null
        if ((status === 422 || status === 429) && e.response.data.errors) {
          for (let key in e.response.data.errors) {
            if (message === null) {
              message = message
            } else {
              message = message + '　' + e.response.data.errors[key]
            }
          }
          this.$store.dispatch('message/setMessage', message, 'warning')
          this.$store.dispatch('message/setError')
        } else {
          this.$store.dispatch('message/setMessage', e.response.data.message)
          this.$store.dispatch('message/setError')
        }
      }
    }
  }
}
</script>
