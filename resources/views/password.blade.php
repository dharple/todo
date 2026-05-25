@extends('layouts.base')

@section('title', 'Change Password')

@section('body')
@include('partials.page.head', ['title' => 'Change Password'])

<form method="POST" action="{{ route('password') }}">
    @csrf

    <div class="d-flex flex-column mb-3">
        <div>
            <label for="oldPasswordInput" class="form-label">Old Password:</label>
            <input type="password" class="form-control" id="oldPasswordInput" name="old_password">
        </div>

        <div>
            <label for="newPasswordInput" class="form-label">New Password:</label>
            <input type="password" class="form-control" id="newPasswordInput" name="new_password">
        </div>

        <div>
            <label for="confirmInput" class="form-label">Confirm:</label>
            <input type="password" class="form-control" id="confirmInput" name="confirm">
        </div>

        <div>
            <button type="submit" class="btn btn-primary mb-3">Change Pasword</button>
        </div>
    </div>
</form>
@endsection
