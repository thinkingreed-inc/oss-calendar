<template>
  <v-menu bottom left>
    <template v-slot:activator="{ on }">
      <v-btn icon class="admin-menu-button" v-on="on">
        <v-icon>settings</v-icon>
      </v-btn>
    </template>

    <v-list class="admin-manu-item">
      <v-list-item v-for="(item, i) in items" :key="i" :to="item.to">
        <v-list-item-content>
          <v-list-item-title v-text="item.title"></v-list-item-title>
        </v-list-item-content>
      </v-list-item>
    </v-list>
  </v-menu>
</template>
<script>
export default {
  data() {
    return {
      items: [
        {
          title: 'ユーザー管理',
          to: '/user/admin'
        },
        {
          title: '部署マスタ',
          to: '/department/admin'
        },
        {
          title: '予定タイプマスタ',
          to: '/event_type/admin'
        },
        {
          title: '共有グループマスタ',
          to: '/common_group/admin'
        },
        {
          title: '休祝日設定',
          to: '/holiday/admin'
        }
      ],
      menu: false
    }
  }
}
</script>
