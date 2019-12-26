<template>
  <div class="list">
    <h2>{{ title }}</h2>
    <div class="management-tablewrap">
      <!-- データテーブル部分
        rows-per-page-text="" 1ページあたり件数テキストの非表示
        :rows-per-page-items="[]" 1ページあたり件数セレクトの非表示
       -->
      <v-data-table
        :headers="headers"
        :items="dataList"
        :options.sync="options"
        class="elevation-1"
        :loading="tableLoading"
        :server-items-length="totalCount"
      >
        <template v-slot:top>
          <v-toolbar flat color="white">
            <!-- ユーザ追加ボタン フォームは下部 -->
            <v-btn
              small
              color="dark"
              class="mb-2"
              @click="
                selectedObj = {}
                dialogProfile = true
              "
            >
              追加
            </v-btn>

            <v-spacer></v-spacer>

            <v-text-field
              v-model="search"
              append-icon="search"
              label="検索"
              placeholder="検索"
              single-line
            ></v-text-field>

            <v-spacer></v-spacer>

            <!-- リスト更新ボタン -->
            <v-icon @click="refresh()">refresh</v-icon>
          </v-toolbar>
        </template>

        <template v-slot:item.is_enable="props">
          {{ getFilterdText(isEnableDatas, props.item.is_enable) }}
        </template>

        <template v-slot:item.action="props">
          <!-- 編集ボタン -->
          <v-icon
            class="mr-2"
            @click="
              selectedObj = props.item
              dialogProfile = true
            "
          >
            edit
          </v-icon>
          <!-- 有効無効ボタン -->
          <v-icon
            @click="
              selectedObj = props.item
              dialogEnableDisable = true
            "
          >
            how_to_reg
          </v-icon>
          <!-- 削除ボタン -->
          <v-icon
            @click="
              selectedObj = props.item
              dialogDestroy = true
            "
          >
            delete
          </v-icon>
        </template>

        <!-- 検索結果なし -->
        <template v-slot:no-results :value="true">
          "{{ search }}" の検索結果なし
        </template>
        <!-- データなしの時の表示 -->
        <template v-slot:no-data>
          データはありません。
        </template>

        <!-- フッターの件数表示 -->
        <template v-slot:pageText="props">
          {{ props.itemsLength }} 件中 {{ props.pageStart }} 件目 〜
          {{ props.pageStop }} 件目
        </template>
      </v-data-table>

      <!-- 削除確認ダイアログのレイアウト -->
      <data-destroy
        props-model-url="/api/department/admin/"
        :props-dialog="dialogDestroy"
        :props-selected-obj="selectedObj"
        @passByReference="copyByRefToTargetObj"
        @refresh="indexDatas"
        @close="close"
      ></data-destroy>

      <!-- 新規登録、編集のダイアログのレイアウト -->
      <department-profile
        :props-dialog="dialogProfile"
        :props-selected-obj="selectedObj"
        @passByReference="copyByRefToTargetObj"
        @refresh="indexDatas"
        @close="close"
      ></department-profile>

      <!-- 有効無効ダイアログのレイアウト -->
      <data-enable-disable
        props-model-url="/api/department/admin/"
        props-model-name="Department"
        :props-dialog="dialogEnableDisable"
        :props-selected-obj="selectedObj"
        @passByReference="copyByRefToTargetObj"
        @close="close"
      ></data-enable-disable>
    </div>
  </div>
</template>
<script>
import DepartmentProfile from '~/components/dialogs/department/admin/DepartmentProfile'
import DataEnableDisable from '~/components/dialogs/DataEnableDisable'
import DataDestroy from '~/components/dialogs/DataDestroy'

export default {
  middleware: 'auth',
  components: {
    DepartmentProfile,
    DataEnableDisable,
    DataDestroy
  },
  data() {
    return {
      title: '部署管理',
      // データ一覧が入る配列
      dataList: [],
      // テーブルヘッダ
      headers: [
        { text: '部署', align: 'center', value: 'name' },
        { text: '表示順', align: 'center', value: 'rank' },
        { text: '状態', align: 'center', value: 'is_enable' },
        { text: '操作', align: 'center', value: 'action', sortable: false }
      ],
      isEnableDatas: [
        { text: '有効', value: true },
        { text: '無効', value: false }
      ],

      totalCount: 1,
      tableLoading: true,
      options: {},

      dialogProfile: false,
      dialogEnableDisable: false,
      dialogDestroy: false,

      // 選択中のオブジェクト
      selectedObj: {},
      search: '' // フィルタリング検索キーワード
    }
  },
  watch: {
    options: {
      handler() {
        this.indexDatas()
      },
      deep: true
    },
    search(after_val, before_val) {
      this.indexDatas(after_val)
    }
  },
  mounted() {
    this.indexDatas()
  },

  methods: {
    // データ一覧
    async indexDatas(search = null) {
      const { sortBy, sortDesc, page, itemsPerPage } = this.options
      const options = {
        sortBy: sortBy[0],
        sortDesc: sortDesc[0],
        page: page,
        itemsPerPage: itemsPerPage,
        search: search
      }
      this.tableLoading = true
      try {
        const res = await this.$axios.get('/api/department/admin', {
          params: options
        })
        this.dataList = res.data.data
        this.totalCount = res.data.total
        console.log('Index : record num=' + this.dataList.length)
      } catch (e) {
        console.log('Error : ' + e.response.data)
      }
      this.tableLoading = false
    },
    // 一覧の最新化
    refresh() {
      console.log('Refresh')
      this.indexDatas()
    },
    // ダイアログのキャンセルボタン
    close() {
      this.dialogProfile = false
      this.dialogEnableDisable = false
      this.dialogDestroy = false

      this.initialValidate()
      this.selected = this.deepCopy(this.defaultValue)

      this.selectedObj = {}

      setTimeout(() => {
        // this.selected = Object.assign({}, this.defaultValue)
      }, 500)
    },
    async filterText(value, search, item) {
      console.log('value', value)
      console.log('search', search)
      if (value != null && search != null && typeof value === 'string') {
        await this.indexDatas(search)
        return true
      } else {
        return false
      }
    }
  }
}
</script>

<style scoped></style>
