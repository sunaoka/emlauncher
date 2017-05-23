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
    <div id="request-form">
      <form class="form-horizontal" method="post" action="<?=url('/package/request_post')?>">
        <div id="alert-nomessage" class="alert alert-danger hidden">
          コメントが入力されていません
        </div>
        <input type="hidden" name="id" value="<?=$package->getId()?>">
        <label for="message" class="sr-only">Message</label>
        <textarea name="message" class="form-control" rows="3"></textarea>
      <div class="col-xs-7">
        <h3>
          <a href="<?=url("/package?id={$package->getId()}")?>">
            <?=block('platform_icon')?>
            <?=htmlspecialchars($package->getTitle())?>
          </a>
        </h3>
        <p>
          <?=nl2br(htmlspecialchars($package->getDescription()))?>
        </p>
      </div>
        <div class="controls text-right">
          <button name="submit" class="btn btn-primary"><i class="fa fa-envelope"></i> Request</button>
        </div>
      </form>
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
