<template>
    <div class="l-contents">

        <div class="p-contents__area--narrow">
            <section class="c-title">
                <h2 class="c-title__head">Twitter自動集客システム「神ったー」</h2>
                <span class="c-title__description">Twitterを使用して、自動でターゲットを見つけ出し、自動フォローや自動いいねをしたり、自動ツイートができるWEBサービスです。</span>
            </section>
            <section class="c-tab">
                <ul class="c-tab__list">
                    <li
                            class="c-tab__item"
                            :class="{'c-tab__item--active': tab ===1}"
                            @click="tab = 1"
                    >ログイン
                    </li>
                    <li
                            class="c-tab__item"
                            :class="{'c-tab__item--active': tab ===2}"
                            @click="tab = 2"

                    >新規登録
                    </li>
                    <li
                            class="c-tab__item"
                            :class="{'c-tab__item--active': tab ===3}"
                            @click="tab = 3"

                    >パスワード再設定
                    </li>
                </ul>
            </section>

            <section class="p-login">
                <transition-group name="tab">
                    <div class="c-panel p-login__panel u-color__bg--white" v-show="tab === 1" key="login">
                        <form class="p-form" @submit.prevent="login">
                            <div v-if="loginErrors" class="p-form__errors">
                                <ul v-if="loginErrors.email">
                                    <li v-for="msg in loginErrors.email" :key="msg">{{ msg }}</li>
                                </ul>
                                <ul v-if="loginErrors.password">
                                    <li v-for="msg in loginErrors.password" :key="msg">{{ msg }}</li>
                                </ul>
                            </div>
                            <label class="p-form__label" for="login-email">メールアドレス</label>
                            <input type="email" class="p-form__item" id="login-email"
                                   v-model="loginForm.email" required>
                            <label class="p-form__label" for="login-password">パスワード</label>
                            <input type="password" class="p-form__item" id="login-password"
                                   v-model="loginForm.password" required>
                            <label>
                                <input type="checkbox" id="login-remember" v-model="loginForm.remember">ログイン状態を保持する
                            </label>
                            <div class="p-form__button">
                                <button type="submit" class="c-button c-button--inverse">ログイン</button>
                            </div>
                        </form>
                    </div>

                    <div class="c-panel p-login__panel u-color__bg--white" v-show="tab === 2" key="register">
                        <form class="p-form" @submit.prevent="register">
                            <div v-if="registerErrors" class="p-form__errors">
                                <ul v-if="registerErrors.name">
                                    <li v-for="msg in registerErrors.name" :key="msg">{{ msg }}</li>
                                </ul>
                                <ul v-if="registerErrors.email">
                                    <li v-for="msg in registerErrors.email" :key="msg">{{ msg }}</li>
                                </ul>
                                <ul v-if="registerErrors.password">
                                    <li v-for="msg in registerErrors.password" :key="msg">{{ msg }}</li>
                                </ul>
                            </div>
                            <label class="p-form__label" for="username">ユーザー名</label>
                            <input type="text" class="p-form__item" id="username"
                                   placeholder="サンプル"
                                   v-model="registerForm.name" required>
                            <label class="p-form__label" for="email">メールアドレス</label>
                            <input type="email" class="p-form__item" id="email"
                                   placeholder="sample@sample"
                                   v-model="registerForm.email" required>
                            <label class="p-form__label" for="password">パスワード</label>
                            <input type="password" class="p-form__item" id="password"
                                   placeholder="半角英数8文字以上"
                                   v-model="registerForm.password">
                            <label class="p-form__label" for="confirm_password">パスワード(確認)</label>
                            <input type="password" class="p-form__item" id="confirm_password"
                                   placeholder=""
                                   v-model="registerForm.password_confirmation" required>
                            <div class="p-form__button">
                                <button type="submit" class="c-button c-button--inverse">登録</button>
                            </div>
                        </form>
                    </div>

                    <div class="c-panel p-login__panel u-color__bg--white" v-show="tab === 3" key="password">
                        <form class="p-form" @submit.prevent="passwordReset">
                            <div v-if="passwordError" class="p-form__errors">
                                <p>{{ passwordError.message }}</p>
                            </div>
                            <label class="p-form__label" for="login-email">メールアドレス</label>
                            <input type="email" class="p-form__item" id="password-email"
                                   v-model="passwordForm.email" required>
                            <p v-show="showMailMessage">メールを送信しました。メールボックスをご確認ください。</p>
                            <div class="p-form__button">
                                <button type="submit"
                                        class="c-button c-button--inverse"
                                        v-bind:disabled="isPush">メール送信
                                </button>
                            </div>
                        </form>
                    </div>
                </transition-group>
            </section>
        </div>
    </div>
</template>

<script>
    import {mapState} from 'vuex'
    import {OK, UNPROCESSABLE_ENTRY} from '../utility'
    export default {
        data() {
            return {
                tab: 1,
                showMailMessage: false,
                isPush: false,
                loginForm: {
                    email: '',
                    password: '',
                    remember: ''
                },
                registerForm: {
                    name: '',
                    email: '',
                    password: '',
                    password_confirmation: ''
                },
                passwordForm: {
                    email: '',
                },
                passwordError: '',
            }
        },
        computed: {
            ...mapState({
                apiStatus: state => state.auth.apiStatus,
                loginErrors: state => state.auth.loginErrorMessages,
                registerErrors: state => state.auth.registerErrorMessages,
            }),
        },
        methods: {
            /**
             * ログインAPIを使用してユーザーログインを行う
             * 正常時Twitterページに遷移
             */
            async login() {
                // authストアのloginアクションを呼び出す
                await this.$store.dispatch('auth/login', this.loginForm)
                if (this.apiStatus) {
                    // トップページに移動する
                    this.$router.push('/twitter')
                }
            },
            /**
             * ユーザー登録APIを使用してユーザー登録を行う
             * 正常時Twitterページに遷移
             */
            async register() {
                // authストアのregisterアクションを呼び出す
                await this.$store.dispatch('auth/register', this.registerForm)
                if (this.apiStatus) {
                    // トップページに移動する
                    this.$router.push('/twitter')
                }
            },
            /**
             * パスワードリマインダーAPIを使用して、リセット用のリンクをメールで送信する
             */
            async passwordReset() {
                this.clearError()
                this.$set(this, 'isPush', true)
                // パスワードリセット用メール送信APIを呼び出す
                const response = await axios.post('/api/password/create', this.passwordForm)
                this.$set(this, 'isPush', false)
                if (response.status === UNPROCESSABLE_ENTRY) {
                    //バリデーションエラー
                    this.passwordError = response.data.errors
                    return false
                } else if (response.status !== OK) {
                    //システムエラー類
                    this.$store.commit('error/setCode', response.status)
                    return false
                }
                this.passwordForm.email = ''
                this.$set(this, 'showMailMessage', true)
            },
            /**
             * エラーメッセージのリセットを行う
             */
            clearError() {
                this.$store.commit('auth/setLoginErrorMessages', null)
                this.$store.commit('auth/setRegisterErrorMessages', null)
                this.$set(this, 'passwordError', '')
            }
        },
        created() {
            this.clearError()
        },
    }
</script>
