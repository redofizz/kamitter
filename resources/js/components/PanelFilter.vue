<template>
    <div class="c-panel u-color__bg--white">
        <div class="p-table__title">
            <h2 class="p-table__caption">○キーワードリスト</h2>
            <button class="c-button c-button--twitter" @click="newModal = ! newModal">
                <i class="c-icon c-icon--white fas fa-plus"></i>
                キーワードを追加
            </button>
        </div>

        <p class="p-dashboard__notion">※ 自動いいね機能でキーワードを設定した場合は、登録されたキーワードをつぶやいたツイートをいいねします。除外ワードが含まれていた場合は、いいねしません。</p><br />
        <p class="p-dashboard__notion">※ 自動フォロー機能でキーワードを設定した場合は、ターゲットアカウントのフォロワーから登録されたキーワードがTwitterのプロフィールに含まれたユーザーを抽出し、「フォロワーターゲットリスト」を作成します。除外ワードが含まれていた場合のターゲットアカウントのフォロワーは、抽出しません。</p><br />
        <p class="p-dashboard__notion">※ 条件タイプが「次のワードを含む」を選んだ際は、キーワードが複数の場合には複数ワードが全て含まれる条件に該当するものが抽出されます。</p><br />
        <p class="p-dashboard__notion">※ 条件タイプが「いずれかのワードを含む」を選んだ際は、キーワードが複数の場合には複数ワードの一つでも含まれている条件に該当するものが抽出されます。</p><br />
        <p class="p-dashboard__notion">※ キーワードと除外ワードで複数ワードを指定する際は、「ツイッター 神」のようにスペースで区切ってください。</p><br />


        <table class="p-table">
            <tr class="p-table__head">
                <th class="p-table__th p-table__th--filter">条件タイプ</th>
                <th class="p-table__th p-table__th--filter">キーワード</th>
                <th class="p-table__th p-table__th--filter">除外ワード</th>
                <th class="p-table__th p-table__th--filter">操作</th>
            </tr>
            <tr class="p-table__tr" v-for="(filter, index) in filters" :key='index'>
                <td aria-label="条件タイプ" class="p-table__td">{{filter.type_label}}</td>
                <td aria-label="キーワード" class="p-table__td">{{filter.word}}</td>
                <td aria-label="除外ワード" class="p-table__td">{{filter.remove}}</td>
                <td class="p-table__btnarea">
                    <button class="c-button c-button--twitter p-table__button"
                            @click.stop="showEditModal(filter, index)"
                    >編集
                    </button>
                    <button class="c-button c-button--danger p-table__button"
                            @click.stop="removeFilter(filter.id, index)"
                    >削除
                    </button>
                </td>
            </tr>

        </table>

        <div class="p-modal__wrapper">
            <section class="p-modal p-modal--opened" v-show="newModal">
                <div class="p-modal__contents">
                            <span class="p-modal__cancel u-color__bg--white" @click="newModal = ! newModal">
                                <i class="c-icon--gray p-modal__icon fas fa-times"></i>
                            </span>
                    <form class="p-form" @submit.prevent="addFilter">

                        <div v-if="addErrors" class="p-form__errors">
                            <ul v-if="addErrors.word">
                                <li v-for="msg in addErrors.word" :key="msg">{{ msg }}</li>
                            </ul>
                            <ul v-if="addErrors.type">
                                <li v-for="msg in addErrors.type" :key="msg">{{ msg }}</li>
                            </ul>
                            <ul v-if="addErrors.remove">
                                <li v-for="msg in addErrors.remove" :key="msg">{{ msg }}</li>
                            </ul>
                        </div>

                        <label class="p-form__label" for="add-filter">条件タイプ</label>
                        <select class="p-form__select" id="add-filter" v-model="addForm.type">
                            <option value="1">次のワードを含む</option>
                            <option value="2">いずれかのワードを含む</option>
                        </select>
                        <label class="p-form__label" for="keyword">キーワード *必須</label>
                        <input type="text" class="p-form__item" id="keyword"
                               v-model="addForm.word" required maxlength="50">

                        <label class="p-form__label" for="remove_word">除外ワード</label>
                        <input type="text" class="p-form__item" id="remove_word"
                               v-model="addForm.remove" maxlength="50">
                        <p class="p-form__notion">※キーワードと除外ワードで複数ワードを指定する際は、「ツイッター 神」のようにスペースで区切ってください。</p>
                        <p class="p-form__notion">※条件タイプが「次のワードを含む」を選んだ際に、キーワードが複数の場合には複数ワードが全て含まれる条件に該当するものが抽出されます。</p>
                        <p class="p-form__notion">※条件タイプが「いずれかのワードを含む」を選んだ際に、キーワードが複数の場合には複数ワードの一つでも含まれている条件に該当するものが抽出されます。</p>
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
                    <form class="p-form" @submit.prevent="editFilter">

                        <div v-if="editErrors" class="p-form__errors">
                            <ul v-if="editErrors.word">
                                <li v-for="msg in editErrors.word" :key="msg">{{ msg }}</li>
                            </ul>
                            <ul v-if="editErrors.type">
                                <li v-for="msg in editErrors.type" :key="msg">{{ msg }}</li>
                            </ul>
                            <ul v-if="editErrors.remove">
                                <li v-for="msg in editErrors.remove" :key="msg">{{ msg }}</li>
                            </ul>
                        </div>

                        <label class="p-form__label" for="edit-filter">条件タイプ</label>
                        <select class="p-form__select" id="edit-filter" v-model="editForm.type">
                            <option value="1">次のワードを含む</option>
                            <option value="2">いずれかのワードを含む</option>
                        </select>
                        <label class="p-form__label" for="edit-keyword">キーワード ※必須</label>
                        <input type="text" class="p-form__item" id="edit-keyword"
                               v-model="editForm.word" required maxlength="50">

                        <label class="p-form__label" for="edit-remove_keyword">除外ワード</label>
                        <input type="text" class="p-form__item" id="edit-remove_keyword"
                               v-model="editForm.remove" maxlength="50">
                        <p class="p-form__notion">※キーワードと除外ワードで複数ワードを指定する際は、「ツイッター 神」のようにスペースで区切ってください。</p>
                        <p class="p-form__notion">※条件タイプが「次のワードを含む」を選んだ際は、キーワードが複数の場合には複数ワードが全て含まれる条件に該当するものが抽出されます。</p>
                        <p class="p-form__notion">※条件タイプが「いずれかのワードを含む」を選んだ際は、キーワードが複数の場合には複数ワードの一つでも含まれている条件に該当するものが抽出されます。</p>
                        <div class="p-form__button">
                            <button type="submit" class="c-button c-button--twitter">変更</button>
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
                filters: [],
                newModal: false,
                editModal: false,
                editIndex: null,
                addErrors: null,
                editErrors: null,
                addForm: {
                    type: 1,
                    word: '',
                    remove: ''
                },
                editForm: {
                    id: null,
                    type: '',
                    word: '',
                    remove: '',
                },
            }
        },
        methods: {
            /**
             * 登録したフィルターキーワード一覧をAPIで取得する
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
             * 新規フィルターキーワードをAPIで登録する
             */
            async addFilter() {
                this.clearErrors()
                const response = await axios.post('/api/filter', this.addForm)
                if (response.status === UNPROCESSABLE_ENTRY) {
                    //バリデーションエラー
                    this.addErrors = response.data.errors
                    return false
                }
                this.resetAddForm()
                if (response.status !== CREATED) {
                    //システムエラー
                    this.$store.commit('error/setCode', response.status)
                    return false
                }
                //取得したデータを格納する
                const addedFilter = response.data;
                this.filters.push(addedFilter)
                this.newModal = false
                //自動ツイート、自動いいね機能ではフィルターキーワードを参照しているので、
                //フィルターキーワードに変更があった際に変更を通知する
                this.$store.commit('dashboard/setNoticeToTweet', true)
                this.$store.commit('dashboard/setNoticeToLike', true)
            },
            /**
             * 編集フォームモーダルの表示を行って、値を入力しておく
             */
            showEditModal(filter, index) {
                this.editModal = true
                this.editForm.id = filter.id
                this.editForm.type = filter.type
                this.editForm.word = filter.word
                this.editForm.remove = filter.remove
                this.editIndex = index
            },
            /**
             * APIを利用してフィルターキーワードの変更を行う
             */
            async editFilter() {
                this.clearErrors()
                const response = await axios.put(`/api/filter/${this.editForm.id}`, this.editForm)
                if (response.status === UNPROCESSABLE_ENTRY) {
                    //バリデーションエラー
                    this.editErrors = response.data.errors
                    return false
                }
                if (response.status !== OK) {
                    //システムエラー類
                    this.$store.commit('error/setCode', response.status)
                    return false
                }
                this.filters.splice(this.editIndex, 1, response.data)
                this.resetEditForm()
                //自動ツイート、自動いいね機能ではフィルターキーワードを参照しているので、
                //フィルターキーワードに変更があった際に変更を通知する
                this.$store.commit('dashboard/setNoticeToTweet', true)
                this.$store.commit('dashboard/setNoticeToLike', true)
            },
            /**
             * APIを利用してフィルターキーワードの削除を行う
             */
            async removeFilter(id, index) {
                const response = await axios.delete(`/api/filter/${id}`)
                if (response.status !== OK) {
                    this.$store.commit('error/setCode', response.status)
                    return false
                }
                this.filters.splice(index, 1)
                this.$store.commit('dashboard/setNoticeToTweet', true)
                this.$store.commit('dashboard/setNoticeToLike', true)
            },
            /**
             * 登録フォームの入力欄を空にする
             */
            resetAddForm() {
                this.addForm.type = 1
                this.addForm.word = ''
                this.addForm.remove = ''
            },
            /**
             * 編集フォームの入力欄を空にする
             */
            resetEditForm() {
                this.editModal = false
                this.editForm.id = null
                this.editForm.type = ''
                this.editForm.word = ''
                this.editForm.remove = ''
                this.editIndex = null
            },
            /**
             * エラーメッセージを空にする
             */
            clearErrors() {
                this.addErrors = null
                this.editErrors = null
            }
        },
        created() {
            this.fetchFilters();
        },
    }
</script>
