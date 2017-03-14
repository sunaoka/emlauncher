
<div id="user-create-dialog" class="col-sm-6 col-sm-offset-3">

  <div class="panel panel-success">
    <div class="panel-heading">
      <h2 class="panel-title">Completed</h2>
    </div>
    <div class="panel-body">
      <p>新規ユーザーの登録が完了しました。</p>
      <?php if($sendResetMail){?>
      <p>パスワードが設定されていないので、パスワードリセットのメールを送信しました。</p>
      <?php }?>
      <a href="<?=url('/top')?>" class="btn btn-primary">Top</a>
    </div>
  </div>

</div>

