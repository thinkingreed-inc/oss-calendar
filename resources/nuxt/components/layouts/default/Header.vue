<template>
  <v-app-bar app clipped-right flat>
    <v-app-bar-nav-icon @click="$store.commit('toggleDrawer')" />
    <v-toolbar-title>
      <router-link to="/">
        <img src="~/assets/img/logo.png" alt="OSS-Calendar" class="icon_logo" />
      </router-link>
    </v-toolbar-title>
    <v-spacer />
    <admin-menu v-if="$can('show', 'adminMenu')"></admin-menu>

    <!-- プロフィールメニュー -->
    <v-menu
      v-model="menu"
      :close-on-content-click="false"
      :nudge-width="200"
      offset-x
    >
      <template v-slot:activator="{ on }">
        <v-btn icon v-on="on">
          <v-icon>more_vert</v-icon>
        </v-btn>
      </template>

      <v-card>
        <v-list style="background-color: #e84b64;">
          <v-list-item>
            <v-list-item-avatar>
              <v-icon style="color: #ffffff;">face</v-icon>
            </v-list-item-avatar>

            <v-list-item-content v-if="$auth.user">
              <v-list-item-title style="color: #ffffff; font-weight: bold;"
                >{{ $auth.user.lastname }}
                {{ $auth.user.firstname }}</v-list-item-title
              >
              <v-list-item-subtitle
                style="color: #ffffff; font-weight: bold;"
                >{{ $auth.user.username }}</v-list-item-subtitle
              >
            </v-list-item-content>
          </v-list-item>
        </v-list>

        <v-divider></v-divider>

        <v-list>
          <v-list-item
            router
            exact
            @click="
              onMyPasswordClick()
              menu = false
            "
          >
            <v-list-item-action>
              <v-icon>lock</v-icon>
            </v-list-item-action>
            <v-list-item-content>
              <v-list-item-title v-text="'パスワード変更'" />
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            router
            exact
            @click="
              onMyEmailClick()
              menu = false
            "
          >
            <v-list-item-action>
              <v-icon>mail</v-icon>
            </v-list-item-action>
            <v-list-item-content>
              <v-list-item-title v-text="'メールアドレス変更'" />
            </v-list-item-content>
          </v-list-item>
          <v-list-item v-for="(item, i) in items" :key="i" :to="item.to">
            <v-list-item-action>
              <v-icon>{{ item.icon }}</v-icon>
            </v-list-item-action>
            <v-list-item-content>
              <v-list-item-title
                @click="menu = false"
                v-text="item.title"
              ></v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            router
            exact
            @click="
              onMySettingClick()
              menu = false
            "
          >
            <v-list-item-action>
              <v-icon>settings_applications</v-icon>
            </v-list-item-action>
            <v-list-item-content>
              <v-list-item-title v-text="'個人設定'" />
            </v-list-item-content>
          </v-list-item>
        </v-list>

        <v-divider></v-divider>

        <v-list>
          <v-list-item
            @click="
              logout()
              menu = false
            "
          >
            <v-list-item-action>
              <v-icon>logout</v-icon>
            </v-list-item-action>
            <v-list-item-content>
              <v-list-item-title v-text="'ログアウト'" />
            </v-list-item-content>
          </v-list-item>
        </v-list>
      </v-card>
    </v-menu>
    <!-- ここまで　プロフィールメニュー -->
  </v-app-bar>
</template>

<script>
import AdminMenu from '~/components/layouts/default/header/AdminMenu'

export default {
  components: {
    AdminMenu
  },
  data() {
    return {
      items: [
        {
          icon: 'group',
          title: '個別グループ',
          to: '/individual_group'
        }
      ],
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
    onMyEmailClick() {
      this.$emit('onMyEmailClick')
    },
    onMySettingClick() {
      this.$emit('onMySettingClick')
    }
  }
}
</script>
