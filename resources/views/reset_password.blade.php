@extends('layouts.mail')

@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <?php  if (Session::has('error_message')){?>
                        <span class="help-block error forhide" style="color:red;"><?php echo Session::get('error_message');?></span>
                <?php  }?>

                <div class="card-header">{{ __('Reset Password') }} for {{ $data->email }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('update_password') }}">
                        @csrf
                        <input type="hidden" name="token" value="{{ $data->mail_token }}">
                        <input type="hidden" name="hidden_id" value="{{ $data->id }}">
                        <input type="hidden" name="hidden_email" value="{{ $data->email }}">
                        
                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>

                                @if ($errors->has('password'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-right">{{ __('Confirm Password') }}</label>

                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Reset Password') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection