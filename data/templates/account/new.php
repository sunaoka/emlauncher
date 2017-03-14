
<div class="col-sm-6 col-sm-offset-3">
  <div class="panel panel-primary">
    <div class="panel-heading">
      <h2 class="panel-title">New account</h2>
    </div>

    <div class="panel-body">
      <div id="alert_no_email" class="alert alert-danger hidden">
        EMailが入力されていません
      </div>
      <div id="alert_missmatch_email" class="alert alert-danger hidden">
        EMailが一致しません
      </div>
      <div id="alert_missmatch_password" class="alert alert-danger hidden">
        パスワードが一致しません
      </div>

      <form class="form-horizontal" method="post" action="<?=url('/account/create')?>">
        <input type="hidden" name="key" value="<?=$key?>">

        <div class="form-group">
          <label class="control-label col-sm-3" for="email">email</label>
          <div class="col-sm-9">
            <input class="form-control" type="text" id="email" name="email">
          </div>
        </div>

        <div class="form-group">
          <label class="control-label col-sm-3" for="confirm_email">confirm_email</label>
          <div class="col-sm-9">
            <input class="form-control" type="text" id="confirm_email" name="confirm_email">
          </div>
        </div>

        <div class="form-group">
          <label class="control-label col-sm-3" for="password">password</label>
          <div class="col-sm-9">
            <input class="form-control" type="password" id="password" name="password">
          </div>
        </div>

        <div class="form-group">
          <label class="control-label col-sm-3" for="confirm_password">confirm_password</label>
          <div class="col-sm-9">
            <input class="form-control" type="password" id="confirm_password" name="confirm_password">
          </div>
        </div>

        <div class="form-group">
          <label class="control-label col-sm-3" for="as_administrator">as_administrator</label>
          <div class="col-sm-9">
            <input class="form-control" type="checkbox" id="as_admin" name="as_admin" value="1">
          </div>
        </div>

        <div class="col-sm-9 col-sm-offset-3">
          <input type="submit" class="btn btn-primary" value="new user">
        </div>
      </form>
    </div>
  </div>
</div>
<script type="text/javascript">
$('form').on('submit',function(){
  var email1 = $('input[name="email"]',this).val();
  var email2 = $('input[name="confirm_email"]',this).val();
  var pass1 = $('input[name="password"]',this).val();
  var pass2 = $('input[name="confirm_password"]',this).val();
  $('.alert').addClass('hidden');
  if ( email1=='' || email2=='' ) {
    $('#alert_no_email').removeClass('hidden');
    return false;
  } 
  if ( email1 != email2 ) {
    $('#alert_missmatch_email').removeClass('hidden');
    return false;
  }
  if(pass1!=pass2){
    $('#alert_missmatch_password').removeClass('hidden');
    return false;
  }
  return true;
});
</script>
