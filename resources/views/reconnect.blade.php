@extends('layouts.app')

@section('content')
 <!-- Begin Page Content -->
 <div class="container-fluid">

<!-- DataTales Example -->
<div class="card shadow mb-4">
  <div class="card-header py-3">
    <h6 class="m-0 font-weight-bold text-primary float-left">Reconnect Questions</h6>
    <a class="m-0 font-weight-bold text-primary float-right" href="/add_reconnect_qs">Add More</a>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
        <thead>
          <tr>
            <th>Questions</th>
          </tr>
        </thead>
        <tfoot>
          <tr>
          <th>Questions</th>
          </tr>
        </tfoot>
        <tbody>
        @foreach($data as $key => $user)
        <tr>
            <td>{{ucfirst($user->questions)}}</td>
        </tr>
        @endforeach  
        </tbody>
      </table>
    </div>
    <?php echo $data->render(); ?>
  </div>
</div>
</div>
@endsection