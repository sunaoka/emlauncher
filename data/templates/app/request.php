<div class="media">
  <p class="pull-left">
    <a href="<?=url("/app?id={$app->getId()}")?>">
      <img class="app-icon media-object img-rounded" src="<?=$app->getIconUrl()?>">
    </a>
  </p>
  <div class="media-body">
    <h2 class="media-hedding"><a href="<?=url("/app?id={$app->getId()}")?>"><?=htmlspecialchars($app->getTitle())?></a></h2>
    <p><?=nl2br(htmlspecialchars($app->getDescription()))?></p>
  </div>
</div>

<div class="row">
  <div class="col-sm-4 col-md-3 hidden-xs">
    <?=block('app_infopanel')?>
  </div>

  <div class="col-xs-12 col-sm-8 col-md-9">

    <div id="requests">
      <h3><?=$request_count?> requests</h3>
      <ul class="list-group">
<?php
foreach($requests as $c):
    $pkg = ($c->getPackageId())? $requested_package[$c->getPackageId()]: null;
    $request_page = floor(($request_count-$c->getNumber())/$requests_in_page)+1;
?>
        <li class="list-group-item" id="request-<?=$c->getNumber()?>">
          <dl>
            <dt><a href="<?=url("/app/request?id={$app->getId()}&page=$request_page#request-{$c->getNumber()}")?>"><?=$c->getNumber()?></a></dt>
            <dd><?=htmlspecialchars($c->getMessage())?></dd>
          </dl>
          <div class="text">
            <span>UDID: <?=$c->getDeviceUDID()?></SPAN>
          </div>
          <div class="text-right">
<?php if($pkg): ?>
            <a href="<?=url("/package?id={$pkg->getId()}")?>">
              <?=block('platform_icon',array('package'=>$pkg))?> <?=htmlspecialchars($pkg->getTitle())?></a>
<?php else: ?>
            <span>No package requested</span>
<?php endif ?>
            (<?=$c->getCreated('Y-m-d H:i')?>)
          </div>
        </li>
<?php endforeach ?>
      </ul>
    </div>

    <div class="text-center">
      <?=block('paging',array('urlbase'=>mfwRequest::url()))?>
    </div>

  </div>
</div>

<div class="visible-xs">
  <?=block('app_infopanel')?>
</div>

<script type="text/javascript">

$('#request-form form').submit(function(){
 var msg = $('textarea[name="message"]',this).val();
 if(msg.length==0){
   $('#alert-nomessage').removeClass('hidden');
   return false;
 }
 return true;
});


</script>
