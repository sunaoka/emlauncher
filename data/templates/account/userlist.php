<div class="page-header">
  <h2 class="headding">User List</h2>
</div>

<div>
  <table id="user-list" class="table table-hover">

    <tr class="hidden-xs">
      <th>Email</th>
      <th>As admin</th>
      <th>Last login</th>
      <th>iOS UDID</th>
      <th>delete</th>
    </tr>

<?php foreach($userlist as $user):?>
    <tr>
      <td>
        <?print $user->getMail()?>
      </td>
      <td>
        <?print ( $user->isAdmin() ? "Yes" : "No" )?>
      </td>
      <td>not implemented yet</td>
      <td>not implemented yet</td>
      <td class="text-center hidden-xs">
        <button class="btn btn-danger delete" data-email="<?=$user->getMail()?>"><i class="fa fa-trash-o"></i></button>
      </td>
    </tr>
<?php endforeach ?>
  </table>

</div>

<script type="text/javascript">

$('.notification-toggle button').on('click',function(event){
  var id = $(this).parent().attr('data-email');
  var value = $(this).attr('value');
  $.ajax({
    url: "<?=url('/api/notification_setting?id=')?>"+id+"&value="+value,
    type: "POST",
    success: function(data){
      if(data.notify){
         $('[data-email="'+id+'"]>button[value="1"]').addClass('active');
         $('[data-email="'+id+'"]>button[value="0"]').removeClass('active');
      }
      else{
         $('[data-email="'+id+'"]>button[value="1"]').removeClass('active');
         $('[data-email="'+id+'"]>button[value="0"]').addClass('active');
      }
    }
  });
});

$('button.delete').on('click',function(event){
  if(confirm("このアカウントをユーザーリストから削除します.\n個々のパッケージのインストール履歴は削除されません.\n削除しますか?")){
    location.href = '<?=url('/account/delete?email=')?>' + $(this).attr('data-email');
  }
});

$('.app-list-item-info').on('click',function(event){
  $('a',this)[0].click();
});

</script>
