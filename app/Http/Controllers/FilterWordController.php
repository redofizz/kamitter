<?php
namespace App\Http\Controllers;
use App\FilterWord;
use App\Http\Requests\AddFilterWord;
use Illuminate\Support\Facades\Auth;
use App\TwitterUser;
class FilterWordController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * 新規フィルターワードを登録する
     * @param AddFilterWord $request
     */
    public function add(AddFilterWord $request)
    {
        $twitter_user_id = session()->get('twitter_id');
        $this->authCheck($twitter_user_id);
        $filter = new FilterWord();
        $filter->type = $request->type;
        $filter->word = $this->adjustWordStyle($request->word);
        $filter->remove = $this->adjustWordStyle($request->remove);
        Auth::user()->filterWords()->save($filter);
        $new_filter = FilterWord::where('id', $filter->id)->with('user')->first();
        return response($new_filter, 201);
    }
    /**
     * 入力文字列の全角スペースを半角スペースに変換して、半角スペースの重複を排除する
     * @param $preAdjustWord
     */
    private function adjustWordStyle($preAdjustWord)
    {
        //全角スペースを半角スペースに変換
        $adjustedWord = mb_convert_kana($preAdjustWord, 's', 'UTF-8');
        //重複した半角スペースを1つに置換
        $adjustedWord = preg_replace('/\s+/', ' ', $adjustedWord);
        //先頭が半角スペースの場合削除する
        if (' ' === mb_substr($adjustedWord, 0, 1))
        {
            $adjustedWord = mb_substr($adjustedWord, 1);
        }
        return $adjustedWord;
    }
    /**
     * 登録したフィルターワード一覧を返す
     */
    public function show()
    {
        $user_filter = Auth::user()->filterWords()->get();
        return response($user_filter, 200);
    }
    /**
     * 指定したフィルターワードを1つ返す
     */
    public function showOneFilter(int $id)
    {
        $user_filter = Auth::user()->filterWords()->where('id', $id)->first();
        return $user_filter ?? abort(404);
    }
    /**
     * フィルターワード情報を変更する
     * @param int $id
     * @param AddFilterWord $request
     */
    public function editFilter(int $id, AddFilterWord $request)
    {
        $user_filter = Auth::user()->filterWords()->where('id', $id)->first();
        if (! $user_filter){
            abort(404);
        }
        $user_filter->type = $request->type;
        $user_filter->word = $this->adjustWordStyle($request->word);
        $user_filter->remove = $this->adjustWordStyle($request->remove);
        $user_filter->save();
        return response($user_filter, 200);
    }
    /**
     * フィルターワードを削除する
     */
    public function deleteFilter(int $id){
        $user_filter = Auth::user()->filterWords()->where('id', $id)->first();
        if (! $user_filter){
            abort(404);
        }
        $user_filter->delete();
    }
    /**
     * TwitterUserが見つからなかった場合にはNOTFOUND
     * 権限のないアクセスをした場合にはForbiddenを返す
     */
    public function authCheck($twitter_user_id)
    {
        $user_id = Auth::id();
        $twitter_user = TwitterUser::where('id', $twitter_user_id)->first();
        if (is_null($twitter_user)) {
            abort(404);
        }
        if ($twitter_user->user_id !== $user_id) {
            abort(403);
        }
    }
}
