<template>
  <v-app style="background-color: #ffffff;">
    <default-sidebar />
    <default-header
      @onMyPasswordClick="myPasswordDialog = true"
      @onMyEmailClick="myEmailDialog = true"
      @onMySettingClick="mySettingDialog = true"
    />
    <v-content>
      <v-container id="main-container-wrap">
        <nuxt />
      </v-container>
    </v-content>
    <!-- <common-footer /> -->
    <message-snackbar />
    <my-password-dialog
      :props-dialog="myPasswordDialog"
      @close="myPasswordDialog = false"
    />
    <my-email-dialog
      :props-dialog="myEmailDialog"
      @close="myEmailDialog = false"
    />
    <my-setting-dialog
      :props-dialog="mySettingDialog"
      @close="mySettingDialog = false"
    />
  </v-app>
</template>

<script>
import DefaultHeader from '~/components/layouts/default/Header'
import DefaultSidebar from '~/components/layouts/default/Sidebar'
import MessageSnackbar from '~/components/MessageSnackbar'
import MyPasswordDialog from '~/components/dialogs/mypage/MyPassword.vue'
import MyEmailDialog from '~/components/dialogs/mypage/MyEmail.vue'
import MySettingDialog from '~/components/dialogs/mypage/MySetting.vue'

export default {
  components: {
    DefaultHeader,
    DefaultSidebar,
    MessageSnackbar,
    MyPasswordDialog,
    MyEmailDialog,
    MySettingDialog
  },
  data() {
    return {
      myPasswordDialog: false,
      myEmailDialog: false,
      mySettingDialog: false
    }
  }
}
</script>
