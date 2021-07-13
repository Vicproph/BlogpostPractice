@extends('layouts.app')
@section('content')
    <div style="background-color: #ddd;" class="container mt-5  card card-body d-flex flex-column align-items-center ">
        <h2 class="my-4">Login Form</h2>
        @if ($errors->any())
            <div class="card alert alert-danger ">
                @foreach ($errors->all() as $error)
                    {{$error}}
                @endforeach
            </div>
        @endif
        <form class="my-4" action="{{route('auth.signin')}}" id="form" method="POST">
            @csrf
            <div class="form-group my-4">
                <label for="">Identifier</label>
                <input type="email" name="email" id="">
            </div>
            <div class="form-group my-4">
                <label for="">Password</label>
                <input type="password" name="password" id="">
            </div>
            <button type="submit" class="g- btn btn-primary" 
            data-sitekey="{{env("SITE_KEY")}}">Submit</button>
            <div class="g-recaptcha" data-sitekey="your_site_key"></div>

        </form>
        <script>
            function onSubmit(token) {
              document.getElementById("form").submit();
            }
          </script>
    </div>
@endsection