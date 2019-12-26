<template>
  <v-container fluid fill-height>
    <!-- beforeCreateだけだと一瞬以下の画面が表示されるので、表示されないようにif文を入れる -->
    <v-layout
      v-if="transferParams.email !== undefined"
      align-center
      justify-center
    >
      <v-flex xs12 sm8 md4>
        <v-card class="elevation-12">
          <v-toolbar
            dark
            color="primary"
            style="box-shadow: none; border: none;"
          >
            <v-toolbar-title>メールを確認する</v-toolbar-title>
          </v-toolbar>
          <v-card-text>
            {{
              email
            }}にメールを送信しました。<br />メールに記載されたリンクをクリックするとパスワードをリセットできます。<br />
            メールが届かない場合、迷惑メールやスパムフォルダーなどもご確認ください。
          </v-card-text>
          <v-card-actions class="cardtext_padding">
            <router-link to="/">ホームに戻る</router-link>
            <v-spacer></v-spacer>
          </v-card-actions>
        </v-card>
      </v-flex>
    </v-layout>
  </v-container>
</template>

<script>
export default {
  layout: 'noauth',
  data() {
    return {
      email: '',
      color: 'primary'
    }
  },
  beforeCreate() {
    if (this.transferParams.email === undefined) {
      this.$router.push('/')
    }
  },
  created() {
    this.email = this.transferParams.email
  }
}
</script>
