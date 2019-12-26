export const state = () => ({
  message: '',
  color: 'info'
})

export const getters = {
  getMessage: state => state.message,
  getColor: state => state.color,
  existsMessage: state => state.message !== ''
}

export const mutations = {
  setMessage(state, message) {
    state.message = message
  },
  setColor(state, color) {
    state.color = color
  },
  clearMessage(state) {
    state.message = ''
    state.color = ''
  }
}

export const actions = {
  setMessage({ commit }, message) {
    commit('setMessage', message)
  },
  setError({ commit }) {
    commit('setColor', 'error')
  },
  setWarning({ commit }) {
    commit('setColor', 'warning')
  },
  setInfo({ commit }) {
    commit('setColor', 'info')
  },
  setSuccess({ commit }) {
    commit('setColor', 'success')
  },
  clearMessage({ commit }) {
    commit('clearMessage')
  }
}
