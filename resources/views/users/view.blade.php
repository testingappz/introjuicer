@extends('layouts.app')
@section('content')
 <!-- Begin Page Content -->
<div class="container-fluid">
<!-- DataTales Example -->
<div class="card shadow mb-4">
  <div class="card-header py-3">
    <h6 class="m-0 font-weight-bold text-primary">User Information</h6>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
        @foreach($data->getOriginal() as $key => $user)
            <tr>
                <th>{{ucfirst($key)}}</th>
                <td>{{ucfirst($user)}}</td>
            </tr>
        @endforeach
      </table>
    </div>
  </div>
</div>
</div>
@endsection