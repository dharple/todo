@extends('layouts.base')

@section('title', 'Account Editor')

@section('body')
@include('partials.page.head', ['title' => 'Account Editor'])

<form method="POST" action="{{ route('account') }}">
    @csrf

    <table>
        <tr>
            <td align=right>
                Full Name:
            </td>
            <td align=left>
                <input type="text" name="fullname" value="{{ $user->getFullname() }}" data-lpignore="true" />
            </td>
        </tr>

        <tr>
            <td align=right>
                Timezone:
            </td>
            <td align=left>
                <select name="timezone">
                    @foreach ($timezones as $timezone)
                        <option value="{{ $timezone }}" @if ($user->getTimezone() === $timezone) selected @endif>{{ $timezone }}</option>
                    @endforeach
                    <option value="Other" @if (!in_array($user->getTimezone(), $timezones)) selected @endif>Other</option>
                </select>
                <input type="text" name="timezone_other" value="@if (!in_array($user->getTimezone(), $timezones)){{ $user->getTimezone() }}@endif" data-lpignore="true" />
            </td>
        </tr>
    </table>

    <input type=submit name="submitButton" value="Update">

    <hr />

    Change Password<br /><br />

    <table>
        <tr><td align=right>Old Password:</td><td><input type="password" name="old_password" value="" /></td></tr>
        <tr><td align=right>New Password:</td><td><input type="password" name="new_password" value="" /></td></tr>
        <tr><td align=right>Confirm:</td><td><input type="password" name="confirm" value="" /></td></tr>
    </table>

    <input type=submit name="submitButton" value="Change Password" />

</form>
@endsection
