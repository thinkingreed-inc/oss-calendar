/* eslint-disable vue/require-valid-default-prop */
<template>
  <v-layout row justify-center>
    <v-dialog v-model="dialog" persistent max-width="100%">
      <v-form lazy-validation>
        <v-card>
          <v-card-title>
            <span class="headline"
              ><slot name="title">{{ title }}</slot></span
            >
          </v-card-title>
          <v-card-text>
            <v-container grid-list-md>
              <v-layout wrap>
                <v-flex class="xs8">
                  <v-text-field
                    v-model="selected.name"
                    v-validate="'required'"
                    label="部署"
                    data-vv-name="name"
                    :error-messages="errors.collect('name')"
                    counter="100"
                    required
                    data-vv-as="部署"
                  >
                    <template v-slot:label><required-label propsLabelName="部署" /></template>
                  </v-text-field>
                </v-flex>

                <!-- 所属ユーザー -->
                <v-flex class="xs12">
                  <two-user-select
                    ref="users"
                    :props-default-users="selected.users"
                    @setSelectedUsers="setSelectedUsers"
                  />
                </v-flex>

                <v-flex class="xs6">
                  <v-text-field
                    v-model="selected.rank"
                    v-validate="'required|numeric'"
                    label="表示順"
                    data-vv-name="rank"
                    :error-messages="errors.collect('rank')"
                    required
                    data-vv-as="表示順"
                  >
                    <template v-slot:label><required-label propsLabelName="表示順" /></template>
                  </v-text-field>
                </v-flex>
              </v-layout>
            </v-container>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <single-submit-button :disabled="errors.any()" flat :onclick="save">
              <template v-slot:text>
                保存
              </template>
            </single-submit-button>
            <v-btn color="blue darken-1" text @click="close">キャンセル</v-btn>
          </v-card-actions>
        </v-card>
      </v-form>
    </v-dialog>
  </v-layout>
</template>

<script>
import RequiredLabel from '~/components/label/RequiredLabel'
import TreeDepartmentRadio from '~/components/TreeDepartmentRadio'
import SingleSubmitButton from '~/components/buttons/SingleSubmitButton'
import TwoUserSelect from '~/components/TwoUserSelect'

var defaultDepartmentTopValue = 0
var defaultDepartmentTopLabel = '階層TOP'

export default {
  components: {
    RequiredLabel,
    TreeDepartmentRadio,
    SingleSubmitButton,
    TwoUserSelect
  },
  props: {
    propsDialog: {
      type: Boolean,
      default: false
    },
    propsSelectedObj: {
      type: Object,
      default() {
        return {}
      }
    }
  },
  data() {
    return {
      dialog: false,
      // 選択中のレコードの値
      selected: {},
      // レコードの初期値
      defaultValue: {
        id: -1,
        name: '',
        parent_id: 0,
        rank: 1,
        departments: [
          {
            value: defaultDepartmentTopValue,
            label: defaultDepartmentTopLabel
          }
        ],
        users: [
          {
            value: this.$auth.user.id,
            text: this.$auth.user.lastname + ' ' + this.$auth.user.firstname
          }
        ]
      }
    }
  },
  computed: {
    // ダイアログのタイトルを作成と更新で使い分ける
    title() {
      return this.selected.id === -1 ? '新規登録' : '編集'
    }
  },
  watch: {
    propsDialog(after_val, before_val) {
      if (after_val) {
        if (Object.keys(this.propsSelectedObj).length === 0) {
          this.create()
        } else {
          this.edit()
        }
      }
    }
  },
  created() {
    this.selected = this.deepCopy(this.defaultValue)
  },
  methods: {
    // データ作成(読み出し)
    async create() {
      try {
        console.log('Created : ')
        this.dialog = true
      } catch (e) {
        console.log('Error : ' + e.response.data)
      }

      // 部門マスタデータ取得 & セット
      this.getDepartments()
    },
    // データ作成(書き込み)
    async store() {
      try {
        const res = await this.$axios.post(
          '/api/department/admin',
          this.selected
        )
        this.selected = res.data
        console.log('stored : id=' + res.data.id)
        this.$emit('refresh')
      } catch (e) {
        throw e
      }
    },

    // データ編集(読み出し)
    async edit() {
      try {
        const res = await this.$axios.get(
          '/api/department/admin/' + this.propsSelectedObj.id
        )
        this.copyByRefToTargetObj(this.selected, res.data)
        this.dialog = true
        console.log('Edit : id=' + this.selected.id)
      } catch (e) {
        console.log('Error : ' + e.response.data)
      }

      // 所属ユーザー取得
      this.getUserOfDepartment(this.selected.id)

      // 部門マスタデータ取得 & セット
      this.getDepartments()
    },

    // データ編集(書き込み)
    async update() {
      try {
        const res = await this.$axios.put(
          '/api/department/admin/' + this.selected.id,
          this.selected
        )
        // 対象一覧の更新
        this.$emit('passByReference', this.propsSelectedObj, res.data)
        console.log('Updated : id=' + res.data.id)
      } catch (e) {
        throw e
      }
    },

    // ダイアログの保存ボタン
    async save() {
      try {
        await this.localValidate()
        if (this.selected.id === -1) {
          // 作成
          await this.store()
        } else {
          // 更新
          await this.update()
        }
        this.close()
      } catch (e) {
        await this.serverSideValidate()
        console.log('validate Error : ' + e.response.data)
        console.log('validate this.dialog: ' + this.dialog)
      }
    },
    close() {
      console.log('close')
      this.initialValidate()
      this.selected = this.deepCopy(this.defaultValue)
      this.$refs.users.setInit(this.defaultValue.users) //２つのセレクトボックスの初期化
      this.dialog = false
      this.$emit('close')
    },

    // 所属ユーザー
    async getUserOfDepartment(department_id) {
      try {
        // 所属ユーザー取得
        const option = { id: department_id, join_model: 'department' }
        const res = await this.$axios.get('/api/user', { params: option })
        var departmentUsers = res.data
        // 所属ユーザーを画面セット用に変換
        var userItems = []
        departmentUsers.forEach(function(departmentUser) {
          userItems.push({
            value: departmentUser.id,
            text: departmentUser.lastname + ' ' + departmentUser.firstname
          })
        })
        // グループユーザーセット
        this.$refs.users.setInit(userItems)
      } catch (e) {
        throw e
      }
    },
    // 部署階層
    async getDepartments() {
      try {
        // 部署取得
        const res = await this.$axios.get('/api/department')
        var departments = res.data
        // 部署を画面セット用に変換
        var own_id = this.selected.id
        var departmentItems = []
        departmentItems.push({
          value: defaultDepartmentTopValue,
          label: defaultDepartmentTopLabel
        })
        departments.forEach(function(department) {
          // 自分自身の部署は選択不可のため、Itemsに入れない
          if (department.id != own_id) {
            departmentItems.push({
              value: department.id,
              label: department.name
            })
          }
        })
        // 部署をセット
        this.$refs.department.setInit(this.selected.parent_id, departmentItems)
      } catch (e) {
        console.log('Error : ' + e.response.data)
      }
    },
    // 部署階層 選択設定
    setSelectedDepartments(departments) {
      this.selected.parent_id = departments
    },
    // ユーザー選択設定
    setSelectedUsers(users) {
      this.selected.users = users
    }
  }
}
</script>
