<template>
  <v-layout wrap>
    <v-flex class="xs12 sm6">
      <p>所属部署</p>
      <v-card class="mx-auto">
        <v-list style="height: 200px;overflow-y: scroll">
          <v-list-item-group v-model="selected_departments" mandatory multiple>
            <v-list-item
              v-for="(department, i) in departments"
              :key="i"
              :value="department"
            >
              <v-list-item-content>
                <v-list-item-title v-text="department.text"></v-list-item-title>
              </v-list-item-content>
            </v-list-item>
          </v-list-item-group>
        </v-list>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn text @click="deleteDepartments">削除</v-btn>
        </v-card-actions>
      </v-card>
    </v-flex>
    <v-flex class="xs12 sm6">
      <p>部署一覧</p>
      <v-card class="mx-auto">
        <v-list style="height: 200px;overflow-y: scroll">
          <v-list-item-group v-model="selected_group_departments" multiple>
            <v-list-item
              v-for="(group_department, i) in group_departments"
              :key="i"
              :value="group_department"
            >
              <v-list-item-content>
                <v-list-item-title
                  v-text="group_department.text"
                ></v-list-item-title>
              </v-list-item-content>
            </v-list-item>
          </v-list-item-group>
        </v-list>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn text @click="addDepartments">追加</v-btn>
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
    propsDefaultDepartments: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      selected_departments: [],
      departments: this.propsDefaultDepartments,
      selected_group_departments: [],
      group_departments: [],
      groups: []
    }
  },
  watch: {
    selected_group_departments(after_val, before_val) {},
    departments(after_val, before_val) {
      //console.log('departments change:', before_val, '->', after_val)
      this.selected_departments = []
      this.selected_group_departments = []
      this.$emit('setSelectedDepartments', this.departments)
    },
    selected_departments(after_val, before_val) {}
  },
  async mounted() {
    await this.indexDatas()
    this.getAllGroups()
  },
  methods: {
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
        const res = await this.$axios.get('/api/department/two_select')
        this.group_departments = res.data
        this.checkGroupDepartments()
        console.log('Index : record num=' + this.group_departments.length)
      } catch (e) {
        console.log('Error : ' + e.response.data)
      }
    },
    addDepartments() {
      console.log(this.selected_group_departments)
      for (let i = 0; i < this.selected_group_departments.length; i++) {
        const selected_group_department = this.selected_group_departments[i]
        const searchKey = this.departments.findIndex(
          item => item.value === selected_group_department.value
        )
        // 検索にヒットしなければ、追加
        if (searchKey === -1) {
          this.departments.push(selected_group_department)
          const deleteKey = this.group_departments.findIndex(
            item => item.value === selected_group_department.value
          )
          // 検索にヒットすれば、削除
          if (deleteKey !== -1) {
            this.group_departments.splice(deleteKey, 1)
          }
        }
      }
    },
    deleteDepartments() {
      console.log(this.selected_departments)
      for (let i = 0; i < this.selected_departments.length; i++) {
        const selected_department = this.selected_departments[i]
        const searchKey = this.group_departments.findIndex(
          item => item.value === selected_department.value
        )
        // 検索にヒットしなければ、追加
        if (searchKey === -1) {
          this.group_departments.push(selected_department)
          const deleteKey = this.departments.findIndex(
            item => item.value === selected_department.value
          )
          // 検索にヒットすれば、削除
          if (deleteKey !== -1) {
            this.departments.splice(deleteKey, 1)
          }
        }
      }
    },
    checkGroupDepartments() {
      var nowDepartments = this.departments
      var nowGroupDepartments = this.group_departments
      var deleteIndex = []
      nowDepartments.forEach(function(nowDepartment) {
        const searchKey = nowGroupDepartments.findIndex(
          nowGroupDepartment => nowGroupDepartment.value === nowDepartment.value
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
        nowGroupDepartments.splice(index, 1)
      })
      this.group_departments = nowGroupDepartments
    },
    //再び初期化するときに実行する関数
    async setInit(departments) {
      this.departments = departments
      await this.indexDatas()
      this.getAllGroups()
    }
  }
}
</script>
