<tr data-template-id="{{ $tpl->id }}">
  <td><input type="text" class="form-control form-control-sm pr-tpl-field" data-field="activity_description" value="{{ $tpl->activity_description }}" placeholder="Activity description"></td>
  <td><input type="text" class="form-control form-control-sm pr-tpl-field" data-field="default_planned_activities" value="{{ $tpl->default_planned_activities }}" placeholder="One per line"></td>
  <td class="text-center"><input type="checkbox" class="pr-tpl-field" data-field="is_active" {{ $tpl->is_active ? 'checked' : '' }}></td>
  <td class="text-center"><button type="button" class="btn btn-sm btn-danger pr-tpl-delete" title="Delete"><i class="fas fa-trash"></i></button></td>
</tr>
