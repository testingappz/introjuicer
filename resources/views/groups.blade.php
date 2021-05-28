@extends('layouts.app')

@section('content')
 <!-- Begin Page Content -->
 <div class="container-fluid">

<!-- DataTales Example -->
<div class="card shadow mb-4">
  <div class="card-header py-3">
    <h6 class="m-0 font-weight-bold text-primary float-left">Groups</h6>
    <!-- <a class="m-0 font-weight-bold text-primary float-right" href="/add_category">Add More</a> -->
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
        <thead>
          <tr>
            <th>ID</th>
            <th>Heading</th>
            <th>Tagline</th>
            <th>Description</th>
			<th>Actions</h2>
          </tr>
        </thead>
        <tfoot>
          <tr>
          <th>ID</th>
          <th>Heading</th>
          <th>Tagline</th>
          <th>Description</th>
		  <th>Actions</h2>
          </tr>
        </tfoot>
        <tbody>
        @foreach($data as $key => $user)
        <tr>
            <td>{{ $data->firstItem() + $key }}</td>
            <td>{{ucfirst($user->heading)}}</td>
            <td>{{ucfirst($user->tagline)}}</td>
            <td>{{ucfirst($user->description)}}</td>
			<td><a onclick="return confirm('Are you sure to delete this group ?\nThis action cannot be undone.')" href="{{ url('groups').'/'.$user->id }}">Delete</a></td>
        </tr>
        @endforeach  
        </tbody>
      </table>
    </div>
    <?php echo $data->links(); ?>
  </div>
</div>
</div>
@endsection