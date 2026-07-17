@extends('layout.app')

@section('content')
  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-12">
            <h1 style="font-size:1.4rem;">{{ $header_title }}</h1>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        @include('_message')
        <div class="card">
          <div class="card-body">
            @if($role && $role->is_system)
              <div class="alert alert-info">
                <i class="fas fa-lock mr-1"></i>Super Admin is a protected system role — it always has every permission and can't be edited or deleted.
              </div>
            @endif

            <form method="post" action="{{ $role ? url('admin/roles/edit/'.$role->id) : url('admin/roles/add') }}">
              @csrf
              <div class="form-row">
                <div class="form-group col-md-4">
                  <label>Role Name</label>
                  <input type="text" name="name" class="form-control" required
                         value="{{ old('name', $role->name ?? '') }}"
                         {{ ($role && $role->is_system) ? 'readonly' : '' }}>
                </div>
                <div class="form-group col-md-8">
                  <label>Description</label>
                  <input type="text" name="description" class="form-control"
                         value="{{ old('description', $role->description ?? '') }}"
                         {{ ($role && $role->is_system) ? 'readonly' : '' }}>
                </div>
              </div>

              <h5 class="mt-3">Permissions</h5>
              <p class="text-muted" style="font-size:.85rem;">
                Tick <strong>View</strong> to let this role see a module's pages, and <strong>Manage</strong> for
                the specific actions listed under it (manage always implies view).
              </p>

              <div class="table-responsive">
                <table class="table table-bordered table-sm" style="font-size:.85rem;">
                  <thead class="thead-light">
                    <tr>
                      <th style="width:16%;">Module</th>
                      <th style="width:34%;">View grants</th>
                      <th style="width:8%;" class="text-center">View</th>
                      <th style="width:34%;">Manage grants</th>
                      <th style="width:8%;" class="text-center">Manage</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($modules as $key => $module)
                      <tr>
                        <td class="font-weight-bold align-middle">{{ $module['label'] }}</td>
                        <td class="text-muted align-middle">{{ $module['view'] ?? '—' }}</td>
                        <td class="text-center align-middle">
                          <input type="checkbox" name="permissions[]" value="{{ $key }}.view"
                                 {{ ($role && $role->is_system) || in_array($key.'.view', $checkedKeys) ? 'checked' : '' }}
                                 {{ ($role && $role->is_system) ? 'disabled' : '' }}>
                        </td>
                        <td class="text-muted align-middle">
                          @if($module['manage'] ?? null)
                            {{ $module['manage'] }}
                          @else
                            <span class="font-italic">No management actions for this module</span>
                          @endif
                        </td>
                        <td class="text-center align-middle">
                          @if($module['manage'] ?? null)
                            <input type="checkbox" name="permissions[]" value="{{ $key }}.manage"
                                   {{ ($role && $role->is_system) || in_array($key.'.manage', $checkedKeys) ? 'checked' : '' }}
                                   {{ ($role && $role->is_system) ? 'disabled' : '' }}>
                          @else
                            —
                          @endif
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>

              @if(!($role && $role->is_system))
                <button type="submit" class="btn btn-primary mt-2">{{ $role ? 'Update Role' : 'Create Role' }}</button>
              @endif
              <a href="{{ url('admin/roles/list') }}" class="btn btn-secondary mt-2">Back to List</a>
            </form>
          </div>
        </div>
      </div>
    </section>
  </div>
@endsection
