<template>
    <div class="c-panel u-color__bg--white">

        <div class="p-status">
            <p class="p-status__show">{{serviceStatusLabel}}</p>
            <button class="p-status__button c-button c-button--success"
                    v-show="showRunButton"
                    @click.stop="runFollowService">サービス開始
            </button>
            <button class="p-status__button c-button c-button--danger"
                    v-show="showStopButton"
                    @click.stop="stopFollowService">停止
            </button>
        </div>


        <div class="p-table__title">
            <h2 class="p-table__caption">○ターゲットアカウントリスト</h2>
            <button class="c-button c-button--twitter" @click="newModal = ! newModal">
                <i class="c-icon c-icon--white fas fa-plus"></i>
                ターゲットを追加
            </button>
        </div>
        <p class="p-dashboard__notion">※ 自動フォロー機能は全てのターゲットアカウントリストで作成された「フォロワーターゲットリスト」のフォローが完了した場合、自動的にフォロー完了メールが送られます。</p><br />
        <p class="p-dashboard__notion">※ 自動フォロー機能でキーワードを設定した場合は、ターゲットアカウントのフォロワーから登録されたキーワードがTwitterのプロフィールに含まれたユーザーを抽出し、自動フォローするための「フォロワーターゲットリスト」を作成します。プロフィール欄に除外ワードが含まれていた場合のターゲットアカウントのフォロワーは、抽出しません。</p><br />
        <p class="p-dashboard__notion">※ 「フォロワーターゲットリスト」は自動フォロー機能でフォローするTwitterアカウントのリストです。</p><br />
        <p class="p-dashboard__notion">※ 「フォロワーターゲットリスト」は日本語の５０音がプロフィールに含まれていないアカウントと自動アンフォロー機能でアンフォローしたアカウントと自動フォロー機能でフォローしてから30日以内にフォロー済みのアカウントを「フォロワーターゲットリスト」には含めません。</p><br />
        <p class="p-dashboard__notion">※ ターゲットアカウントリストのステータスが「リスト作成中」の時に削除すると、自動フォロー機能を開始している場合は途中まで作成された「フォロワーターゲットリスト」から自動でフォローします。</p><br />
        <table class="p-table">
            <tr class="p-table__head">
                <th class="p-table__th p-table__th--follow">ステータス</th>
                <th class="p-table__th p-table__th--follow">ターゲット</th>
                <th class="p-table__th p-table__th--follow">条件</th>
                <th class="p-table__th p-table__th--follow">操作</th>
            </tr>

            <tr class="p-table__tr" v-for="(followTarget, index) in followTargets" :key='index'>
                <td aria-label="ステータス" class="p-table__td">{{followTarget.status_label}}</td>
                <td aria-label="ターゲット" class="p-table__td">@{{followTarget.target}}</td>
                <td aria-label="条件" class="p-table__td">{{followTarget.filter_word.merged_word}}</td>
                <td class="p-table__btnarea">
                    <template v-if="followTarget.status_label !== 'リスト作成済' ">
                        <button class="c-button c-button--twitter p-table__button"
                                @click.stop="showEditModal(followTarget, index)"
                                v-if="followTarget.status_label !== 'リスト作成中' "
                        >編集
                        </button>
                        <button class="c-button c-button--danger p-table__button"
                                @click.stop="removeFollowTarget(followTarget.id, index)"
                        >削除
                        </button>
                    </template>
                </td>
            </tr>
        </table>

        <div class="p-modal__wrapper">
            <section class="p-modal p-modal--opened" v-show="newModal">
                <div class="p-modal__contents">
                            <span class="p-modal__cancel u-color__bg--white" @click="newModal = ! newModal">
                                <i class="c-icon--gray p-modal__icon fas fa-times"></i>
                            </span>
                    <form class="p-form" @submit.prevent="addFollowTarget">

                        <div v-if="addErrors" class="p-form__errors">
                            <ul v-if="addErrors.target">
                                <li v-for="msg in addErrors.target" :key="msg">{{ msg }}</li>
                            </ul>
                            <ul v-if="addErrors.filter_word_id">
                                <li v-for="msg in addErrors.filter_word_id" :key="msg">{{ msg }}</li>
                            </ul>
                        </div>

                        <label class="p-form__label" for="add-target">ターゲット名 *必須</label>
                        <input type="text" class="p-form__item" id="add-target"
                               v-model="addForm.target" required maxlength="15" placeholder="例) kamitter_1111">

                        <label class="p-form__label" for="add-target_filter_id">フォロー条件の選択 *必須</label>
                        <select class="p-form__select" id="add-target_filter_id"
                                v-model="addForm.filter_word_id"
                                required
                        >
                            <option v-for="(filter,index) in filters" :value="filter.id" :key='index'>{{filter.merged_word}}</option>
                            <optgroup></optgroup>
                        </select>
                        <p class="p-form__notion">※ターゲット名は、Twitterのユーザー名の「@」を除いた部分を入力してください</p>
                        <p class="p-form__notion">※ターゲット名は、非公開ユーザー（鍵アカウント）を指定できません。</p>
                        <p class="p-form__notion">※条件のキーワードは、「キーワード登録」から登録することができます。</p>
                        <div class="p-form__button">
                            <button type="submit" class="c-button c-button--twitter">追加</button>
                        </div>
                    </form>
                </div>
            </section>

            <section class="p-modal p-modal--opened" v-show="editModal">
                <div class="p-modal__contents">
                            <span class="p-modal__cancel u-color__bg--white" @click="editModal = ! editModal">
                                <i class="c-icon--gray p-modal__icon fas fa-times"></i>
                            </span>
                    <form class="p-form" @submit.prevent="editFollowTarget">

                        <div v-if="editErrors" class="p-form__errors">
                            <ul v-if="editErrors.target">
                                <li v-for="msg in editErrors.target" :key="msg">{{ msg }}</li>
                            </ul>
                            <ul v-if="editErrors.filter_word_id">
                                <li v-for="msg in editErrors.filter_word_id" :key="msg">{{ msg }}</li>
                            </ul>
                        </div>

                        <label class="p-form__label" for="edit-target">ターゲット名 *必須</label>
                        <input type="text" class="p-form__item" id="edit-target"
                               v-model="editForm.target" required maxlength="15" placeholder="例) kamitter_1234">

                        <label class="p-form__label" for="eidt-target_filter_id">キーワード条件の選択 *必須</label>
                        <select class="p-form__select" id="eidt-target_filter_id"
                                v-model="editForm.filter_word_id"
                                required
                        >
                            <option v-for="(filter,index) in filters" :key="index" :value="filter.id" >{{filter.merged_word}}</option>
                            <optgroup></optgroup>
                        </select>
                        <p class="p-form__notion">※ターゲット名は、Twitterのユーザー名の「@」を除いた部分を入力してください</p>
                        <p class="p-form__notion">※ターゲット名は、非公開ユーザー（鍵アカウント）を指定できません。</p>
                        <p class="p-form__notion">※条件のキーワードは、「キーワード登録」から登録することができます。</p>
                        <div class="p-form__button">
                            <button type="submit" class="c-button c-button--twitter">編集</button>
                        </div>
                    </form>
                </div>
            </section>

        </div>

    </div>
