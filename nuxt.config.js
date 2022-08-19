import fs from 'fs-extra'
import path from 'path'
require('dotenv').config()
const url = require('url')
const client_url = url.parse(process.env.CLIENT_URL)
const app_url = url.parse(process.env.APP_URL)
const copyHtaccessFile = 'copy/.htaccess'
const desHtaccessFile = 'public/.htaccess'

module.exports = {
  ssr: false,
  srcDir: 'resources/nuxt/',
  server: {
    port: client_url.port || '3000', // デフォルト: 3000
    host: '0.0.0.0' // 0.0.0.0で固定
  },
  router: {
    base: client_url.path || '/'
  },
  axios: {
    baseURL: process.env.APP_URL || 'http://localhost',
    browserBaseURL: process.env.APP_URL || 'http://localhost'
  },
  // 静的ファイルとしてビルド時の書き出し先（この場合はstorage/app/nuxt/以下に書き出される）
  generate: {
    dir: 'storage/app/nuxt'
  },
  hooks: {
    build: {
      done(nuxt) {
        // copy/.htaccessファイルをpublic/.htaccessにコピーし、baseurlを設定する。
        fs.copyFileSync(copyHtaccessFile, desHtaccessFile)
        fs.readFile(desHtaccessFile, 'utf8', function(err, data) {
          if (err) {
            return console.log(err)
          }
          const base_path = app_url.path || '/'
          var result = data.replace(/base_path/g, base_path)
          fs.writeFile(desHtaccessFile, result, 'utf8', function(err) {
            if (err) return console.log(err)
          })
        })
        console.log('√Copyed .htaccess!')
      }
    },
    generate: {
      async done(nuxt) {
        // storage/app/nuxt/_nuxtフォルダをpublic/_nuxtに移動する。
        const dir = nuxt.options.generate.dir
        const publicPath = path.resolve(
          'public' + nuxt.options.build.publicPath
        )
        fs.moveSync(
          path.resolve(dir + nuxt.options.build.publicPath),
          publicPath,
          {
            overwrite: true
          }
        )
        // storage/app/nuxt/の各index.htmlは全て同じ内容のため、storage/app/nuxt/index.htmlのみpublic/_nuxtに書き込みする。
        const { html } = await nuxt.nuxt.renderer.renderRoute('/', { url: '/' })
        fs.writeFileSync(path.resolve(publicPath, 'index.html'), html)

        fs.removeSync(path.resolve(dir))
        console.log('√Generated public/_nuxt')
      }
    }
  },
  // ソースファイルのディレクトリ
  // デフォルトからディレクトリ構成を変える場合は指定
  // srcDir: 'nuxt/',
  /*
   ** Headers of the page
   */
  head: {
    title: process.env.APP_NAME,
    meta: [
      { charset: 'utf-8' },
      { name: 'viewport', content: 'width=device-width, initial-scale=1' },
      { hid: 'description', name: 'description', content: process.env.APP_NAME }
    ],
    link: [
      { rel: 'icon', type: 'image/x-icon', href: '/favicon.ico' },
      {
        rel: 'stylesheet',
        href:
          'https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Material+Icons'
      }
    ]
  },
  plugins: [
    '~/plugins/vuetify.js',
    '~/plugins/vee-validate.js',
    '~/plugins/axios.js',
    '~/plugins/acl.js',
    '~/plugins/util.js',
    '~/plugins/routerOption.js',
    '~/plugins/variables.js'
  ],
  css: [
    { src: '~/assets/style/app.styl', lang: 'styl' },
    { src: '@fullcalendar/core/main.css', lang: 'scss' },
    { src: '@fullcalendar/daygrid/main.css', lang: 'scss' },
    { src: '@fullcalendar/timegrid/main.css', lang: 'scss' },
    { src: '@fullcalendar/timeline/main.css', lang: 'scss' },
    { src: '@fullcalendar/resource-timeline/main.css', lang: 'scss' },
    { src: '~/assets/style/global.scss', lang: 'scss' }
  ],
  /*
   ** Customize the progress bar color
   */
  /*loading: { color: '#3B8070' },*/
  loading: '~/components/Loading.vue',
  /*
   ** Build configuration
   */
  build: {
    extractCSS: true,
    extend(config) {
      if (process.server && process.browser) {
        config.module.rules.push({
          enforce: 'pre',
          test: /\.(js|vue)$/,
          loader: 'eslint-loader',
          exclude: /(node_modules)/
        })
      }
    },
    terser: {
      terserOptions: {
        compress: {
          drop_console:
            process.env.NODE_ENV === 'production' ||
            process.env.NODE_ENV === 'training'
        }
      }
    }
  },
  // ライブラリの読込
  modules: ['@nuxtjs/axios', '@nuxtjs/auth', '@nuxtjs/dotenv'],
  auth: {
    login: '/auth/login',
    logout: '/',
    strategies: {
      local: {
        endpoints: {
          login: {
            url: '/api/auth/login',
            method: 'post',
            propertyName: 'access_token'
          },
          logout: { url: '/api/auth/logout', method: 'post' },
          user: { url: '/api/myself', method: 'get', propertyName: '' }
        },
        tokenRequired: true,
        tokenType: 'Bearer' // Case sensitive when dealing with Laravel backend.
      }
    },
    redirect: {
      // login は 未ログイン時に認証ルートへアクセスした際のリダイレクトURLです。
      login: '/auth/login',
      // logout はログアウト時のリダイレクトURLです。
      logout: '/auth/login',
      // callback はOauth認証等で必要となる コールバックルートです。
      callback: '/callback',
      // home はログイン後のリダイレクトURLです。
      home: false
    }
  }
}
