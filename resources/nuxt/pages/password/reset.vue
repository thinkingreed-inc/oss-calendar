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
            <v-toolbar-title>パスワードをお忘れの方</v-toolbar-title>
          </v-toolbar>
          <v-card-text>
            <v-form>
              <v-text-field
                v-model="email"
                v-validate="'required|email'"
                prepend-icon="email"
                label="メールアドレス"
                data-vv-name="email"
                :error-messages="errors.collect('email')"
                counter="256"
                placeholder="登録しているメールアドレス"
                required
                autofocus
                type="tel"
              >
              </v-text-field>
            </v-form>
          </v-card-text>
          <v-card-actions class="cardtext_padding">
            <v-spacer></v-spacer>
            <single-submit-button
              :disabled="errors.any()"
              :onclick="send"
              :color="color"
            >
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
      color: 'primary'
    }
  },
  methods: {
    async send() {
      try {
        const data = { email: this.email }
        await this.$axios.post('/api/password/email', data)
        this.$router.push({ path: '/password/reset_email_sent' })
        this.setTransferParams(data)
      } catch (e) {
        await this.serverSideValidate()
      }
    }
  }
}
</script>
