<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add New Workflow</title>
<link rel="stylesheet" href="{{ asset('css/add-workflow.css') }}">
</head>
<body>

<form class="modal-card" method="POST" action="{{ route('workflow.store') }}">
@csrf

  <div class="modal-header">
    <h1>Add New Workflow</h1>
  </div>

  <div class="modal-body">

    @if ($errors->any())
      <div class="form-errors">
        <ul>
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <label class="field-label">Workflow Name <span class="required">*</span></label>
    <input type="text" class="field-input" name="name" value="{{ old('name') }}" placeholder="e.g. Welcome Series" required>

    <div class="field-row">
      <div class="field-col">
        <label class="field-label">Trigger <span class="required">*</span></label>
        <select class="field-input select-input" name="trigger" id="triggerSelect" required>
          <option value="" disabled {{ old('trigger') ? '' : 'selected' }}>Select Trigger</option>
          @foreach (['New Lead', 'Clicked Link', 'No Activity (7 days)', 'Purchase Completed', 'Form Submitted'] as $option)
            <option value="{{ $option }}" @selected(old('trigger') === $option)>{{ $option }}</option>
          @endforeach
        </select>
      </div>
      <div class="field-col">
        <label class="field-label">Status</label>
        <input type="hidden" name="status" id="statusInput" value="{{ old('status', 'active') }}">
        <div class="status-toggle-wrap">
          <button type="button" class="status-toggle {{ old('status', 'active') === 'active' ? 'active' : '' }}" data-status="active" id="activeBtn">Active</button>
          <button type="button" class="status-toggle {{ old('status') === 'paused' ? 'active' : '' }}" data-status="paused" id="pausedBtn">Paused</button>
        </div>
      </div>
    </div>

    <label class="field-label">Action <span class="required">*</span></label>
    <select class="field-input select-input" name="action" required>
      <option value="" disabled {{ old('action') ? '' : 'selected' }}>Select Action</option>
      @foreach (['Send Email', 'Send SMS', 'Wait / Delay', 'Add Tag', 'Notify Sales Team'] as $option)
        <option value="{{ $option }}" @selected(old('action') === $option)>{{ $option }}</option>
      @endforeach
    </select>

    <label class="field-label">Enrollment Audience</label>
    <select class="field-input select-input" name="audience">
      <option value="" disabled {{ old('audience') ? '' : 'selected' }}>Select Audience</option>
      @foreach (['All Leads', 'New Leads Only', 'Qualified Leads', 'Past Customers'] as $option)
        <option value="{{ $option }}" @selected(old('audience') === $option)>{{ $option }}</option>
      @endforeach
    </select>

    <label class="field-label">Description</label>
    <textarea class="field-input textarea-input" name="description" placeholder="Enter workflow description (optional)">{{ old('description') }}</textarea>

  </div>

  <div class="modal-footer">
    <button type="button" class="btn-cancel" id="cancelBtn">Cancel</button>
    <button type="submit" class="btn-next" id="saveBtn">Save Workflow</button>
  </div>

</form>

<script>
  const activeBtn = document.getElementById('activeBtn');
  const pausedBtn = document.getElementById('pausedBtn');
  const statusInput = document.getElementById('statusInput');
  const cancelBtn = document.getElementById('cancelBtn');

  [activeBtn, pausedBtn].forEach(btn => {
    btn.addEventListener('click', () => {
      activeBtn.classList.remove('active');
      pausedBtn.classList.remove('active');
      btn.classList.add('active');
      statusInput.value = btn.dataset.status;
    });
  });

  cancelBtn.addEventListener('click', () => {
    window.location.href = "{{ route('mcm.index') }}";
  });
</script>

</body>
</html>
