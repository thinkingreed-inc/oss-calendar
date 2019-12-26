<template>
  <v-navigation-drawer
    v-model="drawer"
    :mini-variant="$store.getters.getMiniVariant"
    fixed
    app
    mobile-break-point="1000"
  >
    <v-list class="sidebar-listwrap">
      <h3>表示カレンダー</h3>
      <v-list-item-group v-model="selectedCalendar" color="secondary">
        <v-list-item>
          <v-list-item-content
            exact
            @click="changeCalendarViewType('dayGridMonth')"
          >
            <v-list-item-title>月</v-list-item-title>
          </v-list-item-content>
        </v-list-item>
        <v-list-item>
          <v-list-item-content @click="changeCalendarViewType('timeGridWeek')">
            <v-list-item-title>週</v-list-item-title>
          </v-list-item-content>
        </v-list-item>
        <v-list-item>
          <v-list-item-content @click="changeCalendarViewType('timeGridDay')">
            <v-list-item-title>日</v-list-item-title>
          </v-list-item-content>
        </v-list-item>
        <v-list-item>
          <v-list-item-content
            @click="changeCalendarViewType('resourceTimelineWeek')"
          >
            <v-list-item-title>グループ週</v-list-item-title>
          </v-list-item-content>
        </v-list-item>
        <v-list-item>
          <v-list-item-content
            @click="changeCalendarViewType('resourceTimelineDay')"
          >
            <v-list-item-title>グループ日</v-list-item-title>
          </v-list-item-content>
        </v-list-item>
      </v-list-item-group>

      <h3>グループ選択</h3>
      <v-list-item-group v-model="selectedGroup" color="secondary">
        <v-list-item
          v-for="(item, i) in items"
          :key="i"
          router
          exact
          @click="updateCalendarView(item.id)"
        >
          <v-list-item-content>
            <v-list-item-title v-text="item.title" />
          </v-list-item-content>
        </v-list-item>
      </v-list-item-group>
    </v-list>
  </v-navigation-drawer>
</template>

<script>
export default {
  data() {
    return {
      items: [{ title: '自分のみ', id: '0', to: '/' }],
      selectedCalendar: this.returnCalendarViewType(), //初期表示は月次カレンダーな為、デフォルトを指定
      selectedGroup: 0 //初期表示は自分のみカレンダーな為、デフォルトを指定
      // items: [
      //   {
      //     icon: 'apps',
      //     title: 'Welcome',
      //     to: '/'
      //   },
      //   {
      //     icon: 'bubble_chart',
      //     title: 'Inspire',
      //     to: '/inspire'
      //   }
      // ],
    }
  },
  computed: {
    drawer: {
      get() {
        return this.$store.getters.getDrawer
      },
      set(val) {
        this.$store.commit('setDrawer', val)
      }
    }
  },
  mounted() {
    // カレンダー表示設定(プルダウン)内容セット
    //   text= [name]
    //   value= [model]:[id]
    var user_id = this.$auth.user.id
    this.setDepartmentOfUser(user_id)
    this.setCommonGroupOfUser(user_id)
    this.setIndividualGroupOfUser(user_id)
  },
  methods: {
    async setDepartmentOfUser(user_id) {
      var items = []
      try {
        const option = { user_id: user_id, join_model: 'user' }
        const res = await this.$axios.get('/api/department', { params: option })
        var departments = res.data
        // 部署を画面セット用に変換
        departments.forEach(function(department) {
          items.push({
            to: '/',
            id: 'department:' + department.id,
            title: department.name
          })
        })
        this.items = this.items.concat(items)
      } catch (e) {
        console.log('Error : ' + e.response.data)
      }
    },
    async setCommonGroupOfUser(user_id) {
      var items = []
      try {
        // ユーザー共有グループ取得
        const option = { user_id: user_id, join_model: 'user' }
        const res = await this.$axios.get('/api/common_group', {
          params: option
        })
        var common_groups = res.data
        // 共有グループを画面セット用に変換
        common_groups.forEach(function(common_group) {
          items.push({
            to: '/',
            id: 'common_group:' + common_group.id,
            title: common_group.name
          })
        })
        //console.log(items);
        this.items = this.items.concat(items)
      } catch (e) {
        console.log('Error : ' + e.response.data)
      }
    },
    async setIndividualGroupOfUser(user_id) {
      var items = []
      try {
        // ユーザー個別グループ取得
        const option = { user_id: user_id, join_model: 'user' }
        const res = await this.$axios.get('/api/individual_group', {
          params: option
        })
        var individual_groups = res.data
        // 個別グループを画面セット用に変換
        individual_groups.forEach(function(individual_group) {
          items.push({
            to: '/',
            id: 'individual_group:' + individual_group.id,
            title: individual_group.name
          })
        })
        //console.log(items);
        this.items = this.items.concat(items)
      } catch (e) {
        console.log('Error : ' + e.response.data)
      }
    },
    async updateCalendarView(id, isNotRoute) {
      console.log('updateCalendarView: ' + id)
      // this.$store.commit('toggleDrawer');

      if (this.$isMobile()) {
        this.$store.commit('toggleDrawer')
      }

      if (this.$getCalendar()) {
        this.$getCalendar().updateGroup(id)
      } else {
        if (!isNotRoute) {
          await this.$router.push('/')
          if (this.$isMobile()) {
            this.$store.commit('toggleDrawer')
          }
        }
        const self = this
        setTimeout(function() {
          self.updateCalendarView(id, true)
        }, 100)
      }
    },
    async changeCalendarViewType(viewName, isNotRoute) {
      console.log(viewName)
      const calendar = this.$getCalendar()

      if (calendar) {
        calendar.changeCalendarViewType(viewName)
      } else {
        if (!isNotRoute) {
          await this.$router.push('/')
          if (this.$isMobile()) {
            this.$store.commit('toggleDrawer')
          }
        }
        const self = this
        setTimeout(function() {
          self.changeCalendarViewType(viewName, true)
        }, 100)
      }
    },
    returnCalendarViewType() {
      const viewId = this.$auth.user.home_page_id - 1
      return viewId
    }
  }
}
</script>
