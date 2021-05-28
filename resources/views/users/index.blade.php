@extends('layouts.app')

@section('content')
 <!-- Begin Page Content -->
 <div class="container-fluid">

<!-- DataTales Example -->
<div class="card shadow mb-4">
  <div class="card-header py-3">
    <h6 class="m-0 font-weight-bold text-primary">Users</h6>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Visiblity</th>
            <th>Action</th>
          </tr>
        </thead>
        <tfoot>
          <tr>
          <th>Name</th>
            <th>Email</th>
            <th>Visiblity</th>
            <th>Action</th>
          </tr>
        </tfoot>
        <tbody>
        @foreach($data as $key => $user)
        <tr>
            <td>{{ucfirst($user->name)}}</td>
            <td>{{$user->email}}</td>
            <td class="visibl{{$user->id}}">{{ucfirst($user->visibility)}}</td>
            <td>    <span>
                        <a href="/get_user_info/{{base64_encode($user->id)}}">Edit </a>
                    </span>
                    ||
                    <span>
                    <?php $visiblity = $user->visibility == 'hidden' ? 'visible' : 'hidden'; ?>
                        <a href="javascript:void(0)" data-id="{{$user->id}}" data-visibility="{{$user->visibility}}" onclick="updatestatus(this)" >  Update to {{$visiblity}}
                        </a>
                    </span>    
            </td>
            
          </tr>
            
        @endforeach
            
        
          
        </tbody>
      </table>
    </div>
  </div>
</div>

</div>




<script type="text/javascript">
    function updatestatus(obj){
        var id = $(obj).data('id');
        var visibility = ($(obj).data('visibility') == 'hidden') ? 'visible' : 'hidden';

        $.ajax({
            url:'update_user_status',
            data:{id:id, visibility:visibility},
            success:function(res){
                var cur_visibility = (visibility == 'hidden') ? 'visible' : 'hidden';
                $('.visibl'+id).text((visibility.capitalize()));
                $(obj).text('Update to ' + cur_visibility);
                $(obj).data('visibility', visibility);
            },error:function(err){
                alert('Something went wrong!');
            }
        });
    }
    String.prototype.capitalize = function() {
        return this.charAt(0).toUpperCase() + this.slice(1);
    }

</script>
@endsection