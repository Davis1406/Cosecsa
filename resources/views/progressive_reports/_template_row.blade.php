<tr data-template-id="{{ $tpl->id }}">
  <td><textarea class="form-control form-control-sm pr-tpl-field pr-tpl-autogrow" data-field="activity_description" rows="1" placeholder="Activity description">{{ $tpl->activity_description }}</textarea></td>
  <td><textarea class="form-control form-control-sm pr-tpl-field pr-tpl-autogrow" data-field="default_planned_activities" rows="1" placeholder="One per line">{{ $tpl->default_planned_activities }}</textarea></td>
  <td class="text-center"><input type="checkbox" class="pr-tpl-field" data-field="is_active" {{ $tpl->is_active ? 'checked' : '' }}></td>
  <td class="text-center"><button type="button" class="btn btn-sm btn-danger pr-tpl-delete" title="Delete"><i class="fas fa-trash"></i></button></td>
</tr>