</template>

<script>
    import {CREATED, OK, UNPROCESSABLE_ENTRY} from "../utility"
    export default {
        data() {
            return {
                followTargets: [],
                filters: [],
                newModal: false,
                editModal: false,
                editIndex: null,
                serviceStatus: null,
                serviceStatusLabel: null,
                addErrors: null,
                editErrors: null,
                addForm: {
                    target: null,
                    filter_word_id: null,
                },
                editForm: {
                    id: null,
                    target: null,
                    filter_word_id: null,
                },
            }
        },
        computed: {
            /**
             * フィルターキワードの追加、変更、削除イベントの通知を取得する
             */
            dashChange() {
                return this.$store.state.dashboard.noticeToTweet
            },
            showRunButton() {
                return this.serviceStatus === 1 || this.serviceStatus === 3
            },
            showStopButton() {
                return this.serviceStatus === 2 || this.serviceStatus === 3
            }
        },
        methods: {
            /**
             * 登録したフォローターゲット一覧を取得する
             */
            async fetchFollowTargets() {
                const response = await axios.get('/api/follow')
                if (response.status !== OK) {
                    this.$store.commit('error/setCode', response.status)
                    return false
                }
                this.followTargets = response.data
            },
            /**
             * フィルターワード一覧を取得する
             */
            async fetchFilters() {
                const response = await axios.get('/api/filter')
                if (response.status !== OK) {
                    this.$store.commit('error/setCode', response.status)
                    return false
                }
                this.filters = response.data
            },
            /**
             * 新規フォローターゲットを追加する
             */
            async addFollowTarget() {
                this.clearErrors()
                const response = await axios.post('/api/follow', this.addForm)
                if (response.status === UNPROCESSABLE_ENTRY) {
                    this.addErrors = response.data.errors
                    return false
                }
                this.resetAddForm()
                if (response.status !== CREATED) {
                    this.$store.commit('error/setCode', response.status)
                    return false
                }
                this.followTargets.unshift(response.data)
                this.newModal = false
            },
            /**
             * 編集用のモーダルフォームを表示する
             * 表示の際にフォローターゲットのデータを入力しておく
             */
            showEditModal(followTarget, index) {
                this.editModal = true
                this.editForm.id = followTarget.id
                this.editForm.target = followTarget.target
                this.editForm.filter_word_id = followTarget.filter_word_id
                this.editIndex = index
            },
            /**
             * フォーローターゲットを編集する
             */
            async editFollowTarget() {
                this.clearErrors()
                const response = await axios.put(`/api/follow/${this.editForm.id}`, this.editForm)
                if (response.status === UNPROCESSABLE_ENTRY) {
                    this.editErrors = response.data.errors
                    return false
                }
                if (response.status !== OK) {
                    this.$store.commit('error/setCode', response.status)
                    return false
                }
                this.followTargets.splice(this.editIndex, 1, response.data)
                this.resetEditForm()
            },
            /**
             * フォローターゲットを削除する
             */
            async removeFollowTarget(id, index) {
                const response = await axios.delete(`/api/follow/${id}`)
                if (response.status !== OK) {
                    this.$store.commit('error/setCode', response.status)
                    return false
                }
                this.followTargets.splice(index, 1)
            },
            /**
             * 新規追加フォームのデータを空にする
             */
            resetAddForm() {
                this.addForm.target = null
                this.addForm.filter_word_id = null
            },
            /**
             * 編集フォームのデータを空にする
             */
            resetEditForm() {
                this.editModal = null
                this.editForm.id = null
                this.editForm.target = null
                this.editForm.filter_word_id = null
                this.editIndex = null
            },
            /**
             * 自動フォロー機能のサービスステータスを取得する
             */
            async fetchServiceStatus() {
                const response = await axios.get('/api/system/status')
                if (response.status !== OK) {
                    this.$store.commit('error/setCode', response.status)
                    return false
                }
                this.serviceStatus = response.data.auto_follow_status
                this.serviceStatusLabel = response.data.status_labels.auto_follow
            },
            /**
             * 自動フォロー機能を稼働状態にする
             */
            async runFollowService() {
                const serviceType = 1
                const data = {type: serviceType}
                const response = await axios.post('/api/system/run', data)
                if (response.status !== OK) {
                    this.$store.commit('error/setCode', response.status)
                    return false
                }
                this.serviceStatus = response.data.auto_follow_status
                this.serviceStatusLabel = response.data.status_labels.auto_follow
            },
            /**
             * 自動フォロー機能を停止状態にする
             */
            async stopFollowService() {
                const serviceType = 1
                const data = {type: serviceType}
                const response = await axios.post('/api/system/stop', data)
                if (response.status !== OK) {
                    this.$store.commit('error/setCode', response.status)
                    return false
                }
                this.serviceStatus = response.data.auto_follow_status
                this.serviceStatusLabel = response.data.status_labels.auto_follow
            },
            /**
             * 入力フォームエラーメッセージをクリアする
             */
            clearErrors() {
                this.addErrors = null
                this.editErrors = null
            }
        },
        created() {
            this.fetchFollowTargets()
            this.fetchFilters()
            this.fetchServiceStatus()
        },
        watch: {
            /**
             * フィルターワードの通知を受け取ったら
             * フォロワーターゲットと、フィルターワードを再取得する
             */
            dashChange: {
                handler(val) {
                    if (val === true) {
                        this.fetchFollowTargets()
                        this.fetchFilters()
                        this.$store.commit('dashboard/setNoticeToTweet', null)
                    }
                }
            },
        },
    }
</script>
