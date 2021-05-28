@extends('layouts.app')

@section('content')
 <!-- Begin Page Content -->
 <div class="container-fluid">

<!-- DataTales Example -->
<div class="card shadow mb-4">
  <div class="card-header py-3">
    <h6 class="m-0 font-weight-bold text-primary float-left">Categories</h6>
    <a class="m-0 font-weight-bold text-primary float-right" href="/add_category">Add More</a>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
        <thead>
          <tr>
            <th>Categories</th>
			<th colspan="2">Actions</h2>
          </tr>
        </thead>
        <tfoot>
          <tr>
          <th>Categories</th>
		  <th colspan="2">Actions</h2>
          </tr>
        </tfoot>
        <tbody>
        @foreach($data as $key => $user)
        <tr>
            <td>{{ucfirst($user->cat_name)}}</td>
			<td><a href="/update_cat/{{ $user->id }}">Edit</a></td>
			<td><a href="/sub_categories/{{base64_encode($user->id)}}">View sub Categories</a></td>
        </tr>
        @endforeach  
        </tbody>
      </table>
    </div>
    <?php //echo $data->render(); ?>
  </div>
</div>
</div>
@endsection