@php($student = $portal === 'student')
<!doctype html>
<html lang="en">
<head>
    @include('layouts.head')
    <title>Sign in · {{ setting('school_name') }}</title>
</head>
<body>
<div class="grid min-h-screen lg:grid-cols-2">
    <div class="relative hidden lg:block">
        <img src="{{ asset('images/school/classroom.jpg') }}" alt="" class="absolute inset-0 size-full object-cover">
        <div class="absolute inset-0" style="background: rgb(23 24 63 / .82)"></div>
        <div class="relative flex h-full flex-col justify-between p-12 text-white">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <img src="{{ asset('logo.svg') }}" alt="" class="size-12">
                <span class="text-xl font-extrabold">{{ setting('school_name') }}</span>
            </a>
            <div>
                <p class="max-w-md text-4xl font-extrabold leading-tight">{{ setting('tagline') }}</p>
                <p class="mt-3 max-w-md text-indigo-100">Attendance, results, fees and notices, all in one place.</p>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-center p-6">
        <div class="w-full max-w-md" x-data="{ show: false }">
            <a href="{{ url('/') }}" class="mb-8 flex items-center gap-3 lg:hidden">
                <img src="{{ asset('logo.svg') }}" alt="" class="size-11">
                <span class="text-lg font-extrabold">{{ setting('school_name') }}</span>
            </a>
            <h1 class="text-3xl font-extrabold tracking-tight">Sign in</h1>
            <p class="mt-1.5 text-ink-2">{{ $student ? 'Students and guardians' : 'Teachers and school administrators' }}</p>

            <form method="POST" action="{{ route('login') }}" class="mt-7 space-y-4">
                @csrf
                <input type="hidden" name="portal" value="{{ $portal }}">
                <div>
                    <label class="field-label" for="login">{{ $student ? 'Student ID' : 'User ID' }}</label>
                    <input id="login" name="login" value="{{ old('login') }}" class="input" autocomplete="username" required autofocus>
                    @error('login') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="field-label" for="password">Password</label>
                    <div class="relative">
                        <input id="password" name="password" :type="show ? 'text' : 'password'" class="input pr-16" autocomplete="current-password" required>
                        <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-[13px] font-semibold text-primary-ink" x-text="show ? 'Hide' : 'Show'"></button>
                    </div>
                </div>
                <label class="flex items-center gap-2 text-sm text-ink-2"><input type="checkbox" name="remember" value="1" class="size-4 accent-indigo-600"> Keep me signed in</label>
                <button class="btn btn-primary w-full py-3">Sign in</button>
            </form>

            <div class="mt-7 space-y-2 border-t border-line pt-5 text-sm text-ink-2">
                @if ($student)
                    <p>Teacher or staff? <a class="font-semibold text-primary-ink" href="{{ route('staff.login') }}">Staff sign in</a></p>
                @else
                    <p>Student or guardian? <a class="font-semibold text-primary-ink" href="{{ route('student.login') }}">Student sign in</a></p>
                @endif
                <p>Forgot your password? Please contact the school office.</p>
                <p><a class="font-semibold text-primary-ink" href="{{ url('/') }}">Back to website</a></p>
            </div>
        </div>
    </div>
</div>
</body>
</html>
