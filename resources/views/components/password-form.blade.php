<form method="POST" action="{{ route('password.change') }}" class="card">
    @csrf @method('PUT')
    <h2 class="card-head card-title">Change password</h2>
    <div class="space-y-4 p-5">
        <x-field name="current_password" label="Current password" type="password" required />
        <x-field name="password" label="New password (at least 8 characters)" type="password" required />
        <x-field name="password_confirmation" label="Confirm new password" type="password" required />
        <button class="btn btn-primary">Update password</button>
    </div>
</form>
