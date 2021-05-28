@extends('layouts.app')

@section('content')
 <!-- Begin Page Content -->
 <div class="container-fluid">

<!-- DataTales Example -->
<div class="card shadow mb-4">
  <div class="card-header py-3">
    <h6 class="m-0 font-weight-bold text-primary float-left">Update sub categorie</h6>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
        <tbody>
		<form action="{{ url('/adding_qs')}}" method="POST"  enctype="multipart/form-data">
		@csrf

			<div class="form-group row">
				<label for="staticEmail" class="col-sm-2 col-form-label">Email</label>
				<div class="col-sm-10">
				<input type="text" class="form-control" aria-describedby="emailHelp" placeholder="Add sub category" name="qs" value="{{ $name->sub_cat_name }}" required>
				</div>
			</div>

			</div>
				<div class="form-group">
				<label class="col-sm-2 col-form-label">Icon</label>
				<img src="{{ url('/') .'/uploads/category_icons/'.$name->icon}}" style="height:60px;">
				<input type="hidden" name="update_subcategory">
        		<input type="hidden" name="subcategory_id" value="{{ $id }}">
				</div>
			</div>
			<div class="form-group">
				<label>Update Icon</label>
				<div class="col-sm-10">
				<input type="file" class="form-control-file" name="icon" accept="image/x-png,image/gif,image/jpeg">
				</div>
			</div>
			<button type="submit" class="btn btn-primary" name="btnsubmit">Submit</button>
		</form>
        </tbody>
      </table>
    </div>
  </div>
</div>
</div>
@endsection
