@extends('layout.app')
@section('content')
<div class="content-wrapper">
<div class="ms2-wrap">

    <div class="ms2-page-title">Edit Admin User</div>
    <div class="ms2-page-sub">Update account information for this administrator.</div>

    <div class="ms2-card">
        <form method="POST" action="{{ url('admin/edit/' . $getRecord->id) }}" enctype="multipart/form-data">
            @csrf
            <div class="ms2-body">

                <div class="ms2-row">
                    <div class="ms2-col">
                        <label class="ms2-label">Full Name <span class="req">*</span></label>
                        <div class="ms2-input-group">
                            <i class="fas fa-user"></i>
                            <input type="text" name="name" class="ms2-input"
                                   value="{{ old('name', $getRecord->name) }}" required placeholder="Full name">
                        </div>
                    </div>
                    <div class="ms2-col">
                        <label class="ms2-label">Email Address <span class="req">*</span></label>
                        <div class="ms2-input-group">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" class="ms2-input"
                                   value="{{ old('email', $getRecord->email) }}" required placeholder="admin@email.com">
                        </div>
                        @if($errors->has('email'))
                            <div style="color:#a02626;font-size:.8rem;margin-top:4px;">{{ $errors->first('email') }}</div>
                        @endif
                    </div>
                </div>

                <div class="ms2-row">
                    <div class="ms2-col">
                        <label class="ms2-label">Password <span style="font-weight:400;opacity:.6;">(leave blank to keep current)</span></label>
                        <div class="ms2-input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" class="ms2-input" placeholder="New password">
                        </div>
                    </div>
                    <div class="ms2-col">
                        <label class="ms2-label">Role</label>
                        <select name="role_id" class="ms2-input">
                            <option value="" {{ old('role_id', $getRecord->role_id) == null ? 'selected' : '' }}>Super Admin (full access)</option>
                            @foreach($roles as $r)
                                <option value="{{ $r->id }}" {{ old('role_id', $getRecord->role_id) == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="ms2-row">
                    <div class="ms2-col">
                        <label class="ms2-label">Profile Photo</label>
                        @if(!empty($getRecord->profile_image))
                            <div style="margin-bottom:8px;">
                                <img src="{{ asset('storage/' . $getRecord->profile_image) }}"
                                     style="width:56px;height:56px;object-fit:cover;border-radius:50%;border:2px solid #a02626;">
                            </div>
                        @endif
                        <label class="ms2-file-row" id="photoLabel">
                            <i class="fas fa-camera"></i>
                            <span id="photoName">{{ $getRecord->profile_image ? 'Replace photo…' : 'Choose photo…' }}</span>
                            <input type="file" name="profile_image" accept="image/*" style="display:none"
                                   onchange="document.getElementById('photoName').textContent = this.files[0]?.name || 'Choose photo…'">
                        </label>
                    </div>
                </div>

            </div>
            <div class="ms2-footer">
                <a href="{{ url('admin/list') }}" class="ms2-btn-back">Cancel</a>
                <button type="submit" class="ms2-btn-submit">
                    <i class="fas fa-save mr-1"></i> Save Changes
                </button>
            </div>
        </form>
    </div>

</div>
</div>
@endsection
