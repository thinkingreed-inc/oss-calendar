export const state = () => ({
  params: {}
})

export const getters = {
  getParams(state) {
    return state.params
  }
}

export const mutations = {
  setParams(state, params) {
    state.params = params
  },
  clearParams(state) {
    state.params = {}
  }
}

export const actions = {
  setParams({ commit }, params) {
    commit('setParams', params)
  },
  clearParams({ commit }) {
    commit('clearParams')
  }
}
