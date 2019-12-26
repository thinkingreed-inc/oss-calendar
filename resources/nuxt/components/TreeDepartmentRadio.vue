<template>
  <v-layout wrap>
    <v-flex>
      <v-card>
        <v-card-title class="subtitle-1" style="height: 80px">
          部署階層設定
        </v-card-title>
        <v-list style="height: 200px;overflow-y: scroll">
          <v-radio-group
            v-model="selected_departments"
            data-vv-name="parent_id"
          >
            <v-radio
              v-for="(department, i) in departments"
              :key="i"
              :value="department.value"
              :label="department.label"
            >
            </v-radio>
          </v-radio-group>
        </v-list>
      </v-card>
    </v-flex>
  </v-layout>
</template>

<script>
export default {
  props: {
    propsSelectedDepartment: {
      type: Number,
      default() {
        return null
      }
    },
    propsDefaultDepartments: {
      type: Array,
      default() {
        return []
      }
    }
  },
  data() {
    return {
      selected_departments: this.propsSelectedDepartment,
      departments: this.propsDefaultDepartments
    }
  },
  watch: {
    selected_departments() {
      this.$emit('setSelectedDepartments', this.selected_departments)
    }
  },
  methods: {
    //再び初期化するときに実行する関数
    async setInit(parent_id, departments) {
      this.selected_departments = parent_id
      this.departments = departments
    }
  }
}
</script>
