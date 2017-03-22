
<div id="user-create-dialog" class="col-sm-6 col-sm-offset-3">

  <div class="panel panel-danger">
    <div class="panel-heading">
      <h2 class="panel-title">Install Provisioning Profile</h2>
    </div>
    <div class="panel-body">
      <p>ホーム画面のWebClip(ショートカット)からアクセスするか、</p>
      <p>デバイスに以下のプロファイルをインストールしてホーム画面のアイコンからアクセスしてください。</p>
      <a href="<?=url('/profile/download?device_uuid='.$device_uuid)?>" class="btn btn-primary">プロファイルをインストール</a>
    </div>
  </div>

</div>

<script type="text/javascript">
'use strict';
function handleVisibilitychange (event) {
  var doc = event.target;

  if ( doc.visibilityState === 'visible' ) {
    //alert(event.target.visibilityState);
    //doc.getElementById('play-sound').play();
    location.href = "<?=url('/profile/done?device_uuid='.$device_uuid)?>"
  }
}

function handleFocus (event) {
  //alert(event.type);
  //event.currentTarget.document.getElementById('play-sound').play();
  location.href = "<?=url('/profile/done?device_uuid='.$device_uuid)?>"
}

if ('visibilityState' in document) {
  document.addEventListener('visibilitychange', handleVisibilitychange, false);
} else if ('onfocus' in this) {
  addEventListener('focus', handleFocus, false);
}
</script>
