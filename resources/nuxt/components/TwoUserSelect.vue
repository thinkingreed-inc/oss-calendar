<template>
  <v-layout wrap>
    <v-flex class="xs12 sm6">
      <v-card class="mx-auto">
        <v-card-title class="subtitle-1" style="height: 60px">
          参加者一覧
        </v-card-title>
        <v-list style="height: 200px;overflow-y: scroll">
          <v-list-item-group v-model="selected_users" mandatory multiple>
            <v-list-item v-for="(user, i) in users" :key="i" :value="user">
              <v-list-item-content>
                <v-list-item-title v-text="user.text"></v-list-item-title>
              </v-list-item-content>
            </v-list-item>
          </v-list-item-group>
        </v-list>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn text @click="deleteUsers">削除</v-btn>
        </v-card-actions>
      </v-card>
    </v-flex>
    <v-flex class="xs12 sm6">
      <v-card class="mx-auto">
        <v-card-title style="height: 60px" class="departments-select-wrap">
          <v-autocomplete v-model="group_id" :items="groups" label="部署"></v-autocomplete>
        </v-card-title>
        <v-list style="height: 200px;overflow-y: scroll">
          <v-list-item-group v-model="selected_group_users" multiple>
            <v-list-item
              v-for="(group_user, i) in group_users"
              :key="i"
              :value="group_user"
            >
              <v-list-item-content>
                <v-list-item-title v-text="group_user.text"></v-list-item-title>
              </v-list-item-content>
            </v-list-item>
          </v-list-item-group>
        </v-list>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn text @click="addUsers">追加</v-btn>
        </v-card-actions>
      </v-card>
    </v-flex>
  </v-layout>
</template>

<script>

var defaultDepartmentTopValue = '0'
var defaultDepartmentTopText = '全て'
export default {
  props: {
    propsDefaultUsers: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      selected_users: [],
      users: this.propsDefaultUsers,
      group_id: 0,
      selected_group_users: [],
      group_users: [],
      groups: []
    }
  },
  watch: {
    users(after_val, before_val) {
      //console.log('users change:', before_val, '->', after_val)
      this.selected_users = []
      this.selected_group_users = []
      this.$emit('setSelectedUsers', after_val)
    },
    selected_users(after_val, before_val) {
      //console.log('selected_users change:', before_val, '->', after_val)
    },
    group_id(after_val, before_val) {
      // 部署単位ユーザー取得
      this.getUserOfDepartment(after_val)
    }
  },
  async mounted() {
    await this.indexDatas()
    this.getAllGroups()
  },
  methods: {
    // 部署毎ユーザー取得
    async getUserOfDepartment(department_id) {
      let userItems = []
      try {
        // 部署毎ユーザー取得(全ユーザー時はPOSTデータブランク)
        let option = {}
        if (department_id > 0) {
          option = { id: department_id, join_model: 'department' }
        }
        const res = await this.$axios.get('/api/user', { params: option })
        var users = res.data
        // ユーザーを画面セット用に変換
        users.forEach(function(user) {
          userItems.push({
            value: user.id,
            text: user.lastname + ' ' + user.firstname
          })
        })
        // グループユーザーをセット
        this.group_users = userItems
        this.checkGroupUsers() // 選択ユーザーをグループユーザーから除外
      } catch (e) {
        console.log('Error : ' + e.response.data)
      }
    },

    // 部署取得
    async getAllGroups() {
      let departmentItems = []
      departmentItems.push({
        value: defaultDepartmentTopValue,
        text: defaultDepartmentTopText
      })
      try {
        // 部署取得
        const res = await this.$axios.get('/api/department')
        var departments = res.data
        // 部署を画面セット用に変換
        departments.forEach(function(department) {
          departmentItems.push({
            value: department.id,
            text: department.name
          })
        })
      } catch (e) {
        console.log('Error : ' + e.response.data)
      }
      this.groups = departmentItems
    },

    // データ一覧
    async indexDatas() {
      try {
        const group_id = this.group_id
        const res = await this.$axios.get('/api/user/two_select/' + group_id)
        this.group_users = res.data
        this.checkGroupUsers()
        console.log('Index : record num=' + this.group_users.length)
      } catch (e) {
        console.log('Error : ' + e.response.data)
      }
    },
    addUsers() {
      //this.users
      // this.$set(this, "users", this.selected_group_users)
      //this.replaceObject(this.users, this.selected_group_users)
      console.log(this.selected_group_users)
      for (let i = 0; i < this.selected_group_users.length; i++) {
        const selected_group_user = this.selected_group_users[i]
        const searchKey = this.users.findIndex(
          item => item.value === selected_group_user.value
        )
        // 検索にヒットしなければ、追加
        if (searchKey === -1) {
          this.users.push(selected_group_user)
          const deleteKey = this.group_users.findIndex(
            item => item.value === selected_group_user.value
          )
          // 検索にヒットすれば、削除
          if (deleteKey !== -1) {
            this.group_users.splice(deleteKey, 1)
          }
        }
      }
    },
    deleteUsers() {
      console.log(this.selected_users)
      for (let i = 0; i < this.selected_users.length; i++) {
        const selected_user = this.selected_users[i]
        const searchKey = this.group_users.findIndex(
          item => item.value === selected_user.value
        )
        // 検索にヒットしなければ、追加
        if (searchKey === -1) {
          this.group_users.push(selected_user)
          const deleteKey = this.users.findIndex(
            item => item.value === selected_user.value
          )
          // 検索にヒットすれば、削除
          if (deleteKey !== -1) {
            this.users.splice(deleteKey, 1)
          }
        }
      }
    },
    checkGroupUsers() {
      var nowUsers = this.users
      var nowGroupUsers = this.group_users
      var deleteIndex = []
      nowUsers.forEach(function(nowUser) {
        const searchKey = nowGroupUsers.findIndex(
          nowGroupUser => nowGroupUser.value === nowUser.value
        )
        if (searchKey !== -1) {
          deleteIndex.push(searchKey)
        }
      })
      // 降順に並び替え
      deleteIndex.sort(function(a, b) {
        return a < b ? 1 : -1
      })
      // 削除
      deleteIndex.forEach(function(index) {
        nowGroupUsers.splice(index, 1)
      })
      this.group_users = nowGroupUsers
    },
    //再び初期化するときに実行する関数
    async setInit(users) {
      this.users = users
      await this.indexDatas()
      this.getAllGroups()
    }
  }
}
</script>
