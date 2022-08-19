<template>
  <v-container fluid fill-height>
    <v-layout align-center justify-center>
      <v-flex xs12 sm8 md5>
        <v-card
          class="elevation-12"
          style="box-shadow: none; border: solid 1px #e4e4e4;"
        >
          <v-toolbar
            dark
            color="primary"
            style="box-shadow: none; border: none;"
          >
            <v-toolbar-title>ログイン</v-toolbar-title>
          </v-toolbar>
          <v-card-text style="padding: 30px;">
            <v-form ref="form" @submit.prevent>
              <v-text-field
                v-model="username"
                v-validate="'required'"
                prepend-icon="person"
                label="ユーザ名"
                data-vv-name="username"
                :error-messages="errors.collect('username')"
                counter="256"
                required
                type="text"
                @keyup.enter="login()"
              >
                <template v-slot:label><required-label propsLabelName="ユーザ名" /></template>
              </v-text-field>
              <v-text-field
                v-model="password"
                v-validate="'required'"
                prepend-icon="lock"
                label="パスワード"
                data-vv-name="password"
                :error-messages="errors.collect('password')"
                counter="256"
                type="password"
                style="margin-top: 20px;"
                @keyup.enter="login()"
              >
                <template v-slot:label><required-label propsLabelName="パスワード" /></template>
              </v-text-field>
            </v-form>
          </v-card-text>
          <v-card-actions style="padding: 30px;">
            パスワードを忘れた場合、
            <router-link to="/password/reset">こちら</router-link>から
            <v-spacer></v-spacer>
            <single-submit-button :onclick="login" :color="color" type="submit">
              <template v-slot:text>ログイン</template>
            </single-submit-button>
          </v-card-actions>
          <div style="height: 4px;">
            <template v-if="doLogining === true">
              <v-progress-linear indeterminate></v-progress-linear>
            </template>
          </div>
        </v-card>
      </v-flex>
    </v-layout>
  </v-container>
</template>

<script>
import RequiredLabel from '~/components/label/RequiredLabel'
import SingleSubmitButton from '~/components/buttons/SingleSubmitButton'

export default {
  layout: 'noauth',
  components: {
    RequiredLabel,
    SingleSubmitButton
  },
  data() {
    return {
      username: '',
      password: '',
      color: 'primary',
      doLogining: false
    }
  },
  methods: {
    async login() {
      try {
        this.doLogining = true
        const data = { username: this.username, password: this.password }
        await this.$auth.loginWith('local', { data: data })
      } catch (e) {
        await this.serverSideValidate()
        this.doLogining = false
      }
    }
  }
}
</script>
