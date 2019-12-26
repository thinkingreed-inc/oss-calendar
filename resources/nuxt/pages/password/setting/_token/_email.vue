<template>
  <v-container fluid fill-height>
    <v-layout align-center justify-center>
      <v-flex xs12 sm8 md4>
        <v-card class="elevation-12">
          <v-toolbar
            dark
            color="primary"
            style="box-shadow: none; border: none;"
          >
            <v-toolbar-title>パスワードをリセット</v-toolbar-title>
          </v-toolbar>
          <v-card-text>
            <v-form>
              <p>{{ email }}</p>
              <v-text-field
                ref="password"
                v-model="password"
                v-validate="'required'"
                label="新しいパスワードを入力"
                data-vv-name="password"
                :error-messages="errors.collect('password')"
                counter="256"
                autofocus
                type="password"
              ></v-text-field>
              <v-text-field
                v-model="password_confirmation"
                v-validate="'required|confirmed:password'"
                label="新しいパスワードを再度入力してください"
                data-vv-name="password_confirmation"
                :error-messages="errors.collect('password_confirmation')"
                counter="256"
                type="password"
              ></v-text-field>
            </v-form>
          </v-card-text>
          <v-card-actions class="cardtext_padding">
            <v-spacer></v-spacer>
            <single-submit-button :disabled="errors.any()" :onclick="send">
              <template v-slot:text>
                送信する
              </template>
            </single-submit-button>
          </v-card-actions>
        </v-card>
      </v-flex>
    </v-layout>
  </v-container>
</template>

<script>
import SingleSubmitButton from '~/components/buttons/SingleSubmitButton'

export default {
  layout: 'noauth',
  components: {
    SingleSubmitButton
  },
  data() {
    return {
      email: '',
      token: '',
      password: '',
      password_confirmation: ''
    }
  },
  created() {
    const params = this.$route.params
    this.email = params.email
    this.token = params.token
  },
  methods: {
    async send() {
      try {
        const data = {
          email: this.email,
          token: this.token,
          password: this.password,
          password_confirmation: this.password_confirmation
        }
        const res = await this.$axios.post('/api/password/reset', data)
        const access_token = res.data.access_token
        this.$auth.setUserToken(access_token)
        this.$store.dispatch(
          'message/setMessage',
          'パスワードが変更されました！'
        )
      } catch (e) {
        await this.serverSideValidate()
      }
    }
  }
}
</script>
